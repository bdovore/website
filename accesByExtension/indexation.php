<?php


// Début du code
include_once (BDO_DIR."inc/bdovore.php");



// prépare le template
$t = new Template(BDO_DIR."public/templates");
$t->set_file(array(
"tpBase" => "google_index_serie.tpl"
));

// complète le block série
$t->set_block('tpBase','SeriesBlock','SBlock');
for($letter = ord('a'); $letter <= ord('z'); $letter++)
{
    $t->set_var (array(
    "INITIALE" => chr($letter),
    "URLSERIE" => BDO_URL."indexserie.php?init=".chr($letter)));
    $t->parse ("SBlock", "SeriesBlock",true);
}

// complète la partie autre
$t->set_var (array(
"URLAUTRE" => BDO_URL."indexserie.php?init=0"));



// Envoie les info générales et publie la page
$css_sheets = array("fiche.css");
$t->set_var (array(
"URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
"DOCTITRE" => "Séries BD présentes sur Bdovore"));

$t->pparse("MyFinalOutput","tpBase");
