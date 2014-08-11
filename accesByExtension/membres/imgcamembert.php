<?php


minAccessLevel(2);
		
// création des camembert
// paramètres : user
//			    type_img : 1 pour camembert par genre, 2 par editeur

header("Content-type: image/png");
include("camemberti.php");

$id = intval($user);
if ($type_img == 1) {

	$query = "
	SELECT 
		count(ua.id_edition) as nbtome, 
		g.id_genre, 
		g.libelle 
	FROM 
		users_album ua 
		INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
		INNER JOIN bd_tome t ON t.ID_TOME = en.ID_TOME
		INNER JOIN bd_serie s ON t.ID_SERIE=s.ID_SERIE
		INNER JOIN bd_genre g ON s.ID_GENRE=g.ID_GENRE
	WHERE
		ua.user_id = ".$id." 
	GROUP BY g.id_genre 
	ORDER BY nbtome DESC ,g.libelle ASC";

}
else if ($type_img == 2) {
	$query = "
	SELECT 
		count(ua.id_edition) as nbtome, 
		er.id_editeur, 
		er.nom libelle 
	FROM 
		users_album ua 
		INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
		INNER JOIN bd_tome t ON t.ID_TOME = en.ID_TOME
		INNER JOIN bd_collection c ON en.ID_COLLECTION=c.ID_COLLECTION
		INNER JOIN bd_editeur er ON c.ID_EDITEUR=er.ID_EDITEUR
	WHERE
		ua.user_id = ".$id." 
	GROUP BY er.id_editeur 
	ORDER BY nbtome DESC ,er.nom ASC";
}


if ($query) {

	$DB->query ($query);
	$i = 0;

	while ($DB->next_record()) {
		$freq[$i] = $DB->f("nbtome");
		$aff[$i] = $DB->f("libelle");
		$i++;

	}
	$im = camembert($freq,0,320,230,15,10,1,$aff,200);
	imagepng($im);
	imagedestroy($im);

}