<?php

class ParabdException extends RuntimeException
{
    public $errorCode;
    public $fields;

    public function __construct($errorCode, $message, $fields = array())
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->fields = $fields;
    }
}

/**
 * Application service for the isolated Para-BD domain.
 *
 * It intentionally uses the existing Db_* helpers: the project has no service
 * container and the legacy MyISAM references cannot participate in foreign keys.
 */
class ParabdService
{
    const GLOBAL_SCHEMES = 'EAN13,UPCA,ISBN10,ISBN13';

    private function connection()
    {
        return Bdo_Cfg::getVar('connexion');
    }

    private function escape($value)
    {
        return Db_Escape_String((string) $value, $this->connection());
    }

    private function query($sql)
    {
        $result = Db_query($sql, $this->connection());
        if ($result === false) {
            throw new RuntimeException($this->connection()->error ?: 'Erreur SQL Para-BD');
        }
        return $result;
    }

    private function rows($result)
    {
        $rows = array();
        while ($row = Db_fetch_array($result)) $rows[] = $row;
        Db_free_result($result);
        return $rows;
    }

    private function one($sql)
    {
        $result = $this->query($sql);
        $row = Db_fetch_array($result);
        Db_free_result($result);
        return $row ?: null;
    }

    private function mediaPathSql($mediaAlias = 'm', $itemAlias = 'i')
    {
        if (Bdo_Cfg::getVar('explicit')) return $mediaAlias . '.FILE_PATH';
        return "IF($itemAlias.IS_EXPLICIT=1,CONCAT('?source=',$mediaAlias.FILE_PATH),$mediaAlias.FILE_PATH)";
    }

    public static function normalizeText($value)
    {
        $value = trim(mb_strtolower((string) $value, 'UTF-8'));
        if (class_exists('Normalizer')) {
            $value = Normalizer::normalize($value, Normalizer::FORM_D);
        }
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($ascii !== false) $value = $ascii;
        $value = preg_replace('/[^a-z0-9]+/i', ' ', $value);
        return trim(preg_replace('/\s+/', ' ', $value));
    }

    public static function normalizeIdentifier($scheme, $value)
    {
        $value = strtoupper(trim((string) $value));
        if (in_array($scheme, array('EAN13', 'UPCA', 'ISBN10', 'ISBN13'), true)) {
            return preg_replace('/[^0-9X]/', '', $value);
        }
        return preg_replace('/[^A-Z0-9]+/', '', $value);
    }

    public static function isValidIdentifier($scheme, $value)
    {
        $value = self::normalizeIdentifier($scheme, $value);
        if ($scheme === 'EAN13' || $scheme === 'ISBN13') {
            if (!preg_match('/^\d{13}$/', $value)) return false;
            $sum = 0;
            for ($i = 0; $i < 12; $i++) $sum += intval($value[$i]) * (($i % 2) ? 3 : 1);
            return ((10 - ($sum % 10)) % 10) === intval($value[12]);
        }
        if ($scheme === 'UPCA') {
            if (!preg_match('/^\d{12}$/', $value)) return false;
            $sum = 0;
            for ($i = 0; $i < 11; $i++) $sum += intval($value[$i]) * (($i % 2) ? 1 : 3);
            return ((10 - ($sum % 10)) % 10) === intval($value[11]);
        }
        if ($scheme === 'ISBN10') {
            if (!preg_match('/^\d{9}[\dX]$/', $value)) return false;
            $sum = 0;
            for ($i = 0; $i < 10; $i++) $sum += (10 - $i) * (($value[$i] === 'X') ? 10 : intval($value[$i]));
            return ($sum % 11) === 0;
        }
        return $value !== '';
    }

    public static function parsePartialDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') return array('date' => null, 'precision' => 'UNKNOWN');
        if (preg_match('/^(\d{4})$/', $value, $m) && checkdate(1, 1, intval($m[1]))) {
            return array('date' => $m[1] . '-01-01', 'precision' => 'YEAR');
        }
        if (preg_match('/^(\d{4})-(\d{2})$/', $value, $m) && checkdate(intval($m[2]), 1, intval($m[1]))) {
            return array('date' => $value . '-01', 'precision' => 'MONTH');
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) && checkdate(intval($m[2]), intval($m[3]), intval($m[1]))) {
            return array('date' => $value, 'precision' => 'DAY');
        }
        throw new ParabdException('VALIDATION_ERROR', 'La date est invalide.', array('release_date' => 'Format attendu : AAAA, AAAA-MM ou AAAA-MM-JJ.'));
    }

    public static function displayPartialDate($date, $precision)
    {
        if (!$date || $precision === 'UNKNOWN') return 'Date inconnue';
        if ($precision === 'YEAR') return substr($date, 0, 4);
        if ($precision === 'MONTH') return substr($date, 5, 2) . '/' . substr($date, 0, 4);
        return date('d/m/Y', strtotime($date));
    }

    public static function titleSimilarity($left, $right)
    {
        $left = self::normalizeText($left);
        $right = self::normalizeText($right);
        if ($left === '' || $right === '') return 0.0;
        similar_text($left, $right, $percent);
        return round($percent, 2);
    }

    public static function duplicateLevel($candidate, $input)
    {
        if (!empty($input['exact_identifier'])) return array('level' => 'CERTAIN', 'score' => 100, 'reasons' => array('Identifiant exact'));
        $similarity = self::titleSimilarity($candidate['TITLE'], $input['TITLE']);
        $sameRelation = !empty($input['common_relation']);
        $sameType = intval($candidate['TYPE_ID']) === intval($input['TYPE_ID']);
        $sameManufacturer = self::normalizeText($candidate['MANUFACTURER']) !== ''
            && self::normalizeText($candidate['MANUFACTURER']) === self::normalizeText(isset($input['MANUFACTURER']) ? $input['MANUFACTURER'] : '');
        $year = !empty($candidate['RELEASE_DATE']) && !empty($input['RELEASE_DATE'])
            && substr($candidate['RELEASE_DATE'], 0, 4) === substr($input['RELEASE_DATE'], 0, 4);
        $dimensions = self::dimensionsMatch($candidate, $input);
        if ($sameType && $similarity >= 85 && $sameManufacturer && ($year || $dimensions || $sameRelation)) {
            return array('level' => 'STRONG', 'score' => $similarity, 'reasons' => array('Titre très proche', 'Même fabricant', $year ? 'Même année' : ($dimensions ? 'Dimensions proches' : 'Rattachement commun')));
        }
        if ($similarity >= 70 || $sameRelation) {
            return array('level' => 'POSSIBLE', 'score' => $similarity, 'reasons' => array($sameRelation ? 'Rattachement commun' : 'Titre proche'));
        }
        return null;
    }

    private static function dimensionsMatch($left, $right)
    {
        $checked = 0;
        foreach (array('WIDTH_MM', 'HEIGHT_MM', 'DEPTH_MM') as $field) {
            if (!empty($left[$field]) && !empty($right[$field])) {
                $checked++;
                if (abs(floatval($left[$field]) - floatval($right[$field])) / max(floatval($left[$field]), 1) > 0.05) return false;
            }
        }
        return $checked > 0;
    }

    public function getTypes()
    {
        return $this->rows($this->query("SELECT * FROM parabd_type WHERE IS_ACTIVE=1 ORDER BY PARENT_ID IS NOT NULL, SORT_ORDER, LABEL"));
    }

    public function getCatalogue($search = '', $filterType = '', $filterId = 0, $filterValue = '', $limit = 60)
    {
        $where = "i.STATUS='ACTIVE'";
        $filterType = strtolower(trim((string) $filterType));
        $filterId = intval($filterId);
        $filterValue = trim((string) $filterValue);
        if ($filterType === 'author' && $filterId) {
            $where .= " AND EXISTS (SELECT 1 FROM parabd_item_author ia WHERE ia.ITEM_ID=i.ID_ITEM AND ia.AUTHOR_ID=$filterId)";
        } elseif ($filterType === 'series' && $filterId) {
            $where .= " AND EXISTS (SELECT 1 FROM parabd_item_series isa WHERE isa.ITEM_ID=i.ID_ITEM AND isa.SERIES_ID=$filterId)";
        } elseif ($filterType === 'tome' && $filterId) {
            $where .= " AND EXISTS (SELECT 1 FROM parabd_item_tome it WHERE it.ITEM_ID=i.ID_ITEM AND it.TOME_ID=$filterId)";
        } elseif ($filterType === 'manufacturer' && $filterValue !== '') {
            $where .= " AND i.MANUFACTURER_NORMALIZED='" . $this->escape(self::normalizeText($filterValue)) . "'";
        } elseif ($filterType === 'publisher' && $filterValue !== '') {
            $where .= " AND i.PUBLISHER='" . $this->escape($filterValue) . "'";
        } elseif (trim($search) !== '') {
            $raw = $this->escape(trim($search));
            $normalized = $this->escape(self::normalizeText($search));
            $where .= " AND (i.TITLE_NORMALIZED LIKE '%$normalized%' OR i.MANUFACTURER_NORMALIZED LIKE '%$normalized%'
                OR i.PUBLISHER LIKE '%$raw%'
                OR EXISTS (SELECT 1 FROM parabd_item_author ia JOIN bd_auteur a ON a.ID_AUTEUR=ia.AUTHOR_ID
                    WHERE ia.ITEM_ID=i.ID_ITEM AND (a.PSEUDO LIKE '%$raw%' OR a.NOM LIKE '%$raw%' OR a.PRENOM LIKE '%$raw%'))
                OR EXISTS (SELECT 1 FROM parabd_item_series isa JOIN bd_serie s ON s.ID_SERIE=isa.SERIES_ID
                    WHERE isa.ITEM_ID=i.ID_ITEM AND s.NOM LIKE '%$raw%')
                OR EXISTS (SELECT 1 FROM parabd_item_tome it JOIN bd_tome bt ON bt.ID_TOME=it.TOME_ID
                    WHERE it.ITEM_ID=i.ID_ITEM AND bt.TITRE LIKE '%$raw%'))";
        }
        $mediaPath = $this->mediaPathSql('m', 'i');
        return $this->rows($this->query("SELECT i.*, t.LABEL TYPE_LABEL, st.LABEL SUBTYPE_LABEL, $mediaPath PRIMARY_IMAGE
            FROM parabd_item i JOIN parabd_type t ON t.ID_TYPE=i.TYPE_ID
            LEFT JOIN parabd_type st ON st.ID_TYPE=i.SUBTYPE_ID
            LEFT JOIN parabd_media m ON m.ITEM_ID=i.ID_ITEM AND m.IS_PRIMARY=1 AND m.IS_HIDDEN=0
            WHERE $where ORDER BY i.UPDATED_AT DESC LIMIT " . max(1, min(200, intval($limit)))));
    }

    public function getAdminCatalogue($search = '', $status = '', $limit = 200)
    {
        $where = '1=1';
        $search = trim((string) $search);
        $status = strtoupper(trim((string) $status));
        if ($search !== '') {
            if (ctype_digit($search)) $where .= ' AND (i.ID_ITEM=' . intval($search) . " OR i.TITLE LIKE '%" . $this->escape($search) . "%')";
            else $where .= " AND (i.TITLE_NORMALIZED LIKE '%" . $this->escape(self::normalizeText($search)) . "%' OR i.MANUFACTURER LIKE '%" . $this->escape($search) . "%' OR i.PUBLISHER LIKE '%" . $this->escape($search) . "%')";
        }
        if (in_array($status, array('ACTIVE','HIDDEN','MERGED'), true)) $where .= " AND i.STATUS='$status'";
        return $this->rows($this->query("SELECT i.*,t.LABEL TYPE_LABEL,st.LABEL SUBTYPE_LABEL,
            (SELECT COUNT(*) FROM users_parabd up WHERE up.ITEM_ID=i.ID_ITEM) COPY_COUNT,
            (SELECT COUNT(*) FROM parabd_revision r WHERE r.ITEM_ID=i.ID_ITEM) HISTORY_COUNT
            FROM parabd_item i JOIN parabd_type t ON t.ID_TYPE=i.TYPE_ID
            LEFT JOIN parabd_type st ON st.ID_TYPE=i.SUBTYPE_ID
            WHERE $where ORDER BY i.UPDATED_AT DESC LIMIT " . max(1, min(500, intval($limit)))));
    }

    public function autocompleteCatalogue($term, $limitPerCategory = 6)
    {
        $term = trim((string) $term);
        if (mb_strlen($term, 'UTF-8') < 2) return array();
        $like = $this->escape($term);
        $normalized = $this->escape(self::normalizeText($term));
        $limit = max(1, min(10, intval($limitPerCategory)));
        $result = array();

        $authors = $this->rows($this->query("SELECT ID_AUTEUR id, COALESCE(NULLIF(PSEUDO,''),TRIM(CONCAT_WS(' ',PRENOM,NOM))) label
            FROM bd_auteur WHERE PSEUDO LIKE '%$like%' OR NOM LIKE '%$like%' OR PRENOM LIKE '%$like%'
            ORDER BY (PSEUDO='$like') DESC, PSEUDO LIMIT $limit"));
        foreach ($authors as $row) $result[] = array('category' => 'Auteurs', 'type' => 'author', 'id' => intval($row['id']), 'label' => $row['label']);

        $series = $this->rows($this->query("SELECT ID_SERIE id, NOM label FROM bd_serie WHERE NOM LIKE '%$like%'
            ORDER BY (NOM='$like') DESC, NOM LIMIT $limit"));
        foreach ($series as $row) $result[] = array('category' => 'Séries', 'type' => 'series', 'id' => intval($row['id']), 'label' => $row['label']);

        $tomes = $this->rows($this->query("SELECT t.ID_TOME id, t.TITRE label, s.NOM series_label FROM bd_tome t
            LEFT JOIN bd_serie s ON s.ID_SERIE=t.ID_SERIE WHERE t.TITRE LIKE '%$like%'
            ORDER BY (t.TITRE='$like') DESC, t.TITRE LIMIT $limit"));
        foreach ($tomes as $row) {
            $label = $row['label'] . (!empty($row['series_label']) ? ' — ' . $row['series_label'] : '');
            $result[] = array('category' => 'Albums', 'type' => 'tome', 'id' => intval($row['id']), 'label' => $label);
        }

        $manufacturers = $this->rows($this->query("SELECT DISTINCT MANUFACTURER label FROM parabd_item
            WHERE STATUS='ACTIVE' AND MANUFACTURER_NORMALIZED LIKE '%$normalized%' AND MANUFACTURER IS NOT NULL AND MANUFACTURER<>''
            ORDER BY MANUFACTURER LIMIT $limit"));
        foreach ($manufacturers as $row) $result[] = array('category' => 'Fabricants', 'type' => 'manufacturer', 'id' => 0, 'label' => $row['label']);

        $publishers = $this->rows($this->query("SELECT DISTINCT PUBLISHER label FROM parabd_item
            WHERE STATUS='ACTIVE' AND PUBLISHER LIKE '%$like%' AND PUBLISHER IS NOT NULL AND PUBLISHER<>''
            ORDER BY PUBLISHER LIMIT $limit"));
        foreach ($publishers as $row) $result[] = array('category' => 'Éditeurs', 'type' => 'publisher', 'id' => 0, 'label' => $row['label']);
        return $result;
    }

    public function getItem($itemId, $includeHidden = false)
    {
        $item = $this->one("SELECT i.*, t.CODE TYPE_CODE, t.LABEL TYPE_LABEL, st.CODE SUBTYPE_CODE, st.LABEL SUBTYPE_LABEL
            FROM parabd_item i JOIN parabd_type t ON t.ID_TYPE=i.TYPE_ID LEFT JOIN parabd_type st ON st.ID_TYPE=i.SUBTYPE_ID
            WHERE i.ID_ITEM=" . intval($itemId) . ($includeHidden ? '' : " AND i.STATUS IN ('ACTIVE','MERGED')"));
        if (!$item) return null;
        if ($item['STATUS'] === 'MERGED' && !empty($item['MERGED_INTO_ID'])) $item['REDIRECT_ID'] = intval($item['MERGED_INTO_ID']);
        $item['identifiers'] = $this->rows($this->query("SELECT * FROM parabd_identifier WHERE ITEM_ID=" . intval($itemId) . " ORDER BY ID_IDENTIFIER"));
        $item['media'] = $this->rows($this->query("SELECT * FROM parabd_media WHERE ITEM_ID=" . intval($itemId) . " AND IS_HIDDEN=0 ORDER BY IS_PRIMARY DESC, SORT_ORDER, ID_MEDIA"));
        if (!Bdo_Cfg::getVar('explicit') && !empty($item['IS_EXPLICIT'])) {
            foreach ($item['media'] as &$media) $media['FILE_PATH'] = '?source=' . $media['FILE_PATH'];
            unset($media);
        }
        $item['sources'] = $this->rows($this->query("SELECT * FROM parabd_source WHERE ITEM_ID=" . intval($itemId) . " ORDER BY ID_SOURCE"));
        $item['authors'] = $this->rows($this->query("SELECT l.*, COALESCE(NULLIF(a.PSEUDO,''),NULLIF(TRIM(CONCAT_WS(' ',a.PRENOM,a.NOM)),''),CONCAT('Auteur #',l.AUTHOR_ID)) LABEL FROM parabd_item_author l LEFT JOIN bd_auteur a ON a.ID_AUTEUR=l.AUTHOR_ID WHERE l.ITEM_ID=" . intval($itemId)));
        $item['series'] = $this->rows($this->query("SELECT l.*, COALESCE(s.NOM,CONCAT('Série #',l.SERIES_ID)) LABEL FROM parabd_item_series l LEFT JOIN bd_serie s ON s.ID_SERIE=l.SERIES_ID WHERE l.ITEM_ID=" . intval($itemId)));
        $item['tomes'] = $this->rows($this->query("SELECT l.*, COALESCE(t.TITRE,CONCAT('Album #',l.TOME_ID)) LABEL FROM parabd_item_tome l LEFT JOIN bd_tome t ON t.ID_TOME=l.TOME_ID WHERE l.ITEM_ID=" . intval($itemId)));
        return $item;
    }

    public function getAdminItem($itemId)
    {
        $item = $this->getItem($itemId, true);
        if (!$item) return null;
        $item['media'] = $this->rows($this->query("SELECT * FROM parabd_media WHERE ITEM_ID=" . intval($itemId) . " ORDER BY IS_PRIMARY DESC,SORT_ORDER,ID_MEDIA"));
        return $item;
    }

    public function getAdminItemHistory($itemId)
    {
        return $this->rows($this->query("SELECT r.*,ua.username AUTHOR_NAME,uv.username VALIDATOR_NAME,
            SUM(v.VOTE='CONFIRM') CONFIRMS,SUM(v.VOTE='CONTEST') CONTESTS
            FROM parabd_revision r
            LEFT JOIN users ua ON ua.user_id=r.AUTHOR_ID
            LEFT JOIN users uv ON uv.user_id=r.VALIDATED_BY
            LEFT JOIN parabd_revision_vote v ON v.REVISION_ID=r.ID_REVISION
            WHERE r.ITEM_ID=" . intval($itemId) . " GROUP BY r.ID_REVISION ORDER BY r.CREATED_AT DESC,r.ID_REVISION DESC"));
    }

    public function searchDuplicates($input, $limit = 20)
    {
        $identifiers = isset($input['identifiers']) ? $input['identifiers'] : array();
        foreach ($identifiers as $identifier) {
            $scheme = isset($identifier['scheme']) ? strtoupper($identifier['scheme']) : '';
            $value = self::normalizeIdentifier($scheme, isset($identifier['value']) ? $identifier['value'] : '');
            $issuer = in_array($scheme, explode(',', self::GLOBAL_SCHEMES), true) ? '' : self::normalizeText(isset($identifier['issuer']) ? $identifier['issuer'] : '');
            if ($value === '') continue;
            $existing = $this->one("SELECT i.ID_ITEM, i.TITLE, i.STATUS FROM parabd_identifier x JOIN parabd_item i ON i.ID_ITEM=x.ITEM_ID
                WHERE x.SCHEME='" . $this->escape($scheme) . "' AND x.ISSUER_NORMALIZED='" . $this->escape($issuer) . "'
                AND x.VALUE_NORMALIZED='" . $this->escape($value) . "' LIMIT 1");
            if ($existing && !empty($input['ID_ITEM']) && intval($existing['ID_ITEM']) === intval($input['ID_ITEM'])) continue;
            if ($existing) return array(array_merge($existing, array('level' => 'CERTAIN', 'score' => 100, 'reasons' => array('Identifiant exact'))));
        }
        $title = self::normalizeText(isset($input['TITLE']) ? $input['TITLE'] : '');
        $words = array_values(array_filter(explode(' ', $title), function ($word) { return strlen($word) >= 3; }));
        usort($words, function ($a, $b) { return strlen($b) <=> strlen($a); });
        $words = array_slice($words, 0, 3);
        $where = "STATUS='ACTIVE'";
        $candidateClauses = array();
        foreach ($words as $word) $candidateClauses[] = "TITLE_NORMALIZED LIKE '%" . $this->escape($word) . "%'";
        if (!empty($input['AUTHOR_ID'])) $candidateClauses[] = "ID_ITEM IN (SELECT ITEM_ID FROM parabd_item_author WHERE AUTHOR_ID=" . intval($input['AUTHOR_ID']) . ")";
        if (!empty($input['SERIES_ID'])) $candidateClauses[] = "ID_ITEM IN (SELECT ITEM_ID FROM parabd_item_series WHERE SERIES_ID=" . intval($input['SERIES_ID']) . ")";
        if (!empty($input['TOME_ID'])) $candidateClauses[] = "ID_ITEM IN (SELECT ITEM_ID FROM parabd_item_tome WHERE TOME_ID=" . intval($input['TOME_ID']) . ")";
        if ($candidateClauses) $where .= ' AND (' . implode(' OR ', $candidateClauses) . ')';
        elseif (!empty($input['TYPE_ID'])) $where .= ' AND TYPE_ID=' . intval($input['TYPE_ID']);
        $candidates = $this->rows($this->query("SELECT * FROM parabd_item WHERE $where ORDER BY UPDATED_AT DESC LIMIT 200"));
        $result = array();
        foreach ($candidates as $candidate) {
            if (!empty($input['ID_ITEM']) && intval($candidate['ID_ITEM']) === intval($input['ID_ITEM'])) continue;
            $test = $input;
            $test['common_relation'] = $this->hasCommonRelation(intval($candidate['ID_ITEM']), $input);
            $level = self::duplicateLevel($candidate, $test);
            if ($level) $result[] = array_merge(array('ID_ITEM' => $candidate['ID_ITEM'], 'TITLE' => $candidate['TITLE'], 'PRIMARY_IMAGE' => $this->primaryImage(intval($candidate['ID_ITEM']))), $level);
        }
        usort($result, function ($a, $b) { return $b['score'] <=> $a['score']; });
        return array_slice($result, 0, $limit);
    }

    private function primaryImage($itemId)
    {
        $mediaPath = $this->mediaPathSql('m', 'i');
        $row = $this->one("SELECT $mediaPath FILE_PATH FROM parabd_media m JOIN parabd_item i ON i.ID_ITEM=m.ITEM_ID WHERE m.ITEM_ID=$itemId AND m.IS_PRIMARY=1 AND m.IS_HIDDEN=0 LIMIT 1");
        return $row ? $row['FILE_PATH'] : null;
    }

    private function hasCommonRelation($itemId, $input)
    {
        $map = array('AUTHOR_ID' => array('parabd_item_author', 'AUTHOR_ID'), 'SERIES_ID' => array('parabd_item_series', 'SERIES_ID'), 'TOME_ID' => array('parabd_item_tome', 'TOME_ID'));
        foreach ($map as $key => $meta) {
            if (!empty($input[$key])) {
                $row = $this->one("SELECT 1 found FROM {$meta[0]} WHERE ITEM_ID=$itemId AND {$meta[1]}=" . intval($input[$key]) . " LIMIT 1");
                if ($row) return true;
            }
        }
        return false;
    }

    public function isTrusted($userId)
    {
        $profile = $this->one("SELECT TRUST_OVERRIDE FROM parabd_user_profile WHERE USER_ID=" . intval($userId));
        $user = $this->one("SELECT CREATED_AT FROM users WHERE user_id=" . intval($userId));
        if (!$user) return false;
        $legacy = $this->one("SELECT
            (SELECT COUNT(*) FROM users_alb_prop WHERE USER_ID=" . intval($userId) . " AND STATUS=1) +
            (SELECT COUNT(*) FROM bd_edition WHERE USER_ID=" . intval($userId) . " AND PROP_STATUS=1) +
            (SELECT COUNT(*) FROM parabd_revision WHERE AUTHOR_ID=" . intval($userId) . " AND VALIDATED_AT IS NOT NULL AND STATUS IN ('ACCEPTED','APPLIED')) total");
        return self::calculateTrust($user['CREATED_AT'], $legacy ? intval($legacy['total']) : 0, $profile ? $profile['TRUST_OVERRIDE'] : 'NONE');
    }

    public static function calculateTrust($createdAt, $validatedContributions, $override = 'NONE', $now = null)
    {
        if ($override === 'REVOKE') return false;
        if ($override === 'GRANT') return true;
        $now = $now === null ? time() : intval($now);
        return strtotime($createdAt) <= strtotime('-1 year', $now) && intval($validatedContributions) >= 5;
    }

    public function acceptCharter($userId, $accepted)
    {
        if (!$accepted) throw new ParabdException('VALIDATION_ERROR', 'Vous devez accepter la charte de contribution.', array('charter' => 'Acceptation obligatoire.'));
        $version = $this->escape(defined('BDO_PARABD_CHARTER_VERSION') ? BDO_PARABD_CHARTER_VERSION : '1');
        $this->query("INSERT INTO parabd_user_profile (USER_ID,CHARTER_VERSION,CHARTER_ACCEPTED_AT) VALUES (" . intval($userId) . ",'$version',NOW())
            ON DUPLICATE KEY UPDATE CHARTER_VERSION=VALUES(CHARTER_VERSION), CHARTER_ACCEPTED_AT=NOW()");
    }

    public function requireCharter($userId)
    {
        $version = defined('BDO_PARABD_CHARTER_VERSION') ? BDO_PARABD_CHARTER_VERSION : '1';
        $profile = $this->one("SELECT CHARTER_VERSION FROM parabd_user_profile WHERE USER_ID=" . intval($userId));
        if (!$profile || $profile['CHARTER_VERSION'] !== $version) throw new ParabdException('VALIDATION_ERROR', 'La charte Para-BD doit être acceptée.', array('charter' => 'Acceptation obligatoire.'));
    }

    public function consumeRate($userId, $kind)
    {
        if ($this->isTrusted($userId) || User::minAccesslevel(1)) return;
        $columnAt = $kind === 'upload' ? 'UPLOADS_WINDOW_AT' : 'CREATIONS_WINDOW_AT';
        $columnCount = $kind === 'upload' ? 'UPLOADS_IN_WINDOW' : 'CREATIONS_IN_WINDOW';
        $limit = $kind === 'upload' ? (defined('BDO_PARABD_UPLOADS_PER_HOUR') ? BDO_PARABD_UPLOADS_PER_HOUR : 20) : (defined('BDO_PARABD_CREATIONS_PER_HOUR') ? BDO_PARABD_CREATIONS_PER_HOUR : 10);
        $connection = $this->connection(); Db_autocommit(false, $connection);
        try {
            $this->query("INSERT IGNORE INTO parabd_user_profile (USER_ID) VALUES (" . intval($userId) . ")");
            $row = $this->one("SELECT $columnAt window_at, $columnCount count_value FROM parabd_user_profile WHERE USER_ID=" . intval($userId) . " FOR UPDATE");
            $fresh = empty($row['window_at']) || strtotime($row['window_at']) <= strtotime('-1 hour');
            if (!$fresh && intval($row['count_value']) >= $limit) throw new ParabdException('RATE_LIMITED', 'Limite horaire atteinte. Réessayez plus tard.');
            $this->query("UPDATE parabd_user_profile SET $columnAt=" . ($fresh ? 'NOW()' : $columnAt) . ", $columnCount=" . ($fresh ? '1' : "$columnCount+1") . " WHERE USER_ID=" . intval($userId));
            Db_commit($connection); Db_autocommit(true, $connection);
        } catch (Throwable $error) { Db_rollback($connection); Db_autocommit(true, $connection); throw $error; }
    }

    private function typeIds($typeCode, $subtypeCode)
    {
        $type = $this->one("SELECT * FROM parabd_type WHERE CODE='" . $this->escape(strtoupper($typeCode)) . "' AND PARENT_ID IS NULL AND IS_ACTIVE=1");
        if (!$type) throw new ParabdException('VALIDATION_ERROR', 'Type Para-BD invalide.', array('type_code' => 'Type invalide.'));
        $subtype = null;
        if ($subtypeCode !== '') $subtype = $this->one("SELECT * FROM parabd_type WHERE CODE='" . $this->escape(strtoupper($subtypeCode)) . "' AND PARENT_ID=" . intval($type['ID_TYPE']) . " AND IS_ACTIVE=1");
        if (intval($type['IS_REQUIRED_SUBTYPE']) && !$subtype) throw new ParabdException('VALIDATION_ERROR', 'Le sous-type est obligatoire.', array('subtype_code' => 'Sous-type obligatoire.'));
        if ($subtypeCode !== '' && !$subtype) throw new ParabdException('VALIDATION_ERROR', 'Sous-type Para-BD invalide.', array('subtype_code' => 'Sous-type invalide.'));
        return array(intval($type['ID_TYPE']), $subtype ? intval($subtype['ID_TYPE']) : null);
    }

    private function cleanInput($input)
    {
        $title = trim(isset($input['title']) ? $input['title'] : '');
        if ($title === '') throw new ParabdException('VALIDATION_ERROR', 'Le titre est obligatoire.', array('title' => 'Titre obligatoire.'));
        list($typeId, $subtypeId) = $this->typeIds(isset($input['type_code']) ? $input['type_code'] : '', isset($input['subtype_code']) ? $input['subtype_code'] : '');
        $date = self::parsePartialDate(isset($input['release_date']) ? $input['release_date'] : '');
        $data = array(
            'TYPE_ID' => $typeId, 'SUBTYPE_ID' => $subtypeId, 'TITLE' => $title, 'TITLE_NORMALIZED' => self::normalizeText($title),
            'DESCRIPTION' => trim(isset($input['description']) ? $input['description'] : ''), 'MATERIAL' => trim(isset($input['material']) ? $input['material'] : ''),
            'COLOR' => trim(isset($input['color']) ? $input['color'] : ''), 'WIDTH_MM' => $this->decimal(isset($input['width_mm']) ? $input['width_mm'] : null),
            'HEIGHT_MM' => $this->decimal(isset($input['height_mm']) ? $input['height_mm'] : null), 'DEPTH_MM' => $this->decimal(isset($input['depth_mm']) ? $input['depth_mm'] : null),
            'WEIGHT_G' => $this->decimal(isset($input['weight_g']) ? $input['weight_g'] : null), 'SCALE' => trim(isset($input['scale']) ? $input['scale'] : ''),
            'RELEASE_DATE' => $date['date'], 'DATE_PRECISION' => $date['precision'], 'PRINT_RUN' => $this->positiveInt(isset($input['print_run']) ? $input['print_run'] : null),
            'IS_NUMBERED' => $this->tri(isset($input['is_numbered']) ? $input['is_numbered'] : ''), 'IS_SIGNED' => $this->tri(isset($input['is_signed']) ? $input['is_signed'] : ''),
            'HAS_CERTIFICATE' => $this->tri(isset($input['has_certificate']) ? $input['has_certificate'] : ''), 'IS_LIMITED' => $this->tri(isset($input['is_limited']) ? $input['is_limited'] : ''),
            'MANUFACTURER' => trim(isset($input['manufacturer']) ? $input['manufacturer'] : ''), 'PUBLISHER' => trim(isset($input['publisher']) ? $input['publisher'] : ''),
            'LICENSE_NAME' => trim(isset($input['license_name']) ? $input['license_name'] : ''), 'RANGE_NAME' => trim(isset($input['range_name']) ? $input['range_name'] : ''),
            'UNIVERSE_NAME' => trim(isset($input['universe_name']) ? $input['universe_name'] : ''),
            'IS_EXPLICIT' => !empty($input['is_explicit']) ? 1 : 0
        );
        $data['MANUFACTURER_NORMALIZED'] = self::normalizeText($data['MANUFACTURER']);
        return $data;
    }

    private function decimal($value)
    {
        if ($value === null || trim((string) $value) === '') return null;
        $value = str_replace(',', '.', $value);
        if (!is_numeric($value) || floatval($value) < 0) throw new ParabdException('VALIDATION_ERROR', 'Une valeur numérique est invalide.');
        return number_format(floatval($value), 2, '.', '');
    }

    private function positiveInt($value)
    {
        if ($value === null || trim((string) $value) === '') return null;
        if (!ctype_digit((string) $value) || intval($value) < 0) throw new ParabdException('VALIDATION_ERROR', 'Une valeur entière est invalide.');
        return intval($value);
    }

    private function tri($value)
    {
        if ($value === '' || $value === null || $value === 'unknown') return null;
        if (in_array((string) $value, array('1', 'yes', 'Y'), true)) return 1;
        if (in_array((string) $value, array('0', 'no', 'N'), true)) return 0;
        throw new ParabdException('VALIDATION_ERROR', 'Une valeur oui/non/inconnu est invalide.');
    }

    private function identifiersFromInput($input)
    {
        $identifiers = array();
        if (!empty($input['identifiers_json'])) {
            $decoded = json_decode($input['identifiers_json'], true);
            if (!is_array($decoded)) throw new ParabdException('VALIDATION_ERROR', 'Les identifiants sont invalides.', array('identifiers' => 'JSON invalide.'));
            $identifiers = $decoded;
        } elseif (isset($input['identifiers']) && is_array($input['identifiers'])) {
            $identifiers = $input['identifiers'];
        } elseif (!empty($input['identifier_scheme']) || !empty($input['identifier_value'])) {
            $identifiers[] = array('scheme' => isset($input['identifier_scheme']) ? $input['identifier_scheme'] : '', 'issuer' => isset($input['identifier_issuer']) ? $input['identifier_issuer'] : '', 'value' => isset($input['identifier_value']) ? $input['identifier_value'] : '');
        }
        $allowed = array('EAN13','UPCA','ISBN10','ISBN13','MANUFACTURER_REF','PUBLISHER_REF','EXTERNAL_DB');
        $clean = array();
        foreach ($identifiers as $identifier) {
            if (empty($identifier['scheme']) && empty($identifier['value'])) continue;
            $scheme = strtoupper(isset($identifier['scheme']) ? $identifier['scheme'] : '');
            if (!in_array($scheme, $allowed, true)) throw new ParabdException('VALIDATION_ERROR', 'Schéma d’identifiant invalide.');
            $value = self::normalizeIdentifier($scheme, isset($identifier['value']) ? $identifier['value'] : '');
            if (!self::isValidIdentifier($scheme, $value)) throw new ParabdException('VALIDATION_ERROR', 'Clé ' . $scheme . ' invalide.', array('identifier_value' => 'Clé invalide.'));
            $issuer = in_array($scheme, explode(',', self::GLOBAL_SCHEMES), true) ? '' : trim(isset($identifier['issuer']) ? $identifier['issuer'] : '');
            if ($scheme === 'MANUFACTURER_REF' && $issuer === '' && !empty($input['manufacturer'])) $issuer = $input['manufacturer'];
            if (!in_array($scheme, explode(',', self::GLOBAL_SCHEMES), true) && $issuer === '') throw new ParabdException('VALIDATION_ERROR', 'L’émetteur de cette référence est obligatoire.');
            $clean[] = array('scheme' => $scheme, 'issuer' => $issuer, 'issuer_normalized' => self::normalizeText($issuer), 'value' => trim($identifier['value']), 'value_normalized' => $value);
        }
        return $clean;
    }

    private function relationsFromInput($input)
    {
        $relations = array('authors' => array(), 'series' => array(), 'tomes' => array());
        if (isset($input['authors']) && is_array($input['authors'])) {
            foreach ($input['authors'] as $row) if (!empty($row['id'])) $relations['authors'][] = array('id' => intval($row['id']), 'role' => trim(isset($row['role']) ? $row['role'] : 'ARTIST') ?: 'ARTIST');
        } elseif (!empty($input['author_id'])) $relations['authors'][] = array('id' => intval($input['author_id']), 'role' => trim(isset($input['author_role']) ? $input['author_role'] : 'ARTIST') ?: 'ARTIST');
        if (isset($input['series']) && is_array($input['series'])) {
            foreach ($input['series'] as $row) if (!empty($row['id'])) $relations['series'][] = array('id' => intval($row['id']), 'relation_type' => trim(isset($row['relation_type']) ? $row['relation_type'] : 'RELATED') ?: 'RELATED');
        } elseif (!empty($input['series_id'])) $relations['series'][] = array('id' => intval($input['series_id']), 'relation_type' => 'RELATED');
        if (isset($input['tomes']) && is_array($input['tomes'])) {
            foreach ($input['tomes'] as $row) if (!empty($row['id'])) $relations['tomes'][] = array('id' => intval($row['id']), 'relation_type' => trim(isset($row['relation_type']) ? $row['relation_type'] : 'RELATED') ?: 'RELATED', 'page_no' => $this->positiveInt(isset($row['page_no']) ? $row['page_no'] : null), 'panel_no' => $this->positiveInt(isset($row['panel_no']) ? $row['panel_no'] : null));
        } elseif (!empty($input['tome_id'])) $relations['tomes'][] = array('id' => intval($input['tome_id']), 'relation_type' => 'RELATED', 'page_no' => $this->positiveInt(isset($input['page_no']) ? $input['page_no'] : null), 'panel_no' => $this->positiveInt(isset($input['panel_no']) ? $input['panel_no'] : null));
        return $relations;
    }

    private function sourcesFromInput($input)
    {
        $sources = array();
        if (isset($input['sources']) && is_array($input['sources'])) {
            foreach ($input['sources'] as $row) {
                $url = trim(isset($row['url']) ? $row['url'] : '');
                if ($url !== '') $sources[] = array('url' => $url, 'label' => trim(isset($row['label']) ? $row['label'] : ''), 'notes' => trim(isset($row['notes']) ? $row['notes'] : ''));
            }
        } elseif (!empty($input['source_url'])) $sources[] = array('url' => trim($input['source_url']), 'label' => trim(isset($input['source_label']) ? $input['source_label'] : ''), 'notes' => '');
        foreach ($sources as $source) if (!filter_var($source['url'], FILTER_VALIDATE_URL) || !in_array(strtolower(parse_url($source['url'], PHP_URL_SCHEME)), array('http','https'), true)) throw new ParabdException('VALIDATION_ERROR', 'URL source invalide.');
        return $sources;
    }

    private function sqlValue($value)
    {
        if ($value === null || $value === '') return 'NULL';
        return "'" . $this->escape($value) . "'";
    }

    private function sqlItemValue($field, $value)
    {
        if ($value === '' && in_array($field, array('TITLE_NORMALIZED','MANUFACTURER_NORMALIZED'), true)) return "''";
        return $this->sqlValue($value);
    }

    public function createItem($userId, $input, $file, $adminDirect = false)
    {
        if (!$adminDirect) $this->requireCharter($userId);
        $visualUrl = trim(isset($input['visual_url']) ? $input['visual_url'] : '');
        $hasUpload = $file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK;
        if (!$hasUpload && $visualUrl === '') throw new ParabdException('VALIDATION_ERROR', 'Un fichier ou une URL d’image est obligatoire.', array('visual' => 'Visuel obligatoire.'));
        $data = $this->cleanInput($input);
        if ($adminDirect) {
            $adminStatus = strtoupper(trim(isset($input['status']) ? $input['status'] : 'ACTIVE'));
            if (!in_array($adminStatus, array('ACTIVE','HIDDEN'), true)) throw new ParabdException('VALIDATION_ERROR', 'Statut de fiche invalide.');
            $data['STATUS'] = $adminStatus;
        }
        $identifiers = $this->identifiersFromInput($input);
        $relations = $this->relationsFromInput($input);
        $sources = $this->sourcesFromInput($input);
        $duplicateInput = array_merge($data, array('identifiers' => $identifiers, 'AUTHOR_ID' => isset($input['author_id']) ? intval($input['author_id']) : 0, 'SERIES_ID' => isset($input['series_id']) ? intval($input['series_id']) : 0, 'TOME_ID' => isset($input['tome_id']) ? intval($input['tome_id']) : 0));
        $duplicates = $this->searchDuplicates($duplicateInput);
        foreach ($duplicates as $duplicate) {
            if ($duplicate['level'] === 'CERTAIN') throw new ParabdException('DUPLICATE_EXACT', 'Cet objet existe déjà.', array('existing_item_id' => intval($duplicate['ID_ITEM'])));
            if ($duplicate['level'] === 'STRONG' && (empty($input['duplicate_reviewed']) || trim(isset($input['duplicate_reason']) ? $input['duplicate_reason'] : '') === '')) {
                throw new ParabdException('VALIDATION_ERROR', 'Consultez les doublons probables et indiquez pourquoi il s’agit d’un autre objet.', array('duplicate_reason' => 'Motif obligatoire.', 'duplicate_candidates' => $duplicates));
            }
        }
        // Attempts are counted outside the catalogue transaction so malformed
        // uploads cannot bypass throttling by forcing a rollback.
        $this->consumeRate($userId, 'creation');
        $this->consumeRate($userId, 'upload');
        $remoteFile = null;
        if (!$hasUpload) {
            $file = $this->downloadRemoteImage($visualUrl);
            $remoteFile = $file['tmp_name'];
        }
        $connection = $this->connection();
        $writtenFile = null;
        Db_autocommit(false, $connection);
        try {
            $columns = array_keys($data); $values = array();
            foreach ($data as $field => $value) $values[] = $this->sqlItemValue($field, $value);
            $this->query("INSERT INTO parabd_item (" . implode(',', $columns) . ",CREATED_BY,UPDATED_BY) VALUES (" . implode(',', $values) . "," . intval($userId) . "," . intval($userId) . ")");
            $itemId = Db_insert_id($connection);
            foreach ($identifiers as $identifier) {
                $this->query("INSERT INTO parabd_identifier (ITEM_ID,SCHEME,ISSUER,ISSUER_NORMALIZED,VALUE,VALUE_NORMALIZED,CREATED_BY) VALUES
                    ($itemId,'" . $this->escape($identifier['scheme']) . "'," . $this->sqlValue($identifier['issuer']) . ",'" . $this->escape($identifier['issuer_normalized']) . "','" . $this->escape($identifier['value']) . "','" . $this->escape($identifier['value_normalized']) . "'," . intval($userId) . ")");
            }
            $this->insertRelations($itemId, $userId, $relations);
            $image = $this->storeImage($file, $itemId, 1);
            $writtenFile = $image['absolute_path'];
            $this->query("INSERT INTO parabd_media (ITEM_ID,MEDIA_TYPE,FILE_PATH,MIME_TYPE,WIDTH_PX,HEIGHT_PX,IS_PRIMARY,CREATED_BY) VALUES
                ($itemId,'PRIMARY','" . $this->escape($image['relative_path']) . "','" . $this->escape($image['mime']) . "'," . intval($image['width']) . "," . intval($image['height']) . ",1," . intval($userId) . ")");
            foreach ($sources as $source) $this->addSource($itemId, $userId, $source['url'], $source['label'], $source['notes']);
            if ($visualUrl !== '' && !in_array($visualUrl, array_column($sources, 'url'), true)) $this->addSource($itemId, $userId, $visualUrl, 'Source du visuel principal');
            $snapshot = json_encode($this->adminSnapshot($itemId), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $this->query("INSERT INTO parabd_revision (ITEM_ID,AUTHOR_ID,BASE_REVISION_NO,PATCH_AFTER,CHANGE_KIND,STATUS,APPLIED_AT,VALIDATED_BY,VALIDATED_AT) VALUES ($itemId," . intval($userId) . ",0,'" . $this->escape($snapshot) . "','CREATE','" . ($adminDirect ? 'ACCEPTED' : 'APPLIED') . "',NOW()," . ($adminDirect ? intval($userId) : 'NULL') . "," . ($adminDirect ? 'NOW()' : 'NULL') . ")");
            $copyId = null;
            $collectionAction = isset($input['collection_action']) ? $input['collection_action'] : 'none';
            if (in_array($collectionAction, array('OWNED','WISHLIST'), true)) $copyId = $this->saveCopy($userId, array_merge($input, array('item_id' => $itemId, 'state' => $collectionAction)), true);
            foreach ($duplicates as $duplicate) $this->recordDuplicate($itemId, intval($duplicate['ID_ITEM']), $duplicate);
            Db_commit($connection);
            Db_autocommit(true, $connection);
            if ($remoteFile && is_file($remoteFile)) @unlink($remoteFile);
            return array('item_id' => intval($itemId), 'copy_id' => $copyId, 'duplicates' => $duplicates);
        } catch (Throwable $error) {
            Db_rollback($connection);
            Db_autocommit(true, $connection);
            if ($writtenFile && is_file($writtenFile)) @unlink($writtenFile);
            if ($remoteFile && is_file($remoteFile)) @unlink($remoteFile);
            if ($error instanceof ParabdException) throw $error;
            if ($connection->errno === 1062 || strpos($error->getMessage(), 'Duplicate entry') !== false) throw new ParabdException('DUPLICATE_EXACT', 'Un identifiant identique vient d’être créé. Relancez la recherche.');
            throw $error;
        }
    }

    public function adminCreateItem($adminId, $input, $file)
    {
        $input['collection_action'] = 'none';
        return $this->createItem($adminId, $input, $file, true);
    }

    private function insertRelations($itemId, $userId, $relations)
    {
        foreach ($relations['authors'] as $row) {
            if (!$this->one("SELECT ID_AUTEUR FROM bd_auteur WHERE ID_AUTEUR=" . intval($row['id']))) throw new ParabdException('VALIDATION_ERROR', 'Auteur inconnu.');
            $this->query("INSERT INTO parabd_item_author (ITEM_ID,AUTHOR_ID,ROLE,CREATED_BY) VALUES ($itemId," . intval($row['id']) . ",'" . $this->escape($row['role']) . "'," . intval($userId) . ")");
        }
        foreach ($relations['series'] as $row) {
            if (!$this->one("SELECT ID_SERIE FROM bd_serie WHERE ID_SERIE=" . intval($row['id']))) throw new ParabdException('VALIDATION_ERROR', 'Série inconnue.');
            $this->query("INSERT INTO parabd_item_series (ITEM_ID,SERIES_ID,RELATION_TYPE,CREATED_BY) VALUES ($itemId," . intval($row['id']) . ",'" . $this->escape($row['relation_type']) . "'," . intval($userId) . ")");
        }
        foreach ($relations['tomes'] as $row) {
            if (!$this->one("SELECT ID_TOME FROM bd_tome WHERE ID_TOME=" . intval($row['id']))) throw new ParabdException('VALIDATION_ERROR', 'Album inconnu.');
            $this->query("INSERT INTO parabd_item_tome (ITEM_ID,TOME_ID,RELATION_TYPE,PAGE_NO,PANEL_NO,CREATED_BY) VALUES ($itemId," . intval($row['id']) . ",'" . $this->escape($row['relation_type']) . "'," . $this->sqlValue($row['page_no']) . "," . $this->sqlValue($row['panel_no']) . "," . intval($userId) . ")");
        }
    }

    private function addSource($itemId, $userId, $url, $label, $notes = '')
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array(strtolower(parse_url($url, PHP_URL_SCHEME)), array('http','https'), true)) throw new ParabdException('VALIDATION_ERROR', 'URL source invalide.');
        $this->query("INSERT INTO parabd_source (ITEM_ID,SOURCE_TYPE,URL,LABEL,NOTES,CREATED_BY) VALUES ($itemId,'URL','" . $this->escape($url) . "','" . $this->escape($label) . "','" . $this->escape($notes) . "'," . intval($userId) . ")");
    }

    public function storeImage($file, $itemId, $sequence)
    {
        if (!isset($file['tmp_name']) || (!is_uploaded_file($file['tmp_name']) && PHP_SAPI !== 'cli' && empty($file['_parabd_remote']))) throw new ParabdException('VALIDATION_ERROR', 'Fichier uploadé invalide.', array('visual' => 'Upload invalide.'));
        $maxBytes = defined('BDO_PARABD_MAX_UPLOAD_BYTES') ? BDO_PARABD_MAX_UPLOAD_BYTES : 5242880;
        $actualSize = @filesize($file['tmp_name']);
        if ($actualSize === false || $actualSize <= 0 || $actualSize > $maxBytes) throw new ParabdException('VALIDATION_ERROR', 'Le visuel dépasse 5 Mo ou est vide.', array('visual' => '5 Mo maximum.'));
        $info = @getimagesize($file['tmp_name']);
        if (!$info || empty($info[0]) || empty($info[1])) throw new ParabdException('VALIDATION_ERROR', 'Le fichier n’est pas une image valide.', array('visual' => 'Image invalide.'));
        if ($info[0] * $info[1] > (defined('BDO_PARABD_MAX_IMAGE_PIXELS') ? BDO_PARABD_MAX_IMAGE_PIXELS : 30000000)) throw new ParabdException('VALIDATION_ERROR', 'Le visuel dépasse 30 mégapixels.');
        $allowed = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!isset($allowed[$mime]) || $info['mime'] !== $mime) throw new ParabdException('VALIDATION_ERROR', 'Format image non autorisé ou MIME incohérent.');
        $extension = strtolower(pathinfo(isset($file['name']) ? $file['name'] : '', PATHINFO_EXTENSION));
        $extensionAliases = array('jpeg' => 'jpg');
        if (isset($extensionAliases[$extension])) $extension = $extensionAliases[$extension];
        if ($extension !== $allowed[$mime]) throw new ParabdException('VALIDATION_ERROR', 'L’extension du fichier ne correspond pas à son contenu.');
        $source = @imagecreatefromstring(file_get_contents($file['tmp_name']));
        if (!$source) throw new ParabdException('VALIDATION_ERROR', 'Impossible de décoder le visuel.');
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($file['tmp_name']);
            $source = self::orientImage($source, isset($exif['Orientation']) ? intval($exif['Orientation']) : 1);
        }
        $sourceWidth = imagesx($source); $sourceHeight = imagesy($source);
        $ratio = min(1, 1600 / max($sourceWidth, $sourceHeight));
        $width = max(1, intval(round($sourceWidth * $ratio))); $height = max(1, intval(round($sourceHeight * $ratio)));
        $target = imagecreatetruecolor($width, $height);
        if ($mime === 'image/png') {
            imagealphablending($target, false); imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127); imagefilledrectangle($target, 0, 0, $width, $height, $transparent);
        }
        imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);
        $shard = sprintf('%03d', intval($itemId / 1000));
        $folder = rtrim(BDO_DIR_PARABD, DS) . DS . $shard . DS . intval($itemId) . DS;
        if (!is_dir($folder) && !mkdir($folder, 0775, true) && !is_dir($folder)) throw new RuntimeException('Impossible de créer le répertoire Para-BD.');
        $filename = sprintf('PBD-%06d-%02d.%s', $itemId, $sequence, $allowed[$mime]);
        $absolute = $folder . $filename;
        if ($mime === 'image/webp' && !function_exists('imagewebp')) throw new ParabdException('VALIDATION_ERROR', 'Le support WebP est indisponible sur ce serveur.');
        $ok = $mime === 'image/png' ? imagepng($target, $absolute, 6) : ($mime === 'image/webp' ? imagewebp($target, $absolute, 85) : imagejpeg($target, $absolute, 85));
        imagedestroy($source); imagedestroy($target);
        if (!$ok) throw new RuntimeException('Impossible d’enregistrer le visuel Para-BD.');
        return array('absolute_path' => $absolute, 'relative_path' => $shard . '/' . intval($itemId) . '/' . $filename, 'mime' => $mime, 'width' => $width, 'height' => $height);
    }

    public static function isPublicRemoteIp($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    private function publicAddressesForHost($host)
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) return self::isPublicRemoteIp($host) ? array($host) : array();
        $addresses = array();
        if (function_exists('dns_get_record')) {
            $records = @dns_get_record($host, DNS_A | DNS_AAAA);
            if (is_array($records)) foreach ($records as $record) {
                if (!empty($record['ip'])) $addresses[] = $record['ip'];
                if (!empty($record['ipv6'])) $addresses[] = $record['ipv6'];
            }
        }
        if (!$addresses) {
            $ipv4 = @gethostbynamel($host);
            if (is_array($ipv4)) $addresses = $ipv4;
        }
        return array_values(array_filter(array_unique($addresses), array(__CLASS__, 'isPublicRemoteIp')));
    }

    private function absoluteRedirectUrl($base, $location)
    {
        if (preg_match('#^https?://#i', $location)) return $location;
        $parts = parse_url($base);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) return '';
        $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . intval($parts['port']) : '');
        if (strpos($location, '//') === 0) return $parts['scheme'] . ':' . $location;
        if (strpos($location, '/') === 0) return $origin . $location;
        $path = isset($parts['path']) ? $parts['path'] : '/';
        return $origin . preg_replace('#/[^/]*$#', '/', $path) . $location;
    }

    private function downloadRemoteImage($url)
    {
        if (!function_exists('curl_init')) throw new ParabdException('VALIDATION_ERROR', 'L’import d’image par URL est indisponible sur ce serveur.');
        $maxBytes = defined('BDO_PARABD_MAX_UPLOAD_BYTES') ? BDO_PARABD_MAX_UPLOAD_BYTES : 5242880;
        $currentUrl = trim((string) $url);
        for ($redirect = 0; $redirect <= 3; $redirect++) {
            if (strlen($currentUrl) > 1000 || !filter_var($currentUrl, FILTER_VALIDATE_URL)) {
                throw new ParabdException('VALIDATION_ERROR', 'URL d’image invalide.', array('visual_url' => 'URL HTTP(S) valide attendue.'));
            }
            $parts = parse_url($currentUrl);
            $scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
            $host = isset($parts['host']) ? strtolower($parts['host']) : '';
            $port = isset($parts['port']) ? intval($parts['port']) : ($scheme === 'https' ? 443 : 80);
            if (!in_array($scheme, array('http','https'), true) || $host === '' || !empty($parts['user']) || !empty($parts['pass']) || !in_array($port, array(80,443), true)) {
                throw new ParabdException('VALIDATION_ERROR', 'URL d’image invalide ou protocole non autorisé.', array('visual_url' => 'URL HTTP(S) publique attendue.'));
            }
            $addresses = $this->publicAddressesForHost($host);
            if (!$addresses) throw new ParabdException('VALIDATION_ERROR', 'L’adresse de l’image est locale, privée ou introuvable.', array('visual_url' => 'Adresse publique requise.'));

            $body = '';
            $location = '';
            $tooLarge = false;
            $resolvedAddress = strpos($addresses[0], ':') !== false ? '[' . $addresses[0] . ']' : $addresses[0];
            $curl = curl_init($currentUrl);
            curl_setopt_array($curl, array(
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_USERAGENT => 'BDovore-ParaBD-Image/1.0',
                CURLOPT_PROXY => '',
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_RESOLVE => array($host . ':' . $port . ':' . $resolvedAddress),
                CURLOPT_WRITEFUNCTION => function ($handle, $chunk) use (&$body, &$tooLarge, $maxBytes) {
                    if (strlen($body) + strlen($chunk) > $maxBytes) { $tooLarge = true; return 0; }
                    $body .= $chunk; return strlen($chunk);
                },
                CURLOPT_HEADERFUNCTION => function ($handle, $header) use (&$location) {
                    if (stripos($header, 'Location:') === 0) $location = trim(substr($header, 9));
                    return strlen($header);
                }
            ));
            $ok = curl_exec($curl);
            $status = intval(curl_getinfo($curl, CURLINFO_RESPONSE_CODE));
            $error = curl_error($curl);
            curl_close($curl);
            if ($tooLarge) throw new ParabdException('VALIDATION_ERROR', 'L’image distante dépasse 5 Mo.', array('visual_url' => '5 Mo maximum.'));
            if ($status >= 300 && $status < 400 && $location !== '') {
                $currentUrl = $this->absoluteRedirectUrl($currentUrl, $location);
                continue;
            }
            if (!$ok || $status < 200 || $status >= 300 || $body === '') throw new ParabdException('VALIDATION_ERROR', 'Impossible de télécharger l’image distante' . ($error ? ' : ' . $error : '.'), array('visual_url' => 'Téléchargement impossible.'));

            $tmp = tempnam(sys_get_temp_dir(), 'parabd-url-');
            if (!$tmp || file_put_contents($tmp, $body) === false) throw new RuntimeException('Impossible de préparer l’image distante.');
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmp);
            $extensions = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
            if (!isset($extensions[$mime])) { @unlink($tmp); throw new ParabdException('VALIDATION_ERROR', 'L’URL ne désigne pas une image JPEG, PNG ou WebP.'); }
            return array('tmp_name' => $tmp, 'name' => 'image-distante.' . $extensions[$mime], 'size' => strlen($body), 'error' => UPLOAD_ERR_OK, '_parabd_remote' => true);
        }
        throw new ParabdException('VALIDATION_ERROR', 'L’image distante effectue trop de redirections.');
    }

    public static function orientImage($image, $orientation)
    {
        if ($orientation === 2 || $orientation === 4 || $orientation === 5 || $orientation === 7) imageflip($image, IMG_FLIP_HORIZONTAL);
        if ($orientation === 3 || $orientation === 4) $image = imagerotate($image, 180, 0);
        elseif ($orientation === 5 || $orientation === 6) $image = imagerotate($image, -90, 0);
        elseif ($orientation === 7 || $orientation === 8) $image = imagerotate($image, 90, 0);
        return $image;
    }

    private function recordDuplicate($itemId, $otherId, $duplicate)
    {
        $low = min($itemId, $otherId); $high = max($itemId, $otherId);
        $this->query("INSERT INTO parabd_duplicate (ITEM_ID_LOW,ITEM_ID_HIGH,LEVEL,SCORE,REASONS) VALUES ($low,$high,'" . $this->escape($duplicate['level']) . "'," . floatval($duplicate['score']) . ",'" . $this->escape(json_encode($duplicate['reasons'], JSON_UNESCAPED_UNICODE)) . "')
            ON DUPLICATE KEY UPDATE LEVEL=VALUES(LEVEL), SCORE=VALUES(SCORE), REASONS=VALUES(REASONS), STATUS='OPEN'");
    }

    public function getUserCopies($userId, $state = null, $publicOnly = false)
    {
        $where = 'c.USER_ID=' . intval($userId) . " AND i.STATUS='ACTIVE'";
        if ($state) $where .= " AND c.STATE='" . $this->escape($state) . "'";
        $mediaPath = $this->mediaPathSql('m', 'i');
        $copyFields = $publicOnly
            ? "c.ID_COPY,c.USER_ID,c.ITEM_ID,c.STATE,c.QUANTITY,c.COPY_NUMBER,c.COPY_IS_SIGNED,c.COPY_IS_DEDICATED,c.IS_PRICE_PUBLIC,IF(c.IS_PRICE_PUBLIC=1,c.PRICE,NULL) PRICE,IF(c.IS_PRICE_PUBLIC=1,c.CURRENCY,NULL) CURRENCY,c.CREATED_AT,c.UPDATED_AT"
            : 'c.*';
        return $this->rows($this->query("SELECT $copyFields, i.TITLE, i.TYPE_ID, i.IS_EXPLICIT, t.LABEL TYPE_LABEL, st.LABEL SUBTYPE_LABEL, $mediaPath PRIMARY_IMAGE
            FROM users_parabd c JOIN parabd_item i ON i.ID_ITEM=c.ITEM_ID JOIN parabd_type t ON t.ID_TYPE=i.TYPE_ID
            LEFT JOIN parabd_type st ON st.ID_TYPE=i.SUBTYPE_ID LEFT JOIN parabd_media m ON m.ITEM_ID=i.ID_ITEM AND m.IS_PRIMARY=1 AND m.IS_HIDDEN=0
            WHERE $where ORDER BY c.CREATED_AT DESC"));
    }

    public function saveCopy($userId, $input, $insideTransaction = false)
    {
        $connection = $this->connection();
        if (!$insideTransaction) Db_autocommit(false, $connection);
        try {
        $itemId = intval(isset($input['item_id']) ? $input['item_id'] : 0);
        if (!$this->one("SELECT ID_ITEM FROM parabd_item WHERE ID_ITEM=$itemId AND STATUS='ACTIVE'")) throw new ParabdException('NOT_FOUND', 'Objet Para-BD introuvable.');
        $copyId = intval(isset($input['copy_id']) ? $input['copy_id'] : 0);
        $state = isset($input['state']) && $input['state'] === 'WISHLIST' ? 'WISHLIST' : 'OWNED';
        $quantity = $this->copyQuantity(isset($input['quantity']) ? $input['quantity'] : 1);
        $copyNumber = trim(isset($input['copy_number']) ? $input['copy_number'] : '');
        if (mb_strlen($copyNumber, 'UTF-8') > 80) throw new ParabdException('VALIDATION_ERROR', 'Le numéro d’exemplaire est trop long.', array('copy_number' => '80 caractères maximum.'));
        $condition = strtoupper(trim(isset($input['condition_code']) ? $input['condition_code'] : 'UNKNOWN'));
        if (!in_array($condition, array('UNKNOWN','MINT','NEAR_MINT','VERY_GOOD','GOOD','FAIR','POOR'), true)) throw new ParabdException('VALIDATION_ERROR', 'État de conservation invalide.', array('condition_code' => 'Valeur invalide.'));
        $purchaseDate = $this->copyDate(isset($input['purchase_date']) ? $input['purchase_date'] : '');
        $price = $this->decimal(isset($input['price']) ? $input['price'] : null);
        $currency = strtoupper(trim(isset($input['currency']) ? $input['currency'] : 'EUR'));
        if ($price !== null && !preg_match('/^[A-Z]{3}$/', $currency)) throw new ParabdException('VALIDATION_ERROR', 'Devise ISO-3 invalide.');
        $seller = trim(isset($input['seller']) ? $input['seller'] : '');
        if (mb_strlen($seller, 'UTF-8') > 255) throw new ParabdException('VALIDATION_ERROR', 'Le nom du vendeur est trop long.', array('seller' => '255 caractères maximum.'));
        $fields = "STATE='$state',QUANTITY=$quantity,COPY_NUMBER=" . $this->sqlValue($copyNumber) . ",CONDITION_CODE='" . $this->escape($condition) . "',COPY_IS_SIGNED=" . $this->sqlValue($this->tri(isset($input['copy_is_signed']) ? $input['copy_is_signed'] : '')) . ",COPY_IS_DEDICATED=" . $this->sqlValue($this->tri(isset($input['copy_is_dedicated']) ? $input['copy_is_dedicated'] : '')) . ",HAS_BOX=" . $this->sqlValue($this->tri(isset($input['has_box']) ? $input['has_box'] : '')) . ",HAS_CERTIFICATE=" . $this->sqlValue($this->tri(isset($input['copy_has_certificate']) ? $input['copy_has_certificate'] : '')) . ",IS_GIFT=" . (!empty($input['is_gift']) ? 1 : 0) . ",PURCHASE_DATE=" . $this->sqlValue($purchaseDate) . ",PRICE=" . $this->sqlValue($price) . ",CURRENCY=" . $this->sqlValue($price === null ? null : $currency) . ",IS_PRICE_PUBLIC=" . (!empty($input['is_price_public']) ? 1 : 0) . ",SELLER=" . $this->sqlValue($seller) . ",ESTIMATED_VALUE=" . $this->sqlValue($this->decimal(isset($input['estimated_value']) ? $input['estimated_value'] : null)) . ",PERSONAL_NOTES=" . $this->sqlValue(trim(isset($input['personal_notes']) ? $input['personal_notes'] : ''));
        if ($copyId) {
            $this->query("UPDATE users_parabd SET $fields WHERE ID_COPY=$copyId AND USER_ID=" . intval($userId));
            if (Db_affected_rows($this->connection()) === 0 && !$this->one("SELECT ID_COPY FROM users_parabd WHERE ID_COPY=$copyId AND USER_ID=" . intval($userId))) throw new ParabdException('NOT_FOUND', 'Exemplaire introuvable.');
            if (!$insideTransaction) { Db_commit($connection); Db_autocommit(true, $connection); }
            return $copyId;
        }
        $this->query("INSERT INTO users_parabd (USER_ID,ITEM_ID) VALUES (" . intval($userId) . ",$itemId)");
        $copyId = Db_insert_id($this->connection());
        $this->query("UPDATE users_parabd SET $fields WHERE ID_COPY=$copyId AND USER_ID=" . intval($userId));
        if (!$insideTransaction) { Db_commit($connection); Db_autocommit(true, $connection); }
        return intval($copyId);
        } catch (Throwable $error) {
            if (!$insideTransaction) { Db_rollback($connection); Db_autocommit(true, $connection); }
            throw $error;
        }
    }

    private function copyQuantity($value)
    {
        $value = trim((string) $value);
        if (!ctype_digit($value) || intval($value) < 1 || intval($value) > 65535) throw new ParabdException('VALIDATION_ERROR', 'La quantité doit être comprise entre 1 et 65 535.', array('quantity' => 'Quantité invalide.'));
        return intval($value);
    }

    private function copyDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') return null;
        if (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $value, $match) && checkdate(intval($match[2]), intval($match[3]), intval($match[1]))) return $value;
        if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $value, $match) && checkdate(intval($match[2]), intval($match[1]), intval($match[3]))) return $match[3] . '-' . $match[2] . '-' . $match[1];
        throw new ParabdException('VALIDATION_ERROR', 'La date d’achat est invalide.', array('purchase_date' => 'Format attendu : JJ/MM/AAAA.'));
    }

    public function removeCopy($userId, $copyId)
    {
        $this->query("DELETE FROM users_parabd WHERE ID_COPY=" . intval($copyId) . " AND USER_ID=" . intval($userId));
        if (Db_affected_rows($this->connection()) !== 1) throw new ParabdException('NOT_FOUND', 'Exemplaire introuvable.');
    }

    public function contribute($userId, $itemId, $baseRevision, $field, $value)
    {
        $this->requireCharter($userId);
        $allowed = array('TITLE','DESCRIPTION','MATERIAL','COLOR','WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G','SCALE','RELEASE_DATE','PRINT_RUN','IS_NUMBERED','IS_SIGNED','HAS_CERTIFICATE','IS_LIMITED','IS_EXPLICIT','MANUFACTURER','PUBLISHER','LICENSE_NAME','RANGE_NAME','UNIVERSE_NAME','TYPE_ID','SUBTYPE_ID');
        $deleteRelations = array('DELETE_AUTHOR' => array('parabd_item_author','AUTHOR_ID'), 'DELETE_SERIES' => array('parabd_item_series','SERIES_ID'), 'DELETE_TOME' => array('parabd_item_tome','TOME_ID'));
        if (!in_array($field, $allowed, true) && !isset($deleteRelations[$field])) throw new ParabdException('VALIDATION_ERROR', 'Champ de contribution invalide.');
        $connection = $this->connection(); Db_autocommit(false, $connection);
        try {
        $item = $this->one("SELECT * FROM parabd_item WHERE ID_ITEM=" . intval($itemId) . " AND STATUS='ACTIVE' FOR UPDATE");
        if (!$item) throw new ParabdException('NOT_FOUND', 'Objet Para-BD introuvable.');
        if (intval($item['REVISION_NO']) !== intval($baseRevision)) throw new ParabdException('REVISION_CONFLICT', 'La fiche a changé. Rechargez-la avant de contribuer.');
        if (isset($deleteRelations[$field])) {
            $relation = $deleteRelations[$field];
            if (!$this->one("SELECT 1 found FROM {$relation[0]} WHERE ITEM_ID=" . intval($itemId) . " AND {$relation[1]}=" . intval($value) . " LIMIT 1")) throw new ParabdException('NOT_FOUND', 'Lien Para-BD introuvable.');
            $patch = json_encode(array('_operation' => $field, '_id' => intval($value)), JSON_UNESCAPED_UNICODE);
            $this->query("INSERT INTO parabd_revision (ITEM_ID,AUTHOR_ID,BASE_REVISION_NO,PATCH_BEFORE,PATCH_AFTER,CHANGE_KIND,STATUS) VALUES (" . intval($itemId) . "," . intval($userId) . "," . intval($baseRevision) . ",'" . $this->escape($patch) . "','" . $this->escape($patch) . "','DELETE_LINK','PENDING')");
            $revisionId = intval(Db_insert_id($connection)); Db_commit($connection); Db_autocommit(true, $connection);
            return array('revision_id' => $revisionId, 'status' => 'PENDING');
        }
        $extraAfter = array();
        $extraBefore = array();
        if (in_array($field, array('WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G'), true)) $value = $this->decimal($value);
        elseif ($field === 'PRINT_RUN') $value = $this->positiveInt($value);
        elseif (in_array($field, array('IS_NUMBERED','IS_SIGNED','HAS_CERTIFICATE','IS_LIMITED','IS_EXPLICIT'), true)) $value = $this->tri($value);
        elseif ($field === 'RELEASE_DATE') {
            $parsed = self::parsePartialDate($value); $value = $parsed['date'];
            $extraBefore['DATE_PRECISION'] = $item['DATE_PRECISION']; $extraAfter['DATE_PRECISION'] = $parsed['precision'];
        } elseif ($field === 'TITLE') {
            if (trim($value) === '') throw new ParabdException('VALIDATION_ERROR', 'Le titre ne peut pas être vide.');
            $extraBefore['TITLE_NORMALIZED'] = $item['TITLE_NORMALIZED']; $extraAfter['TITLE_NORMALIZED'] = self::normalizeText($value);
        } elseif ($field === 'MANUFACTURER') {
            $extraBefore['MANUFACTURER_NORMALIZED'] = $item['MANUFACTURER_NORMALIZED']; $extraAfter['MANUFACTURER_NORMALIZED'] = self::normalizeText($value);
        }
        $before = $item[$field];
        $protected = in_array($field, array('TYPE_ID','SUBTYPE_ID'), true);
        $apply = !$protected && (($field === 'IS_EXPLICIT' && intval($value) === 1) || ($before === null || $before === '') || $this->isTrusted($userId));
        $kind = $protected ? 'TYPE_CHANGE' : 'UPDATE';
        $beforePatch = array_merge(array($field => $before), $extraBefore);
        $afterPatch = array_merge(array($field => $value), $extraAfter);
        $beforeJson = json_encode($beforePatch, JSON_UNESCAPED_UNICODE);
        $afterJson = json_encode($afterPatch, JSON_UNESCAPED_UNICODE);
        if ($apply) {
            $sets = array(); foreach ($afterPatch as $patchField => $patchValue) $sets[] = "`$patchField`=" . $this->sqlItemValue($patchField, $patchValue);
            $this->query("UPDATE parabd_item SET " . implode(',', $sets) . ",REVISION_NO=REVISION_NO+1,UPDATED_BY=" . intval($userId) . " WHERE ID_ITEM=" . intval($itemId));
        }
        $this->query("INSERT INTO parabd_revision (ITEM_ID,AUTHOR_ID,BASE_REVISION_NO,PATCH_BEFORE,PATCH_AFTER,CHANGE_KIND,STATUS,APPLIED_AT) VALUES (" . intval($itemId) . "," . intval($userId) . "," . intval($baseRevision) . ",'" . $this->escape($beforeJson) . "','" . $this->escape($afterJson) . "','$kind','" . ($apply ? 'APPLIED' : 'PENDING') . "'," . ($apply ? 'NOW()' : 'NULL') . ")");
        $revisionId = intval(Db_insert_id($connection)); Db_commit($connection); Db_autocommit(true, $connection);
        return array('revision_id' => $revisionId, 'status' => $apply ? 'APPLIED' : 'PENDING');
        } catch (Throwable $error) { Db_rollback($connection); Db_autocommit(true, $connection); throw $error; }
    }

    public function vote($userId, $revisionId, $vote)
    {
        $vote = strtoupper($vote);
        if (!in_array($vote, array('CONFIRM','CONTEST'), true)) throw new ParabdException('VALIDATION_ERROR', 'Vote invalide.');
        $connection = $this->connection(); Db_autocommit(false, $connection);
        try {
            $revision = $this->one("SELECT r.*,i.CREATED_BY FROM parabd_revision r JOIN parabd_item i ON i.ID_ITEM=r.ITEM_ID WHERE r.ID_REVISION=" . intval($revisionId) . " FOR UPDATE");
            if (!$revision) throw new ParabdException('NOT_FOUND', 'Contribution introuvable.');
            if (!in_array($revision['STATUS'], array('PENDING','APPLIED'), true)) throw new ParabdException('REVISION_CONFLICT', 'Les votes sont clos.');
            if (intval($revision['AUTHOR_ID']) === intval($userId) || intval($revision['CREATED_BY']) === intval($userId)) throw new ParabdException('VALIDATION_ERROR', 'Vous ne pouvez pas confirmer cette contribution.');
            $this->query("INSERT INTO parabd_revision_vote (REVISION_ID,USER_ID,VOTE) VALUES (" . intval($revisionId) . "," . intval($userId) . ",'$vote') ON DUPLICATE KEY UPDATE VOTE=VALUES(VOTE),CREATED_AT=NOW()");
            $counts = $this->one("SELECT SUM(VOTE='CONFIRM') confirms,SUM(VOTE='CONTEST') contests FROM parabd_revision_vote WHERE REVISION_ID=" . intval($revisionId));
            $status = $revision['STATUS'];
            if (intval($counts['contests']) > 0) {
                $status = 'CONFLICT';
                $this->query("UPDATE parabd_revision SET STATUS='CONFLICT' WHERE ID_REVISION=" . intval($revisionId));
            } elseif (intval($counts['confirms']) >= 2) {
                if ($revision['STATUS'] === 'PENDING') {
                    $this->applyRevision($revision, $userId);
                    $this->query("UPDATE parabd_revision SET APPLIED_AT=NOW() WHERE ID_REVISION=" . intval($revisionId));
                }
                $status = 'ACCEPTED';
                $this->query("UPDATE parabd_revision SET STATUS='ACCEPTED',VALIDATED_BY=" . intval($userId) . ",VALIDATED_AT=NOW() WHERE ID_REVISION=" . intval($revisionId));
            }
            Db_commit($connection); Db_autocommit(true, $connection);
            return array('status' => $status, 'confirms' => intval($counts['confirms']), 'contests' => intval($counts['contests']));
        } catch (Throwable $error) {
            Db_rollback($connection); Db_autocommit(true, $connection); throw $error;
        }
    }

    public function getRevisionsForItem($itemId)
    {
        return $this->rows($this->query("SELECT r.*,SUM(v.VOTE='CONFIRM') CONFIRMS,SUM(v.VOTE='CONTEST') CONTESTS
            FROM parabd_revision r LEFT JOIN parabd_revision_vote v ON v.REVISION_ID=r.ID_REVISION
            WHERE r.ITEM_ID=" . intval($itemId) . " AND r.STATUS IN ('PENDING','APPLIED') AND r.CHANGE_KIND<>'CREATE'
            GROUP BY r.ID_REVISION ORDER BY r.CREATED_AT DESC"));
    }

    private function applyRevision($revision, $validatorId)
    {
        $item = $this->one("SELECT REVISION_NO FROM parabd_item WHERE ID_ITEM=" . intval($revision['ITEM_ID']) . " FOR UPDATE");
        if (!$item || intval($item['REVISION_NO']) !== intval($revision['BASE_REVISION_NO'])) throw new ParabdException('REVISION_CONFLICT', 'La fiche a changé depuis cette proposition.');
        $patch = json_decode($revision['PATCH_AFTER'], true);
        if (!is_array($patch) || !$patch) throw new RuntimeException('Patch de contribution invalide.');
        if (isset($patch['_operation'])) {
            $relations = array('DELETE_AUTHOR' => array('parabd_item_author','AUTHOR_ID'), 'DELETE_SERIES' => array('parabd_item_series','SERIES_ID'), 'DELETE_TOME' => array('parabd_item_tome','TOME_ID'));
            if (!isset($relations[$patch['_operation']])) throw new RuntimeException('Opération de lien Para-BD invalide.');
            $relation = $relations[$patch['_operation']];
            $this->query("DELETE FROM {$relation[0]} WHERE ITEM_ID=" . intval($revision['ITEM_ID']) . " AND {$relation[1]}=" . intval($patch['_id']));
            $this->query("UPDATE parabd_item SET REVISION_NO=REVISION_NO+1,UPDATED_BY=" . intval($validatorId) . " WHERE ID_ITEM=" . intval($revision['ITEM_ID']));
            return;
        }
        $allowed = array('TITLE','TITLE_NORMALIZED','DESCRIPTION','MATERIAL','COLOR','WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G','SCALE','RELEASE_DATE','DATE_PRECISION','PRINT_RUN','IS_NUMBERED','IS_SIGNED','HAS_CERTIFICATE','IS_LIMITED','IS_EXPLICIT','MANUFACTURER','MANUFACTURER_NORMALIZED','PUBLISHER','LICENSE_NAME','RANGE_NAME','UNIVERSE_NAME','TYPE_ID','SUBTYPE_ID');
        $sets = array();
        foreach ($patch as $field => $value) {
            if (!in_array($field, $allowed, true)) throw new RuntimeException('Champ de patch Para-BD invalide.');
            $sets[] = "`$field`=" . $this->sqlItemValue($field, $value);
        }
        $this->query("UPDATE parabd_item SET " . implode(',', $sets) . ",REVISION_NO=REVISION_NO+1,UPDATED_BY=" . intval($validatorId) . " WHERE ID_ITEM=" . intval($revision['ITEM_ID']));
    }

    public function report($userId, $targetType, $targetId, $reason, $details)
    {
        $targetType = strtoupper($targetType);
        if (!in_array($targetType, array('ITEM','MEDIA','REVISION'), true) || intval($targetId) < 1 || trim($reason) === '') throw new ParabdException('VALIDATION_ERROR', 'Signalement invalide.');
        $targets = array('ITEM' => array('parabd_item','ID_ITEM'), 'MEDIA' => array('parabd_media','ID_MEDIA'), 'REVISION' => array('parabd_revision','ID_REVISION'));
        $target = $targets[$targetType];
        if (!$this->one("SELECT {$target[1]} FROM {$target[0]} WHERE {$target[1]}=" . intval($targetId))) throw new ParabdException('NOT_FOUND', 'Cible du signalement introuvable.');
        $this->query("INSERT INTO parabd_report (REPORTER_ID,TARGET_TYPE,TARGET_ID,REASON,DETAILS) VALUES (" . intval($userId) . ",'$targetType'," . intval($targetId) . ",'" . $this->escape($reason) . "','" . $this->escape($details) . "')");
        return intval(Db_insert_id($this->connection()));
    }

    public function addMedia($userId, $itemId, $file, $mediaType, $visualUrl = '')
    {
        $this->requireCharter($userId);
        $mediaType = strtoupper($mediaType);
        if (!in_array($mediaType, array('GALLERY','CERTIFICATE','BOX','DETAIL'), true)) $mediaType = 'GALLERY';
        if (!$this->one("SELECT ID_ITEM FROM parabd_item WHERE ID_ITEM=" . intval($itemId) . " AND STATUS='ACTIVE'")) throw new ParabdException('NOT_FOUND', 'Objet Para-BD introuvable.');
        $this->consumeRate($userId, 'upload');
        $hasUpload = $file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK;
        $remoteFile = null;
        if (!$hasUpload) {
            if (trim($visualUrl) === '') throw new ParabdException('VALIDATION_ERROR', 'Choisissez un fichier ou indiquez une URL d’image.');
            $file = $this->downloadRemoteImage($visualUrl);
            $remoteFile = $file['tmp_name'];
        }
        $connection = $this->connection(); Db_autocommit(false, $connection); $path = null;
        try {
            $count = $this->one("SELECT COUNT(*) total FROM parabd_media WHERE ITEM_ID=" . intval($itemId));
            $image = $this->storeImage($file, intval($itemId), intval($count['total']) + 1); $path = $image['absolute_path'];
            $this->query("INSERT INTO parabd_media (ITEM_ID,MEDIA_TYPE,FILE_PATH,MIME_TYPE,WIDTH_PX,HEIGHT_PX,CREATED_BY) VALUES (" . intval($itemId) . ",'$mediaType','" . $this->escape($image['relative_path']) . "','" . $this->escape($image['mime']) . "'," . intval($image['width']) . "," . intval($image['height']) . "," . intval($userId) . ")");
            if (!$hasUpload) $this->addSource(intval($itemId), $userId, $visualUrl, 'Source du visuel');
            Db_commit($connection); Db_autocommit(true, $connection); if ($remoteFile && is_file($remoteFile)) @unlink($remoteFile); return intval(Db_insert_id($connection));
        } catch (Throwable $error) {
            Db_rollback($connection); Db_autocommit(true, $connection); if ($path && is_file($path)) @unlink($path); if ($remoteFile && is_file($remoteFile)) @unlink($remoteFile); throw $error;
        }
    }

    private function adminSnapshot($itemId)
    {
        $itemId = intval($itemId);
        $item = $this->one("SELECT TYPE_ID,SUBTYPE_ID,TITLE,DESCRIPTION,MATERIAL,COLOR,WIDTH_MM,HEIGHT_MM,DEPTH_MM,WEIGHT_G,SCALE,RELEASE_DATE,DATE_PRECISION,PRINT_RUN,IS_NUMBERED,IS_SIGNED,HAS_CERTIFICATE,IS_LIMITED,MANUFACTURER,PUBLISHER,LICENSE_NAME,RANGE_NAME,UNIVERSE_NAME,IS_EXPLICIT,STATUS,MERGED_INTO_ID FROM parabd_item WHERE ID_ITEM=$itemId");
        if (!$item) return null;
        $item['identifiers'] = $this->rows($this->query("SELECT SCHEME,ISSUER,VALUE FROM parabd_identifier WHERE ITEM_ID=$itemId ORDER BY ID_IDENTIFIER"));
        $item['authors'] = $this->rows($this->query("SELECT AUTHOR_ID,ROLE FROM parabd_item_author WHERE ITEM_ID=$itemId ORDER BY AUTHOR_ID,ROLE"));
        $item['series'] = $this->rows($this->query("SELECT SERIES_ID,RELATION_TYPE FROM parabd_item_series WHERE ITEM_ID=$itemId ORDER BY SERIES_ID,RELATION_TYPE"));
        $item['tomes'] = $this->rows($this->query("SELECT TOME_ID,RELATION_TYPE,PAGE_NO,PANEL_NO FROM parabd_item_tome WHERE ITEM_ID=$itemId ORDER BY TOME_ID,RELATION_TYPE"));
        $item['sources'] = $this->rows($this->query("SELECT SOURCE_TYPE,URL,LABEL,NOTES FROM parabd_source WHERE ITEM_ID=$itemId ORDER BY ID_SOURCE"));
        $item['media'] = $this->rows($this->query("SELECT ID_MEDIA,MEDIA_TYPE,FILE_PATH,IS_PRIMARY,IS_HIDDEN,SORT_ORDER FROM parabd_media WHERE ITEM_ID=$itemId ORDER BY ID_MEDIA"));
        return $item;
    }

    public function adminUpdateItem($adminId, $itemId, $input, $file = null)
    {
        $itemId = intval($itemId);
        $data = $this->cleanInput($input);
        $identifiers = $this->identifiersFromInput($input);
        $relations = $this->relationsFromInput($input);
        $sources = $this->sourcesFromInput($input);
        $status = strtoupper(trim(isset($input['status']) ? $input['status'] : 'ACTIVE'));
        if (!in_array($status, array('ACTIVE','HIDDEN'), true)) throw new ParabdException('VALIDATION_ERROR', 'Statut de fiche invalide.');

        $visualUrl = trim(isset($input['visual_url']) ? $input['visual_url'] : '');
        $hasUpload = $file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK;
        $remoteFile = null;
        if (!$hasUpload && $visualUrl !== '') {
            $file = $this->downloadRemoteImage($visualUrl);
            $remoteFile = $file['tmp_name'];
        }

        $connection = $this->connection();
        $writtenFile = null;
        Db_autocommit(false, $connection);
        try {
            $locked = $this->one("SELECT * FROM parabd_item WHERE ID_ITEM=$itemId FOR UPDATE");
            if (!$locked) throw new ParabdException('NOT_FOUND', 'Objet Para-BD introuvable.');
            if ($locked['STATUS'] === 'MERGED') throw new ParabdException('VALIDATION_ERROR', 'Une fiche fusionnée est consultable mais ne peut plus être modifiée.');
            $before = $this->adminSnapshot($itemId);

            $sets = array();
            foreach ($data as $field => $value) $sets[] = "`$field`=" . $this->sqlItemValue($field, $value);
            $sets[] = "STATUS='$status'";
            $this->query("UPDATE parabd_item SET " . implode(',', $sets) . " WHERE ID_ITEM=$itemId");

            $this->query("DELETE FROM parabd_identifier WHERE ITEM_ID=$itemId");
            foreach ($identifiers as $identifier) $this->query("INSERT INTO parabd_identifier (ITEM_ID,SCHEME,ISSUER,ISSUER_NORMALIZED,VALUE,VALUE_NORMALIZED,CREATED_BY) VALUES
                ($itemId,'" . $this->escape($identifier['scheme']) . "'," . $this->sqlValue($identifier['issuer']) . ",'" . $this->escape($identifier['issuer_normalized']) . "','" . $this->escape($identifier['value']) . "','" . $this->escape($identifier['value_normalized']) . "'," . intval($adminId) . ")");

            foreach (array('parabd_item_author','parabd_item_series','parabd_item_tome') as $table) $this->query("DELETE FROM $table WHERE ITEM_ID=$itemId");
            $this->insertRelations($itemId, $adminId, $relations);

            $this->query("DELETE FROM parabd_source WHERE ITEM_ID=$itemId");
            foreach ($sources as $source) $this->addSource($itemId, $adminId, $source['url'], $source['label'], $source['notes']);

            $mediaRows = $this->rows($this->query("SELECT ID_MEDIA,IS_PRIMARY FROM parabd_media WHERE ITEM_ID=$itemId ORDER BY ID_MEDIA"));
            $mediaIds = array_map(function ($row) { return intval($row['ID_MEDIA']); }, $mediaRows);
            $hidden = isset($input['media_hidden']) && is_array($input['media_hidden']) ? array_map('intval', array_keys($input['media_hidden'])) : array();
            foreach ($mediaIds as $mediaId) $this->query("UPDATE parabd_media SET IS_HIDDEN=" . (in_array($mediaId, $hidden, true) ? '1' : '0') . " WHERE ID_MEDIA=$mediaId AND ITEM_ID=$itemId");

            $primaryMediaId = isset($input['primary_media_id']) ? intval($input['primary_media_id']) : 0;
            if (!$primaryMediaId) foreach ($mediaRows as $row) if ($row['IS_PRIMARY']) { $primaryMediaId = intval($row['ID_MEDIA']); break; }
            if ($hasUpload || $visualUrl !== '') {
                $count = $this->one("SELECT COUNT(*) total FROM parabd_media WHERE ITEM_ID=$itemId");
                $image = $this->storeImage($file, $itemId, intval($count['total']) + 1);
                $writtenFile = $image['absolute_path'];
                $mediaType = strtoupper(trim(isset($input['new_media_type']) ? $input['new_media_type'] : 'GALLERY'));
                if (!in_array($mediaType, array('GALLERY','CERTIFICATE','BOX','DETAIL'), true)) $mediaType = 'GALLERY';
                $this->query("INSERT INTO parabd_media (ITEM_ID,MEDIA_TYPE,FILE_PATH,MIME_TYPE,WIDTH_PX,HEIGHT_PX,CREATED_BY) VALUES ($itemId,'$mediaType','" . $this->escape($image['relative_path']) . "','" . $this->escape($image['mime']) . "'," . intval($image['width']) . ',' . intval($image['height']) . ',' . intval($adminId) . ')');
                $newMediaId = intval(Db_insert_id($connection));
                if (!empty($input['new_media_primary']) || !$primaryMediaId) $primaryMediaId = $newMediaId;
                if ($visualUrl !== '' && !in_array($visualUrl, array_column($sources, 'url'), true)) $this->addSource($itemId, $adminId, $visualUrl, 'Source du nouveau visuel');
            }
            if (!$primaryMediaId || (!$this->one("SELECT ID_MEDIA FROM parabd_media WHERE ID_MEDIA=$primaryMediaId AND ITEM_ID=$itemId AND IS_HIDDEN=0"))) throw new ParabdException('VALIDATION_ERROR', 'Choisissez un visuel principal visible.');
            $this->query("UPDATE parabd_media SET IS_PRIMARY=0,MEDIA_TYPE=IF(MEDIA_TYPE='PRIMARY','GALLERY',MEDIA_TYPE) WHERE ITEM_ID=$itemId");
            $this->query("UPDATE parabd_media SET IS_PRIMARY=1,MEDIA_TYPE='PRIMARY' WHERE ID_MEDIA=$primaryMediaId AND ITEM_ID=$itemId");

            $after = $this->adminSnapshot($itemId);
            $beforeJson = json_encode($before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $afterJson = json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($beforeJson !== $afterJson) {
                $baseRevision = intval($locked['REVISION_NO']);
                $this->query("UPDATE parabd_item SET REVISION_NO=REVISION_NO+1,UPDATED_BY=" . intval($adminId) . " WHERE ID_ITEM=$itemId");
                $this->query("INSERT INTO parabd_revision (ITEM_ID,AUTHOR_ID,BASE_REVISION_NO,PATCH_BEFORE,PATCH_AFTER,CHANGE_KIND,STATUS,APPLIED_AT,VALIDATED_BY,VALIDATED_AT) VALUES ($itemId," . intval($adminId) . ",$baseRevision,'" . $this->escape($beforeJson) . "','" . $this->escape($afterJson) . "','UPDATE','ACCEPTED',NOW()," . intval($adminId) . ",NOW())");
            }
            Db_commit($connection); Db_autocommit(true, $connection);
            if ($remoteFile && is_file($remoteFile)) @unlink($remoteFile);
            return array('item_id' => $itemId, 'changed' => $beforeJson !== $afterJson);
        } catch (Throwable $error) {
            Db_rollback($connection); Db_autocommit(true, $connection);
            if ($writtenFile && is_file($writtenFile)) @unlink($writtenFile);
            if ($remoteFile && is_file($remoteFile)) @unlink($remoteFile);
            if ($connection->errno === 1062 || strpos($error->getMessage(), 'Duplicate entry') !== false) throw new ParabdException('DUPLICATE_EXACT', 'Cet identifiant appartient déjà à une autre fiche.');
            throw $error;
        }
    }

    public function adminQueues()
    {
        return array(
            'duplicates' => $this->rows($this->query("SELECT d.*,a.TITLE TITLE_LOW,b.TITLE TITLE_HIGH FROM parabd_duplicate d JOIN parabd_item a ON a.ID_ITEM=d.ITEM_ID_LOW JOIN parabd_item b ON b.ID_ITEM=d.ITEM_ID_HIGH WHERE d.STATUS='OPEN' ORDER BY FIELD(d.LEVEL,'CERTAIN','STRONG','POSSIBLE'),d.SCORE DESC")),
            'reports' => $this->rows($this->query("SELECT * FROM parabd_report WHERE STATUS='OPEN' ORDER BY CREATED_AT")),
            'conflicts' => $this->rows($this->query("SELECT r.*,i.TITLE FROM parabd_revision r JOIN parabd_item i ON i.ID_ITEM=r.ITEM_ID WHERE r.STATUS='CONFLICT' ORDER BY r.CREATED_AT")),
            'incomplete' => $this->rows($this->query("SELECT i.* FROM parabd_item i LEFT JOIN parabd_media m ON m.ITEM_ID=i.ID_ITEM AND m.IS_PRIMARY=1 WHERE i.STATUS='ACTIVE' AND (i.DESCRIPTION IS NULL OR i.DESCRIPTION='' OR m.ID_MEDIA IS NULL) GROUP BY i.ID_ITEM ORDER BY i.UPDATED_AT")),
            'hidden' => $this->rows($this->query("SELECT * FROM parabd_item WHERE STATUS='HIDDEN' ORDER BY UPDATED_AT DESC"))
        );
    }

    public function resolveDuplicate($adminId, $duplicateId, $status)
    {
        if (!in_array($status, array('IGNORED','COLLISION'), true)) throw new ParabdException('VALIDATION_ERROR', 'Résolution de doublon invalide.');
        $this->query("UPDATE parabd_duplicate SET STATUS='$status',RESOLVED_BY=" . intval($adminId) . ",RESOLVED_AT=NOW() WHERE ID_DUPLICATE=" . intval($duplicateId) . " AND STATUS='OPEN'");
        if (Db_affected_rows($this->connection()) !== 1) throw new ParabdException('NOT_FOUND', 'Doublon introuvable.');
    }

    public function moderateItem($adminId, $itemId, $status)
    {
        if (!in_array($status, array('ACTIVE','HIDDEN'), true)) throw new ParabdException('VALIDATION_ERROR', 'Statut invalide.');
        $this->query("UPDATE parabd_item SET STATUS='$status',UPDATED_BY=" . intval($adminId) . ",REVISION_NO=REVISION_NO+1 WHERE ID_ITEM=" . intval($itemId));
        if (Db_affected_rows($this->connection()) !== 1) throw new ParabdException('NOT_FOUND', 'Objet introuvable.');
    }

    public function resolveReport($adminId, $reportId, $status)
    {
        if (!in_array($status, array('RESOLVED','DISMISSED'), true)) throw new ParabdException('VALIDATION_ERROR', 'Résolution invalide.');
        $this->query("UPDATE parabd_report SET STATUS='$status',RESOLVED_BY=" . intval($adminId) . ",RESOLVED_AT=NOW() WHERE ID_REPORT=" . intval($reportId) . " AND STATUS='OPEN'");
    }

    public function resolveRevision($adminId, $revisionId, $accept)
    {
        $connection = $this->connection(); Db_autocommit(false, $connection);
        try {
            $revision = $this->one("SELECT * FROM parabd_revision WHERE ID_REVISION=" . intval($revisionId) . " FOR UPDATE");
            if (!$revision || !in_array($revision['STATUS'], array('PENDING','CONFLICT'), true)) throw new ParabdException('NOT_FOUND', 'Contribution à résoudre introuvable.');
            if ($accept && empty($revision['APPLIED_AT'])) {
                $this->applyRevision($revision, $adminId);
                $this->query("UPDATE parabd_revision SET APPLIED_AT=NOW() WHERE ID_REVISION=" . intval($revisionId));
            } elseif (!$accept && !empty($revision['APPLIED_AT']) && $revision['CHANGE_KIND'] !== 'CREATE') {
                $this->revertRevision($revision, $adminId);
            }
            $this->query("UPDATE parabd_revision SET STATUS='" . ($accept ? 'ACCEPTED' : 'REJECTED') . "',VALIDATED_BY=" . intval($adminId) . ",VALIDATED_AT=NOW() WHERE ID_REVISION=" . intval($revisionId));
            Db_commit($connection); Db_autocommit(true, $connection);
        } catch (Throwable $error) { Db_rollback($connection); Db_autocommit(true, $connection); throw $error; }
    }

    private function revertRevision($revision, $adminId)
    {
        $patch = json_decode($revision['PATCH_BEFORE'], true);
        if (!is_array($patch) || !$patch || isset($patch['_operation'])) return;
        $allowed = array('TITLE','TITLE_NORMALIZED','DESCRIPTION','MATERIAL','COLOR','WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G','SCALE','RELEASE_DATE','DATE_PRECISION','PRINT_RUN','IS_NUMBERED','IS_SIGNED','HAS_CERTIFICATE','IS_LIMITED','IS_EXPLICIT','MANUFACTURER','MANUFACTURER_NORMALIZED','PUBLISHER','LICENSE_NAME','RANGE_NAME','UNIVERSE_NAME','TYPE_ID','SUBTYPE_ID');
        $sets = array();
        foreach ($patch as $field => $value) {
            if (!in_array($field, $allowed, true)) throw new RuntimeException('Champ de restauration Para-BD invalide.');
            $sets[] = "`$field`=" . $this->sqlItemValue($field, $value);
        }
        $this->query("UPDATE parabd_item SET " . implode(',', $sets) . ",REVISION_NO=REVISION_NO+1,UPDATED_BY=" . intval($adminId) . " WHERE ID_ITEM=" . intval($revision['ITEM_ID']));
    }

    public function merge($adminId, $sourceId, $targetId, $preferredFields = array(), $primaryMediaId = 0)
    {
        if ($sourceId === $targetId) throw new ParabdException('VALIDATION_ERROR', 'Source et cible doivent être différentes.');
        $connection = $this->connection(); Db_autocommit(false, $connection);
        try {
            $low = min($sourceId, $targetId); $high = max($sourceId, $targetId);
            $locked = $this->rows($this->query("SELECT * FROM parabd_item WHERE ID_ITEM IN (" . intval($low) . ',' . intval($high) . ") FOR UPDATE"));
            if (count($locked) !== 2) throw new ParabdException('NOT_FOUND', 'Une fiche à fusionner est introuvable.');
            foreach ($preferredFields as $field => $value) {
                if (in_array($field, array('TITLE','DESCRIPTION','MATERIAL','COLOR','WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G','SCALE','RELEASE_DATE','DATE_PRECISION','PRINT_RUN','IS_EXPLICIT','MANUFACTURER','PUBLISHER','LICENSE_NAME','RANGE_NAME','UNIVERSE_NAME'), true)) {
                    $this->query("UPDATE parabd_item SET `$field`=" . $this->sqlItemValue($field, $value) . " WHERE ID_ITEM=" . intval($targetId));
                }
            }
            foreach (array(array('parabd_item_author','AUTHOR_ID,ROLE'),array('parabd_item_series','SERIES_ID,RELATION_TYPE'),array('parabd_item_tome','TOME_ID,RELATION_TYPE,PAGE_NO,PANEL_NO')) as $relation) {
                $this->query("INSERT IGNORE INTO {$relation[0]} (ITEM_ID,{$relation[1]},CREATED_BY,CREATED_AT) SELECT " . intval($targetId) . ",{$relation[1]},CREATED_BY,CREATED_AT FROM {$relation[0]} WHERE ITEM_ID=" . intval($sourceId));
                $this->query("DELETE FROM {$relation[0]} WHERE ITEM_ID=" . intval($sourceId));
            }
            foreach (array('parabd_identifier','parabd_media','parabd_source','parabd_revision','users_parabd') as $table) $this->query("UPDATE $table SET ITEM_ID=" . intval($targetId) . " WHERE ITEM_ID=" . intval($sourceId));
            if ($primaryMediaId) {
                $this->query("UPDATE parabd_media SET IS_PRIMARY=0,MEDIA_TYPE=IF(MEDIA_TYPE='PRIMARY','GALLERY',MEDIA_TYPE) WHERE ITEM_ID=" . intval($targetId));
                $this->query("UPDATE parabd_media SET IS_PRIMARY=1,MEDIA_TYPE='PRIMARY' WHERE ID_MEDIA=" . intval($primaryMediaId) . " AND ITEM_ID=" . intval($targetId));
            }
            $this->query("UPDATE parabd_item SET STATUS='MERGED',MERGED_INTO_ID=" . intval($targetId) . ",UPDATED_BY=" . intval($adminId) . ",REVISION_NO=REVISION_NO+1 WHERE ID_ITEM=" . intval($sourceId));
            $this->query("UPDATE parabd_item SET REVISION_NO=REVISION_NO+1,UPDATED_BY=" . intval($adminId) . " WHERE ID_ITEM=" . intval($targetId));
            $this->query("UPDATE parabd_duplicate SET STATUS='MERGED',RESOLVED_BY=" . intval($adminId) . ",RESOLVED_AT=NOW() WHERE ITEM_ID_LOW=" . intval($sourceId) . " OR ITEM_ID_HIGH=" . intval($sourceId));
            $audit = json_encode(array('source_id' => intval($sourceId), 'target_id' => intval($targetId)), JSON_UNESCAPED_UNICODE);
            $this->query("INSERT INTO parabd_revision (ITEM_ID,AUTHOR_ID,BASE_REVISION_NO,PATCH_AFTER,CHANGE_KIND,STATUS,APPLIED_AT,VALIDATED_BY,VALIDATED_AT) SELECT ID_ITEM," . intval($adminId) . ",REVISION_NO-1,'" . $this->escape($audit) . "','MERGE','ACCEPTED',NOW()," . intval($adminId) . ",NOW() FROM parabd_item WHERE ID_ITEM=" . intval($targetId));
            $target = $this->getItem($targetId, true);
            if ($target) {
                $target['identifiers'] = array_map(function ($identifier) { return array('scheme' => $identifier['SCHEME'], 'issuer' => $identifier['ISSUER'], 'value' => $identifier['VALUE']); }, $target['identifiers']);
                $target['ID_ITEM'] = intval($targetId);
                foreach ($this->searchDuplicates($target) as $duplicate) $this->recordDuplicate($targetId, intval($duplicate['ID_ITEM']), $duplicate);
            }
            Db_commit($connection); Db_autocommit(true, $connection);
        } catch (Throwable $error) { Db_rollback($connection); Db_autocommit(true, $connection); throw $error; }
    }
}
