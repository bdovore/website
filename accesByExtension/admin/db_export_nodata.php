<?php
/**
 * Dump MySQL de bdovore_db5
 *
 **/


minAccessLevel(0);


echo "Votre base est en cours de sauvegarde.......\n<br />";
flush();

// nom du fichier d'export
$fileDump = 'dump_bdovore_db5_nodata_'.date('Ymd-Hi').'';

// extraction avec mysqldump
system("mysqldump --no-data --host=mysql5-3 --user=bdovore_db5 --password=mJCAN5jS bdovore_db5 > " . $fileDump. ".sql");

// compression avec zip
system("zip -r " . $fileDump . ".zip " . $fileDump . ".sql");

// deplacement
system("mv " . $fileDump . ".zip /homez.95/bdovore/backup/mysqldump/bdovore_db5");

// suppression
system("rm " . $fileDump . ".sql");

// etudier la copie dans repertoire backup pour eviter le download en http et pour archive

echo "<hr>C'est fini. Vous pouvez récupérer la base par FTP <br />
Fichier : /homez.95/bdovore/backup/mysqldump/bdovore_db5/" . $fileDump . '.zip';

