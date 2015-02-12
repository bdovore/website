#!/usr/local/bin/php
<?php
/**
 * mise a jour table bd_edition_stat
 * effacement complet et réinitialisation
 * toutes valeurs
 * 
 */

set_time_limit(1800); // 30 min

$_SERVER['SERVER_NAME'] = "beta.bdovore.com";

require_once ('..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'constante.php');
// ----------------------------------------------------
// time Zone
//date_default_timezone_set ( 'Europe/Paris' );

// fichiers de fonctions
include_once (BDO_DIR . 'inc' . DS . 'util.inc.php');
include_once (BDO_DIR . 'inc' . DS . 'mysql.inc.php');
include_once (BDO_DIR . 'inc' . DS . 'function.inc.php');

include_once (BDO_DIR . 'inc' . DS . 'function_cron.inc.php');

ob_start();

register_shutdown_function('saveBatchExec',__FILE__);

// ---------------------------------------------------------------
// connexion base
Bdo_Cfg::Db_connect();
// ---------------------------------------------------------------

// valeurs necessaires aux executions
// effectuees maintenant pour eviter la perte de la connexion mysql
$resultat = Db_query("SELECT MAX(ID_EDITION) as ID_EDITION FROM bd_edition");
while ($obj = Db_fetch_object($resultat)) {
    $max_id_edition = $obj->ID_EDITION;
}
$resultat = Db_query("SELECT MAX(ID_TOME) as ID_TOME FROM bd_tome");
while ($obj = Db_fetch_object($resultat)) {
    $max_id_tome = $obj->ID_TOME;
}
$resultat = Db_query("SELECT MAX(ID_SERIE) as ID_SERIE FROM bd_serie");
while ($obj = Db_fetch_object($resultat)) {
    $max_id_serie = $obj->ID_SERIE;
}

// --------------------------------------------------------------------------------
// régénération de la table bd_edition_stat
echo "\nRegeneration de la table bd_edition_stat\n";

$query = "
ALTER TABLE `bd_edition_stat` DISABLE KEYS;

TRUNCATE TABLE `bd_edition_stat`;

INSERT INTO `bd_edition_stat` (
SELECT
bd_edition.ID_EDITION,
bd_edition.ID_TOME,
bd_serie.ID_SERIE,
bd_serie.ID_GENRE,
bd_collection.ID_EDITEUR,
bd_collection.ID_COLLECTION,
0,
0,
0
FROM bd_edition
INNER JOIN bd_tome  ON bd_tome.ID_TOME = bd_edition.ID_TOME
INNER JOIN bd_serie ON bd_tome.ID_SERIE = bd_serie.ID_SERIE
INNER JOIN bd_collection ON bd_edition.ID_COLLECTION = bd_collection.ID_COLLECTION
WHERE bd_edition.ID_EDITION<70000);

INSERT INTO `bd_edition_stat` (
SELECT
bd_edition.ID_EDITION,
bd_edition.ID_TOME,
bd_serie.ID_SERIE,
bd_serie.ID_GENRE,
bd_collection.ID_EDITEUR,
bd_collection.ID_COLLECTION,
0,
0,
0
FROM bd_edition
INNER JOIN bd_tome  ON bd_tome.ID_TOME = bd_edition.ID_TOME
INNER JOIN bd_serie ON bd_tome.ID_SERIE = bd_serie.ID_SERIE
INNER JOIN bd_collection ON bd_edition.ID_COLLECTION = bd_collection.ID_COLLECTION
WHERE bd_edition.ID_EDITION>69999 AND bd_edition.ID_EDITION<140000);

INSERT INTO `bd_edition_stat` (
SELECT
bd_edition.ID_EDITION,
bd_edition.ID_TOME,
bd_serie.ID_SERIE,
bd_serie.ID_GENRE,
bd_collection.ID_EDITEUR,
bd_collection.ID_COLLECTION,
0,
0,
0
FROM bd_edition
INNER JOIN bd_tome  ON bd_tome.ID_TOME = bd_edition.ID_TOME
INNER JOIN bd_serie ON bd_tome.ID_SERIE = bd_serie.ID_SERIE
INNER JOIN bd_collection ON bd_edition.ID_COLLECTION = bd_collection.ID_COLLECTION
WHERE bd_edition.ID_EDITION>139999);

ALTER TABLE `bd_edition_stat` ENABLE KEYS;
";
Db_multi_query($query);
// --------------------------------------------------------------------------------

// fermeture de la connexion base
Db_close();

// --------------------------------------------------------------------------------
// maj valeur pour les editions
echo "\n\n------ MaJ valeur pour les editions";

$a_urlForCurl[0] = BDO_URL . "cache/nbuserbyedition?noRender&boucle=1&nbruser_ID_EDITION=" . $max_id_edition;

echo "\nDebut => " . date('d/m/Y H:i:s');
echo "\nPremier ID_EDITION => " . $max_id_edition;

$mr = 1000;
$ch = curl_init($a_urlForCurl[0]);
curl_exec_follow($ch, $mr);
// --------------------------------------------------------------------------------

// --------------------------------------------------------------------------------
// maj valeur pour les tomes
echo "\n\n------ MaJ valeur pour les tomes";

$a_urlForCurl[0] = BDO_URL."cache/nbuserbytome?noRender&boucle=1&nbruser_ID_TOME=" . $max_id_tome;

echo "\nDebut => ".date('d/m/Y H:i:s');
echo "\nPremier ID_TOME => ".$max_id_tome;

$mr = 1000;
$ch = curl_init($a_urlForCurl[0]);
curl_exec_follow($ch,$mr);
// --------------------------------------------------------------------------------

// --------------------------------------------------------------------------------
// maj valeur pour les series
echo "\n\n------ MaJ valeur pour les series";

$a_urlForCurl[0] = BDO_URL . "cache/nbuserbyserie?noRender&boucle=1&nbruser_ID_SERIE=" . $max_id_serie;

echo "\nDebut => " . date('d/m/Y H:i:s');
echo "\nPremier ID_SERIE => " . $max_id_serie;

$mr = 1000;
$ch = curl_init($a_urlForCurl[0]);
curl_exec_follow($ch, $mr);
// --------------------------------------------------------------------------------
