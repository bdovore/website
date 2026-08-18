<?php

$database = getenv('PARABD_TEST_DATABASE');
if (!$database || strpos($database, 'parabd_test') !== 0) {
    fwrite(STDERR, "PARABD_TEST_DATABASE doit commencer par parabd_test.\n");
    exit(2);
}

$host = getenv('PARABD_TEST_HOST') ?: 'localhost';
$user = getenv('PARABD_TEST_USER') ?: 'root';
$password = getenv('PARABD_TEST_PASSWORD') === false ? 'root' : getenv('PARABD_TEST_PASSWORD');
$socket = getenv('PARABD_TEST_SOCKET') ?: ini_get('mysqli.default_socket');
$port = intval(getenv('PARABD_TEST_PORT') ?: ini_get('mysqli.default_port') ?: 3306);
$server = new mysqli($host, $user, $password, '', $port, $socket);
if ($server->connect_errno) { fwrite(STDERR, $server->connect_error . "\n"); exit(2); }

$failures = 0; $checks = 0; $imageRoot = sys_get_temp_dir() . '/bdovore-parabd-mysql-test-' . getmypid();
function dbAssert($condition, $message) { global $failures, $checks; $checks++; if (!$condition) { $failures++; fwrite(STDERR, "ECHEC: $message\n"); } }
function drainMulti($db) { do { if ($result = $db->store_result()) $result->free(); } while ($db->more_results() && $db->next_result()); if ($db->errno) throw new RuntimeException($db->error); }
function removeTestTree($path) { if (!is_dir($path)) return; foreach (scandir($path) as $name) { if ($name === '.' || $name === '..') continue; $child = $path . DIRECTORY_SEPARATOR . $name; is_dir($child) ? removeTestTree($child) : unlink($child); } rmdir($path); }

try {
    $server->query("DROP DATABASE IF EXISTS `$database`");
    if (!$server->query("CREATE DATABASE `$database` CHARACTER SET utf8 COLLATE utf8_unicode_ci")) throw new RuntimeException($server->error);
    $server->select_db($database);
    $history = "
        CREATE TABLE users (user_id MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY, username VARCHAR(25) NOT NULL DEFAULT '', level TINYINT UNSIGNED NOT NULL DEFAULT 2, OPEN_COLLEC CHAR(1) NOT NULL DEFAULT 'Y', LAST_UPDATE TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        CREATE TABLE users_album (user_id MEDIUMINT UNSIGNED NOT NULL, date_ajout DATETIME NULL) ENGINE=MyISAM;
        CREATE TABLE users_alb_prop (USER_ID MEDIUMINT UNSIGNED NOT NULL, PROP_DTE DATETIME NOT NULL, STATUS TINYINT NOT NULL DEFAULT 0) ENGINE=MyISAM;
        CREATE TABLE users_comment (USER_ID MEDIUMINT UNSIGNED NOT NULL, DTE_POST DATETIME NOT NULL) ENGINE=MyISAM;
        CREATE TABLE bd_auteur (ID_AUTEUR MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY, PSEUDO VARCHAR(100), PRENOM VARCHAR(100), NOM VARCHAR(100)) ENGINE=MyISAM;
        CREATE TABLE bd_serie (ID_SERIE MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY, NOM VARCHAR(150)) ENGINE=MyISAM;
        CREATE TABLE bd_tome (ID_TOME MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY, TITRE VARCHAR(150)) ENGINE=MyISAM;
        CREATE TABLE bd_edition (ID_EDITION MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY, USER_ID MEDIUMINT UNSIGNED NULL, PROP_STATUS TINYINT NOT NULL DEFAULT 0) ENGINE=MyISAM;
    ";
    if (!$server->multi_query($history)) throw new RuntimeException($server->error); drainMulti($server);
    $migration = file_get_contents(dirname(__DIR__) . '/sql/2026-08-09-create-parabd.sql');
    if (!$server->multi_query($migration)) throw new RuntimeException($server->error); drainMulti($server);
    $discussionMigration = file_get_contents(dirname(__DIR__) . '/sql/2026-08-13-add-parabd-discussion.sql');
    if (!$server->multi_query($discussionMigration)) throw new RuntimeException($server->error); drainMulti($server);
    $server->query("INSERT INTO users (user_id,username,level,OPEN_COLLEC,CREATED_AT) VALUES (1,'creator',2,'Y','2020-01-01'),(2,'contributor',2,'Y','2020-01-01'),(3,'voter1',2,'Y','2020-01-01'),(4,'voter2',2,'Y','2020-01-01'),(5,'collector',2,'Y','2020-01-01')");
    $server->query("INSERT INTO bd_auteur (ID_AUTEUR,PSEUDO) VALUES (10,'Auteur admin')");
    $server->query("INSERT INTO bd_serie (ID_SERIE,NOM) VALUES (20,'Série admin')");
    $server->query("INSERT INTO bd_tome (ID_TOME,TITRE) VALUES (30,'Album admin')");

    if (!defined('DEBUG')) define('DEBUG', false);
    if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
    if (!defined('BDO_DIR')) define('BDO_DIR', dirname(__DIR__) . DS);
    if (!defined('BDO_DIR_PARABD')) define('BDO_DIR_PARABD', $imageRoot . DS);
    if (!defined('BDO_PARABD_CHARTER_VERSION')) define('BDO_PARABD_CHARTER_VERSION', '1');
    if (!defined('BDO_PARABD_MAX_UPLOAD_BYTES')) define('BDO_PARABD_MAX_UPLOAD_BYTES', 5242880);
    if (!defined('BDO_PARABD_MAX_IMAGE_PIXELS')) define('BDO_PARABD_MAX_IMAGE_PIXELS', 30000000);
    if (!defined('BDO_PARABD_CREATIONS_PER_HOUR')) define('BDO_PARABD_CREATIONS_PER_HOUR', 10);
    if (!defined('BDO_PARABD_UPLOADS_PER_HOUR')) define('BDO_PARABD_UPLOADS_PER_HOUR', 20);
    class Bdo_Cfg {
        private static $values = array();
        public static function getVar($key) { return isset(self::$values[$key]) ? self::$values[$key] : null; }
        public static function setVar($key, $value) { self::$values[$key] = $value; }
        public static function schema() { return self::getVar('schema'); }
    }
    class ParabdTestSchema {
        public $schema;
        public $dbColumn = array();
        public $dbConstraint = array();

        public function __construct($connection, $schema) {
            $this->schema = $schema;
            $escaped = $connection->real_escape_string($schema);
            $columns = $connection->query("SELECT TABLE_NAME,COLUMN_NAME,COLUMN_DEFAULT,IS_NULLABLE,DATA_TYPE,CHARACTER_MAXIMUM_LENGTH,COLUMN_TYPE,COLUMN_KEY,EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$escaped'");
            while ($column = $columns->fetch_object()) {
                $column->TITRE_CHAMP = $column->COLUMN_NAME;
                $column->EXTRA_CHAMP = '';
                if (in_array($column->DATA_TYPE, array('enum', 'set'), true)) {
                    $values = preg_replace("#(?:enum|set)\\('([^\\)].*)'\\)$#i", '$1', $column->COLUMN_TYPE);
                    $column->TAB_CHECK_VALUE = explode("','", $values);
                }
                $this->dbColumn[$column->TABLE_NAME][$column->COLUMN_NAME] = $column;
            }
            $constraints = $connection->query("SELECT k.TABLE_NAME,k.COLUMN_NAME,k.CONSTRAINT_NAME,c.CONSTRAINT_TYPE,k.REFERENCED_TABLE_NAME,k.REFERENCED_COLUMN_NAME FROM information_schema.TABLE_CONSTRAINTS c JOIN information_schema.KEY_COLUMN_USAGE k USING (TABLE_NAME,CONSTRAINT_NAME,CONSTRAINT_SCHEMA) WHERE c.CONSTRAINT_SCHEMA='$escaped'");
            while ($constraint = $constraints->fetch_object()) {
                $this->dbConstraint[$constraint->TABLE_NAME][$constraint->CONSTRAINT_NAME][$constraint->COLUMN_NAME] = $constraint;
            }
            foreach ($this->dbColumn as $table => $unused) if (!isset($this->dbConstraint[$table])) $this->dbConstraint[$table] = array();
        }

        public function is_table($table) { return !empty($this->dbColumn[$table]); }
    }
    class User { public static function minAccesslevel($level = 5) { return isset($_SESSION['userConnect']->level) && $_SESSION['userConnect']->level <= $level; } }
    Bdo_Cfg::setVar('connexion', $server);
    Bdo_Cfg::setVar('schema', new ParabdTestSchema($server, $database));
    $_SESSION['userConnect'] = (object) array('user_id' => 1, 'level' => 2);
    require_once dirname(__DIR__) . '/inc/mysql.inc.php';
    require_once dirname(__DIR__) . '/inc/util.inc.php';
    require_once dirname(__DIR__) . '/library/Bdo/Security.php';
    require_once dirname(__DIR__) . '/mvc/models/ParabdService.php';
    $service = new ParabdService();
    foreach (range(1, 5) as $userId) $service->acceptCharter($userId, true);
    dbAssert($service->hasAcceptedCharter(1), 'charte courante reconnue comme acceptée');
    $server->query("UPDATE parabd_user_profile SET CHARTER_VERSION='ancienne' WHERE USER_ID=1");
    dbAssert(!$service->hasAcceptedCharter(1), 'nouvelle version de charte à accepter de nouveau');
    $service->acceptCharter(1, true);
    dbAssert($service->hasAcceptedCharter(1), 'acceptation de la nouvelle version mémorisée');
    $charter = $service->getCharterAcceptance(1);
    dbAssert($charter['accepted'] && $charter['accepted_version'] === BDO_PARABD_CHARTER_VERSION && !empty($charter['accepted_at']), 'statut et date de la charte disponibles pour le profil');
    $service->setCharterAcceptance(1, false);
    $charter = $service->getCharterAcceptance(1);
    dbAssert(!$charter['accepted'] && $charter['accepted_version'] === null && $charter['accepted_at'] === null, 'acceptation de la charte révocable depuis le profil');
    try { $service->requireCharter(1); dbAssert(false, 'contribution bloquée après retrait de la charte'); }
    catch (ParabdException $expected) { dbAssert($expected->errorCode === 'VALIDATION_ERROR', 'contribution bloquée après retrait de la charte'); }
    $service->setCharterAcceptance(1, true);

    mkdir($imageRoot, 0775, true);
    $validPath = $imageRoot . '/valid.jpg';
    $image = imagecreatetruecolor(120, 80); imagejpeg($image, $validPath, 90); imagedestroy($image);
    $validUpload = array('tmp_name' => $validPath, 'name' => 'valid.jpg', 'size' => filesize($validPath), 'error' => UPLOAD_ERR_OK);
    $badPath = $imageRoot . '/bad.jpg'; file_put_contents($badPath, '<html>not an image</html>');
    $badUpload = array('tmp_name' => $badPath, 'name' => 'bad.jpg', 'size' => filesize($badPath), 'error' => UPLOAD_ERR_OK);
    try { $service->createItem(1, array('title' => 'Invalide', 'type_code' => 'STATUETTE'), $badUpload); dbAssert(false, 'upload invalide refusé'); } catch (ParabdException $expected) { dbAssert($expected->errorCode === 'VALIDATION_ERROR', 'upload invalide refusé'); }
    $row = $server->query('SELECT COUNT(*) total FROM parabd_item')->fetch_assoc(); dbAssert(intval($row['total']) === 0, 'transaction annulée sur upload invalide');

    $created = $service->createItem(1, array('title' => 'Statuette test', 'type_code' => 'STATUETTE', 'description' => 'Initiale', 'manufacturer' => 'Pixi', 'release_date' => '2025', 'width_mm' => '100', 'identifier_scheme' => 'EAN13', 'identifier_value' => '4006381333931', 'is_explicit' => 1, 'collection_action' => 'none'), $validUpload);
    $itemId = $created['item_id']; dbAssert($itemId > 0, 'création sans rattachement BD');
    $blurredItem = $service->getItem($itemId); dbAssert(strpos($blurredItem['media'][0]['FILE_PATH'], '?source=') === 0, 'visuel explicite flouté par défaut');
    Bdo_Cfg::setVar('explicit', 1);
    $visibleItem = $service->getItem($itemId); dbAssert(strpos($visibleItem['media'][0]['FILE_PATH'], '?source=') !== 0, 'visuel explicite affiché après autorisation');
    Bdo_Cfg::setVar('explicit', 0);
    $plainMediaId = $service->addMedia(1, $itemId, $validUpload, 'DETAIL', '', false);
    $mixedMediaItem = $service->getItem($itemId); $mixedPaths = array(); foreach ($mixedMediaItem['media'] as $media) $mixedPaths[intval($media['ID_MEDIA'])] = $media['FILE_PATH'];
    dbAssert(strpos($mixedPaths[intval($mixedMediaItem['media'][0]['ID_MEDIA'])], '?source=') === 0 && strpos($mixedPaths[$plainMediaId], '?source=') !== 0, 'floutage limité au seul visuel marqué explicite');
    $itemExplicitColumn = $server->query("SELECT COUNT(*) total FROM information_schema.columns WHERE table_schema='" . $server->real_escape_string($database) . "' AND table_name='parabd_item' AND column_name='IS_EXPLICIT'")->fetch_assoc();
    $mediaExplicitColumn = $server->query("SELECT COUNT(*) total FROM information_schema.columns WHERE table_schema='" . $server->real_escape_string($database) . "' AND table_name='parabd_media' AND column_name='IS_EXPLICIT'")->fetch_assoc();
    dbAssert(intval($itemExplicitColumn['total']) === 0 && intval($mediaExplicitColumn['total']) === 1, 'contenu explicite stocké sur chaque média et non sur la fiche');
    $tomePositionColumns = $server->query("SELECT COUNT(*) total FROM information_schema.columns WHERE table_schema='" . $server->real_escape_string($database) . "' AND table_name='parabd_item_tome' AND column_name IN ('PAGE_NO','PANEL_NO')")->fetch_assoc();
    dbAssert(intval($tomePositionColumns['total']) === 0, 'schéma de rattachement album sans page ni case');
    try { $service->createItem(2, array('title' => 'Copie', 'type_code' => 'STATUETTE', 'identifier_scheme' => 'EAN13', 'identifier_value' => '4006381333931'), $validUpload); dbAssert(false, 'doublon exact bloqué'); } catch (ParabdException $expected) { dbAssert($expected->errorCode === 'DUPLICATE_EXACT', 'doublon exact bloqué'); }
    try { $service->createItem(2, array('title' => 'Statuette test', 'type_code' => 'STATUETTE', 'manufacturer' => 'Pixi', 'release_date' => '2025', 'width_mm' => '102'), $validUpload); dbAssert(false, 'doublon fort exigeant un motif'); } catch (ParabdException $expected) { dbAssert($expected->errorCode === 'VALIDATION_ERROR', 'doublon fort exigeant un motif'); }
    $distinct = $service->createItem(2, array('title' => 'Statuette test', 'type_code' => 'STATUETTE', 'manufacturer' => 'Pixi', 'release_date' => '2025', 'width_mm' => '102', 'duplicate_reviewed' => 1, 'duplicate_reason' => 'Variante collector vérifiée'), $validUpload);
    dbAssert($distinct['item_id'] > 0, 'doublon fort contournable avec motif et sans identifiant');
    $duplicateRow = $server->query('SELECT ID_DUPLICATE FROM parabd_duplicate WHERE STATUS=\'OPEN\' LIMIT 1')->fetch_assoc();
    $service->resolveDuplicate(1, intval($duplicateRow['ID_DUPLICATE']), 'IGNORED');
    $duplicateStatus = $server->query('SELECT STATUS FROM parabd_duplicate WHERE ID_DUPLICATE=' . intval($duplicateRow['ID_DUPLICATE']))->fetch_assoc();
    dbAssert($duplicateStatus['STATUS'] === 'IGNORED', 'résolution de doublon déléguée au modèle');
    $reportId = $service->report(2, 'ITEM', $itemId, 'Information à vérifier', 'Test de signalement');
    $openReport = $service->getOpenReportForItem($reportId, $itemId);
    dbAssert($openReport && intval($openReport['ID_ITEM']) === intval($itemId) && $openReport['TITLE'] === 'Statuette test', 'signalement ouvert rattaché à sa fiche administrative');
    $service->resolveReport(1, $reportId, 'RESOLVED');
    $reportStatus = $server->query('SELECT STATUS FROM parabd_report WHERE ID_REPORT=' . intval($reportId))->fetch_assoc();
    dbAssert($reportStatus['STATUS'] === 'RESOLVED', 'création et résolution de signalement déléguées au modèle');
    $service->moderateItem(1, $distinct['item_id'], 'HIDDEN');
    $service->moderateItem(1, $distinct['item_id'], 'ACTIVE');
    dbAssert($service->getItem($distinct['item_id'])['STATUS'] === 'ACTIVE', 'modération de fiche déléguée au modèle');
    $moderationRows = $server->query("SELECT COUNT(*) total FROM parabd_revision WHERE ITEM_ID=" . intval($distinct['item_id']) . " AND CHANGE_KIND='MODERATION'")->fetch_assoc();
    dbAssert(intval($moderationRows['total']) === 2, 'masquage et restauration historisés');

    $firstCopy = $service->saveCopy(1, array('item_id' => $itemId, 'state' => 'OWNED', 'quantity' => 2, 'copy_number' => '12/100', 'condition_code' => 'GOOD', 'copy_is_signed' => '1', 'copy_is_dedicated' => '0', 'has_box' => '1', 'copy_has_certificate' => '1', 'is_gift' => '1', 'purchase_date' => '09/08/2026', 'price' => '42.50', 'currency' => 'EUR', 'seller' => 'Privé', 'estimated_value' => '70', 'personal_notes' => 'Secret'));
    $service->saveCopy(1, array('item_id' => $itemId, 'state' => 'OWNED', 'copy_number' => '13/100'));
    dbAssert(count($service->getUserCopies(1, 'OWNED')) === 2, 'plusieurs exemplaires conservés');
    $service->saveCopy(1, array('item_id' => $itemId, 'copy_id' => $firstCopy, 'state' => 'OWNED', 'quantity' => 3, 'copy_number' => '12/100', 'condition_code' => 'VERY_GOOD', 'copy_is_signed' => '1', 'copy_is_dedicated' => '1', 'has_box' => '1', 'copy_has_certificate' => '0', 'is_gift' => '1', 'purchase_date' => '2026-08-10', 'price' => '45', 'currency' => 'EUR', 'seller' => 'Vendeur privé', 'estimated_value' => '75', 'personal_notes' => 'Note privée modifiée'));
    $ownCopies = $service->getUserCopies(1, 'OWNED'); $editedCopy = null; foreach ($ownCopies as $copy) if (intval($copy['ID_COPY']) === intval($firstCopy)) $editedCopy = $copy;
    dbAssert($editedCopy && intval($editedCopy['QUANTITY']) === 3 && $editedCopy['PURCHASE_DATE'] === '2026-08-10' && $editedCopy['CONDITION_CODE'] === 'VERY_GOOD' && intval($editedCopy['COPY_IS_DEDICATED']) === 1 && $editedCopy['PERSONAL_NOTES'] === 'Note privée modifiée', 'toutes les informations personnelles de l’exemplaire sont modifiables');
    $service->saveCopy(1, array('item_id' => $distinct['item_id'], 'state' => 'WISHLIST'));
    $public = $service->getPublicUserCollection(1);
    dbAssert(count($public) === 1 && intval($public[0]['ITEM_ID']) === intval($itemId), 'page guest limitée à la liste dédupliquée des objets possédés et excluant la wishlist');
    $guestAllowedFields = array('ITEM_ID','TITLE','TYPE_ID','TYPE_LABEL','SUBTYPE_LABEL','PRIMARY_IMAGE','PRIMARY_IMAGE_IS_EXPLICIT');
    dbAssert(count(array_diff(array_keys($public[0]), $guestAllowedFields)) === 0, 'aucune information personnelle d’exemplaire récupérée pour la page guest');
    $visibilityColumn = $server->query("SELECT COUNT(*) total FROM information_schema.columns WHERE table_schema='" . $server->real_escape_string($database) . "' AND table_name='users_parabd' AND column_name='IS_PUBLIC'")->fetch_assoc(); dbAssert(intval($visibilityColumn['total']) === 0, 'aucun réglage de visibilité par exemplaire dans le schéma initial');
    $priceVisibilityColumn = $server->query("SELECT COUNT(*) total FROM information_schema.columns WHERE table_schema='" . $server->real_escape_string($database) . "' AND table_name='users_parabd' AND column_name='IS_PRICE_PUBLIC'")->fetch_assoc(); dbAssert(intval($priceVisibilityColumn['total']) === 0, 'aucun réglage de partage du prix dans le schéma initial');
    try { $service->saveCopy(2, array('item_id' => $itemId, 'copy_id' => $firstCopy, 'state' => 'WISHLIST')); dbAssert(false, 'modification d’un exemplaire tiers refusée'); } catch (ParabdException $expected) { dbAssert($expected->errorCode === 'NOT_FOUND', 'modification d’un exemplaire tiers refusée'); }

    $copiesBeforeAdminEdit = count($service->getUserCopies(1));
    $discussionBeforeAdminEdit = $service->getDiscussion($itemId, true);
    $adminItem = $service->getAdminItem($itemId); $primaryMediaId = intval($adminItem['media'][0]['ID_MEDIA']);
    $service->adminUpdateItem(1, $itemId, array('type_code' => 'STATUETTE', 'title' => 'Statuette administrée', 'description' => 'Fiche commune modifiée directement', 'manufacturer' => 'Pixi', 'publisher' => 'Éditeur admin', 'release_date' => '2026', 'status' => 'ACTIVE', 'primary_media_id' => $primaryMediaId, 'media_explicit' => array($primaryMediaId => 1), 'identifiers' => array(array('scheme' => 'EAN13', 'issuer' => '', 'value' => '4006381333931')), 'authors' => array(array('id' => 10, 'role' => 'ARTIST')), 'series' => array(array('id' => 20, 'relation_type' => 'RELATED')), 'tomes' => array(array('id' => 30, 'relation_type' => 'RELATED')), 'sources' => array(array('url' => 'https://example.test/catalogue', 'label' => 'Catalogue admin', 'notes' => 'Vérifié'))));
    $adminEdited = $service->getAdminItem($itemId); $adminHistory = $service->getAdminItemHistory($itemId);
    dbAssert($adminEdited['TITLE'] === 'Statuette administrée' && $adminEdited['PUBLISHER'] === 'Éditeur admin' && count($adminEdited['authors']) === 1 && count($adminEdited['series']) === 1 && count($adminEdited['tomes']) === 1 && count($adminEdited['sources']) === 1 && intval($adminEdited['media'][0]['IS_EXPLICIT']) === 1, 'édition admin directe de toute la fiche commune et du statut explicite par média');
    dbAssert(!array_key_exists('PAGE_NO', $adminEdited['tomes'][0]) && !array_key_exists('PANEL_NO', $adminEdited['tomes'][0]), 'rattachement album sans notion de page ni de case');
    dbAssert(count($service->getUserCopies(1)) === $copiesBeforeAdminEdit, 'édition admin sans impact sur la collection personnelle');
    dbAssert($adminHistory[0]['STATUS'] === 'ACCEPTED' && $adminHistory[0]['CHANGE_KIND'] === 'UPDATE' && !empty($adminHistory[0]['PATCH_BEFORE']) && !empty($adminHistory[0]['PATCH_AFTER']), 'édition admin ajoutée à l’historique complet');
    dbAssert(count($service->getDiscussion($itemId, true)['entries']) === count($discussionBeforeAdminEdit['entries']), 'édition admin directe absente de la discussion');

    $mediaCountBeforeAdminAdd = count($adminEdited['media']);
    $historyCountBeforeAdminAdd = count($adminHistory);
    $firstAdminMedia = $service->adminAddMedia(1, $itemId, array('media_type' => 'BOX', 'is_explicit' => 1), $validUpload);
    $secondAdminMedia = $service->adminAddMedia(1, $itemId, array('media_type' => 'DETAIL', 'is_primary' => 1), $validUpload);
    $afterAdminMedia = $service->getAdminItem($itemId); $addedMedia = array(); foreach ($afterAdminMedia['media'] as $media) $addedMedia[intval($media['ID_MEDIA'])] = $media;
    $historyAfterAdminMedia = $service->getAdminItemHistory($itemId);
    dbAssert(count($afterAdminMedia['media']) === $mediaCountBeforeAdminAdd + 2 && isset($addedMedia[$firstAdminMedia['media_id']]) && isset($addedMedia[$secondAdminMedia['media_id']]), 'plusieurs visuels admin ajoutés successivement');
    dbAssert($addedMedia[$firstAdminMedia['media_id']]['MEDIA_TYPE'] === 'BOX' && intval($addedMedia[$firstAdminMedia['media_id']]['IS_EXPLICIT']) === 1 && intval($addedMedia[$secondAdminMedia['media_id']]['IS_PRIMARY']) === 1, 'options du formulaire visuel enregistrées indépendamment');
    dbAssert($afterAdminMedia['TITLE'] === $adminEdited['TITLE'] && $afterAdminMedia['DESCRIPTION'] === $adminEdited['DESCRIPTION'] && count($afterAdminMedia['authors']) === count($adminEdited['authors']) && count($afterAdminMedia['sources']) === count($adminEdited['sources']), 'ajout de visuel sans modification des autres informations de la fiche');
    dbAssert(count($historyAfterAdminMedia) === $historyCountBeforeAdminAdd + 2 && $historyAfterAdminMedia[0]['STATUS'] === 'ACCEPTED', 'chaque ajout de visuel historisé séparément');

    $adminCreated = $service->adminCreateItem(1, array('title' => 'Création administrative', 'type_code' => 'STATUETTE', 'description' => 'Fiche créée pour la communauté', 'manufacturer' => 'Admin', 'status' => 'HIDDEN', 'is_explicit' => 1, 'collection_action' => 'OWNED', 'authors' => array(array('id' => 10, 'role' => 'ARTIST'))), $validUpload);
    $adminCreatedHistory = $service->getAdminItemHistory($adminCreated['item_id']);
    $adminCopyCount = $server->query('SELECT COUNT(*) total FROM users_parabd WHERE ITEM_ID=' . intval($adminCreated['item_id']))->fetch_assoc();
    dbAssert(intval($adminCopyCount['total']) === 0, 'création admin indépendante de la collection même si une action personnelle est envoyée');
    $adminCreatedItem = $service->getAdminItem($adminCreated['item_id']);
    dbAssert(count($adminCreatedHistory) === 1 && $adminCreatedHistory[0]['CHANGE_KIND'] === 'CREATE' && $adminCreatedHistory[0]['STATUS'] === 'ACCEPTED' && $adminCreatedItem['STATUS'] === 'ACTIVE' && intval($adminCreatedItem['media'][0]['IS_EXPLICIT']) === 1, 'création admin historisée active avec statut explicite du visuel');

    $adminAccepted = $service->contribute(2, $itemId, intval($service->getItem($itemId)['REVISION_NO']), 'DESCRIPTION', 'Validée par administration');
    $adminAcceptedResult = $service->resolveRevision(1, $adminAccepted['revision_id'], true);
    $adminAcceptedRow = $server->query('SELECT STATUS FROM parabd_revision WHERE ID_REVISION=' . intval($adminAccepted['revision_id']))->fetch_assoc();
    dbAssert($adminAcceptedResult['item_id'] === $itemId && $adminAcceptedRow['STATUS'] === 'ACCEPTED' && $service->getItem($itemId)['DESCRIPTION'] === 'Validée par administration', 'administrateur validant une modification en attente depuis la fiche');
    $adminRejected = $service->contribute(2, $itemId, intval($service->getItem($itemId)['REVISION_NO']), 'DESCRIPTION', 'Modification invalidée');
    $adminRejectedResult = $service->resolveRevision(1, $adminRejected['revision_id'], false);
    $adminRejectedRow = $server->query('SELECT STATUS FROM parabd_revision WHERE ID_REVISION=' . intval($adminRejected['revision_id']))->fetch_assoc();
    dbAssert($adminRejectedResult['item_id'] === $itemId && $adminRejectedRow['STATUS'] === 'REJECTED' && $service->getItem($itemId)['DESCRIPTION'] === 'Validée par administration', 'administrateur invalidant une modification en attente depuis la fiche');
    $adminRejectedApplied = $service->contribute(2, $itemId, intval($service->getItem($itemId)['REVISION_NO']), 'LICENSE_NAME', 'Licence temporaire');
    dbAssert($adminRejectedApplied['status'] === 'APPLIED' && $service->getItem($itemId)['LICENSE_NAME'] === 'Licence temporaire', 'enrichissement de champ vide appliqué immédiatement');
    $pendingIds = array_map(function ($row) { return intval($row['ID_REVISION']); }, $service->getRevisionsForItem($itemId));
    dbAssert(!in_array(intval($adminRejectedApplied['revision_id']), $pendingIds, true), 'enrichissement immédiat absent des validations à traiter');

    $revision = $service->contribute(2, $itemId, intval($service->getItem($itemId)['REVISION_NO']), 'DESCRIPTION', 'Corrigée');
    dbAssert($revision['status'] === 'PENDING', 'remplacement non fiable en attente');
    try { $service->vote(2, $revision['revision_id'], 'CONFIRM'); dbAssert(false, 'auteur ne validant pas sa propre proposition'); } catch (ParabdException $expected) { dbAssert($expected->errorCode === 'VALIDATION_ERROR', 'auteur ne validant pas sa propre proposition'); }
    $service->vote(1, $revision['revision_id'], 'CONFIRM'); $vote = $service->vote(3, $revision['revision_id'], 'CONFIRM');
    $item = $service->getItem($itemId); dbAssert($vote['status'] === 'ACCEPTED' && $item['DESCRIPTION'] === 'Corrigée', 'correction appliquée après deux votes');
    $conflict = $service->contribute(2, $itemId, intval($item['REVISION_NO']), 'DESCRIPTION', 'Contestée');
    try { $service->vote(2, $conflict['revision_id'], 'CONTEST'); dbAssert(false, 'auteur ne contestant pas sa propre proposition'); } catch (ParabdException $expected) { dbAssert($expected->errorCode === 'VALIDATION_ERROR', 'auteur ne contestant pas sa propre proposition'); }
    try { $service->vote(1, $conflict['revision_id'], 'CONTEST'); dbAssert(false, 'opposition sans motif refusée'); } catch (ParabdException $expected) { dbAssert($expected->errorCode === 'VALIDATION_ERROR', 'opposition sans motif refusée'); }
    $vote = $service->vote(1, $conflict['revision_id'], 'CONTEST', 'La source indique une autre description.');
    dbAssert($vote['status'] === 'PENDING' && $vote['admin_intervention'], 'opposition motivée laissant la demande en attente administrative');
    $oppositionComment = $server->query("SELECT BODY FROM parabd_discussion WHERE REVISION_ID=" . intval($conflict['revision_id']) . " AND MESSAGE_TYPE='COMMENT'")->fetch_assoc();
    dbAssert($oppositionComment && $oppositionComment['BODY'] === 'La source indique une autre description.', 'motif de l’opposition ajouté à la discussion');
    $service->vote(1, $conflict['revision_id'], 'CONFIRM'); $changedVote = $service->vote(3, $conflict['revision_id'], 'CONFIRM');
    dbAssert($changedVote['status'] === 'ACCEPTED' && $service->getItem($itemId)['DESCRIPTION'] === 'Contestée', 'membre pouvant changer son opposition puis obtenir deux confirmations');

    $stale = $service->contribute(2, $itemId, intval($service->getItem($itemId)['REVISION_NO']), 'DESCRIPTION', 'Proposition devenue obsolète');
    $adminCurrent = $service->getAdminItem($itemId); $adminPrimary = intval($adminCurrent['media'][0]['ID_MEDIA']);
    $service->adminUpdateItem(1, $itemId, array('type_code' => $adminCurrent['TYPE_CODE'], 'subtype_code' => $adminCurrent['SUBTYPE_CODE'], 'title' => $adminCurrent['TITLE'], 'description' => 'Valeur administrative plus récente', 'manufacturer' => $adminCurrent['MANUFACTURER'], 'publisher' => $adminCurrent['PUBLISHER'], 'release_date' => '2026', 'primary_media_id' => $adminPrimary, 'identifiers' => array(array('scheme' => 'EAN13', 'issuer' => '', 'value' => '4006381333931')), 'authors' => array(array('id' => 10, 'role' => 'ARTIST')), 'series' => array(array('id' => 20, 'relation_type' => 'RELATED')), 'tomes' => array(array('id' => 30, 'relation_type' => 'RELATED')), 'sources' => array(array('url' => 'https://example.test/catalogue', 'label' => 'Catalogue admin', 'notes' => 'Vérifié'))));
    $service->vote(3, $stale['revision_id'], 'CONFIRM'); $staleVote = $service->vote(4, $stale['revision_id'], 'CONFIRM');
    dbAssert($staleVote['status'] === 'PENDING' && $staleVote['admin_intervention'] && $service->getItem($itemId)['DESCRIPTION'] === 'Valeur administrative plus récente', 'valeur devenue obsolète suspendant l’application automatique');
    $service->resolveRevision(1, $stale['revision_id'], true);
    dbAssert($service->getItem($itemId)['DESCRIPTION'] === 'Proposition devenue obsolète', 'administrateur pouvant valider explicitement une proposition obsolète');

    $generalCommentId = $service->addDiscussionComment(4, $itemId, 0, 'Commentaire général');
    $contextCommentId = $service->addDiscussionComment(4, $itemId, $conflict['revision_id'], 'Commentaire contextuel');
    try { $service->addDiscussionComment(4, $itemId, 999999, 'Mauvais contexte'); dbAssert(false, 'révision étrangère ou absente refusée'); } catch (ParabdException $expected) { dbAssert($expected->errorCode === 'VALIDATION_ERROR', 'révision étrangère ou absente refusée'); }
    $service->hideDiscussionComment(1, $generalCommentId);
    $memberDiscussion = $service->getDiscussion($itemId); $adminDiscussion = $service->getDiscussion($itemId, true);
    dbAssert(count($adminDiscussion['entries']) === count($memberDiscussion['entries']) + 1 && $contextCommentId > 0, 'commentaire masqué visible seulement dans l’administration');

    $source = $service->createItem(2, array('title' => 'Autre objet', 'type_code' => 'STATUETTE', 'manufacturer' => 'Autre', 'collection_action' => 'none'), $validUpload);
    $service->addDiscussionComment(4, $source['item_id'], 0, 'Discussion déplacée avec la fusion');
    $removableCopy = $service->saveCopy(4, array('item_id' => $source['item_id'], 'state' => 'OWNED'));
    $service->removeCopy(4, $removableCopy);
    dbAssert(count($service->getUserCopies(4, 'OWNED')) === 0, 'bouton de suppression retirant uniquement l’exemplaire possédé');
    $quickCopy = $service->saveCopy(5, array('item_id' => $source['item_id'], 'state' => 'WISHLIST'));
    $wishlist = $service->getUserCopies(5, 'WISHLIST'); dbAssert(count($wishlist) === 1 && intval($wishlist[0]['ID_COPY']) === intval($quickCopy), 'bouton Je veux ajoutant directement à la wishlist');
    $service->saveCopy(5, array('item_id' => $source['item_id'], 'copy_id' => $quickCopy, 'state' => 'OWNED'));
    $owned = $service->getUserCopies(5, 'OWNED'); dbAssert(count($owned) === 1 && intval($owned[0]['ID_COPY']) === intval($quickCopy), 'bouton J’ai transformant l’envie en exemplaire possédé');
    $media = $server->query('SELECT ID_MEDIA FROM parabd_media WHERE ITEM_ID=' . intval($itemId) . ' AND IS_PRIMARY=1')->fetch_assoc();
    $service->merge(1, $source['item_id'], $itemId, array(), intval($media['ID_MEDIA']));
    $merged = $service->getItem($source['item_id'], true); dbAssert($merged['STATUS'] === 'MERGED' && intval($merged['MERGED_INTO_ID']) === $itemId, 'redirection de fusion');
    $mergedAdminRows = $service->getAdminCatalogue((string) $source['item_id'], 'MERGED'); dbAssert(count($mergedAdminRows) === 1 && intval($mergedAdminRows[0]['ID_ITEM']) === intval($source['item_id']) && $service->getAdminItem($source['item_id']) !== null, 'fiche fusionnée toujours consultable dans l’administration');
    $row = $server->query('SELECT COUNT(*) total FROM users_parabd WHERE ITEM_ID=' . intval($itemId))->fetch_assoc(); dbAssert(intval($row['total']) === 3, 'fusion conservant tous les exemplaires');
    $discussionMoved = $server->query("SELECT COUNT(*) total FROM parabd_discussion WHERE ITEM_ID=" . intval($itemId) . " AND BODY='Discussion déplacée avec la fusion'")->fetch_assoc(); dbAssert(intval($discussionMoved['total']) === 1, 'fusion déplaçant la discussion vers la fiche conservée');

    $engines = $server->query("SELECT COUNT(*) total FROM information_schema.tables WHERE table_schema='" . $server->real_escape_string($database) . "' AND table_name LIKE '%parabd%' AND ENGINE='InnoDB' AND TABLE_COLLATION='utf8_unicode_ci'")->fetch_assoc();
    dbAssert(intval($engines['total']) === 15, 'tables Para-BD InnoDB utf8_unicode_ci');
} catch (Throwable $error) {
    $failures++; fwrite(STDERR, "ERREUR: " . $error->getMessage() . "\n");
} finally {
    removeTestTree($imageRoot);
    $server->query("DROP DATABASE IF EXISTS `$database`");
    $server->close();
}

echo $checks . " contrôles MySQL, " . $failures . " échec(s).\n";
exit($failures ? 1 : 0);
