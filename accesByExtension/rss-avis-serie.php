<?php


Header("content-type: application/xml");

echo '<?xml version="1.0" encoding="iso-8859-1"?>';
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';

echo '<channel>';
echo '<atom:link href="http://www.bdovore.com/rss-avis-serie.php" rel="self" type="application/rss+xml" />';
echo '<title>Bdovore - Avis séries</title>';
echo '<link>'.BDO_URL.'</link>';
echo '<description>Les derniers avis de lecture postés sur les séries/one-shots</description>';
echo '<copyright>Bdovore</copyright>';
echo '<language>fr</language>';


// Requête pour récupérer les 50 derniers avis avec leurs couvertures
$select = "
SELECT 
	s.nom serie, 
	s.id_serie,  
	u.username, 
	u.user_id, 
	sc.comment histoire, 
	sc.dte_post, 
	DATE_FORMAT(sc.dte_post,'%d/%m/%Y %H:%i') date_post, 
	sc.note
FROM 
	serie_comment sc 
	INNER JOIN users u ON sc.user_id=u.user_id
	INNER JOIN bd_serie s ON sc.id_serie=s.id_serie
WHERE 
	sc.comment is not null 
	AND sc.comment <> ''
ORDER BY sc.dte_post desc 
LIMIT 0,50
";

$DB->query($select);
while ($DB->next_record())
{
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
    
    //Conversion de la date de MySQL (yyyy-mm-jj hh:mm:ss) à RFC822 (format rss : wed, 30 apr 2009 hh:mm:ss GMT)
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
    echo '<title>'.$serie." - ".$pseudo.'</title>'."\n";
    echo '<link>'.BDO_URL.'serie.php?id_serie='.$DB->f("id_serie").'</link>'."\n";
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


    echo '<guid isPermaLink="true">'.BDO_URL.'serie.php?id_serie='.$DB->f("id_serie").'#'.$var_timestamp.'</guid>';
    echo '</item>';
}

echo '</channel>';
echo '</rss>';
