<?php
Header("content-type: application/xml");

echo '<?xml version="1.0" encoding="utf-8"?>';
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
//echo '   xmlns="http://www.bdovore.com/">';
//echo '   xmlns:Comics="http://www.bdovore.com/">';

echo '<channel>';
echo '<atom:link href="http://www.bdovore.com/rss.php" rel="self" type="application/rss+xml" />';
echo '<title>Bdovore - Albums</title>';
echo '<link>'.BDO_URL.'</link>';
echo '<description>Les derniers albums, coffrets, magazines, fascicules, etc... ajoutés sur le site</description>';
echo '<copyright>Bdovore</copyright>';
echo '<language>fr</language>';


// Requête pour récupérer les 50 derniers albums avec leurs couvertures
$select = "
SELECT 
	t.titre, 
	t.id_tome, 
	t.id_serie, 
	t.histoire, 
	s.nom serie, 
	en.img_couv, 
	en.id_edition, 
	er.nom, 
	er.id_editeur
FROM 
	bd_tome t
	INNER JOIN bd_edition en ON t.id_edition=en.id_edition 
	INNER JOIN bd_collection c ON c.id_collection=en.id_collection 
	INNER JOIN bd_editeur er ON er.id_editeur=c.id_editeur 
	INNER JOIN bd_serie s ON t.id_serie=s.id_serie 
ORDER BY t.id_tome desc 
LIMIT 0,50
";

$DB->query($select);
while ($DB->next_record()) {
	$titre = htmlspecialchars(stripslashes($DB->f("titre")));
	$serie = htmlspecialchars(stripslashes($DB->f("serie")));
	$histoire = nl2br(htmlspecialchars(stripslashes($DB->f("histoire"))));

	$titre = str_replace ( chr(0x92), '\'',  $titre );
	$histoire = str_replace ( chr(0x92), '\'',  $histoire );
        $titre = str_replace ( chr(0x85), '\'',  $titre );
	$histoire = str_replace ( chr(0x85), '\'',  $histoire );
        $titre = str_replace ( chr(0x9c), '\'',  $titre );
	$histoire = str_replace ( chr(0x9c), '\'',  $histoire );

	echo '<item>'."\n";
	echo '<title>'.$titre." (".$serie.")".'</title>'."\n";
	echo '<link>'.BDO_URL.'membres/album_rss.php?id_tome='.$DB->f("id_tome").'</link>'."\n";
	echo '<description><![CDATA["'.$histoire.'"]]></description>'."\n";
    //echo '<bdovore:adsinfos>'."\n";
    //echo '       <bdovore:prix>11500</bdovore:prix>'."\n";
    //echo '       <bdovore:monnaie></bdovore:monnaie>'."\n";
    //echo '       <bdovore:region></bdovore:region>'."\n";
    //echo '       <bdovore:departement></bdovore:departement>'."\n";
    //echo '       <bdovore:ville></bdovore:ville>'."\n";
    //echo '       <bdovore:cp></bdovore:cp>'."\n";
    //echo '</bdovore:adsinfos>'."\n";

	echo '<guid isPermaLink="true">'.BDO_URL.'membres/album.php?id_tome='.$DB->f("id_tome").'</guid>';
	if ($DB->f("img_couv")!=NULL) {
		$taille_couv = filesize(BDO_DIR_COUV.$DB->f("img_couv"));
		echo '<enclosure url="'.BDO_URL_IMAGE.'couv/'.$DB->f("img_couv").'" type="image/jpeg" length="'.$taille_couv.'"/>';
	}
    echo '</item>';
}//fin du while*/

echo '</channel>';
echo '</rss>';
