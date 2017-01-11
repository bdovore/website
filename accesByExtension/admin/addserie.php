<?php



minAccessLevel(1);

$act = $_GET["act"];

// Mettre à jour les informations
if ($act=="insert"){
	$tri = substr(trim(clean_article($_POST['txtNomSerie'])),0,3);

	$query = "
	INSERT INTO `bd_serie` (
		`NOM`,
		`ID_GENRE`,
		`NOTE`,
		`FLG_FINI`,
		`NB_TOME`,
		`TRI`
	) VALUES (
		'".$DB->escape($_POST['txtNomSerie'])."',
		'".$DB->escape($_POST['txtGenreId'])."',
		'0',
		1,
		NULL,
		'".$DB->escape($tri)."'
	)
	";
	$DB->query ($query);

	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file("tpBody","serieexpress.tpl");
	$t->set_var (array(
		"BODYONLOAD" => "window.close()"
	));
	// assigne la barre de login
	$t->pparse("MyFinalOutput","tpBody");
}

// Afficher le formulaire pré - remplis
elseif($act==""){
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file("tpBody","serieexpress.tpl");
	$t->set_var (array(
		"URLACTION" => BDO_URL."admin/addserie.php?act=insert"
	));
	// assigne la barre de login
	$t->pparse("MyFinalOutput","tpBody");
}
