<?php


header('Content-Type: text/html; charset=ISO-8859-15');

include_once (BDO_DIR."inc/bdovore.php");



include (BDO_DIR."inc/queryfunction.php");


mysql_connect(BDO_DB_HOST, BDO_DB_USER, BDO_DB_PWD);
mysql_select_db(BDO_DB_SID);


// defintion des variables
$maxRows = 25;
$pageNum = 0;
$pagetitle = "BDovore.com - Bandes dessinées par ";
$keyword = "";

$pageNum = getVal('pageNum');
$row = getVal('row');

$rb_browse = getVal('rb_browse');
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

if ($rb_browse == 'aut') {

	$query_niv2 = "
	select SQL_CALC_FOUND_ROWS distinct
		bd_serie.id_serie id,
		bd_serie.nom name
	from
		bd_serie
		INNER JOIN bd_tome USING(id_serie)
	where
		bd_tome.id_dessin = ".mysql_real_escape_string($lev_id)."
		or bd_tome.id_scenar = ".mysql_real_escape_string($lev_id)."
		or bd_tome.id_color = ".mysql_real_escape_string($lev_id)."
		or bd_tome.id_scenar_alt = ".mysql_real_escape_string($lev_id)."
		or bd_tome.id_dessin_alt = ".mysql_real_escape_string($lev_id)."
		or bd_tome.id_color_alt = ".mysql_real_escape_string($lev_id)."
	order by name ";

	// URL album
	$act_url = ' onclick="window.open('."'./membres/album.php?id_tome=%d','Album','width=500,height=400,scrollbars=1')".'"';
	$url_alb = "";
	// URL d'edition
	$edit_lev2 = "<a href='".$url_edit["SERIE"]."' target =_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0 /></a>";
}
elseif ($rb_browse == 'edit'){

	$query_niv2 = "
	select SQL_CALC_FOUND_ROWS
		id_collection id,
		nom name
	from
		bd_collection
	where
		id_editeur =" . mysql_real_escape_string($lev_id) . "
	order by name
	";

	// URL affichage
	$url_alb = "serie.php?id_serie=%d";
	$act_url = "";
	// URL d'edition
	$edit_lev2 = "<a href='".$url_edit["COLLECTION"]."' target =_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0></a>";
}
elseif ($rb_browse == 'genr') {

	$query_niv2 = "
	select SQL_CALC_FOUND_ROWS distinct
		Upper(left(nom,1)) id,
		Upper(left(nom,1)) name
	from
		bd_serie
	where
		id_genre =" . mysql_real_escape_string($lev_id) . "
	order by name
	";

	$url_alb = "serie.php?id_serie=%d";
	$act_url = "";
	// URL d'edition
	$edit_lev2 = "";
}


$t = new Template(BDO_DIR."public/templates");
$t->set_file(array(
"tpBody" => "browser_xhr.tpl",
));

// on déclare les blocks contenus sur la feuille
$t->set_block('tpBody','DataBlock','DBlock');


$recLev2 = mysql_query($query_niv2 . " LIMIT ".mysql_real_escape_string($startRow).",".mysql_real_escape_string($maxRows));

$resCount = mysql_query('SELECT FOUND_ROWS() as nb');
$rowCount = mysql_fetch_assoc($resCount);
$totalRows = $rowCount['nb'];

while ($row_niv2 = mysql_fetch_assoc($recLev2)) {
	$t->set_var(array(
	"WSPACER"=>"16px",
	"HSPACER"=>"1px",
	"IMGNAVIG"=>"aro_3_1.gif",
	"URLEVEL"=>"browser_xhr_level2.php"."?rb_browse=".$rb_browse . "&lev_id=".$lev_id."&lev2_id=".$row_niv2['id'],
	"NAMELEVEL"=>htmlspecialchars(stripslashes($row_niv2['name'])),
	"ACTLEVEL"=>"",
	"LEVSIGN" =>"1L".$lev_id."_2L".$row_niv2['id'],
	));

	if (minAccessLevel(1,false)) {
		$t->set_var("URLEDIT", sprintf($edit_lev2,$row_niv2['id']));
	}
	$t->parse("DBlock","DataBlock",true);
}



if (!empty($totalRows) and ($totalRows <= $maxRows))
{
	$t->set_var("URLPREVPAGE",'');
	$t->set_var("URLNEXTPAGE",'');
	$t->set_var("TOTALROW",'');
}
else if (!empty($totalRows))
{
	$totalrowhtml = 'lignes de '.($startRow+1).' à ';
	$totalrowhtml .= ($startRow+$maxRows > $totalRows) ? $totalRows : ($startRow+$maxRows);
	$totalrowhtml .= ' ( sur '.$totalRows.' lignes )';

	$totalPages = ceil($totalRows/$maxRows)-1;

	if ($pageNum > 0)
	{
		$url = '<a href=\'javascript:SubmitXhr("","onglet_div_xhr_1L'.$lev_id . '","browser_xhr.php?rb_browse=' . $rb_browse . '&lev_id=' . $lev_id . '&pageNum='.max(0, $pageNum - 1).'",false)\' >Pr&eacute;c&eacute;dent</a>';
		$t->set_var("URLPREVPAGE",$url);
	}
	else
	$t->set_var("URLPREVPAGE",'');

	if ($pageNum < $totalPages)
	{
		$url = '<a href=\'javascript:SubmitXhr("","onglet_div_xhr_1L'.$lev_id . '","browser_xhr.php?rb_browse=' . $rb_browse . '&lev_id=' . $lev_id . '&pageNum='.min($totalPages, $pageNum + 1).'",false)\' >Suivant</a>';
		$t->set_var("URLNEXTPAGE",$url);
	}
	else
	$t->set_var("URLNEXTPAGE",'');

	$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
	$t->set_var("TOTALROW",(empty($totalRows) ? 'Aucune ligne de résultat !' : $totalrowhtml));
}
else
{
	$t->set_var("URLPREVPAGE",'');
	$t->set_var("URLNEXTPAGE",'');
	$t->set_var("TOTALROW",'Aucune ligne de résultat !');
}


$t->set_var("PAGETITLE",$pagetitle);
$t->set_var("PAGEKEYWORD",$keyword);
$t->set_var("ACTBROWSER",$_SERVER["PHP_SELF"]."?");
$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
$t->set_var (array("LOGINBARRE" => GetIdentificationBar()));

$t->parse("BODY","tpBody");
$t->pparse("MyFinalOutput","tpBody");
