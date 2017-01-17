<?php



minAccessLevel(1);

// dossier contenant les templates
$t = new Template(BDO_DIR."public/templates");
// fichier à utiliser
$t->set_file(array(
"tpBody" => "body.tpl"));

$t->set_var("MENUBARRE",admin_menu());

$t->set_var("LOGINBARRE",GetIdentificationBar());
$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
$t->set_var("URLSITEFORUM",BDO_URL_FORUM);

$t->pparse("MyFinalOutput","tpBody");
