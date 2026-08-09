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
    $server->query("INSERT INTO users (user_id,username,level,OPEN_COLLEC,CREATED_AT) VALUES (1,'creator',2,'Y','2020-01-01'),(2,'contributor',2,'Y','2020-01-01'),(3,'voter1',2,'Y','2020-01-01'),(4,'voter2',2,'Y','2020-01-01'),(5,'collector',2,'Y','2020-01-01')");

    if (!defined('DEBUG')) define('DEBUG', false);
    if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
    if (!defined('BDO_DIR_PARABD')) define('BDO_DIR_PARABD', $imageRoot . DS);
    if (!defined('BDO_PARABD_CHARTER_VERSION')) define('BDO_PARABD_CHARTER_VERSION', '1');
    if (!defined('BDO_PARABD_MAX_UPLOAD_BYTES')) define('BDO_PARABD_MAX_UPLOAD_BYTES', 5242880);
    if (!defined('BDO_PARABD_MAX_IMAGE_PIXELS')) define('BDO_PARABD_MAX_IMAGE_PIXELS', 30000000);
    if (!defined('BDO_PARABD_CREATIONS_PER_HOUR')) define('BDO_PARABD_CREATIONS_PER_HOUR', 10);
    if (!defined('BDO_PARABD_UPLOADS_PER_HOUR')) define('BDO_PARABD_UPLOADS_PER_HOUR', 20);
    class Bdo_Cfg { private static $values = array(); public static function getVar($key) { return isset(self::$values[$key]) ? self::$values[$key] : null; } public static function setVar($key, $value) { self::$values[$key] = $value; } }
    class User { public static function minAccesslevel($level = 5) { return isset($_SESSION['userConnect']->level) && $_SESSION['userConnect']->level <= $level; } }
    Bdo_Cfg::setVar('connexion', $server);
    $_SESSION['userConnect'] = (object) array('user_id' => 1, 'level' => 2);
    require_once dirname(__DIR__) . '/inc/mysql.inc.php';
    require_once dirname(__DIR__) . '/mvc/models/ParabdService.php';
    $service = new ParabdService();
    foreach (range(1, 5) as $userId) $service->acceptCharter($userId, true);

    mkdir($imageRoot, 0775, true);
    $validPath = $imageRoot . '/valid.jpg';
    $image = imagecreatetruecolor(120, 80); imagejpeg($image, $validPath, 90); imagedestroy($image);
    $validUpload = array('tmp_name' => $validPath, 'name' => 'valid.jpg', 'size' => filesize($validPath), 'error' => UPLOAD_ERR_OK);
    $badPath = $imageRoot . '/bad.jpg'; file_put_contents($badPath, '<html>not an image</html>');
    $badUpload = array('tmp_name' => $badPath, 'name' => 'bad.jpg', 'size' => filesize($badPath), 'error' => UPLOAD_ERR_OK);
    try { $service->createItem(1, array('title' => 'Invalide', 'type_code' => 'STATUETTE'), $badUpload); dbAssert(false, 'upload invalide refusé'); } catch (ParabdException $expected) { dbAssert($expected->errorCode === 'VALIDATION_ERROR', 'upload invalide refusé'); }
    $row = $server->query('SELECT COUNT(*) total FROM parabd_item')->fetch_assoc(); dbAssert(intval($row['total']) === 0, 'transaction annulée sur upload invalide');

    $created = $service->createItem(1, array('title' => 'Statuette test', 'type_code' => 'STATUETTE', 'description' => 'Initiale', 'manufacturer' => 'Pixi', 'release_date' => '2025', 'width_mm' => '100', 'identifier_scheme' => 'EAN13', 'identifier_value' => '4006381333931', 'collection_action' => 'none'), $validUpload);
    $itemId = $created['item_id']; dbAssert($itemId > 0, 'création sans rattachement BD');
    try { $service->createItem(2, array('title' => 'Copie', 'type_code' => 'STATUETTE', 'identifier_scheme' => 'EAN13', 'identifier_value' => '4006381333931'), $validUpload); dbAssert(false, 'doublon exact bloqué'); } catch (ParabdException $expected) { dbAssert($expected->errorCode === 'DUPLICATE_EXACT', 'doublon exact bloqué'); }
    try { $service->createItem(2, array('title' => 'Statuette test', 'type_code' => 'STATUETTE', 'manufacturer' => 'Pixi', 'release_date' => '2025', 'width_mm' => '102'), $validUpload); dbAssert(false, 'doublon fort exigeant un motif'); } catch (ParabdException $expected) { dbAssert($expected->errorCode === 'VALIDATION_ERROR', 'doublon fort exigeant un motif'); }
    $distinct = $service->createItem(2, array('title' => 'Statuette test', 'type_code' => 'STATUETTE', 'manufacturer' => 'Pixi', 'release_date' => '2025', 'width_mm' => '102', 'duplicate_reviewed' => 1, 'duplicate_reason' => 'Variante collector vérifiée'), $validUpload);
    dbAssert($distinct['item_id'] > 0, 'doublon fort contournable avec motif et sans identifiant');

    $firstCopy = $service->saveCopy(1, array('item_id' => $itemId, 'state' => 'OWNED', 'copy_number' => '12/100', 'price' => '42.50', 'currency' => 'EUR', 'is_public' => 1, 'seller' => 'Privé', 'personal_notes' => 'Secret'));
    $service->saveCopy(1, array('item_id' => $itemId, 'state' => 'OWNED', 'copy_number' => '13/100', 'is_public' => 1));
    dbAssert(count($service->getUserCopies(1, 'OWNED')) === 2, 'plusieurs exemplaires conservés');
    $public = $service->getUserCopies(1, 'OWNED', true); dbAssert(!array_key_exists('SELLER', $public[0]) && !array_key_exists('PERSONAL_NOTES', $public[0]) && $public[0]['PRICE'] === null, 'champs et prix privés absents du guest');
    try { $service->saveCopy(2, array('item_id' => $itemId, 'copy_id' => $firstCopy, 'state' => 'WISHLIST')); dbAssert(false, 'modification d’un exemplaire tiers refusée'); } catch (ParabdException $expected) { dbAssert($expected->errorCode === 'NOT_FOUND', 'modification d’un exemplaire tiers refusée'); }

    $revision = $service->contribute(2, $itemId, 1, 'DESCRIPTION', 'Corrigée');
    dbAssert($revision['status'] === 'PENDING', 'remplacement non fiable en attente');
    $service->vote(3, $revision['revision_id'], 'CONFIRM'); $vote = $service->vote(4, $revision['revision_id'], 'CONFIRM');
    $item = $service->getItem($itemId); dbAssert($vote['status'] === 'ACCEPTED' && $item['DESCRIPTION'] === 'Corrigée', 'correction appliquée après deux votes');
    $conflict = $service->contribute(2, $itemId, intval($item['REVISION_NO']), 'DESCRIPTION', 'Contestée');
    $vote = $service->vote(3, $conflict['revision_id'], 'CONTEST'); dbAssert($vote['status'] === 'CONFLICT', 'contestation envoyée en conflit');

    $source = $service->createItem(2, array('title' => 'Autre objet', 'type_code' => 'STATUETTE', 'manufacturer' => 'Autre', 'collection_action' => 'none'), $validUpload);
    $service->saveCopy(5, array('item_id' => $source['item_id'], 'state' => 'OWNED', 'is_public' => 1));
    $media = $server->query('SELECT ID_MEDIA FROM parabd_media WHERE ITEM_ID=' . intval($itemId) . ' AND IS_PRIMARY=1')->fetch_assoc();
    $service->merge(1, $source['item_id'], $itemId, array(), intval($media['ID_MEDIA']));
    $merged = $service->getItem($source['item_id'], true); dbAssert($merged['STATUS'] === 'MERGED' && intval($merged['MERGED_INTO_ID']) === $itemId, 'redirection de fusion');
    $row = $server->query('SELECT COUNT(*) total FROM users_parabd WHERE ITEM_ID=' . intval($itemId))->fetch_assoc(); dbAssert(intval($row['total']) === 3, 'fusion conservant tous les exemplaires');

    $engines = $server->query("SELECT COUNT(*) total FROM information_schema.tables WHERE table_schema='" . $server->real_escape_string($database) . "' AND table_name LIKE '%parabd%' AND ENGINE='InnoDB' AND TABLE_COLLATION='utf8_unicode_ci'")->fetch_assoc();
    dbAssert(intval($engines['total']) === 14, 'tables Para-BD InnoDB utf8_unicode_ci');
} catch (Throwable $error) {
    $failures++; fwrite(STDERR, "ERREUR: " . $error->getMessage() . "\n");
} finally {
    removeTestTree($imageRoot);
    $server->query("DROP DATABASE IF EXISTS `$database`");
    $server->close();
}

echo $checks . " contrôles MySQL, " . $failures . " échec(s).\n";
exit($failures ? 1 : 0);
