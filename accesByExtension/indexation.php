<?php


// D�but du code
include_once (BDO_DIR."inc/bdovore.php");



// pr�pare le template
$t = new Template(BDO_DIR."public/templates");
$t->set_file(array(
"tpBase" => "google_index_serie.tpl"
));

// compl�te le block s�rie
$t->set_block('tpBase','SeriesBlock','SBlock');
for($letter = ord('a'); $letter <= ord('z'); $letter++)
{
    $t->set_var (array(
    "INITIALE" => chr($letter),
    "URLSERIE" => BDO_URL."indexserie.php?init=".chr($letter)));
    $t->parse ("SBlock", "SeriesBlock",true);
}

// compl�te la partie autre
$t->set_var (array(
"URLAUTRE" => BDO_URL."indexserie.php?init=0"));



// Envoie les info g�n�rales et publie la page
$css_sheets = array("fiche.css");
$t->set_var (array(
"URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
"DOCTITRE" => "S�ries BD pr�sentes sur Bdovore"));

$t->pparse("MyFinalOutput","tpBase");
