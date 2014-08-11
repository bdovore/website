<?php


minAccessLevel(0);

echo "Décompression du fichier.....\n<br>";
//system("gunzip bd.gz");
echo "Votre base est en cours de restauration......\n<br>";
system("cat forum.sql | mysql --host=mysql4.4 --user=bdovore_forum --password=GGCz2GGs bdovore_forum");
echo "C'est fini. Votre base est en place sur cet hébergement.";
