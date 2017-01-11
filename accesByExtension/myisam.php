<?php

echo '<pre>';

set_time_limit(3600);

define('BDO_DB_HOST','localhost');
define('BDO_DB_SID','bdovore_db5');
define('BDO_DB_USER','root');
define('BDO_DB_PWD','');


mysql_connect(BDO_DB_HOST, BDO_DB_USER, BDO_DB_PWD);
mysql_select_db(BDO_DB_USER);
/*
$query = "
SELECT *
FROM `information_schema`.`TABLES`
WHERE `TABLE_SCHEMA` LIKE 'bdovore_db5'
AND `ENGINE` LIKE 'InnoDB'";


$res = mysql_query($query);

while ($obj = mysql_fetch_object($res)) {
	// mysql_query("ALTER TABLE `".$obj."` ENGINE = MYISAM");
	echo "\$a_tableInnodb[]='".$obj->TABLE_NAME."';\n";
}
*/




$query = "
SELECT *
FROM `information_schema`.`TABLE_CONSTRAINTS`
WHERE `TABLE_SCHEMA` LIKE 'bdovore_db5'
AND `CONSTRAINT_TYPE` LIKE 'FOREIGN KEY'";
$res = mysql_query($query);

while ($obj = mysql_fetch_object($res)) {
	// mysql_query("ALTER TABLE `".$obj."` ENGINE = MYISAM");
	echo "ALTER TABLE `".$obj->TABLE_NAME ."` DROP FOREIGN KEY `".$obj->CONSTRAINT_NAME ."` ;\n";
}





exit();



$a_tableInnodb[]='au_role';
$a_tableInnodb[]='auteur';
$a_tableInnodb[]='db_log_edit';
$a_tableInnodb[]='ed_collection';
$a_tableInnodb[]='ed_editeur';
$a_tableInnodb[]='ed_page';
$a_tableInnodb[]='ed_tirage';
$a_tableInnodb[]='edition';
$a_tableInnodb[]='edition_auteur';
$a_tableInnodb[]='edition_coffret';
$a_tableInnodb[]='ihm_edito';
$a_tableInnodb[]='ihm_fil_ariane';
$a_tableInnodb[]='ihm_news';
$a_tableInnodb[]='librairie';
$a_tableInnodb[]='media_newsletter';
$a_tableInnodb[]='pays';
$a_tableInnodb[]='pr_auteur';
$a_tableInnodb[]='pr_genre';
$a_tableInnodb[]='pr_theme';
$a_tableInnodb[]='proposition';
$a_tableInnodb[]='se_langue';
$a_tableInnodb[]='se_nature';
$a_tableInnodb[]='se_origine';
$a_tableInnodb[]='se_public';
$a_tableInnodb[]='serie';
$a_tableInnodb[]='us_ed_carre';
$a_tableInnodb[]='us_ed_comment';
$a_tableInnodb[]='us_ed_copy';
$a_tableInnodb[]='us_ed_pret';
$a_tableInnodb[]='us_se_comment';
$a_tableInnodb[]='us_se_exclusion';
$a_tableInnodb[]='us_vo_exclusion';
$a_tableInnodb[]='user';
$a_tableInnodb[]='user_auteur';
//$a_tableInnodb[]='user_edition';
$a_tableInnodb[]='vo_genre';
$a_tableInnodb[]='vo_support';
$a_tableInnodb[]='vo_theme';
$a_tableInnodb[]='volume';
$a_tableInnodb[]='volume_auteur';
$a_tableInnodb[]='volume_genre';
$a_tableInnodb[]='volume_recueil';
$a_tableInnodb[]='volume_theme';


mysql_query("SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;");
mysql_query("SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;");
mysql_query("SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';");
foreach ($a_tableInnodb as $table_name) {
	$query = "ALTER TABLE `".$table_name."` ENGINE = MYISAM;";
	echo $query."\n";
	mysql_query($query);
}

echo '</pre>';
