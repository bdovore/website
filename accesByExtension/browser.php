<?php


header('Content-Type: text/html; charset=ISO-8859-15');

include_once (BDO_DIR."inc/bdovore.php");



include (BDO_DIR."inc/queryfunction.php");


mysql_connect(BDO_DB_HOST, BDO_DB_USER, BDO_DB_PWD);
mysql_select_db(BDO_DB_SID);


// defintion des variables
$letter = array ('A', 'B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

$maxRows_RecAuteur = 30;
$pageNum_RecAuteur = 0;
$pagetitle = "BDovore.com - Bandes dessinées par ";
$keyword = "";

$a_searchType = array(
'ser'=>'Par Série',
'aut'=>'Par Auteur',
'genr'=>'Par Genre',
'edit'=>'Par Editeur',
);

$pageNum_RecAuteur = isset($_GET['pageNum_RecAuteur']) ? $_GET['pageNum_RecAuteur'] : "";
$rb_browse = (isset($_GET['rb_browse']) and isset($a_searchType[$_GET['rb_browse']])) ? $_GET['rb_browse'] : "ser";
$let = isset($_GET['let']) ? $_GET['let'] : "";


$t = new Template(BDO_DIR."public/templates");
$t->set_file(array(
"tpForm_Recherche" => "form_recherche.tpl",
"tpBody" => "browser_lm.tpl",
"tpMenu" => "menu.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBase" => "body.tpl"
));

// on déclare les blocks contenus sur la feuille
$t->set_block('tpBody','NavBlock','NBlock');

$t->set_var("TYPBROWSE",$rb_browse);

$t->set_var("URLLETTER","javascript:document.browser.let.value='';go()");
$t->set_var("LETTER","Tous");
$t->parse("NBlock","NavBlock",true);
foreach ($letter as $let_c) {
	$t->set_var("URLLETTER","javascript:document.browser.let.value='".$let_c."';go()");
	$t->set_var("LETTER",$let_c);
	$t->parse("NBlock","NavBlock",true);
}

$ACTBROWSER = '-';

foreach($a_searchType as $type=>$lib)
{
	if ($rb_browse == $type)
	$ACTBROWSER .= '&nbsp;<i>'.$lib.'</i>&nbsp;-';
	else
	$ACTBROWSER .= '<a href="'.$_SERVER["PHP_SELF"].'?rb_browse='.$type.'">&nbsp;'.$lib.'&nbsp;</a>-';
}

$t->set_var("VALUELET",$let);
$t->set_var("PAGETITLE",$pagetitle);
$t->set_var("PAGEKEYWORD",$keyword);
$t->set_var("ACTBROWSER",$ACTBROWSER);
$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
$t->set_var (array("LOGINBARRE" => GetIdentificationBar()));

$t->parse("BODY","tpBody");
$t->parse("FORM_RECHERCHE","tpForm_Recherche");
$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBase");
