<?php



include (BDO_DIR."inc/function.html.inc.php");

minAccessLevel(2);

if ($act=="export") {
	$col = $_POST["expCollec"];
	$ach_fut = $_POST["expAchFut"];
	$alb_manq = $_POST["expAlbManq"];

	if (($col == 'O') && ($ach_fut == '') && ($alb_manq == ''))  {
		$report = 1;
	}elseif (($col == '') && ($ach_fut == 'O') && ($alb_manq == ''))  {
		$report = 2;
	}elseif (($col == '') && ($ach_fut == '') && ($alb_manq == 'O'))  {
		$report = 3;
	}elseif (($col == 'O') && ($ach_fut == 'O') && ($alb_manq == ''))  {
		$report = 4;
	}elseif ((($col != '') || ($ach_fut != '')) && ($alb_manq == 'O'))  {
		$report = 5;
		$cond = "&col=".($col =='' ? "N" : "O")."&af=".($ach_fut =='' ? "N" : "O");
	}

	header("Location: ./pdf2.php?rpt=".$report.$cond);
	exit;

}else{

	// Creation d'une nouvelle instance Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "user_export_pdf.tpl",
	"tpMenu" => "user.menu.tpl",
	"tpMenuColl" => "menu_coll.tpl",
	"tpBase" => "body.tpl"));

	// Préremplie les case à cocher
	for ($i=0; $i<=17; $i++) {
		if (substr($codesel,$i,1)=="1") {
			$t->set_var("SELFIELD".$i, 'checked');
		}
	}


	// Tableaux d'option
	$t->set_var (array
	(
	"TYPE0" => ($info==0) ? 'checked' : '',
	"TYPE1" => ($info==1) ? 'checked' : '',
	"TYPE2" => ($info==2) ? 'checked' : '',
	"CONTENU0" => ($contenu==0) ? 'checked' : '',
	"CONTENU1" => ($contenu==1) ? 'checked' : '',
	"CONTENU2" => ($contenu==2) ? 'checked' : '',
	));

	// assigne la barre de login
	$t->set_var (array
	("LOGINBARRE" => GetIdentificationBar(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
	"PAGETITLE" => "Export de données"));
	$t->parse("BODY","tpBody");
	$t->parse("MENUCOLL","tpMenuColl");
	$t->parse("MENUBARRE","tpMenu");
	$t->pparse("MyFinalOutput","tpBase");
}
