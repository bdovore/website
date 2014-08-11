<?PHP


minAccessLevel(0);

$db_charset = "latin1"; /* mettre utf8 ou latin1 */

$db_server         = "mysql5-3";
$db_name           = "bdovore_db5";
$db_username       = "bdovore_db5";
$db_password       = "mJCAN5jS";

$cmd_mysql = "mysqldump";

$archive_GZIP      = "Sauve_Base.gz";

echo " Sauvegarde de la base <font color=red><b>$db_name</b></font> par <b>mysqldump</b> dans le fichier <b>$archive_GZIP</b> <br> \n";
$commande = $cmd_mysql." --host=$db_server --user=$db_username --password=$db_password -C -Q -e --default-character-set=$db_charset  $db_name    | gzip -c > $archive_GZIP ";
$CR_exec = system($commande);

exec("mv " . $archive_GZIP . " /homez.95/bdovore/backup/mysqldump/bdovore_db5");
