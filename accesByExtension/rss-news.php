<?php


Header("content-type: application/xml");

echo '<?xml version="1.0" encoding="iso-8859-1" ?>';
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';

echo '<channel>';
echo '<atom:link href="http://www.bdovore.com/rss-news.php" rel="self" type="application/rss+xml" />';
echo '<title>Bdovore - News</title>';
echo '<link>'.BDO_URL.'</link>';
echo '<description>Les derniers messages postés par l\'équipe du site sur la page d\'accueil</description>';
echo '<copyright>Bdovore</copyright>';
echo '<language>fr</language>';


// Requête pour récupérer les 50 dernières news
$select = 'select *'
        . ' from news'
        . ' order by news_id desc limit 0,50';

$DB->query($select);
while ($DB->next_record()) {
	$titre = htmlspecialchars(stripslashes($DB->f("news_titre")));
	$posteur = htmlspecialchars(stripslashes($DB->f("news_posteur")));
	$histoire = htmlspecialchars(stripslashes($DB->f("news_text")));
    $dte_post = $DB->f("news_date");

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
	echo '<title>'.$titre." (".$posteur.")".'</title>'."\n";
	echo '<link>'.BDO_URL.'</link>'."\n";
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


	echo '<guid isPermaLink="true">'.BDO_URL.'#'.$DB->f("news_id").'</guid>';
    echo '</item>';
}//fin du while*/

echo '</channel>';
echo '</rss>';
