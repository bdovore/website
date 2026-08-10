<?php

/**
 * Seed six real Para-BD items into the local development database.
 *
 * The script is intentionally guarded and idempotent: it only accepts the
 * bdovore_db database and reuses an existing item when its external identifier
 * is already present. Images must already have been downloaded locally.
 *
 * Usage:
 * PARABD_SEED_CONFIRM=bdovore_db PARABD_SEED_IMAGE_DIR=/private/tmp \
 *   php scripts/seed_parabd_demo.php
 */

if (getenv('PARABD_SEED_CONFIRM') !== 'bdovore_db') {
    fwrite(STDERR, "Définissez PARABD_SEED_CONFIRM=bdovore_db pour confirmer la cible.\n");
    exit(2);
}

$imageDir = rtrim(getenv('PARABD_SEED_IMAGE_DIR') ?: '', DIRECTORY_SEPARATOR);
if ($imageDir === '') {
    fwrite(STDERR, "PARABD_SEED_IMAGE_DIR doit désigner le répertoire des six visuels.\n");
    exit(2);
}

$socket = getenv('PARABD_SEED_SOCKET') ?: '/Applications/MAMP/tmp/mysql/mysql.sock';
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
require_once dirname(__DIR__) . '/mvc/models/ParabdRules.php';
require_once dirname(__DIR__) . '/mvc/models/ParabdImageStorage.php';

$items = array(
    array(
        'owner_id' => 7952,
        'title' => 'Human Torch – Marvel Premier Collection 1/7',
        'type' => 'STATUETTE', 'subtype' => 'FIGURINE',
        'description' => 'Statuette en résine peinte à la main, conçue par Caesar et Clayburn Moore. Édition limitée à 3 000 exemplaires avec certificat numéroté.',
        'material' => 'Résine', 'color' => 'Polychrome',
        'width_mm' => null, 'height_mm' => 356, 'depth_mm' => null, 'weight_g' => 2716,
        'scale' => '1/7', 'release_date' => '2022-10-05', 'print_run' => 3000,
        'is_numbered' => 1, 'is_signed' => null, 'has_certificate' => 1, 'is_limited' => 1,
        'manufacturer' => 'Diamond Select Toys', 'publisher' => null, 'license' => 'Marvel',
        'range' => 'Marvel Premier Collection', 'universe' => 'Fantastic Four',
        'identifier' => array('scheme' => 'UPCA', 'issuer' => '', 'value' => '699788843215'),
        'author_id' => 34256, 'series_id' => 62038, 'tome_id' => null,
        'image' => 'human.jpg', 'price' => '220.00', 'copy_number' => null,
        'sources' => array(
            array('https://csmoorestudio.com/human-torch-resin-statue/', 'Fiche du studio Clayburn Moore'),
            array('https://www.midtowncomics.com/product/2060180', 'Référence, tirage et date de sortie'),
            array('https://cdn11.bigcommerce.com/s-4a86qhtxcu/images/stencil/original/products/135/538/Human_Torch_full_figure_part_flame_copy__50373.1769546068.jpg', 'Origine du visuel')
        )
    ),
    array(
        'owner_id' => 7952,
        'title' => 'The Lizard – Marvel Premier Collection 1/7',
        'type' => 'STATUETTE', 'subtype' => 'FIGURINE',
        'description' => 'Statuette du Lézard en résine, sculptée par Alejandro Pereira et conçue par Caesar. Édition limitée à 3 000 exemplaires avec certificat.',
        'material' => 'Résine', 'color' => 'Polychrome',
        'width_mm' => null, 'height_mm' => 305, 'depth_mm' => null, 'weight_g' => 1814,
        'scale' => '1/7', 'release_date' => '2022-01-24', 'print_run' => 3000,
        'is_numbered' => 1, 'is_signed' => null, 'has_certificate' => 1, 'is_limited' => 1,
        'manufacturer' => 'Diamond Select Toys', 'publisher' => null, 'license' => 'Marvel',
        'range' => 'Marvel Premier Collection', 'universe' => 'Spider-Man',
        'identifier' => array('scheme' => 'UPCA', 'issuer' => '', 'value' => '699788841280'),
        'author_id' => null, 'series_id' => 1529, 'tome_id' => null,
        'image' => 'lizard.jpg', 'price' => '209.90', 'copy_number' => null,
        'sources' => array(
            array('https://www.reference-gaming.com/fiche-produit/marvel-comic-premier-collection-statuette-17-the-lizard-30-cm', 'Fiche produit et EAN'),
            array('https://dungeonmarvels.com/estatua-marvel-comic-premier-collection-re-run-lizard-resina-305-cm-62494.html', 'Sculpteur, designer et date de sortie'),
            array('https://www.blacksbricks.de/images/product_images/popup_images/lizardpremier1.jpg', 'Origine du visuel')
        )
    ),
    array(
        'owner_id' => 7952,
        'title' => 'Ex-libris Tarzan – Petar Meseldžija (édition limitée 2012)',
        'type' => 'PRINT', 'subtype' => 'EXLIBRIS',
        'description' => 'Ex-libris cartonné monochrome, numéroté et signé par Petar Meseldžija, joint à l’édition limitée néerlandaise de Tarzan publiée par Dark Dragon Books.',
        'material' => 'Papier cartonné', 'color' => 'Monochrome',
        'width_mm' => 210, 'height_mm' => 297, 'depth_mm' => null, 'weight_g' => 19,
        'scale' => null, 'release_date' => '2012', 'print_run' => 500,
        'is_numbered' => 1, 'is_signed' => 1, 'has_certificate' => 0, 'is_limited' => 1,
        'manufacturer' => null, 'publisher' => 'Dark Dragon Books', 'license' => 'Tarzan',
        'range' => null, 'universe' => 'Tarzan',
        'identifier' => array('scheme' => 'EXTERNAL_DB', 'issuer' => 'LastDodo', 'value' => '3029455'),
        'author_id' => 20930, 'series_id' => 61976, 'tome_id' => 407813,
        'image' => 'tarzan.jpg', 'price' => null, 'copy_number' => '140/500',
        'sources' => array(
            array('https://www.lastdodo.fr/fr/items/3029455-tarzan', 'Notice LastDodo de l’édition et de son ex-libris'),
            array('https://stripinfo.be/reeks/strip/187467_Tarzan_Dark_Dragon_1_Tarzan', 'ISBN et éditeur de l’édition limitée'),
            array('https://pictures.abebooks.com/inventory/31348635965_2.jpg', 'Origine du visuel')
        )
    ),
    array(
        'owner_id' => 23788,
        'title' => 'Scarlet Witch – X-Men BDS Art Scale 1/10',
        'type' => 'STATUETTE', 'subtype' => 'FIGURINE',
        'description' => 'Statuette de Scarlet Witch en résine polystone, peinte à la main, issue de la collection X-Men BDS Art Scale.',
        'material' => 'Polystone', 'color' => 'Polychrome',
        'width_mm' => 190, 'height_mm' => 350, 'depth_mm' => 150, 'weight_g' => 635,
        'scale' => '1/10', 'release_date' => '2022', 'print_run' => null,
        'is_numbered' => null, 'is_signed' => null, 'has_certificate' => 0, 'is_limited' => 1,
        'manufacturer' => 'Iron Studios', 'publisher' => null, 'license' => 'Marvel',
        'range' => 'BDS Art Scale', 'universe' => 'X-Men',
        'identifier' => array('scheme' => 'UPCA', 'issuer' => '', 'value' => '609963127689'),
        'author_id' => null, 'series_id' => null, 'tome_id' => null,
        'image' => 'scarlet.jpg', 'price' => '209.90', 'copy_number' => null,
        'sources' => array(
            array('https://ironstudios.com/products/statue-scarlet-witch-x-men-bds-art-scale-1-10-iron-studios', 'Fiche officielle Iron Studios'),
            array('https://www.derivefigurine.com/en/x-men/15799-marvel-scarlet-witch-figure-bds-art-scale-609963127689.html', 'Dimensions et UPC'),
            array('https://ironstudios.com/cdn/shop/files/090192_0.jpg?v=1752766352', 'Origine du visuel')
        )
    ),
    array(
        'owner_id' => 23788,
        'title' => 'Aquaman Deluxe – DC Comics Art Scale 1/10',
        'type' => 'STATUETTE', 'subtype' => 'DIORAMA',
        'description' => 'Statuette d’Aquaman inspirée des comics des années 1990, sur un décor de vagues et de tentacules. Édition limitée en polystone peinte à la main.',
        'material' => 'Polystone', 'color' => 'Polychrome',
        'width_mm' => 198, 'height_mm' => 259, 'depth_mm' => 259, 'weight_g' => 1890,
        'scale' => '1/10', 'release_date' => '2022', 'print_run' => null,
        'is_numbered' => null, 'is_signed' => null, 'has_certificate' => 0, 'is_limited' => 1,
        'manufacturer' => 'Iron Studios', 'publisher' => null, 'license' => 'DC Comics',
        'range' => 'Deluxe Art Scale', 'universe' => 'Aquaman',
        'identifier' => array('scheme' => 'UPCA', 'issuer' => '', 'value' => '609963127863'),
        'author_id' => null, 'series_id' => 23792, 'tome_id' => null,
        'image' => 'aquaman.jpg', 'price' => '199.90', 'copy_number' => null,
        'sources' => array(
            array('https://staging.ironstudios.com/product/aquaman-deluxe-art-scale-1-10-dc-domics/', 'Fiche officielle Iron Studios'),
            array('https://static.fnac-static.com/multimedia/Images/DB/BD/88/14/21531611-1505-3756-1.jpg', 'Origine du visuel')
        )
    ),
    array(
        'owner_id' => 23788,
        'title' => 'Bécassine – Pile d’albums',
        'type' => 'STATUETTE', 'subtype' => 'FIGURINE',
        'description' => 'Statuette en résine représentant Bécassine avec une pile d’albums, éditée par Collectoys.',
        'material' => 'Résine', 'color' => 'Polychrome',
        'width_mm' => 142, 'height_mm' => 240, 'depth_mm' => 142, 'weight_g' => 2500,
        'scale' => null, 'release_date' => '2018-03-30', 'print_run' => null,
        'is_numbered' => null, 'is_signed' => null, 'has_certificate' => 0, 'is_limited' => 0,
        'manufacturer' => 'Plastoy Collectoys', 'publisher' => 'Collectoys', 'license' => 'Bécassine',
        'range' => null, 'universe' => 'Bécassine',
        'identifier' => array('scheme' => 'EAN13', 'issuer' => '', 'value' => '3521320004143'),
        'author_id' => 6062, 'series_id' => 11407, 'tome_id' => null,
        'image' => 'becassine.jpg', 'price' => '150.00', 'copy_number' => null,
        'sources' => array(
            array('https://ligneclaire.com/detailart.php?art=000505564', 'Fiche produit, EAN et date de parution'),
            array('https://ligneclaire.com/img/article/I505564G01.jpg', 'Origine du visuel')
        )
    )
);

function sqlValue($db, $value)
{
    if ($value === null) return 'NULL';
    return "'" . $db->real_escape_string((string) $value) . "'";
}

function fetchOne($db, $sql)
{
    $result = $db->query($sql);
    if (!$result) throw new RuntimeException($db->error);
    $row = $result->fetch_assoc();
    $result->free();
    return $row ?: null;
}

function executeSql($db, $sql)
{
    if (!$db->query($sql)) throw new RuntimeException($db->error);
}

function typeId($db, $code, $parentId = null)
{
    $whereParent = $parentId === null ? 'PARENT_ID IS NULL' : 'PARENT_ID=' . intval($parentId);
    $row = fetchOne($db, "SELECT ID_TYPE FROM parabd_type WHERE CODE=" . sqlValue($db, $code) . " AND $whereParent AND IS_ACTIVE=1");
    if (!$row) throw new RuntimeException("Type Para-BD introuvable : $code");
    return intval($row['ID_TYPE']);
}

function validateLegacyTarget($db, $table, $column, $id)
{
    if ($id === null) return;
    if (!fetchOne($db, "SELECT $column found FROM $table WHERE $column=" . intval($id) . ' LIMIT 1')) {
        throw new RuntimeException("Rattachement historique introuvable : $table.$column=$id");
    }
}

foreach ($items as $item) {
    $identifier = $item['identifier'];
    $scheme = strtoupper($identifier['scheme']);
    $valueNormalized = ParabdRules::normalizeIdentifier($scheme, $identifier['value']);
    $issuerNormalized = in_array($scheme, array('EAN13', 'UPCA', 'ISBN10', 'ISBN13'), true)
        ? '' : ParabdRules::normalizeText($identifier['issuer']);
    if (!ParabdRules::isValidIdentifier($scheme, $identifier['value'])) {
        throw new RuntimeException("Identifiant invalide pour {$item['title']}");
    }

    $existing = fetchOne($db, "SELECT ITEM_ID FROM parabd_identifier WHERE SCHEME=" . sqlValue($db, $scheme)
        . " AND ISSUER_NORMALIZED=" . sqlValue($db, $issuerNormalized)
        . " AND VALUE_NORMALIZED=" . sqlValue($db, $valueNormalized) . ' LIMIT 1');
    $itemId = $existing ? intval($existing['ITEM_ID']) : null;
    $writtenFile = null;

    try {
        $db->begin_transaction();
        if (!$itemId) {
            $typeId = typeId($db, $item['type']);
            $subtypeId = typeId($db, $item['subtype'], $typeId);
            $date = ParabdRules::parsePartialDate($item['release_date']);
            validateLegacyTarget($db, 'users', 'user_id', $item['owner_id']);
            validateLegacyTarget($db, 'bd_auteur', 'ID_AUTEUR', $item['author_id']);
            validateLegacyTarget($db, 'bd_serie', 'ID_SERIE', $item['series_id']);
            validateLegacyTarget($db, 'bd_tome', 'ID_TOME', $item['tome_id']);

            $columns = array('TYPE_ID','SUBTYPE_ID','TITLE','TITLE_NORMALIZED','DESCRIPTION','MATERIAL','COLOR','WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G','SCALE','RELEASE_DATE','DATE_PRECISION','PRINT_RUN','IS_NUMBERED','IS_SIGNED','HAS_CERTIFICATE','IS_LIMITED','MANUFACTURER','MANUFACTURER_NORMALIZED','PUBLISHER','LICENSE_NAME','RANGE_NAME','UNIVERSE_NAME','CREATED_BY','UPDATED_BY');
            $values = array(
                $typeId, $subtypeId, $item['title'], ParabdRules::normalizeText($item['title']), $item['description'],
                $item['material'], $item['color'], $item['width_mm'], $item['height_mm'], $item['depth_mm'], $item['weight_g'],
                $item['scale'], $date['date'], $date['precision'], $item['print_run'], $item['is_numbered'], $item['is_signed'],
                $item['has_certificate'], $item['is_limited'], $item['manufacturer'], ParabdRules::normalizeText($item['manufacturer']),
                $item['publisher'], $item['license'], $item['range'], $item['universe'], $item['owner_id'], $item['owner_id']
            );
            executeSql($db, 'INSERT INTO parabd_item (' . implode(',', $columns) . ') VALUES ('
                . implode(',', array_map(function ($value) use ($db) { return sqlValue($db, $value); }, $values)) . ')');
            $itemId = intval($db->insert_id);

            executeSql($db, "INSERT INTO parabd_identifier (ITEM_ID,SCHEME,ISSUER,ISSUER_NORMALIZED,VALUE,VALUE_NORMALIZED,CREATED_BY) VALUES ("
                . $itemId . ',' . sqlValue($db, $scheme) . ',' . sqlValue($db, $identifier['issuer']) . ',' . sqlValue($db, $issuerNormalized) . ','
                . sqlValue($db, $identifier['value']) . ',' . sqlValue($db, $valueNormalized) . ',' . intval($item['owner_id']) . ')');

            if ($item['author_id']) executeSql($db, "INSERT INTO parabd_item_author (ITEM_ID,AUTHOR_ID,ROLE,CREATED_BY) VALUES ($itemId," . intval($item['author_id']) . ",'ARTIST'," . intval($item['owner_id']) . ')');
            if ($item['series_id']) executeSql($db, "INSERT INTO parabd_item_series (ITEM_ID,SERIES_ID,RELATION_TYPE,CREATED_BY) VALUES ($itemId," . intval($item['series_id']) . ",'RELATED'," . intval($item['owner_id']) . ')');
            if ($item['tome_id']) executeSql($db, "INSERT INTO parabd_item_tome (ITEM_ID,TOME_ID,RELATION_TYPE,CREATED_BY) VALUES ($itemId," . intval($item['tome_id']) . ",'RELATED'," . intval($item['owner_id']) . ')');

            $imagePath = $imageDir . DIRECTORY_SEPARATOR . $item['image'];
            if (!is_file($imagePath)) throw new RuntimeException("Visuel absent : $imagePath");
            $imageStorage = new ParabdImageStorage();
            $image = $imageStorage->store(array(
                'tmp_name' => $imagePath, 'name' => basename($imagePath),
                'size' => filesize($imagePath), 'error' => UPLOAD_ERR_OK
            ), $itemId, 1);
            $writtenFile = $image['absolute_path'];
            executeSql($db, "INSERT INTO parabd_media (ITEM_ID,MEDIA_TYPE,FILE_PATH,MIME_TYPE,WIDTH_PX,HEIGHT_PX,IS_PRIMARY,CREATED_BY) VALUES ($itemId,'PRIMARY',"
                . sqlValue($db, $image['relative_path']) . ',' . sqlValue($db, $image['mime']) . ',' . intval($image['width']) . ',' . intval($image['height']) . ",1," . intval($item['owner_id']) . ')');

            foreach ($item['sources'] as $source) {
                executeSql($db, "INSERT INTO parabd_source (ITEM_ID,SOURCE_TYPE,URL,LABEL,CREATED_BY) VALUES ($itemId,'URL',"
                    . sqlValue($db, $source[0]) . ',' . sqlValue($db, $source[1]) . ',' . intval($item['owner_id']) . ')');
            }

            $snapshot = json_encode(array('TITLE' => $item['title'], 'TYPE' => $item['type'], 'SUBTYPE' => $item['subtype'], 'seed' => 'demo-2026-08'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            executeSql($db, "INSERT INTO parabd_revision (ITEM_ID,AUTHOR_ID,BASE_REVISION_NO,PATCH_AFTER,CHANGE_KIND,STATUS,APPLIED_AT) VALUES ($itemId,"
                . intval($item['owner_id']) . ',0,' . sqlValue($db, $snapshot) . ",'CREATE','APPLIED',NOW())");
        }

        $copy = fetchOne($db, 'SELECT ID_COPY FROM users_parabd WHERE USER_ID=' . intval($item['owner_id']) . ' AND ITEM_ID=' . intval($itemId) . ' LIMIT 1');
        if (!$copy) {
            executeSql($db, "INSERT INTO users_parabd (USER_ID,ITEM_ID,STATE,QUANTITY,COPY_NUMBER,PRICE,CURRENCY,IS_PRICE_PUBLIC,IS_PUBLIC,PERSONAL_NOTES) VALUES ("
                . intval($item['owner_id']) . ',' . intval($itemId) . ",'OWNED',1,"
                . sqlValue($db, $item['copy_number']) . ',' . sqlValue($db, $item['price']) . ','
                . ($item['price'] === null ? 'NULL' : "'EUR'") . ",0,1,'Jeu de démonstration Para-BD')");
        }
        $db->commit();
        echo ($existing ? 'Réutilisé' : 'Créé') . " #$itemId — {$item['title']} → utilisateur {$item['owner_id']}\n";
    } catch (Throwable $error) {
        $db->rollback();
        if ($writtenFile && is_file($writtenFile)) unlink($writtenFile);
        fwrite(STDERR, "Échec pour {$item['title']} : {$error->getMessage()}\n");
        $db->close();
        exit(1);
    }
}

$db->close();
