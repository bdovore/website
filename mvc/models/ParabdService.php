<?php

require_once __DIR__ . '/ParabdException.php';
require_once __DIR__ . '/ParabdRules.php';
require_once __DIR__ . '/ParabdImageStorage.php';
if (!class_exists('Bdo_Db_Line')) require_once dirname(__DIR__, 2) . '/library/Bdo/Db/Line.php';
require_once __DIR__ . '/Parabdtype.php';
require_once __DIR__ . '/Parabditem.php';
require_once __DIR__ . '/Parabdidentifier.php';
require_once __DIR__ . '/Parabditemauthor.php';
require_once __DIR__ . '/Parabditemseries.php';
require_once __DIR__ . '/Parabditemtome.php';
require_once __DIR__ . '/Parabdmedia.php';
require_once __DIR__ . '/Parabdsource.php';
require_once __DIR__ . '/Userparabd.php';
require_once __DIR__ . '/Parabdrevision.php';
require_once __DIR__ . '/Parabdrevisionvote.php';
require_once __DIR__ . '/Parabdduplicate.php';
require_once __DIR__ . '/Parabdreport.php';
require_once __DIR__ . '/Parabddiscussion.php';
require_once __DIR__ . '/Parabduserprofile.php';

/**
 * Application service for the isolated Para-BD domain.
 *
 * It coordinates multi-table use cases and transaction boundaries. Persistence
 * is delegated to the Bdo_Db_Line models for each Para-BD table.
 */
class ParabdService
{
    const GLOBAL_SCHEMES = ParabdRules::GLOBAL_SCHEMES;

    private $models = array();
    private $imageStorage;

    private function model($class)
    {
        if (!isset($this->models[$class])) $this->models[$class] = new $class();
        return $this->models[$class];
    }

    private function imageStorage()
    {
        if (!$this->imageStorage) $this->imageStorage = new ParabdImageStorage();
        return $this->imageStorage;
    }

    private function connection()
    {
        return Bdo_Cfg::getVar('connexion');
    }

    public static function normalizeText($value)
    {
        return ParabdRules::normalizeText($value);
    }

    public static function normalizeIdentifier($scheme, $value)
    {
        return ParabdRules::normalizeIdentifier($scheme, $value);
    }

    public static function isValidIdentifier($scheme, $value)
    {
        return ParabdRules::isValidIdentifier($scheme, $value);
    }

    public static function parsePartialDate($value)
    {
        return ParabdRules::parsePartialDate($value);
    }

    public static function displayPartialDate($date, $precision)
    {
        return ParabdRules::displayPartialDate($date, $precision);
    }

    public static function titleSimilarity($left, $right)
    {
        return ParabdRules::titleSimilarity($left, $right);
    }

    public static function duplicateLevel($candidate, $input)
    {
        return ParabdRules::duplicateLevel($candidate, $input);
    }

    public function getTypes()
    {
        return $this->model('Parabdtype')->active();
    }

    public function getParentTypes()
    {
        return $this->model('Parabdtype')->parentTypes();
    }

    public function getCatalogue($search = '', $filters = array(), $page = 1, $perPage = 20)
    {
        return $this->model('Parabditem')->catalogue($search, $filters, $page, $perPage);
    }

    public function countCatalogue($search = '', $filters = array())
    {
        return $this->model('Parabditem')->countCatalogue($search, $filters);
    }

    public function getRecentByType($typeId, $limit = 5)
    {
        return $this->model('Parabditem')->recentByType($typeId, $limit);
    }

    public function getAdminCatalogue($search = '', $status = '', $sort = 'updated', $dir = 'DESC')
    {
        return $this->model('Parabditem')->adminCatalogue($search, $status, $sort, $dir, 100);
    }

    public function autocompleteCatalogue($term, $limitPerCategory = 6)
    {
        return $this->model('Parabditem')->autocomplete($term, $limitPerCategory);
    }

    public function autocompleteField($field, $term, $limit = 15)
    {
        return $this->model('Parabditem')->autocompleteField(strtolower(trim((string) $field)), $term, $limit);
    }

    public function getItem($itemId, $includeHidden = false)
    {
        $item = $this->model('Parabditem')->findBase($itemId, $includeHidden);
        if (!$item) return null;
        if ($item['STATUS'] === 'MERGED' && !empty($item['MERGED_INTO_ID'])) $item['REDIRECT_ID'] = intval($item['MERGED_INTO_ID']);
        $item['identifiers'] = $this->model('Parabdidentifier')->forItem($itemId);
        $item['media'] = $this->model('Parabdmedia')->forItem($itemId);
        if (!Bdo_Cfg::getVar('explicit')) {
            foreach ($item['media'] as &$media) if (!empty($media['IS_EXPLICIT'])) $media['FILE_PATH'] = '?source=' . $media['FILE_PATH'];
            unset($media);
        }
        $item['sources'] = $this->model('Parabdsource')->forItem($itemId);
        $item['authors'] = $this->model('Parabditemauthor')->forItem($itemId);
        $item['series'] = $this->model('Parabditemseries')->forItem($itemId);
        $item['tomes'] = $this->model('Parabditemtome')->forItem($itemId);
        return $item;
    }

    public function getAdminItem($itemId)
    {
        $item = $this->getItem($itemId, true);
        if (!$item) return null;
        $item['media'] = $this->model('Parabdmedia')->forItem($itemId, true);
        return $item;
    }

    public function getAdminItemHistory($itemId)
    {
        return $this->model('Parabdrevision')->adminHistory($itemId);
    }

    public function getDiscussion($itemId, $includeHidden = false)
    {
        return array(
            'entries' => $this->model('Parabddiscussion')->forItem($itemId, $includeHidden, 100),
            'comment_count' => $this->model('Parabddiscussion')->visibleCommentCount($itemId)
        );
    }

    public function searchDuplicates($input, $limit = 20)
    {
        $identifiers = isset($input['identifiers']) ? $input['identifiers'] : array();
        foreach ($identifiers as $identifier) {
            $scheme = isset($identifier['scheme']) ? strtoupper($identifier['scheme']) : '';
            $value = self::normalizeIdentifier($scheme, isset($identifier['value']) ? $identifier['value'] : '');
            if ($scheme === 'MANUFACTURER_REF') $issuer = self::normalizeText(isset($input['MANUFACTURER']) ? $input['MANUFACTURER'] : (isset($input['manufacturer']) ? $input['manufacturer'] : ''));
            elseif ($scheme === 'PUBLISHER_REF') $issuer = self::normalizeText(isset($input['PUBLISHER']) ? $input['PUBLISHER'] : (isset($input['publisher']) ? $input['publisher'] : ''));
            elseif ($scheme === 'EXTERNAL_DB') $issuer = self::normalizeText(isset($identifier['issuer']) ? $identifier['issuer'] : '');
            else $issuer = '';
            if ($value === '') continue;
            $existing = $this->model('Parabdidentifier')->findExact($scheme, $issuer, $value);
            if ($existing && !empty($input['ID_ITEM']) && intval($existing['ID_ITEM']) === intval($input['ID_ITEM'])) continue;
            if ($existing) return array(array_merge($existing, array('level' => 'CERTAIN', 'score' => 100, 'reasons' => array('Identifiant exact'))));
        }
        $title = self::normalizeText(isset($input['TITLE']) ? $input['TITLE'] : '');
        $candidates = $this->model('Parabditem')->candidateRows($title, $input, !empty($input['TYPE_ID']) ? intval($input['TYPE_ID']) : 0);
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

    public function searchDuplicatesForItem($itemId, $limit = 20)
    {
        $item = $this->getAdminItem(intval($itemId));
        if (!$item) throw new ParabdException('NOT_FOUND', 'Objet Para-BD introuvable.');
        return $this->searchDuplicates($this->duplicateInputForItem($item), $limit);
    }

    private function duplicateInputForItem(array $item)
    {
        $item['ID_ITEM'] = intval($item['ID_ITEM']);
        $item['identifiers'] = array_map(function ($identifier) {
            return array(
                'scheme' => isset($identifier['SCHEME']) ? $identifier['SCHEME'] : (isset($identifier['scheme']) ? $identifier['scheme'] : ''),
                'issuer' => isset($identifier['ISSUER']) ? $identifier['ISSUER'] : (isset($identifier['issuer']) ? $identifier['issuer'] : ''),
                'value' => isset($identifier['VALUE']) ? $identifier['VALUE'] : (isset($identifier['value']) ? $identifier['value'] : '')
            );
        }, isset($item['identifiers']) ? $item['identifiers'] : array());
        $item['AUTHOR_IDS'] = array_values(array_unique(array_map(function ($row) { return intval($row['AUTHOR_ID']); }, isset($item['authors']) ? $item['authors'] : array())));
        $item['SERIES_IDS'] = array_values(array_unique(array_map(function ($row) { return intval($row['SERIES_ID']); }, isset($item['series']) ? $item['series'] : array())));
        $item['TOME_IDS'] = array_values(array_unique(array_map(function ($row) { return intval($row['TOME_ID']); }, isset($item['tomes']) ? $item['tomes'] : array())));
        return $item;
    }

    private function primaryImage($itemId)
    {
        return $this->model('Parabdmedia')->primaryPath($itemId, (bool) Bdo_Cfg::getVar('explicit'));
    }

    private function hasCommonRelation($itemId, $input)
    {
        $map = array(
            'AUTHOR_ID' => array('parabd_item_author', 'AUTHOR_ID', 'AUTHOR_IDS'),
            'SERIES_ID' => array('parabd_item_series', 'SERIES_ID', 'SERIES_IDS'),
            'TOME_ID' => array('parabd_item_tome', 'TOME_ID', 'TOME_IDS')
        );
        foreach ($map as $key => $meta) {
            $values = !empty($input[$meta[2]]) && is_array($input[$meta[2]]) ? $input[$meta[2]] : array();
            if (!empty($input[$key])) $values[] = $input[$key];
            foreach (array_unique(array_map('intval', $values)) as $value) {
                if ($value && $this->model('Parabditem')->hasRelation($itemId, $meta[0], $meta[1], $value)) return true;
            }
        }
        return false;
    }

    public function isTrusted($userId)
    {
        return $this->model('Parabduserprofile')->isTrustedUser($userId);
    }

    public static function calculateTrust($createdAt, $validatedContributions, $override = 'NONE', $now = null)
    {
        return ParabdRules::calculateTrust($createdAt, $validatedContributions, $override, $now);
    }

    public function acceptCharter($userId, $accepted)
    {
        if (!$accepted) throw new ParabdException('VALIDATION_ERROR', 'Vous devez accepter la charte de contribution.', array('charter' => 'Acceptation obligatoire.'));
        if (!$this->hasAcceptedCharter($userId)) {
            $this->model('Parabduserprofile')->acceptCharter($userId, defined('BDO_PARABD_CHARTER_VERSION') ? BDO_PARABD_CHARTER_VERSION : '1');
        }
    }

    public function hasAcceptedCharter($userId)
    {
        $version = defined('BDO_PARABD_CHARTER_VERSION') ? BDO_PARABD_CHARTER_VERSION : '1';
        return $this->model('Parabduserprofile')->charterVersion($userId) === $version;
    }

    public function getCharterAcceptance($userId)
    {
        $version = defined('BDO_PARABD_CHARTER_VERSION') ? BDO_PARABD_CHARTER_VERSION : '1';
        $row = $this->model('Parabduserprofile')->charterAcceptance($userId);
        return array(
            'accepted' => $row && $row['CHARTER_VERSION'] === $version,
            'current_version' => $version,
            'accepted_version' => $row ? $row['CHARTER_VERSION'] : null,
            'accepted_at' => $row ? $row['CHARTER_ACCEPTED_AT'] : null
        );
    }

    public function setCharterAcceptance($userId, $accepted)
    {
        if ($accepted) $this->acceptCharter($userId, true);
        else $this->model('Parabduserprofile')->revokeCharter($userId);
    }

    public function requireCharter($userId)
    {
        if (!$this->hasAcceptedCharter($userId)) throw new ParabdException('VALIDATION_ERROR', 'La charte Para-BD doit être acceptée.', array('charter' => 'Acceptation obligatoire.'));
    }

    public function consumeRate($userId, $kind)
    {
        if ($this->isTrusted($userId) || User::minAccesslevel(1)) return;
        $limit = $kind === 'upload' ? (defined('BDO_PARABD_UPLOADS_PER_HOUR') ? BDO_PARABD_UPLOADS_PER_HOUR : 20) : (defined('BDO_PARABD_CREATIONS_PER_HOUR') ? BDO_PARABD_CREATIONS_PER_HOUR : 10);
        $connection = $this->connection(); Db_autocommit(false, $connection);
        try {
            $this->model('Parabduserprofile')->consume($userId, $kind, $limit);
            Db_commit($connection); Db_autocommit(true, $connection);
        } catch (Throwable $error) { Db_rollback($connection); Db_autocommit(true, $connection); throw $error; }
    }

    private function typeIds($typeCode, $subtypeCode)
    {
        return $this->model('Parabdtype')->resolveCodes($typeCode, $subtypeCode);
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
            'COLOR' => trim(isset($input['color']) ? $input['color'] : ''), 'WIDTH_MM' => $this->positiveInt(isset($input['width_mm']) ? $input['width_mm'] : null),
            'HEIGHT_MM' => $this->positiveInt(isset($input['height_mm']) ? $input['height_mm'] : null), 'DEPTH_MM' => $this->positiveInt(isset($input['depth_mm']) ? $input['depth_mm'] : null),
            'WEIGHT_G' => $this->positiveInt(isset($input['weight_g']) ? $input['weight_g'] : null), 'SCALE' => trim(isset($input['scale']) ? $input['scale'] : ''),
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

    private function positiveInt($value)
    {
        return ParabdRules::positiveInt($value);
    }

    private function tri($value)
    {
        return ParabdRules::tri($value);
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
            if ($scheme === 'MANUFACTURER_REF') $issuer = trim(isset($input['manufacturer']) ? $input['manufacturer'] : '');
            elseif ($scheme === 'PUBLISHER_REF') $issuer = trim(isset($input['publisher']) ? $input['publisher'] : '');
            elseif ($scheme === 'EXTERNAL_DB') $issuer = trim(isset($identifier['issuer']) ? $identifier['issuer'] : '');
            else $issuer = '';
            if ($scheme === 'MANUFACTURER_REF' && $issuer === '') throw new ParabdException('VALIDATION_ERROR', 'Le fabricant est obligatoire pour une référence du fabricant.');
            if ($scheme === 'PUBLISHER_REF' && $issuer === '') throw new ParabdException('VALIDATION_ERROR', 'L’éditeur est obligatoire pour une référence de l’éditeur.');
            if ($scheme === 'EXTERNAL_DB' && $issuer === '') throw new ParabdException('VALIDATION_ERROR', 'L’émetteur de cette référence est obligatoire.');
            $clean[] = array('scheme' => $scheme, 'issuer' => $issuer, 'issuer_normalized' => self::normalizeText($issuer), 'value' => trim($identifier['value']), 'value_normalized' => $value);
        }
        return $clean;
    }

    private function relationsFromInput($input)
    {
        $relations = array('authors' => array(), 'series' => array(), 'tomes' => array());
        if (isset($input['authors']) && is_array($input['authors'])) {
            foreach ($input['authors'] as $row) if (!empty($row['id'])) {
                $role = strtoupper(trim(isset($row['role']) ? $row['role'] : ''));
                if ($role === '') $role = $this->model('Parabditemauthor')->defaultRoleForAuthor($row['id']);
                if ($role === '') throw new ParabdException('VALIDATION_ERROR', 'Choisissez le rôle de l’artiste.');
                if (!in_array($role, array('ARTIST','DESIGNER','PAINTER','SCULPTOR','ILLUSTRATOR'), true)) throw new ParabdException('VALIDATION_ERROR', 'Le rôle de l’artiste est invalide.');
                $relations['authors'][] = array('id' => intval($row['id']), 'role' => $role);
            }
        } elseif (!empty($input['author_id'])) {
            $role = strtoupper(trim(isset($input['author_role']) ? $input['author_role'] : ''));
            if ($role === '') $role = $this->model('Parabditemauthor')->defaultRoleForAuthor($input['author_id']);
            if ($role === '') throw new ParabdException('VALIDATION_ERROR', 'Choisissez le rôle de l’artiste.', array('author_role' => 'Rôle obligatoire.'));
            if (!in_array($role, array('ARTIST','DESIGNER','PAINTER','SCULPTOR','ILLUSTRATOR'), true)) throw new ParabdException('VALIDATION_ERROR', 'Le rôle de l’artiste est invalide.');
            $relations['authors'][] = array('id' => intval($input['author_id']), 'role' => $role);
        }
        if (isset($input['series']) && is_array($input['series'])) {
            foreach ($input['series'] as $row) if (!empty($row['id'])) $relations['series'][] = array('id' => intval($row['id']), 'relation_type' => trim(isset($row['relation_type']) ? $row['relation_type'] : 'RELATED') ?: 'RELATED');
        } elseif (!empty($input['series_id'])) $relations['series'][] = array('id' => intval($input['series_id']), 'relation_type' => 'RELATED');
        if (isset($input['tomes']) && is_array($input['tomes'])) {
            foreach ($input['tomes'] as $row) if (!empty($row['id'])) $relations['tomes'][] = array('id' => intval($row['id']), 'relation_type' => trim(isset($row['relation_type']) ? $row['relation_type'] : 'RELATED') ?: 'RELATED');
        } elseif (!empty($input['tome_id'])) $relations['tomes'][] = array('id' => intval($input['tome_id']), 'relation_type' => 'RELATED');
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
            $itemId = (new Parabditem())->createLine($data, $userId);
            foreach ($identifiers as $identifier) $this->model('Parabdidentifier')->addForItem($itemId, $identifier, $userId);
            $this->insertRelations($itemId, $userId, $relations);
            $image = $this->storeImage($file, $itemId, 1);
            $writtenFile = $image['absolute_path'];
            $this->model('Parabdmedia')->addImage($itemId, 'PRIMARY', $image, $userId, true, !empty($input['is_explicit']));
            foreach ($sources as $source) $this->addSource($itemId, $userId, $source['url'], $source['label'], $source['notes']);
            if ($visualUrl !== '' && !in_array($visualUrl, array_column($sources, 'url'), true)) $this->addSource($itemId, $userId, $visualUrl, 'Source du visuel principal');
            $snapshot = json_encode($this->adminSnapshot($itemId), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $now = date('d/m/Y H:i:s');
            $this->model('Parabdrevision')->createRevision(array('ITEM_ID' => $itemId, 'AUTHOR_ID' => intval($userId), 'BASE_REVISION_NO' => 0, 'PATCH_AFTER' => $snapshot, 'CHANGE_KIND' => 'CREATE', 'STATUS' => $adminDirect ? 'ACCEPTED' : 'APPLIED', 'APPLIED_AT' => $now, 'VALIDATED_BY' => $adminDirect ? intval($userId) : null, 'VALIDATED_AT' => $adminDirect ? $now : null));
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
        $input['status'] = 'ACTIVE';
        return $this->createItem($adminId, $input, $file, true);
    }

    private function insertRelations($itemId, $userId, $relations)
    {
        foreach ($relations['authors'] as $row) $this->model('Parabditemauthor')->addForItem($itemId, $row, $userId);
        foreach ($relations['series'] as $row) $this->model('Parabditemseries')->addForItem($itemId, $row, $userId);
        foreach ($relations['tomes'] as $row) $this->model('Parabditemtome')->addForItem($itemId, $row, $userId);
    }

    private function addSource($itemId, $userId, $url, $label, $notes = '')
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array(strtolower(parse_url($url, PHP_URL_SCHEME)), array('http','https'), true)) throw new ParabdException('VALIDATION_ERROR', 'URL source invalide.');
        $this->model('Parabdsource')->addUrl($itemId, $userId, $url, $label, $notes);
    }

    public function storeImage($file, $itemId, $sequence)
    {
        return $this->imageStorage()->store($file, $itemId, $sequence);
    }

    public static function isPublicRemoteIp($ip)
    {
        return ParabdImageStorage::isPublicRemoteIp($ip);
    }

    private function downloadRemoteImage($url)
    {
        return $this->imageStorage()->download($url);
    }

    public static function orientImage($image, $orientation)
    {
        return ParabdImageStorage::orient($image, $orientation);
    }

    private function recordDuplicate($itemId, $otherId, $duplicate)
    {
        $this->model('Parabdduplicate')->record($itemId, $otherId, $duplicate);
    }

    public function getUserCopies($userId, $state = null, $publicOnly = false)
    {
        return $this->model('Userparabd')->copies($userId, $state, $publicOnly);
    }

    public function getPublicUserCollection($userId)
    {
        return $this->model('Userparabd')->publicCollection($userId);
    }

    public function saveCopy($userId, $input, $insideTransaction = false)
    {
        $connection = $this->connection();
        if (!$insideTransaction) Db_autocommit(false, $connection);
        try {
            $copyId = $this->model('Userparabd')->saveForUser($userId, $input);
            if (!$insideTransaction) { Db_commit($connection); Db_autocommit(true, $connection); }
            return $copyId;
        } catch (Throwable $error) {
            if (!$insideTransaction) { Db_rollback($connection); Db_autocommit(true, $connection); }
            throw $error;
        }
    }

    public function removeCopy($userId, $copyId)
    {
        $this->model('Userparabd')->removeForUser($userId, $copyId);
    }

    public function contribute($userId, $itemId, $baseRevision, $field, $value)
    {
        $this->requireCharter($userId);
        $allowed = array('TITLE','DESCRIPTION','MATERIAL','COLOR','WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G','SCALE','RELEASE_DATE','PRINT_RUN','IS_NUMBERED','IS_SIGNED','HAS_CERTIFICATE','IS_LIMITED','MANUFACTURER','PUBLISHER','LICENSE_NAME','RANGE_NAME','UNIVERSE_NAME','TYPE_ID','SUBTYPE_ID');
        $deleteRelations = array('DELETE_AUTHOR' => 'Parabditemauthor', 'DELETE_SERIES' => 'Parabditemseries', 'DELETE_TOME' => 'Parabditemtome');
        if (!in_array($field, $allowed, true) && !isset($deleteRelations[$field])) throw new ParabdException('VALIDATION_ERROR', 'Champ de contribution invalide.');
        $connection = $this->connection(); Db_autocommit(false, $connection);
        try {
        $item = $this->model('Parabditem')->findActive($itemId, true);
        if (!$item) throw new ParabdException('NOT_FOUND', 'Objet Para-BD introuvable.');
        if (intval($item['REVISION_NO']) !== intval($baseRevision)) throw new ParabdException('REVISION_CONFLICT', 'La fiche a changé. Rechargez-la avant de contribuer.');
        if (isset($deleteRelations[$field])) {
            $relation = $this->model($deleteRelations[$field]);
            if (!$relation->existsForItem($itemId, $value)) throw new ParabdException('NOT_FOUND', 'Lien Para-BD introuvable.');
            $patch = json_encode(array('_operation' => $field, '_id' => intval($value)), JSON_UNESCAPED_UNICODE);
            $revisionId = $this->model('Parabdrevision')->createRevision(array('ITEM_ID' => intval($itemId), 'AUTHOR_ID' => intval($userId), 'BASE_REVISION_NO' => intval($baseRevision), 'PATCH_BEFORE' => $patch, 'PATCH_AFTER' => $patch, 'CHANGE_KIND' => 'DELETE_LINK', 'STATUS' => 'PENDING'));
            $this->model('Parabddiscussion')->addProposal($itemId, $revisionId, $userId);
            Db_commit($connection); Db_autocommit(true, $connection);
            return array('revision_id' => $revisionId, 'status' => 'PENDING');
        }
        $extraAfter = array();
        $extraBefore = array();
        if (in_array($field, array('WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G'), true)) $value = $this->positiveInt($value);
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
        $apply = !$protected && ($before === null || $before === '');
        $kind = $protected ? 'TYPE_CHANGE' : 'UPDATE';
        $beforePatch = array_merge(array($field => $before), $extraBefore);
        $afterPatch = array_merge(array($field => $value), $extraAfter);
        $beforeJson = json_encode($beforePatch, JSON_UNESCAPED_UNICODE);
        $afterJson = json_encode($afterPatch, JSON_UNESCAPED_UNICODE);
        if ($apply) {
            $this->model('Parabditem')->updateFields($itemId, $afterPatch, $userId, true);
        }
        $revisionData = array('ITEM_ID' => intval($itemId), 'AUTHOR_ID' => intval($userId), 'BASE_REVISION_NO' => intval($baseRevision), 'PATCH_BEFORE' => $beforeJson, 'PATCH_AFTER' => $afterJson, 'CHANGE_KIND' => $kind, 'STATUS' => $apply ? 'APPLIED' : 'PENDING');
        if ($apply) $revisionData['APPLIED_AT'] = date('d/m/Y H:i:s');
        $revisionId = $this->model('Parabdrevision')->createRevision($revisionData);
        $this->model('Parabddiscussion')->addProposal($itemId, $revisionId, $userId);
        Db_commit($connection); Db_autocommit(true, $connection);
        return array('revision_id' => $revisionId, 'status' => $apply ? 'APPLIED' : 'PENDING');
        } catch (Throwable $error) { Db_rollback($connection); Db_autocommit(true, $connection); throw $error; }
    }

    public function vote($userId, $revisionId, $vote, $reason = '')
    {
        $vote = strtoupper($vote);
        if (!in_array($vote, array('CONFIRM','CONTEST'), true)) throw new ParabdException('VALIDATION_ERROR', 'Vote invalide.');
        $reason = trim((string) $reason);
        if ($vote === 'CONTEST' && (mb_strlen($reason, 'UTF-8') < 1 || mb_strlen($reason, 'UTF-8') > 2000)) throw new ParabdException('VALIDATION_ERROR', 'Expliquez votre opposition en 2 000 caractères maximum.');
        $connection = $this->connection(); Db_autocommit(false, $connection);
        try {
            $revision = $this->model('Parabdrevision')->findForUpdate($revisionId);
            if (!$revision) throw new ParabdException('NOT_FOUND', 'Contribution introuvable.');
            if (!in_array($revision['STATUS'], array('PENDING','CONFLICT'), true)) throw new ParabdException('REVISION_CONFLICT', 'Les votes sont clos.');
            if (intval($revision['AUTHOR_ID']) === intval($userId)) throw new ParabdException('VALIDATION_ERROR', 'Vous ne pouvez pas voter sur votre propre proposition.');
            $this->model('Parabdrevisionvote')->castVote($revisionId, $userId, $vote);
            if ($vote === 'CONTEST') $this->model('Parabddiscussion')->addComment($revision['ITEM_ID'], $revisionId, $userId, $reason);
            $counts = $this->model('Parabdrevisionvote')->counts($revisionId);
            $status = 'PENDING';
            $intervention = false;
            if (intval($counts['contests']) > 0) {
                $this->model('Parabdrevision')->setPending($revisionId);
            } elseif (intval($counts['confirms']) >= 2) {
                try {
                    $this->applyRevision($revision, $userId);
                    $this->model('Parabdrevision')->markApplied($revisionId);
                    $status = 'ACCEPTED';
                    $this->model('Parabdrevision')->decide($revisionId, 'ACCEPTED', $userId);
                } catch (ParabdException $error) {
                    if ($error->errorCode !== 'REVISION_CONFLICT') throw $error;
                    $intervention = true;
                    $this->model('Parabdrevision')->setPending($revisionId);
                }
            } else {
                $this->model('Parabdrevision')->setPending($revisionId);
            }
            Db_commit($connection); Db_autocommit(true, $connection);
            return array('status' => $status, 'confirms' => intval($counts['confirms']), 'contests' => intval($counts['contests']), 'admin_intervention' => $intervention || intval($counts['contests']) > 0);
        } catch (Throwable $error) {
            Db_rollback($connection); Db_autocommit(true, $connection); throw $error;
        }
    }

    public function getRevisionsForItem($itemId)
    {
        return $this->model('Parabdrevision')->forItem($itemId);
    }

    private function applyRevision($revision, $validatorId, $force = false)
    {
        $item = $this->model('Parabditem')->rowForUpdate($revision['ITEM_ID']);
        if (!$item) throw new ParabdException('NOT_FOUND', 'Objet Para-BD introuvable.');
        $patch = json_decode($revision['PATCH_AFTER'], true);
        $before = json_decode($revision['PATCH_BEFORE'], true);
        if (!is_array($patch) || !$patch) throw new RuntimeException('Patch de contribution invalide.');
        if (isset($patch['_operation'])) {
            $relations = array('DELETE_AUTHOR' => 'Parabditemauthor', 'DELETE_SERIES' => 'Parabditemseries', 'DELETE_TOME' => 'Parabditemtome');
            if (!isset($relations[$patch['_operation']])) throw new RuntimeException('Opération de lien Para-BD invalide.');
            $exists = $this->model($relations[$patch['_operation']])->existsForItem($revision['ITEM_ID'], $patch['_id']);
            if (!$exists && !$force) throw new ParabdException('REVISION_CONFLICT', 'Le rattachement visé a changé depuis cette proposition. Une décision administrative est nécessaire.');
            if ($exists) $this->model($relations[$patch['_operation']])->removeForItem($revision['ITEM_ID'], $patch['_id']);
            $this->model('Parabditem')->incrementRevision($revision['ITEM_ID'], $validatorId);
            return;
        }
        $allowed = array('TITLE','TITLE_NORMALIZED','DESCRIPTION','MATERIAL','COLOR','WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G','SCALE','RELEASE_DATE','DATE_PRECISION','PRINT_RUN','IS_NUMBERED','IS_SIGNED','HAS_CERTIFICATE','IS_LIMITED','MANUFACTURER','MANUFACTURER_NORMALIZED','PUBLISHER','LICENSE_NAME','RANGE_NAME','UNIVERSE_NAME','TYPE_ID','SUBTYPE_ID');
        foreach ($patch as $field => $value) {
            if (!in_array($field, $allowed, true)) throw new RuntimeException('Champ de patch Para-BD invalide.');
        }
        if (!is_array($before)) throw new RuntimeException('Valeur initiale de contribution invalide.');
        if (!$force) foreach ($before as $field => $value) {
            if (!array_key_exists($field, $item) || !$this->sameRevisionValue($item[$field], $value)) throw new ParabdException('REVISION_CONFLICT', 'La valeur concernée a changé depuis cette proposition. Une décision administrative est nécessaire.');
        }
        $this->model('Parabditem')->updateFields($revision['ITEM_ID'], $patch, $validatorId, true);
    }

    private function sameRevisionValue($current, $expected)
    {
        if ($current === null || $current === '') return $expected === null || $expected === '';
        return (string) $current === (string) $expected;
    }

    public function addDiscussionComment($userId, $itemId, $revisionId, $body, $includeHidden = false)
    {
        $body = trim((string) $body);
        if (mb_strlen($body, 'UTF-8') < 1 || mb_strlen($body, 'UTF-8') > 2000) throw new ParabdException('VALIDATION_ERROR', 'Le commentaire doit contenir entre 1 et 2 000 caractères.');
        $item = $includeHidden ? $this->model('Parabditem')->findBase($itemId, true) : $this->model('Parabditem')->findActive($itemId);
        if (!$item || (!$includeHidden && $item['STATUS'] !== 'ACTIVE')) throw new ParabdException('NOT_FOUND', 'Objet Para-BD introuvable.');
        $revisionId = intval($revisionId);
        if ($revisionId && !$this->model('Parabddiscussion')->revisionBelongsToItem($revisionId, $itemId)) throw new ParabdException('VALIDATION_ERROR', 'Cette proposition n’appartient pas à la fiche.');
        return $this->model('Parabddiscussion')->addComment($itemId, $revisionId, $userId, $body);
    }

    public function report($userId, $targetType, $targetId, $reason, $details)
    {
        $targetType = strtoupper($targetType);
        if ($targetType !== 'ITEM' || intval($targetId) < 1 || trim($reason) === '') throw new ParabdException('VALIDATION_ERROR', 'Signalement invalide.');
        if (!$this->model('Parabdreport')->targetExists($targetType, $targetId)) throw new ParabdException('NOT_FOUND', 'Cible du signalement introuvable.');
        return $this->model('Parabdreport')->createReport($userId, $targetType, $targetId, $reason, $details);
    }

    public function addMedia($userId, $itemId, $file, $mediaType, $visualUrl = '', $isExplicit = false)
    {
        $this->requireCharter($userId);
        $mediaType = strtoupper($mediaType);
        if (!in_array($mediaType, array('GALLERY','CERTIFICATE','BOX','DETAIL'), true)) $mediaType = 'GALLERY';
        if (!$this->model('Parabditem')->findActive($itemId)) throw new ParabdException('NOT_FOUND', 'Objet Para-BD introuvable.');
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
            $locked = $this->model('Parabditem')->rowForUpdate($itemId);
            if (!$locked || $locked['STATUS'] !== 'ACTIVE') throw new ParabdException('NOT_FOUND', 'Objet Para-BD introuvable.');
            $before = $this->adminSnapshot($itemId);
            $count = $this->model('Parabdmedia')->countForItem($itemId);
            $image = $this->storeImage($file, intval($itemId), $count + 1); $path = $image['absolute_path'];
            $mediaId = $this->model('Parabdmedia')->addImage($itemId, $mediaType, $image, $userId, false, $isExplicit);
            if (!$hasUpload) $this->addSource(intval($itemId), $userId, $visualUrl, 'Source du visuel');
            $after = $this->adminSnapshot($itemId);
            $this->model('Parabditem')->incrementRevision($itemId, $userId);
            $now = date('d/m/Y H:i:s');
            $revisionId = $this->model('Parabdrevision')->createRevision(array('ITEM_ID' => intval($itemId), 'AUTHOR_ID' => intval($userId), 'BASE_REVISION_NO' => intval($locked['REVISION_NO']), 'PATCH_BEFORE' => json_encode($before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'PATCH_AFTER' => json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'CHANGE_KIND' => 'UPDATE', 'STATUS' => 'APPLIED', 'APPLIED_AT' => $now));
            $this->model('Parabddiscussion')->addProposal($itemId, $revisionId, $userId);
            Db_commit($connection); Db_autocommit(true, $connection); if ($remoteFile && is_file($remoteFile)) @unlink($remoteFile); return intval($mediaId);
        } catch (Throwable $error) {
            Db_rollback($connection); Db_autocommit(true, $connection); if ($path && is_file($path)) @unlink($path); if ($remoteFile && is_file($remoteFile)) @unlink($remoteFile); throw $error;
        }
    }

    private function adminSnapshot($itemId)
    {
        $itemId = intval($itemId);
        $item = $this->model('Parabditem')->snapshotFields($itemId);
        if (!$item) return null;
        $item['identifiers'] = $this->model('Parabdidentifier')->snapshotForItem($itemId);
        $item['authors'] = $this->model('Parabditemauthor')->snapshotForItem($itemId);
        $item['series'] = $this->model('Parabditemseries')->snapshotForItem($itemId);
        $item['tomes'] = $this->model('Parabditemtome')->snapshotForItem($itemId);
        $item['sources'] = $this->model('Parabdsource')->snapshotForItem($itemId);
        $item['media'] = $this->model('Parabdmedia')->snapshotForItem($itemId);
        return $item;
    }

    public function adminUpdateItem($adminId, $itemId, $input)
    {
        $itemId = intval($itemId);
        $data = $this->cleanInput($input);
        $identifiers = $this->identifiersFromInput($input);
        $relations = $this->relationsFromInput($input);
        $sources = $this->sourcesFromInput($input);
        $connection = $this->connection();
        Db_autocommit(false, $connection);
        try {
            $locked = $this->model('Parabditem')->findBase($itemId, true, true);
            if (!$locked) throw new ParabdException('NOT_FOUND', 'Objet Para-BD introuvable.');
            if ($locked['STATUS'] === 'MERGED') throw new ParabdException('VALIDATION_ERROR', 'Une fiche fusionnée est consultable mais ne peut plus être modifiée.');
            $before = $this->adminSnapshot($itemId);

            $this->model('Parabditem')->updateFields($itemId, $data, $adminId);
            $this->model('Parabdidentifier')->replaceForItem($itemId, $identifiers, $adminId);
            $this->model('Parabditemauthor')->replaceForItem($itemId, $relations['authors'], $adminId);
            $this->model('Parabditemseries')->replaceForItem($itemId, $relations['series'], $adminId);
            $this->model('Parabditemtome')->replaceForItem($itemId, $relations['tomes'], $adminId);
            $this->model('Parabdsource')->replaceForItem($itemId, $sources, $adminId);

            $mediaRows = $this->model('Parabdmedia')->selectionForItem($itemId);
            $mediaIds = array_map(function ($row) { return intval($row['ID_MEDIA']); }, $mediaRows);
            $hidden = isset($input['media_hidden']) && is_array($input['media_hidden']) ? array_map('intval', array_keys($input['media_hidden'])) : array();
            $explicit = isset($input['media_explicit']) && is_array($input['media_explicit']) ? array_map('intval', array_keys($input['media_explicit'])) : array();
            foreach ($mediaIds as $mediaId) {
                $this->model('Parabdmedia')->setHidden($itemId, $mediaId, in_array($mediaId, $hidden, true));
                $this->model('Parabdmedia')->setExplicit($itemId, $mediaId, in_array($mediaId, $explicit, true));
            }

            $primaryMediaId = isset($input['primary_media_id']) ? intval($input['primary_media_id']) : 0;
            if (!$primaryMediaId) foreach ($mediaRows as $row) if ($row['IS_PRIMARY']) { $primaryMediaId = intval($row['ID_MEDIA']); break; }
            if (!$primaryMediaId || !$this->model('Parabdmedia')->isVisibleForItem($itemId, $primaryMediaId)) throw new ParabdException('VALIDATION_ERROR', 'Choisissez un visuel principal visible.');
            $this->model('Parabdmedia')->selectPrimary($itemId, $primaryMediaId);

            $after = $this->adminSnapshot($itemId);
            $beforeJson = json_encode($before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $afterJson = json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($beforeJson !== $afterJson) {
                $baseRevision = intval($locked['REVISION_NO']);
                $this->model('Parabditem')->incrementRevision($itemId, $adminId);
                $now = date('d/m/Y H:i:s');
                $this->model('Parabdrevision')->createRevision(array('ITEM_ID' => $itemId, 'AUTHOR_ID' => intval($adminId), 'BASE_REVISION_NO' => $baseRevision, 'PATCH_BEFORE' => $beforeJson, 'PATCH_AFTER' => $afterJson, 'CHANGE_KIND' => 'UPDATE', 'STATUS' => 'ACCEPTED', 'APPLIED_AT' => $now, 'VALIDATED_BY' => intval($adminId), 'VALIDATED_AT' => $now));
            }
            Db_commit($connection); Db_autocommit(true, $connection);
            return array('item_id' => $itemId, 'changed' => $beforeJson !== $afterJson);
        } catch (Throwable $error) {
            Db_rollback($connection); Db_autocommit(true, $connection);
            if ($connection->errno === 1062 || strpos($error->getMessage(), 'Duplicate entry') !== false) throw new ParabdException('DUPLICATE_EXACT', 'Cet identifiant appartient déjà à une autre fiche.');
            throw $error;
        }
    }

    public function adminAddMedia($adminId, $itemId, $input, $file = null)
    {
        $itemId = intval($itemId);
        $mediaType = strtoupper(trim(isset($input['media_type']) ? $input['media_type'] : 'GALLERY'));
        if (!in_array($mediaType, array('GALLERY','CERTIFICATE','BOX','DETAIL'), true)) $mediaType = 'GALLERY';
        $visualUrl = trim(isset($input['visual_url']) ? $input['visual_url'] : '');
        $hasUpload = $file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK;
        $remoteFile = null;
        if (!$hasUpload) {
            if ($visualUrl === '') throw new ParabdException('VALIDATION_ERROR', 'Choisissez un fichier ou indiquez une URL d’image.');
            $file = $this->downloadRemoteImage($visualUrl);
            $remoteFile = $file['tmp_name'];
        }

        $connection = $this->connection();
        $writtenFile = null;
        Db_autocommit(false, $connection);
        try {
            $locked = $this->model('Parabditem')->findBase($itemId, true, true);
            if (!$locked) throw new ParabdException('NOT_FOUND', 'Objet Para-BD introuvable.');
            if ($locked['STATUS'] === 'MERGED') throw new ParabdException('VALIDATION_ERROR', 'Une fiche fusionnée est consultable mais ne peut plus être modifiée.');
            $before = $this->adminSnapshot($itemId);
            $mediaRows = $this->model('Parabdmedia')->selectionForItem($itemId);
            $primaryMediaId = 0;
            foreach ($mediaRows as $media) if ($media['IS_PRIMARY']) { $primaryMediaId = intval($media['ID_MEDIA']); break; }

            $image = $this->storeImage($file, $itemId, count($mediaRows) + 1);
            $writtenFile = $image['absolute_path'];
            $mediaId = $this->model('Parabdmedia')->addImage($itemId, $mediaType, $image, $adminId, false, !empty($input['is_explicit']));
            if (!empty($input['is_primary']) || !$primaryMediaId) $this->model('Parabdmedia')->selectPrimary($itemId, $mediaId);
            if ($visualUrl !== '') $this->addSource($itemId, $adminId, $visualUrl, 'Source du nouveau visuel');

            $after = $this->adminSnapshot($itemId);
            $beforeJson = json_encode($before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $afterJson = json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $baseRevision = intval($locked['REVISION_NO']);
            $this->model('Parabditem')->incrementRevision($itemId, $adminId);
            $now = date('d/m/Y H:i:s');
            $this->model('Parabdrevision')->createRevision(array('ITEM_ID' => $itemId, 'AUTHOR_ID' => intval($adminId), 'BASE_REVISION_NO' => $baseRevision, 'PATCH_BEFORE' => $beforeJson, 'PATCH_AFTER' => $afterJson, 'CHANGE_KIND' => 'UPDATE', 'STATUS' => 'ACCEPTED', 'APPLIED_AT' => $now, 'VALIDATED_BY' => intval($adminId), 'VALIDATED_AT' => $now));
            Db_commit($connection); Db_autocommit(true, $connection);
            if ($remoteFile && is_file($remoteFile)) @unlink($remoteFile);
            return array('item_id' => $itemId, 'media_id' => intval($mediaId));
        } catch (Throwable $error) {
            Db_rollback($connection); Db_autocommit(true, $connection);
            if ($writtenFile && is_file($writtenFile)) @unlink($writtenFile);
            if ($remoteFile && is_file($remoteFile)) @unlink($remoteFile);
            throw $error;
        }
    }

    public function adminDeleteMedia($adminId, $itemId, $mediaId)
    {
        $itemId = intval($itemId);
        $mediaId = intval($mediaId);
        $connection = $this->connection();
        Db_autocommit(false, $connection);
        try {
            $locked = $this->model('Parabditem')->findBase($itemId, true, true);
            if (!$locked) throw new ParabdException('NOT_FOUND', 'Objet Para-BD introuvable.');
            if ($locked['STATUS'] === 'MERGED') throw new ParabdException('VALIDATION_ERROR', 'Une fiche fusionnée est consultable mais ne peut plus être modifiée.');
            $media = $this->model('Parabdmedia')->findForItem($itemId, $mediaId, true);
            if (!$media) throw new ParabdException('NOT_FOUND', 'Visuel Para-BD introuvable.');

            $before = $this->adminSnapshot($itemId);
            if (!$this->model('Parabdmedia')->deleteForItem($itemId, $mediaId)) throw new ParabdException('NOT_FOUND', 'Visuel Para-BD introuvable.');
            if (!empty($media['IS_PRIMARY'])) {
                $replacement = $this->model('Parabdmedia')->firstVisibleForItem($itemId);
                if ($replacement) $this->model('Parabdmedia')->selectPrimary($itemId, intval($replacement['ID_MEDIA']));
            }

            $after = $this->adminSnapshot($itemId);
            $beforeJson = json_encode($before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $afterJson = json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $baseRevision = intval($locked['REVISION_NO']);
            $this->model('Parabditem')->incrementRevision($itemId, $adminId);
            $now = date('d/m/Y H:i:s');
            $this->model('Parabdrevision')->createRevision(array('ITEM_ID' => $itemId, 'AUTHOR_ID' => intval($adminId), 'BASE_REVISION_NO' => $baseRevision, 'PATCH_BEFORE' => $beforeJson, 'PATCH_AFTER' => $afterJson, 'CHANGE_KIND' => 'UPDATE', 'STATUS' => 'ACCEPTED', 'APPLIED_AT' => $now, 'VALIDATED_BY' => intval($adminId), 'VALIDATED_AT' => $now));

            $this->imageStorage()->remove($media['FILE_PATH']);
            Db_commit($connection); Db_autocommit(true, $connection);
            return array('item_id' => $itemId, 'media_id' => $mediaId);
        } catch (Throwable $error) {
            Db_rollback($connection); Db_autocommit(true, $connection);
            throw $error;
        }
    }

    public function adminQueues()
    {
        return array(
            'duplicates' => $this->model('Parabdduplicate')->openQueue(),
            'reports' => $this->model('Parabdreport')->openQueue(),
            'modifications' => $this->model('Parabdrevision')->pendingQueue()
        );
    }

    public function resolveDuplicate($adminId, $duplicateId, $status)
    {
        if ($status !== 'IGNORED') throw new ParabdException('VALIDATION_ERROR', 'Résolution de doublon invalide.');
        if (!$this->model('Parabdduplicate')->resolve($duplicateId, $status, $adminId)) throw new ParabdException('NOT_FOUND', 'Doublon introuvable.');
    }

    public function moderateItem($adminId, $itemId, $status)
    {
        if (!in_array($status, array('ACTIVE','HIDDEN'), true)) throw new ParabdException('VALIDATION_ERROR', 'Statut invalide.');
        $connection = $this->connection(); Db_autocommit(false, $connection);
        try {
            $item = $this->model('Parabditem')->rowForUpdate($itemId);
            if (!$item || $item['STATUS'] === 'MERGED') throw new ParabdException('NOT_FOUND', 'Objet introuvable.');
            if ($item['STATUS'] === $status) { Db_commit($connection); Db_autocommit(true, $connection); return array('item_id' => intval($itemId), 'status' => $status); }
            if (!$this->model('Parabditem')->moderate($itemId, $status, $adminId)) throw new ParabdException('NOT_FOUND', 'Objet introuvable.');
            $now = date('d/m/Y H:i:s');
            $this->model('Parabdrevision')->createRevision(array('ITEM_ID' => intval($itemId), 'AUTHOR_ID' => intval($adminId), 'BASE_REVISION_NO' => intval($item['REVISION_NO']), 'PATCH_BEFORE' => json_encode(array('STATUS' => $item['STATUS'])), 'PATCH_AFTER' => json_encode(array('STATUS' => $status)), 'CHANGE_KIND' => 'MODERATION', 'STATUS' => 'ACCEPTED', 'APPLIED_AT' => $now, 'VALIDATED_BY' => intval($adminId), 'VALIDATED_AT' => $now));
            Db_commit($connection); Db_autocommit(true, $connection);
            return array('item_id' => intval($itemId), 'status' => $status);
        } catch (Throwable $error) { Db_rollback($connection); Db_autocommit(true, $connection); throw $error; }
    }

    public function resolveReport($adminId, $reportId, $status)
    {
        if (!in_array($status, array('RESOLVED','DISMISSED'), true)) throw new ParabdException('VALIDATION_ERROR', 'Résolution invalide.');
        $row = null; foreach ($this->model('Parabdreport')->openQueue() as $candidate) if (intval($candidate['ID_REPORT']) === intval($reportId)) { $row = $candidate; break; }
        if (!$row || !$this->model('Parabdreport')->resolve($reportId, $status, $adminId)) throw new ParabdException('NOT_FOUND', 'Signalement introuvable.');
        return array('item_id' => intval($row['ID_ITEM']), 'status' => $status);
    }

    public function getOpenReportForItem($reportId, $itemId)
    {
        return $reportId ? $this->model('Parabdreport')->openForItem($reportId, $itemId) : null;
    }

    public function hideDiscussionComment($adminId, $discussionId)
    {
        if (!$this->model('Parabddiscussion')->hideComment($discussionId, $adminId)) throw new ParabdException('NOT_FOUND', 'Commentaire visible introuvable.');
    }

    public function resolveRevision($adminId, $revisionId, $accept)
    {
        $connection = $this->connection(); Db_autocommit(false, $connection);
        try {
            $revision = $this->model('Parabdrevision')->findForUpdate($revisionId);
            if (!$revision || !in_array($revision['STATUS'], array('PENDING','APPLIED','CONFLICT'), true)) throw new ParabdException('NOT_FOUND', 'Contribution à résoudre introuvable.');
            if ($accept && empty($revision['APPLIED_AT'])) {
                $this->applyRevision($revision, $adminId, true);
                $this->model('Parabdrevision')->markApplied($revisionId);
            } elseif (!$accept && !empty($revision['APPLIED_AT']) && $revision['CHANGE_KIND'] !== 'CREATE') {
                $this->revertRevision($revision, $adminId);
            }
            $this->model('Parabdrevision')->decide($revisionId, $accept ? 'ACCEPTED' : 'REJECTED', $adminId);
            Db_commit($connection); Db_autocommit(true, $connection);
            return array('item_id' => intval($revision['ITEM_ID']), 'status' => $accept ? 'ACCEPTED' : 'REJECTED');
        } catch (Throwable $error) { Db_rollback($connection); Db_autocommit(true, $connection); throw $error; }
    }

    private function revertRevision($revision, $adminId)
    {
        $patch = json_decode($revision['PATCH_BEFORE'], true);
        if (!is_array($patch) || !$patch || isset($patch['_operation'])) return;
        $allowed = array('TITLE','TITLE_NORMALIZED','DESCRIPTION','MATERIAL','COLOR','WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G','SCALE','RELEASE_DATE','DATE_PRECISION','PRINT_RUN','IS_NUMBERED','IS_SIGNED','HAS_CERTIFICATE','IS_LIMITED','MANUFACTURER','MANUFACTURER_NORMALIZED','PUBLISHER','LICENSE_NAME','RANGE_NAME','UNIVERSE_NAME','TYPE_ID','SUBTYPE_ID');
        foreach ($patch as $field => $value) {
            if (!in_array($field, $allowed, true)) throw new RuntimeException('Champ de restauration Para-BD invalide.');
        }
        $this->model('Parabditem')->updateFields($revision['ITEM_ID'], $patch, $adminId, true);
    }

    public function merge($adminId, $sourceId, $targetId, $preferredFields = array(), $primaryMediaId = 0)
    {
        if ($sourceId === $targetId) throw new ParabdException('VALIDATION_ERROR', 'Source et cible doivent être différentes.');
        $connection = $this->connection(); Db_autocommit(false, $connection);
        try {
            $locked = $this->model('Parabditem')->lockPair($sourceId, $targetId);
            if (count($locked) !== 2) throw new ParabdException('NOT_FOUND', 'Une fiche à fusionner est introuvable.');
            foreach ($preferredFields as $field => $value) {
                if (in_array($field, array('TITLE','DESCRIPTION','MATERIAL','COLOR','WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G','SCALE','RELEASE_DATE','DATE_PRECISION','PRINT_RUN','MANUFACTURER','PUBLISHER','LICENSE_NAME','RANGE_NAME','UNIVERSE_NAME'), true)) {
                    $this->model('Parabditem')->updateFields($targetId, array($field => $value), $adminId);
                }
            }
            $this->model('Parabditemauthor')->mergeInto($sourceId, $targetId);
            $this->model('Parabditemseries')->mergeInto($sourceId, $targetId);
            $this->model('Parabditemtome')->mergeInto($sourceId, $targetId);
            $this->model('Parabdidentifier')->moveToItem($sourceId, $targetId);
            $this->model('Parabdmedia')->moveToItem($sourceId, $targetId);
            $this->model('Parabdsource')->moveToItem($sourceId, $targetId);
            $this->model('Parabdrevision')->moveToItem($sourceId, $targetId);
            $this->model('Parabddiscussion')->moveToItem($sourceId, $targetId);
            $this->model('Userparabd')->moveItem($sourceId, $targetId);
            if ($primaryMediaId) $this->model('Parabdmedia')->selectPrimary($targetId, $primaryMediaId);
            $this->model('Parabditem')->markMerged($sourceId, $targetId, $adminId);
            $this->model('Parabdduplicate')->markMergedForItem($sourceId, $adminId);
            $audit = json_encode(array('source_id' => intval($sourceId), 'target_id' => intval($targetId)), JSON_UNESCAPED_UNICODE);
            $this->model('Parabdrevision')->addMergeAudit($targetId, $adminId, $audit);
            foreach ($this->searchDuplicatesForItem($targetId) as $duplicate) $this->recordDuplicate($targetId, intval($duplicate['ID_ITEM']), $duplicate);
            Db_commit($connection); Db_autocommit(true, $connection);
        } catch (Throwable $error) { Db_rollback($connection); Db_autocommit(true, $connection); throw $error; }
    }
}
