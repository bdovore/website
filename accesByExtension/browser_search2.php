<?php


header('Content-Type: text/html; charset=ISO-8859-15');

include_once (BDO_DIR."inc/bdovore.php");



include (BDO_DIR."inc/queryfunction.php");

mysql_connect(BDO_DB_HOST, BDO_DB_USER, BDO_DB_PWD);
mysql_select_db(BDO_DB_SID);

// defintion des variables
$maxRows = 25;
$pageNum = 0;
$keyword = "";

$pageNum = getVal('pageNum','0');
$rb_browse = getVal('rb_browse','ser');
$lev_id = getVal('lev_id');
$lev2_id = getVal('lev2_id');
$let = getVal('let');

$startRow = $pageNum * $maxRows;

// URL des pages d'edition
$url_edit = array(
"ALBUM"=>"./admin/adminalbums.php?alb_id=%d",
"SERIE"=>"./admin/adminseries.php?serie_id=%d",
"GENRE"=>"./admin/admingenres.php?genre_id=%d",
"COLLECTION"=>"./admin/admincollections.php?collec_id=%d",
"AUTEUR" => "./admin/adminauteurs.php?auteur_id=%d",
"EDITEUR"=> "./admin/adminediteurs.php?editeur_id=%d"
);


if ($rb_browse == 'ser' || !$rb_browse) {
	$query_selectCount = "SELECT count(1) as nbr FROM bd_serie ";
	$query_select = "SELECT distinct bd_serie.id_serie id, bd_serie.nom name	FROM bd_serie ";
	if ($let) {
		/*
		if (strlen($let) < 4) {
			$query_where = "WHERE tri like '".PMA_sqlAddslashes($let,true)."%' ";
		}
		else {
		*/
			$query_where = "WHERE nom  NOT like '".PMA_sqlAddslashes($let,true)."%' AND nom Like'%".PMA_sqlAddslashes($let,true)."%'";
		//}
	}
	else {
		$query_where = "";
	}
//	$query_order = " ORDER BY tri, nom ASC ";
	$query_order = " ORDER BY nom ASC ";


	// URL album
	$act_url = ' onclick="window.open('."'./membres/album.php?id_tome=%d','Album','width=500,height=400,scrollbars=1')".';return false;"';
	$url_alb = "";
	// URL d'edition
	$edit_lev1 ="<a href='".$url_edit["SERIE"]."' target =_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0 /></a>";
}
elseif ($rb_browse == 'aut') {
	$query_selectCount = "SELECT count(1) as nbr FROM bd_auteur ";
	$query_select = "SELECT ID_AUTEUR id, PSEUDO name FROM bd_auteur ";
	if ($let) {
		$query_where = "WHERE pseudo NOT like '".PMA_sqlAddslashes($let,true)."%' AND pseudo Like'%".PMA_sqlAddslashes($let,true)."%'";
	}
	else {
		$query_where = "";
	}
	$query_order = " ORDER BY PSEUDO ASC ";


	// URL album
	$act_url = ' onclick="window.open('."'./membres/album.php?id_tome=%d','Album','width=500,height=400,scrollbars=1')".'"';
	$url_alb = "";
	// URL d'edition
	$edit_lev1 = "<a href='".$url_edit["AUTEUR"]."' target =_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0 /></a>";
}
elseif ($rb_browse == 'edit'){
	$query_selectCount = "SELECT count(1) as nbr FROM bd_editeur ";
	$query_select = "SELECT ID_EDITEUR id, NOM name FROM bd_editeur ";
	if ($let) {
		$query_where = "WHERE NOM NOT like '".PMA_sqlAddslashes($let,true)."%' AND NOM Like'%".PMA_sqlAddslashes($let,true)."%'";
	}
	else {
		$query_where = "";
	}
	$query_order = " ORDER BY NOM ASC ";

	// URL affichage
	$url_alb = "serie.php?id_serie=%d";
	$act_url = "";
	// URL d'edition
	$edit_lev1 = "<a href='".$url_edit["EDITEUR"]."' target =_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0></a>";
}
elseif ($rb_browse == 'genr') {
	$query_selectCount = "SELECT count(1) as nbr FROM bd_genre ";
	$query_select = "SELECT ID_GENRE id, LIBELLE name FROM bd_genre ";
	if ($let) {
		$query_where = "WHERE LIBELLE NOT like '".PMA_sqlAddslashes($let,true)."%' AND LIBELLE Like'%".PMA_sqlAddslashes($let,true)."%'";
	}
	else {
		$query_where = "";
	}
	$query_order = " ORDER BY LIBELLE ASC ";


	$url_alb = "serie.php?id_serie=%d";
	$act_url = "";
	// URL d'edition
	$edit_lev1 = "<a href='".$url_edit["GENRE"]."' target =_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0></a>";
}

$query_limit = $query_select.$query_where.$query_order. " LIMIT ".mysql_real_escape_string($startRow).",".mysql_real_escape_string($maxRows);

if (isset($_GET['totalRows'])) {
	$totalRows = $_GET['totalRows'];
}
else {
	$all = mysql_query($query_selectCount.$query_where) or die(mysql_error());
	if ($row = mysql_fetch_assoc($all)) {
		$totalRows = $row['nbr'];
	}
}
$totalPages = ceil($totalRows/$maxRows)-1;

$RecAuteur = mysql_query($query_limit) or die(mysql_error());

$t = new Template(BDO_DIR."public/templates");
$t->set_file(array(
"tpBody" => "browser_search.tpl",
));

// on déclare les blocks contenus sur la feuille
$t->set_block('tpBody','DataBlock','DBlock');


$queryString = "";
if (!empty($_SERVER['QUERY_STRING'])) {
	$params = explode("&", $_SERVER['QUERY_STRING']);
	$newParams = array();
	foreach ($params as $param) {
		if (stristr($param, "pageNum") == false &&
		stristr($param, "totalRows") == false &&
		stristr($param, "lev_id") == false &&
		stristr($param, "lev2_id") == false &&
		stristr($param, "rb_browse") == false) {
			array_push($newParams, $param);
		}
	}
	if (count($newParams) != 0) {
		$queryString = "&" . implode("&", $newParams);
	}
}
$queryString = sprintf("&totalRows=%d%s", $totalRows, $queryString);

if (!$rb_browse or $rb_browse == 'ser') {
	$t->set_var("TYPBROWSE","ser");
}
if($rb_browse=='aut') {
	$t->set_var("AUTCHECK","checked");
	$t->set_var("TYPBROWSE","aut");
}
if ($rb_browse == 'genr') {
	$t->set_var("GENRCHECK","checked");
	$t->set_var("TYPBROWSE","genr");
}
if ($rb_browse == 'edit') {
	$t->set_var("EDITCHECK","checked");
	$t->set_var("TYPBROWSE","edit");
}

$query_string = "";
if ($rb_browse) {
	$query_string .= "rb_browse=".$rb_browse;
}
else {
	$rb_browse = "ser";
	$query_string .= "rb_browse=".$rb_browse;
}
$queryString .="&".$query_string;


if ($rb_browse != "ser") {
	while ($row = mysql_fetch_assoc($RecAuteur)) {
		$t->set_var(array(
		"WSPACER"=>"0",
		"HSPACER"=>"0",
		"IMGNAVIG"=>"aro_3_1.gif",
		"URLEVEL"=>"browser_xhr.php"."?lev_id=".$row['id'].$queryString,
		"NAMELEVEL"=>htmlspecialchars($row['name']),
		"ACTLEVEL"=>"",
		"LEVSIGN" =>"1L".$row['id']
		));
		$keyword.=htmlspecialchars($row['name']).",";
		if (minAccessLevel(1,false)) {
			$t->set_var("URLEDIT", sprintf($edit_lev1,$row['id']));
		}
		$t->parse("DBlock","DataBlock",true);
	}
}
else {
	// browse par série : seulement 2 niveaux
	while ($row = mysql_fetch_assoc($RecAuteur)) {
		$t->set_var(array(
		"WSPACER"=>"0",
		"HSPACER"=>"0",
		"IMGNAVIG"=>"aro_3_1.gif",
		"URLEVEL"=>"browser_xhr_level2.php"."?lev_id=".$row['id'].$queryString,
		"NAMELEVEL"=>htmlspecialchars(stripslashes($row['name'])),
		"ACTLEVEL"=>"",
		"LEVSIGN" =>"1L".$row['id']
		));
		$keyword.=htmlspecialchars($row['name']).",";
		if (minAccessLevel(1,false)) {
			$t->set_var("URLEDIT", sprintf($edit_lev1,$row['id']));
		}
		$t->parse("DBlock","DataBlock",true);
	}
}


if (!empty($totalRows) and ($totalRows <= $maxRows))
{
	$t->set_var("URLPREVPAGE",'');
	$t->set_var("URLNEXTPAGE",'');
	$t->set_var("TOTALROW",'');
}
else if (!empty($totalRows))
{
	$totalrow = 'lignes de '.($startRow+1).' à ';
	$totalrow .= ($startRow+$maxRows > $totalRows) ? $totalRows : ($startRow+$maxRows);
	$totalrow .= ' ( sur '.$totalRows.' lignes )';

	if ($pageNum > 0)
	$t->set_var("URLPREVPAGE",'<a href="javascript:document.browser.pageNum.value=\''.max(0, $pageNum - 1).'\';go2()">Pr&eacute;c&eacute;dent</a>');
	else
	$t->set_var("URLPREVPAGE",'');
	
	if ($pageNum < $totalPages)
	$t->set_var("URLNEXTPAGE",'<a href="javascript:document.browser.pageNum.value=\''.min($totalPages, $pageNum + 1).'\';go2()">Suivant</a>');
	else
	$t->set_var("URLNEXTPAGE",'');
	$t->set_var("TOTALROW",(empty($totalRows) ? 'Aucune ligne de résultat !' : $totalrow));
}
else 
{
	$t->set_var("URLPREVPAGE",'');
	$t->set_var("URLNEXTPAGE",'');
	$t->set_var("TOTALROW",'Aucune ligne de résultat !');
}

$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
$t->set_var("TITRESEARCH","<b>Contient '...<i>" . htmlspecialchars($let) . "<i>...'</b>");
$t->parse("BODY","tpBody");
$t->pparse("MyFinalOutput","tpBody");
