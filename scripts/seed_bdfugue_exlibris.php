<?php

/**
 * Seed a small, sourced set of BDfugue ex-libris into the local Para-BD catalog.
 *
 * Product metadata comes from BDfugue's public ex-libris category and indexed
 * product pages. The script does not crawl product HTML (protected by
 * Cloudflare); it only downloads the explicitly published promotional images
 * listed in the fixed manifest below.
 *
 * The script is guarded, transactional per item and idempotent through the
 * EXTERNAL_DB/BDfugue identifier. The album EAN is only used to resolve links
 * to the legacy BDovore catalog; it is not an identifier of the ex-libris.
 *
 * Usage:
 * BDFUGUE_SEED_CONFIRM=bdovore_db php scripts/seed_bdfugue_exlibris.php
 *
 * To reuse already downloaded images:
 * BDFUGUE_SEED_CONFIRM=bdovore_db BDFUGUE_SEED_IMAGE_DIR=/private/tmp \
 *   php scripts/seed_bdfugue_exlibris.php
 */

if (getenv('BDFUGUE_SEED_CONFIRM') !== 'bdovore_db') {
    fwrite(STDERR, "Définissez BDFUGUE_SEED_CONFIRM=bdovore_db pour confirmer la cible.\n");
    exit(2);
}

$imageDir = rtrim(getenv('BDFUGUE_SEED_IMAGE_DIR') ?: sys_get_temp_dir() . '/bdovore-bdfugue-seed', DIRECTORY_SEPARATOR);
if (!is_dir($imageDir) && !mkdir($imageDir, 0775, true) && !is_dir($imageDir)) {
    fwrite(STDERR, "Impossible de créer le répertoire temporaire des visuels.\n");
    exit(2);
}

$socket = getenv('BDFUGUE_SEED_SOCKET') ?: '/Applications/MAMP/tmp/mysql/mysql.sock';
$db = new mysqli('localhost', 'root', 'root', 'bdovore_db', 3306, $socket);
if ($db->connect_errno) {
    fwrite(STDERR, $db->connect_error . "\n");
    exit(2);
}
$db->set_charset('utf8');

if (!defined('DEBUG')) define('DEBUG', false);
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if (!defined('BDO_DIR_PARABD')) define('BDO_DIR_PARABD', dirname(__DIR__) . '/public/images/parabd/');
if (!defined('BDO_PARABD_MAX_UPLOAD_BYTES')) define('BDO_PARABD_MAX_UPLOAD_BYTES', 5242880);
if (!defined('BDO_PARABD_MAX_IMAGE_PIXELS')) define('BDO_PARABD_MAX_IMAGE_PIXELS', 30000000);

if (!class_exists('Bdo_Cfg')) {
    class Bdo_Cfg
    {
        private static $values = array();
        public static function getVar($key) { return isset(self::$values[$key]) ? self::$values[$key] : null; }
        public static function setVar($key, $value) { self::$values[$key] = $value; }
    }
}
if (!class_exists('User')) {
    class User
    {
        public static function minAccesslevel($level = 5) { return false; }
    }
}

Bdo_Cfg::setVar('connexion', $db);
require_once dirname(__DIR__) . '/inc/mysql.inc.php';
require_once dirname(__DIR__) . '/mvc/models/ParabdService.php';

$categoryUrl = 'https://www.bdfugue.com/exclusivites/ex-libris-bandes-dessinees';
$items = array(
    array(7952, '9782413049944', 'Les Navigateurs', 'Serge Lehman / Stéphane De Caneva', 'Stéphane De Caneva', 'Delcourt', '2024-10-02', 'Monochrome', 'https://www.bdfugue.com/les-navigateurs'),
    array(7952, '9782302103672', 'Wyoming 1863 tome 2 – L’arbre au pendu', 'Jean-François Di Giorgio / Fabrizio Des Dorides', 'Fabrizio Des Dorides', 'Soleil', '2026-06-18', 'Polychrome', 'https://www.bdfugue.com/wyoming-1863-tome-2-l-arbre-au-pendu'),
    array(7952, '9782302106772', 'Terres d’Ynuma tome 3 – L’honorable Juge Wonq', 'Nicolas Jarry / Jean-Paul Bordier', 'Jean-Paul Bordier', 'Soleil', '2026-06-04', 'Polychrome', 'https://www.bdfugue.com/terres-d-ynuma-tome-3-l-honorable-juge-wonq'),
    array(7952, '9782355747731', 'Le Roy des Ribauds tome 5', 'Vincent Brugeas / Ronan Toulhoat', 'Ronan Toulhoat', 'Akileos', '2026-06-17', 'Polychrome', 'https://www.bdfugue.com/roy-des-ribauds-tome-5'),
    array(7952, '9782413091332', 'Solo – Chemins tracés tome 3 – La fraternité', 'Óscar Martín / Roger Perez / Álvaro Iglesias', 'Álvaro Iglesias', 'Delcourt', '2026-06-11', 'Polychrome', 'https://www.bdfugue.com/solo-chemins-traces-tome-3-la-fraternite'),
    array(7952, '9782302106994', 'L’Inca blanc tome 1 – Le Yuraq Runa', 'Serge Perrotin / Alberto Foche', 'Alberto Foche', 'Soleil', '2026-06-11', 'Polychrome', 'https://www.bdfugue.com/l-inca-blanc-tome-1-le-yuraq-runa'),
    array(23788, '9782808506830', 'Seccotine tome 1 – Mystère à Champignac', 'Sophie Guerrive / Elric', 'Elric', 'Dupuis', '2026-05-22', 'Polychrome', 'https://www.bdfugue.com/seccotine-tome-1-mystere-a-champignac'),
    array(23788, '9782808215961', 'The Puzzle Game', 'Herik Hanna / Denys', 'Denys', 'Le Lombard', '2026-05-29', 'Polychrome', 'https://www.bdfugue.com/the-puzzle-game'),
    array(23788, '9782808212557', 'Chroniques diplomatiques tome 3 – Taiwan, 1955', 'Tristan Roulot / Christophe Simon', 'Christophe Simon', 'Le Lombard', '2026-05-29', 'Polychrome', 'https://www.bdfugue.com/chroniques-diplomatiques-tome-3-taiwan-1955'),
    array(23788, '9782808217798', 'I.R.$ tome 26 – L’art du profit', 'Stephen Desberg / Carlos Rafael Duarte', 'Carlos Rafael Duarte', 'Le Lombard', '2026-05-22', 'Polychrome', 'https://www.bdfugue.com/i-r-s-tome-26-l-art-du-profit'),
    array(23788, '9782808216791', 'Tango tome 9 – Faux frères', 'Matz / Philippe Xavier', 'Philippe Xavier', 'Le Lombard', '2026-05-22', 'Polychrome', 'https://www.bdfugue.com/tango-tome-9-faux-freres'),
    array(23788, '9782205213690', 'Stern tome 6 – Hors du monde', 'Frédéric Maffre / Julien Maffre', 'Julien Maffre', 'Dargaud', '2026-05-22', 'Monochrome', 'https://www.bdfugue.com/stern-tome-6-hors-du-monde')
);

function bdfugueSqlValue($db, $value)
{
    if ($value === null) return 'NULL';
    return "'" . $db->real_escape_string((string) $value) . "'";
}

function bdfugueFetchOne($db, $sql)
{
    $result = $db->query($sql);
    if (!$result) throw new RuntimeException($db->error);
    $row = $result->fetch_assoc();
    $result->free();
    return $row ?: null;
}

function bdfugueExecute($db, $sql)
{
    if (!$db->query($sql)) throw new RuntimeException($db->error);
}

function bdfugueTypeId($db, $code, $parentId = null)
{
    $parent = $parentId === null ? 'PARENT_ID IS NULL' : 'PARENT_ID=' . intval($parentId);
    $row = bdfugueFetchOne($db, 'SELECT ID_TYPE FROM parabd_type WHERE CODE=' . bdfugueSqlValue($db, $code) . " AND $parent AND IS_ACTIVE=1");
    if (!$row) throw new RuntimeException("Type Para-BD introuvable : $code");
    return intval($row['ID_TYPE']);
}

function bdfugueAlbum($db, $ean)
{
    $row = bdfugueFetchOne($db, "SELECT e.ID_EDITION,e.ID_TOME,t.ID_SERIE,t.ID_DESSIN,s.NOM SERIES_NAME
        FROM bd_edition e JOIN bd_tome t ON t.ID_TOME=e.ID_TOME JOIN bd_serie s ON s.ID_SERIE=t.ID_SERIE
        WHERE e.EAN=" . bdfugueSqlValue($db, $ean) . ' LIMIT 1');
    if (!$row) throw new RuntimeException("Album BDovore introuvable pour l’EAN $ean");
    return $row;
}

function bdfugueValidateUser($db, $userId)
{
    if (!bdfugueFetchOne($db, 'SELECT user_id FROM users WHERE user_id=' . intval($userId) . ' LIMIT 1')) {
        throw new RuntimeException("Utilisateur introuvable : $userId");
    }
}

function bdfugueDownloadImage($url, $destination)
{
    if (is_file($destination) && filesize($destination) > 0) return;
    if (!function_exists('curl_init')) throw new RuntimeException('Extension cURL PHP requise pour télécharger les visuels.');
    $parts = parse_url($url);
    if (!$parts || strtolower($parts['scheme']) !== 'https' || strtolower($parts['host']) !== 'www.bdfugue.com') {
        throw new RuntimeException('URL de visuel BDfugue non autorisée.');
    }
    $handle = fopen($destination . '.part', 'wb');
    if (!$handle) throw new RuntimeException("Impossible de créer $destination.part");
    $curl = curl_init($url);
    curl_setopt_array($curl, array(
        CURLOPT_FILE => $handle,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'BDovore local Para-BD fixture seed/1.0'
    ));
    $ok = curl_exec($curl);
    $status = intval(curl_getinfo($curl, CURLINFO_HTTP_CODE));
    $error = curl_error($curl);
    curl_close($curl);
    fclose($handle);
    if (!$ok || $status < 200 || $status >= 300) {
        @unlink($destination . '.part');
        throw new RuntimeException("Téléchargement du visuel impossible (HTTP $status) : $error");
    }
    if (!rename($destination . '.part', $destination)) {
        @unlink($destination . '.part');
        throw new RuntimeException('Impossible de finaliser le visuel téléchargé.');
    }
    usleep(350000);
}

$printTypeId = bdfugueTypeId($db, 'PRINT');
$exlibrisTypeId = bdfugueTypeId($db, 'EXLIBRIS', $printTypeId);

foreach ($items as $fixture) {
    list($ownerId, $ean, $albumTitle, $credits, $artistName, $albumPublisher, $releaseDate, $color, $productUrl) = $fixture;
    $title = 'Ex-libris ' . $albumTitle . ' – exclusivité BDfugue';
    $externalValue = 'EXLIBRIS-' . $ean;
    $externalNormalized = ParabdService::normalizeIdentifier('EXTERNAL_DB', $externalValue);
    $issuerNormalized = ParabdService::normalizeText('BDfugue');
    $imageUrl = 'https://www.bdfugue.com/media/bdfugue_marketing/image/' . $ean . '_xl.jpg';
    $imagePath = $imageDir . DIRECTORY_SEPARATOR . 'bdfugue-' . $ean . '.jpg';
    $writtenFile = null;
    $created = false;

    try {
        bdfugueDownloadImage($imageUrl, $imagePath);
        bdfugueValidateUser($db, $ownerId);
        $album = bdfugueAlbum($db, $ean);
        $date = ParabdService::parsePartialDate($releaseDate);
        $description = "Ex-libris exclusif BDfugue associé à l’album « $albumTitle ». Illustration : $artistName. Crédits de l’album : $credits. Éditeur de l’album : $albumPublisher. EAN de l’album associé : $ean.";

        $db->begin_transaction();
        $existing = bdfugueFetchOne($db, "SELECT ITEM_ID FROM parabd_identifier WHERE SCHEME='EXTERNAL_DB'
            AND ISSUER_NORMALIZED=" . bdfugueSqlValue($db, $issuerNormalized) . '
            AND VALUE_NORMALIZED=' . bdfugueSqlValue($db, $externalNormalized) . ' LIMIT 1 FOR UPDATE');
        $itemId = $existing ? intval($existing['ITEM_ID']) : null;

        if (!$itemId) {
            $columns = array('TYPE_ID','SUBTYPE_ID','TITLE','TITLE_NORMALIZED','DESCRIPTION','MATERIAL','COLOR','RELEASE_DATE','DATE_PRECISION','IS_LIMITED','PUBLISHER','RANGE_NAME','UNIVERSE_NAME','CREATED_BY','UPDATED_BY');
            $values = array($printTypeId, $exlibrisTypeId, $title, ParabdService::normalizeText($title), $description, 'Papier d’art', $color, $date['date'], $date['precision'], 1, 'BDfugue', 'Ex-libris exclusifs BDfugue', $album['SERIES_NAME'], $ownerId, $ownerId);
            bdfugueExecute($db, 'INSERT INTO parabd_item (' . implode(',', $columns) . ') VALUES (' . implode(',', array_map(function ($value) use ($db) {
                return bdfugueSqlValue($db, $value);
            }, $values)) . ')');
            $itemId = intval($db->insert_id);
            $created = true;

            bdfugueExecute($db, "INSERT INTO parabd_identifier (ITEM_ID,SCHEME,ISSUER,ISSUER_NORMALIZED,VALUE,VALUE_NORMALIZED,CREATED_BY) VALUES ($itemId,'EXTERNAL_DB','BDfugue',"
                . bdfugueSqlValue($db, $issuerNormalized) . ',' . bdfugueSqlValue($db, $externalValue) . ',' . bdfugueSqlValue($db, $externalNormalized) . ',' . intval($ownerId) . ')');
            if (intval($album['ID_DESSIN']) > 0) {
                bdfugueExecute($db, "INSERT INTO parabd_item_author (ITEM_ID,AUTHOR_ID,ROLE,CREATED_BY) VALUES ($itemId," . intval($album['ID_DESSIN']) . ",'ARTIST'," . intval($ownerId) . ')');
            }
            bdfugueExecute($db, "INSERT INTO parabd_item_series (ITEM_ID,SERIES_ID,RELATION_TYPE,CREATED_BY) VALUES ($itemId," . intval($album['ID_SERIE']) . ",'RELATED'," . intval($ownerId) . ')');
            bdfugueExecute($db, "INSERT INTO parabd_item_tome (ITEM_ID,TOME_ID,RELATION_TYPE,CREATED_BY) VALUES ($itemId," . intval($album['ID_TOME']) . ",'RELATED'," . intval($ownerId) . ')');

            $service = new ParabdService();
            $image = $service->storeImage(array('tmp_name' => $imagePath, 'name' => basename($imagePath), 'size' => filesize($imagePath), 'error' => UPLOAD_ERR_OK), $itemId, 1);
            $writtenFile = $image['absolute_path'];
            bdfugueExecute($db, "INSERT INTO parabd_media (ITEM_ID,MEDIA_TYPE,FILE_PATH,MIME_TYPE,WIDTH_PX,HEIGHT_PX,IS_PRIMARY,CREATED_BY) VALUES ($itemId,'PRIMARY',"
                . bdfugueSqlValue($db, $image['relative_path']) . ',' . bdfugueSqlValue($db, $image['mime']) . ',' . intval($image['width']) . ',' . intval($image['height']) . ',1,' . intval($ownerId) . ')');
            bdfugueExecute($db, "INSERT INTO parabd_source (ITEM_ID,SOURCE_TYPE,URL,LABEL,NOTES,CREATED_BY) VALUES ($itemId,'URL',"
                . bdfugueSqlValue($db, $productUrl) . ",'Fiche produit BDfugue','Métadonnées de l’album associé et de son ex-libris exclusif'," . intval($ownerId) . ')');
            bdfugueExecute($db, "INSERT INTO parabd_source (ITEM_ID,SOURCE_TYPE,URL,LABEL,NOTES,CREATED_BY) VALUES ($itemId,'URL',"
                . bdfugueSqlValue($db, $imageUrl) . ",'Visuel promotionnel BDfugue','Image réencodée localement par le pipeline Para-BD'," . intval($ownerId) . ')');
            bdfugueExecute($db, "INSERT INTO parabd_source (ITEM_ID,SOURCE_TYPE,URL,LABEL,CREATED_BY) VALUES ($itemId,'URL',"
                . bdfugueSqlValue($db, $categoryUrl) . ",'Catalogue des ex-libris BDfugue'," . intval($ownerId) . ')');

            $snapshot = json_encode(array('TITLE' => $title, 'TYPE' => 'PRINT', 'SUBTYPE' => 'EXLIBRIS', 'album_ean' => $ean, 'seed' => 'bdfugue-2026-08'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            bdfugueExecute($db, "INSERT INTO parabd_revision (ITEM_ID,AUTHOR_ID,BASE_REVISION_NO,PATCH_AFTER,CHANGE_KIND,STATUS,APPLIED_AT) VALUES ($itemId,"
                . intval($ownerId) . ',0,' . bdfugueSqlValue($db, $snapshot) . ",'CREATE','APPLIED',NOW())");
        }

        $copy = bdfugueFetchOne($db, 'SELECT ID_COPY FROM users_parabd WHERE USER_ID=' . intval($ownerId) . ' AND ITEM_ID=' . intval($itemId) . ' LIMIT 1');
        if (!$copy) {
            bdfugueExecute($db, "INSERT INTO users_parabd (USER_ID,ITEM_ID,STATE,QUANTITY,IS_PRICE_PUBLIC,IS_PUBLIC,PERSONAL_NOTES) VALUES ("
                . intval($ownerId) . ',' . intval($itemId) . ",'OWNED',1,0,1,'Jeu de test des ex-libris BDfugue')");
        }
        $db->commit();
        echo ($created ? 'Créé' : 'Réutilisé') . " #$itemId — $title → utilisateur $ownerId\n";
    } catch (Throwable $error) {
        $db->rollback();
        if ($writtenFile && is_file($writtenFile)) @unlink($writtenFile);
        fwrite(STDERR, "Échec pour $title : {$error->getMessage()}\n");
        $db->close();
        exit(1);
    }
}

$db->close();
