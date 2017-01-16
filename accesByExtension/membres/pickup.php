<?php




minAccessLevel(2);

//d�termine la query � utiliser
$query = "SELECT s.nom, t.num_tome, t.titre, t.id_tome
FROM bd_tome t, bd_serie s
WHERE t.id_serie = s.id_serie
AND (s.nom LIKE '%".$DB->escape($text)."%' OR t.titre LIKE '%".$DB->escape($text)."%')
ORDER BY nom, num_tome, titre
";

// lance la query

$DB->query ($query);

// D�clare le template � utiliser
$t = new Template(BDO_DIR."public/templates");
// fichier � utiliser
$t->set_file("tpBody","user_pickup.tpl");
// Block � utiliser
$t->set_block('tpBody','ResultBlock','RBlock');

//liste les users
while ($DB->next_record()){
    $t->set_var (array(
    "ID" => $DB->f("id_tome"),
    "SERIE" => htmlentities($DB->f("nom")),
    "NUMTOME" => htmlentities($DB->f("num_tome")),
    "TITRE" => htmlentities($DB->f("titre")),
    "RETURNURL" => htmlentities("javascript:changeSource('$ReturnDoc','$ReturnElement','".addslashes($DB->f("titre"))."','".$DB->f("id_tome")."')")));
    $t->parse ("RBlock", "ResultBlock",true);
}

$t->set_var ("DOCTITRE","S�lection");

// assigne la barre de login
$t->set_var (array(
"LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
));
$t->pparse("BODY","tpBody");
