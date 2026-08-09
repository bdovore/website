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

    public function getCatalogue($search = '', $limit = 60)
    {
        $where = "i.STATUS='ACTIVE'";
        if (trim($search) !== '') $where .= " AND i.TITLE_NORMALIZED LIKE '%" . $this->escape(self::normalizeText($search)) . "%'";
        return $this->rows($this->query("SELECT i.*, t.LABEL TYPE_LABEL, st.LABEL SUBTYPE_LABEL, m.FILE_PATH PRIMARY_IMAGE
            FROM parabd_item i JOIN parabd_type t ON t.ID_TYPE=i.TYPE_ID
            LEFT JOIN parabd_type st ON st.ID_TYPE=i.SUBTYPE_ID
            LEFT JOIN parabd_media m ON m.ITEM_ID=i.ID_ITEM AND m.IS_PRIMARY=1 AND m.IS_HIDDEN=0
            WHERE $where ORDER BY i.UPDATED_AT DESC LIMIT " . max(1, min(200, intval($limit)))));
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
        $item['sources'] = $this->rows($this->query("SELECT * FROM parabd_source WHERE ITEM_ID=" . intval($itemId) . " ORDER BY ID_SOURCE"));
        $item['authors'] = $this->rows($this->query("SELECT l.*, COALESCE(NULLIF(a.PSEUDO,''), CONCAT_WS(' ',a.PRENOM,a.NOM)) LABEL FROM parabd_item_author l LEFT JOIN bd_auteur a ON a.ID_AUTEUR=l.AUTHOR_ID WHERE l.ITEM_ID=" . intval($itemId)));
        $item['series'] = $this->rows($this->query("SELECT l.*, s.NOM LABEL FROM parabd_item_series l LEFT JOIN bd_serie s ON s.ID_SERIE=l.SERIES_ID WHERE l.ITEM_ID=" . intval($itemId)));
        $item['tomes'] = $this->rows($this->query("SELECT l.*, t.TITRE LABEL FROM parabd_item_tome l LEFT JOIN bd_tome t ON t.ID_TOME=l.TOME_ID WHERE l.ITEM_ID=" . intval($itemId)));
        return $item;
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
        $row = $this->one("SELECT FILE_PATH FROM parabd_media WHERE ITEM_ID=$itemId AND IS_PRIMARY=1 AND IS_HIDDEN=0 LIMIT 1");
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
            'UNIVERSE_NAME' => trim(isset($input['universe_name']) ? $input['universe_name'] : '')
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
        } elseif (!empty($input['identifier_scheme']) || !empty($input['identifier_value'])) {
            $identifiers[] = array('scheme' => isset($input['identifier_scheme']) ? $input['identifier_scheme'] : '', 'issuer' => isset($input['identifier_issuer']) ? $input['identifier_issuer'] : '', 'value' => isset($input['identifier_value']) ? $input['identifier_value'] : '');
        }
        $allowed = array('EAN13','UPCA','ISBN10','ISBN13','MANUFACTURER_REF','PUBLISHER_REF','EXTERNAL_DB');
        $clean = array();
        foreach ($identifiers as $identifier) {
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

    public function createItem($userId, $input, $file)
    {
        $this->requireCharter($userId);
        if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) throw new ParabdException('VALIDATION_ERROR', 'Un visuel principal est obligatoire.', array('visual' => 'Visuel obligatoire.'));
        $data = $this->cleanInput($input);
        $identifiers = $this->identifiersFromInput($input);
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
            $this->insertRelations($itemId, $userId, $input);
            $image = $this->storeImage($file, $itemId, 1);
            $writtenFile = $image['absolute_path'];
            $this->query("INSERT INTO parabd_media (ITEM_ID,MEDIA_TYPE,FILE_PATH,MIME_TYPE,WIDTH_PX,HEIGHT_PX,IS_PRIMARY,CREATED_BY) VALUES
                ($itemId,'PRIMARY','" . $this->escape($image['relative_path']) . "','" . $this->escape($image['mime']) . "'," . intval($image['width']) . "," . intval($image['height']) . ",1," . intval($userId) . ")");
            if (!empty($input['source_url'])) $this->addSource($itemId, $userId, $input['source_url'], isset($input['source_label']) ? $input['source_label'] : '');
            $snapshot = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $this->query("INSERT INTO parabd_revision (ITEM_ID,AUTHOR_ID,BASE_REVISION_NO,PATCH_AFTER,CHANGE_KIND,STATUS,APPLIED_AT) VALUES ($itemId," . intval($userId) . ",0,'" . $this->escape($snapshot) . "','CREATE','APPLIED',NOW())");
            $copyId = null;
            $collectionAction = isset($input['collection_action']) ? $input['collection_action'] : 'none';
            if (in_array($collectionAction, array('OWNED','WISHLIST'), true)) $copyId = $this->saveCopy($userId, array_merge($input, array('item_id' => $itemId, 'state' => $collectionAction)), true);
            foreach ($duplicates as $duplicate) $this->recordDuplicate($itemId, intval($duplicate['ID_ITEM']), $duplicate);
            Db_commit($connection);
            Db_autocommit(true, $connection);
            return array('item_id' => intval($itemId), 'copy_id' => $copyId, 'duplicates' => $duplicates);
        } catch (Throwable $error) {
            Db_rollback($connection);
            Db_autocommit(true, $connection);
            if ($writtenFile && is_file($writtenFile)) @unlink($writtenFile);
            if ($error instanceof ParabdException) throw $error;
            if ($connection->errno === 1062 || strpos($error->getMessage(), 'Duplicate entry') !== false) throw new ParabdException('DUPLICATE_EXACT', 'Un identifiant identique vient d’être créé. Relancez la recherche.');
            throw $error;
        }
    }

    private function insertRelations($itemId, $userId, $input)
    {
        if (!empty($input['author_id'])) {
            if (!$this->one("SELECT ID_AUTEUR FROM bd_auteur WHERE ID_AUTEUR=" . intval($input['author_id']))) throw new ParabdException('VALIDATION_ERROR', 'Auteur inconnu.');
            $this->query("INSERT INTO parabd_item_author (ITEM_ID,AUTHOR_ID,ROLE,CREATED_BY) VALUES ($itemId," . intval($input['author_id']) . ",'" . $this->escape(isset($input['author_role']) ? $input['author_role'] : 'ARTIST') . "'," . intval($userId) . ")");
        }
        if (!empty($input['series_id'])) {
            if (!$this->one("SELECT ID_SERIE FROM bd_serie WHERE ID_SERIE=" . intval($input['series_id']))) throw new ParabdException('VALIDATION_ERROR', 'Série inconnue.');
            $this->query("INSERT INTO parabd_item_series (ITEM_ID,SERIES_ID,RELATION_TYPE,CREATED_BY) VALUES ($itemId," . intval($input['series_id']) . ",'RELATED'," . intval($userId) . ")");
        }
        if (!empty($input['tome_id'])) {
            if (!$this->one("SELECT ID_TOME FROM bd_tome WHERE ID_TOME=" . intval($input['tome_id']))) throw new ParabdException('VALIDATION_ERROR', 'Album inconnu.');
            $this->query("INSERT INTO parabd_item_tome (ITEM_ID,TOME_ID,RELATION_TYPE,PAGE_NO,PANEL_NO,CREATED_BY) VALUES ($itemId," . intval($input['tome_id']) . ",'RELATED'," . $this->sqlValue($this->positiveInt(isset($input['page_no']) ? $input['page_no'] : null)) . "," . $this->sqlValue($this->positiveInt(isset($input['panel_no']) ? $input['panel_no'] : null)) . "," . intval($userId) . ")");
        }
    }

    private function addSource($itemId, $userId, $url, $label)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array(strtolower(parse_url($url, PHP_URL_SCHEME)), array('http','https'), true)) throw new ParabdException('VALIDATION_ERROR', 'URL source invalide.');
        $this->query("INSERT INTO parabd_source (ITEM_ID,SOURCE_TYPE,URL,LABEL,CREATED_BY) VALUES ($itemId,'URL','" . $this->escape($url) . "','" . $this->escape($label) . "'," . intval($userId) . ")");
    }

    public function storeImage($file, $itemId, $sequence)
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name']) && PHP_SAPI !== 'cli') throw new ParabdException('VALIDATION_ERROR', 'Fichier uploadé invalide.', array('visual' => 'Upload invalide.'));
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
        if ($publicOnly) $where .= ' AND c.IS_PUBLIC=1';
        $copyFields = $publicOnly
            ? "c.ID_COPY,c.USER_ID,c.ITEM_ID,c.STATE,c.QUANTITY,c.COPY_NUMBER,c.IS_PUBLIC,c.IS_PRICE_PUBLIC,IF(c.IS_PRICE_PUBLIC=1,c.PRICE,NULL) PRICE,IF(c.IS_PRICE_PUBLIC=1,c.CURRENCY,NULL) CURRENCY,c.CREATED_AT,c.UPDATED_AT"
            : 'c.*';
        return $this->rows($this->query("SELECT $copyFields, i.TITLE, i.TYPE_ID, t.LABEL TYPE_LABEL, st.LABEL SUBTYPE_LABEL, m.FILE_PATH PRIMARY_IMAGE
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
        $price = $this->decimal(isset($input['price']) ? $input['price'] : null);
        $currency = strtoupper(trim(isset($input['currency']) ? $input['currency'] : 'EUR'));
        if ($price !== null && !preg_match('/^[A-Z]{3}$/', $currency)) throw new ParabdException('VALIDATION_ERROR', 'Devise ISO-3 invalide.');
        $fields = "STATE='$state',QUANTITY=" . max(1, intval(isset($input['quantity']) ? $input['quantity'] : 1)) . ",COPY_NUMBER=" . $this->sqlValue(trim(isset($input['copy_number']) ? $input['copy_number'] : '')) . ",PRICE=" . $this->sqlValue($price) . ",CURRENCY=" . $this->sqlValue($price === null ? null : $currency) . ",IS_PRICE_PUBLIC=" . (!empty($input['is_price_public']) ? 1 : 0) . ",IS_PUBLIC=" . (isset($input['is_public']) && !$input['is_public'] ? 0 : 1) . ",SELLER=" . $this->sqlValue(trim(isset($input['seller']) ? $input['seller'] : '')) . ",ESTIMATED_VALUE=" . $this->sqlValue($this->decimal(isset($input['estimated_value']) ? $input['estimated_value'] : null)) . ",PERSONAL_NOTES=" . $this->sqlValue(trim(isset($input['personal_notes']) ? $input['personal_notes'] : ''));
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

    public function removeCopy($userId, $copyId)
    {
        $this->query("DELETE FROM users_parabd WHERE ID_COPY=" . intval($copyId) . " AND USER_ID=" . intval($userId));
        if (Db_affected_rows($this->connection()) !== 1) throw new ParabdException('NOT_FOUND', 'Exemplaire introuvable.');
    }

    public function contribute($userId, $itemId, $baseRevision, $field, $value)
    {
        $this->requireCharter($userId);
        $allowed = array('TITLE','DESCRIPTION','MATERIAL','COLOR','WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G','SCALE','RELEASE_DATE','PRINT_RUN','IS_NUMBERED','IS_SIGNED','HAS_CERTIFICATE','IS_LIMITED','MANUFACTURER','PUBLISHER','LICENSE_NAME','RANGE_NAME','UNIVERSE_NAME','TYPE_ID','SUBTYPE_ID');
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
        elseif (in_array($field, array('IS_NUMBERED','IS_SIGNED','HAS_CERTIFICATE','IS_LIMITED'), true)) $value = $this->tri($value);
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
        $apply = !$protected && (($before === null || $before === '') || $this->isTrusted($userId));
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
            WHERE r.ITEM_ID=" . intval($itemId) . " AND r.STATUS IN ('PENDING','APPLIED')
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
        $allowed = array('TITLE','TITLE_NORMALIZED','DESCRIPTION','MATERIAL','COLOR','WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G','SCALE','RELEASE_DATE','DATE_PRECISION','PRINT_RUN','IS_NUMBERED','IS_SIGNED','HAS_CERTIFICATE','IS_LIMITED','MANUFACTURER','MANUFACTURER_NORMALIZED','PUBLISHER','LICENSE_NAME','RANGE_NAME','UNIVERSE_NAME','TYPE_ID','SUBTYPE_ID');
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

    public function addMedia($userId, $itemId, $file, $mediaType)
    {
        $this->requireCharter($userId);
        $mediaType = strtoupper($mediaType);
        if (!in_array($mediaType, array('GALLERY','CERTIFICATE','BOX','DETAIL'), true)) $mediaType = 'GALLERY';
        if (!$this->one("SELECT ID_ITEM FROM parabd_item WHERE ID_ITEM=" . intval($itemId) . " AND STATUS='ACTIVE'")) throw new ParabdException('NOT_FOUND', 'Objet Para-BD introuvable.');
        $this->consumeRate($userId, 'upload');
        $connection = $this->connection(); Db_autocommit(false, $connection); $path = null;
        try {
            $count = $this->one("SELECT COUNT(*) total FROM parabd_media WHERE ITEM_ID=" . intval($itemId));
            $image = $this->storeImage($file, intval($itemId), intval($count['total']) + 1); $path = $image['absolute_path'];
            $this->query("INSERT INTO parabd_media (ITEM_ID,MEDIA_TYPE,FILE_PATH,MIME_TYPE,WIDTH_PX,HEIGHT_PX,CREATED_BY) VALUES (" . intval($itemId) . ",'$mediaType','" . $this->escape($image['relative_path']) . "','" . $this->escape($image['mime']) . "'," . intval($image['width']) . "," . intval($image['height']) . "," . intval($userId) . ")");
            Db_commit($connection); Db_autocommit(true, $connection); return intval(Db_insert_id($connection));
        } catch (Throwable $error) {
            Db_rollback($connection); Db_autocommit(true, $connection); if ($path && is_file($path)) @unlink($path); throw $error;
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
        $allowed = array('TITLE','TITLE_NORMALIZED','DESCRIPTION','MATERIAL','COLOR','WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G','SCALE','RELEASE_DATE','DATE_PRECISION','PRINT_RUN','IS_NUMBERED','IS_SIGNED','HAS_CERTIFICATE','IS_LIMITED','MANUFACTURER','MANUFACTURER_NORMALIZED','PUBLISHER','LICENSE_NAME','RANGE_NAME','UNIVERSE_NAME','TYPE_ID','SUBTYPE_ID');
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
                if (in_array($field, array('TITLE','DESCRIPTION','MATERIAL','COLOR','WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G','SCALE','RELEASE_DATE','DATE_PRECISION','PRINT_RUN','MANUFACTURER','PUBLISHER','LICENSE_NAME','RANGE_NAME','UNIVERSE_NAME'), true)) {
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
