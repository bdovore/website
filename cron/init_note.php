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
date_default_timezone_set ( 'Europe/Paris' );

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
echo "\nRegeneration de la table note_serie\n";
$query = "
ALTER TABLE `note_serie` DISABlE KEYS;
TRUNCATE TABLE `note_serie`;
INSERT INTO `note_serie` (`ID_SERIE`,`MOYENNE_NOTE_SERIE`,`NB_NOTE_SERIE`)
(SELECT
`id_serie` ,
AVG(`note`) as MOYENNE_NOTE_SERIE,
COUNT(`user_id`) AS NB_NOTE_SERIE
FROM `serie_comment`
WHERE `note` IS NOT NULL
GROUP BY `id_serie`);
ALTER TABLE `note_serie` ENABLE KEYS;
";
Db_multi_query($query);

echo "\nRegeneration de la table note_tome\n";
$query = "
ALTER TABLE `note_tome` DISABlE KEYS;
TRUNCATE TABLE `note_tome`;
INSERT INTO `note_tome` (`ID_TOME`,`MOYENNE_NOTE_TOME`,`NB_NOTE_TOME`)
(SELECT
`ID_TOME` ,
AVG(`NOTE`) as MOYENNE_NOTE_TOME,
COUNT(`USER_ID`) AS NB_NOTE_TOME
FROM `users_comment`
WHERE `NOTE` IS NOT NULL
GROUP BY `ID_TOME`);
ALTER TABLE `note_tome` ENABLE KEYS;";
Db_multi_query($query);
// --------------------------------------------------------------------------------

// fermeture de la connexion base
Db_close();
