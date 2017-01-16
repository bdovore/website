<?php


// D�but du code
include_once (BDO_DIR."inc/bdovore.php");


// r�cup�re la variable de passage
$initiale = $_GET["init"];

// construction de la query
if ($initiale == "0") {
	$query = "SELECT id_serie, nom FROM bd_serie WHERE LOWER(LEFT(tri,1)) < 'a' or LOWER(LEFT(tri,1)) > 'z';";
	$doctitre = "Autres s�ries BD sur BDovore";
}else{
	$query = "SELECT id_serie, nom FROM bd_serie WHERE LOWER(LEFT(tri,1)) = '" . $DB->escape($initiale) . "' ORDER BY tri;";
	$doctitre = "S�ries BD commen�ant par un ".$initiale." sur BDovore";
}

// ex�cution de la query
$DB->query ($query);

// pr�pare le template
$t = new Template(BDO_DIR."public/templates");
$t->set_file(array(
	"tpBase" => "google_serie.tpl"
	));

// d�finition du block � utiliser et remplissage
$t->set_block('tpBase','SeriesBlock','SBlock');
while ($DB->next_record()) {
	$t->set_var (array(
		"SERIE" => stripslashes($DB->f("nom")),
		"URLSERIE" => BDO_URL."serie.php?id_serie=".$DB->f("id_serie")
		));
	$t->parse ("SBlock", "SeriesBlock",true);
}
$t->set_var (array(
	"INITIALE" => $initiale
	));

// Envoie les info g�n�rales et publie la page
$t->set_var (array(
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
	"DOCTITRE" => $doctitre
	));
$t->pparse("MyFinalOutput","tpBase");

