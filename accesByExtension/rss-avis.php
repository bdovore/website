<?php


Header("content-type: application/xml");

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';

echo '<channel>';
echo '<atom:link href="http://www.bdovore.com/rss-avis.php" rel="self" type="application/rss+xml" />';
echo '<title>Bdovore - Avis albums</title>';
echo '<link>'.BDO_URL.'</link>';
echo '<description>Les derniers avis de lecture postés sur les albums</description>';
echo '<copyright>Bdovore</copyright>';
echo '<language>fr</language>';


// Requ�te pour r�cup�rer les 50 derniers avis avec leurs couvertures
$select = "
SELECT 
	t.titre, 
	t.id_tome, 
	sc.pseudo p_scenar, 
	t.id_scenar, 
	de.pseudo p_dessin, 
	t.id_dessin, 
	er.nom editeur, 
	s.nom serie, 
	s.id_serie,  
	t.num_tome , 
	t.flg_int, 
	s.flg_fini, 
	t.flg_type, 
	en.img_couv, 
	u.username, 
	u.user_id, 
	uc.comment histoire, 
	uc.dte_post, 
	DATE_FORMAT(uc.dte_post,'%d/%m/%Y %H:%i') date_post, 
	uc.note, 
	en.dte_parution
FROM 
	users_comment uc 
	INNER JOIN bd_tome t ON uc.id_tome=t.id_tome 
	INNER JOIN bd_edition en ON t.id_edition=en.id_edition 
	INNER JOIN bd_auteur sc ON sc.id_auteur=t.id_scenar 
	INNER JOIN bd_auteur de ON de.id_auteur=t.id_dessin 
	INNER JOIN users u ON uc.user_id=u.user_id 
	INNER JOIN bd_collection c ON c.id_collection=en.id_collection 
	INNER JOIN bd_editeur er ON er.id_editeur=c.id_editeur 
	INNER JOIN bd_serie s ON t.id_serie=s.id_serie 
WHERE 
	uc.comment is not null 
	AND uc.comment <> ''
ORDER BY uc.dte_post desc 
LIMIT 0,50
";

$DB->query($select);
while ($DB->next_record()) {
	$titre = htmlspecialchars(stripslashes($DB->f("titre")));
	$serie = htmlspecialchars(stripslashes($DB->f("serie")));
	$histoire = nl2br(htmlspecialchars(stripslashes($DB->f("histoire"))));
    $pseudo = htmlspecialchars(stripslashes($DB->f("username")));
    $dte_post = $DB->f("dte_post");
    $date_post = $DB->f("date_post");

	$titre = str_replace ( chr(0x92), '\'',  $titre );
	$histoire = str_replace ( chr(0x92), '\'',  $histoire );
    $titre = str_replace ( chr(0x85), '\'',  $titre );
	$histoire = str_replace ( chr(0x85), '\'',  $histoire );
    $titre = str_replace ( chr(0x9c), '\'',  $titre );
	$histoire = str_replace ( chr(0x9c), '\'',  $histoire );
    $titre = str_replace ( chr(0x96), '\'',  $titre );
	$histoire = str_replace ( chr(0x96), '\'',  $histoire );
    
    //Conversion de la date de MySQL (yyyy-mm-jj hh:mm:ss) � RFC822 (format rss : wed, 30 apr 2009 hh:mm:ss GMT)
    $date_array = explode("-",$dte_post);
    $day_array = explode(" ",$date_array[2]);
    $time_array = explode(":",$day_array[1]);
    $var_year = $date_array[0];
    $var_month = $date_array[1];
    $var_day = $day_array[0];
    $var_hour = $time_array[0];
    $var_min = $time_array[1];
    $var_sec = $time_array[2];
    $var_timestamp = mktime($var_hour,$var_min,$var_sec,$var_month,$var_day,$var_year);
    $date = date("D, d M Y H:i:s O",$var_timestamp);

	echo '<item>'."\n";
    echo '<title>'.$titre." (".$serie.") - ".$pseudo.'</title>'."\n";
    echo '<link>'.BDO_URL.'Album?id_tome='.$DB->f("id_tome").'#'.$var_timestamp.'</link>'."\n";
    echo '<description><![CDATA["'.$histoire.'"]]></description>'."\n";
    echo '<pubDate>'.$date.'</pubDate>'."\n";
    //echo '<bdovore:adsinfos>'."\n";
    //echo '       <bdovore:prix>11500</bdovore:prix>'."\n";
    //echo '       <bdovore:monnaie></bdovore:monnaie>'."\n";
    //echo '       <bdovore:region></bdovore:region>'."\n";
    //echo '       <bdovore:departement></bdovore:departement>'."\n";
    //echo '       <bdovore:ville></bdovore:ville>'."\n";
    //echo '       <bdovore:cp></bdovore:cp>'."\n";
    //echo '</bdovore:adsinfos>'."\n";


    echo '<guid isPermaLink="true">'.BDO_URL.'Album?id_tome='.$DB->f("id_tome").'#'.$var_timestamp.'</guid>';
    if ($DB->f("img_couv")!=NULL) {
		$taille_couv = filesize(BDO_DIR_COUV.$DB->f("img_couv"));
        echo '<enclosure url="'.BDO_URL_IMAGE.'couv/'.$DB->f("img_couv").'" type="image/jpeg" length="'.$taille_couv.'"/>';
    }
    echo '</item>';
}//fin du while*/

echo '</channel>';
echo '</rss>';
