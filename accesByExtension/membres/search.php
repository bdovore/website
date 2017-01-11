<?php



include (BDO_DIR."inc/queryfunction.php");

// Défini les paramètres d'affichage
if (minAccessLevel(2,false)) {
	$query = "SELECT row_display FROM users WHERE user_id =".$DB->escape($_SESSION["UserId"]);
	$DB->query($query);
	$DB->next_record();
	$maxRows = $DB->f("row_display");
}else {
	$maxRows = 20;
}

$pageNum = 0;
if (isset($_GET['pageNum'])) {
	$pageNum = $_GET['pageNum'];
}
$startRow = $pageNum * $maxRows;

// Creation d'un nouveau Template
$t = new Template(BDO_DIR."public/templates");

// fichier à utiliser
$t->set_file(array(
"tpForm_Recherche" => "form_recherche.tpl",
"tpBody" => "search.tpl",
"tpMenu" => "menu.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBase" => "body.tpl"
));

$str_search = postVal("str_search", getVal("str_search"));
$cb_serie = postVal("cb_serie", getVal("cb_serie"));
$cb_album = postVal("cb_album", getVal("cb_album"));
$cb_aut = postVal("cb_aut", getVal("cb_aut"));

// on déclare les blocks contenus sur la feuille
$t->set_block('tpBody','ResultBlock','RBlock');
$t->set_block('tpBody','ButtonBlock','BBlock');

if ($str_search)
{
	$nb = 0;
	$Album = new QueryAlbum;
	$strq= "";

	// analyse de la chaine :
	// - test 1 : verification sur expression exact
	if (substr($str_search,0,1) == '"' && substr($str_search,-1,1) == '"')
	{
		$Album->setSearchMode(3);
		$s_filter = substr($str_search,1,strlen($str_search) - 2);
		$s_filter = str_replace("*","%",$s_filter);
	}else {
		$Album->setSearchMode(1);
		$s_filter = $DB->escape($str_search);
	}

	if (strtoupper(substr($str_search,0,5)) == "ISBN:") {
		$Album->filterISBN($DB->escape(substr($str_search,5,10)));
	}else
	{
		if ($cb_album) {
			// ajout du filtre titre album
			$Album->filterTitle($s_filter);
			$strq .= "&cb_album=1";
			$mode = "cb_album";
		}
		if ($cb_serie) {
			$Album->filterSerie($s_filter);
			$strq .= "&cb_serie=1";
			$mode = "cb_serie";
		}
		if ($cb_aut) {
			$Album->filterAuteur($s_filter);
			$strq .= "&cb_aut=1";
			$mode="cb_aut";
		}
	}
	if (isset($_GET['totalRows'])) {
		$totalRows = $_GET['totalRows'];
	} else {
		//echo_pre($Album->getQuery(1,0,0));
		$DB->query($Album->getQuery(1,0,0));
		$DB->next_record();
		$totalRows = $DB->f("total");
	}
	$totalPages = ceil($totalRows/$maxRows)-1;
	$queryString = "";
	if (!empty($_SERVER['QUERY_STRING'])) {
		$params = explode("&", $_SERVER['QUERY_STRING']);
		$newParams = array();
		foreach ($params as $param) {
			if (stristr($param, "pageNum") == false &&
			stristr($param, "totalRows") == false &&
			stristr($param, "str_search") == false &&
			stristr($param, "cb_aut") == false &&
			stristr($param, "cb_serie") == false &&
			stristr($param, "cb_album") == false ) {
				array_push($newParams, $param);
			}
		}
		if (count($newParams) != 0) {
			$queryString = "&" . implode("&", $newParams);
		}
	}
	$queryString = sprintf("&totalRows=%d%s", $totalRows, $queryString);
	$queryString .=  "&str_search=".stripslashes(htmlspecialchars($str_search,ENT_QUOTES)).$strq;

	// page d'edition pour les admin :
	if (minAccessLevel(1,false)) {
		$editAlbum = "<a href='../admin/adminalbums.php?alb_id=%d' target=_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0></a>";
		$editSerie = "<a href='../admin/adminseries.php?serie_id=%d' target=_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0></a>";
	}
	//echo_pre($Album->getQuery(2,$startRow,$maxRows));
	$DB->query($Album->getQuery(2,$startRow,$maxRows));
	if ($DB->nf() != 0 ) {
		while ($DB->next_record()) {
			if ($DB->f("flg_int") == 'O') {
				$lib_serie = stripslashes($DB->f("serie"))." - Intégrale";
			}else {
				if ($DB->f("flg_fini") == 2 ) {
					$lib_serie = stripslashes($DB->f("serie")). " (One shot)";
				}else {
					if ($DB->f("flg_type") == 1) {
						$lib_serie = stripslashes($DB->f("serie"))." - Coffret";
					}else {
						if ($DB->f("num_tome") == 0 || !$DB->f("num_tome")) {
							$lib_serie = stripslashes($DB->f("serie"))." - HS";
						}else {
							$lib_serie = stripslashes($DB->f("serie"))." n°".$DB->f("num_tome");
						}
					}
				}
			}
			$t->set_var (array(
			"URLALBUM" => getAlbumUrl($DB->f("id_tome")),//'"#"'.' onclick="window.open('."'album.php?id_tome=".$DB->f("id_tome")."','Album','width=500,height=400,scrollbars=1')".'"',
			"TITRE" => stripslashes($DB->f("titre")),
			"SERIE" => $lib_serie,
			"EDITEUR" => $DB->f("editeur"),
			"URLSERIE"=> "../serie.php?id_serie=".$DB->f("id_serie"),
			"URLDELETE" => $urldelete,
			"IDALBUM"=> $DB->f("id_tome")));

			// ajout de l'edition si admin
			if (minAccessLevel(1,false)) {
				$t->set_var(array(
				"EDITALBUM"=> sprintf($editAlbum,$DB->f("id_tome")),
				"EDITSERIE" => sprintf($editSerie,$DB->f("id_serie"))
				));
			}

			// Assigne Auteur et Scenariste si necessaire
			if ($DB->f("id_scenar") == $DB->f("id_dessin")) {
				$t->set_var (array(
				"SCEN" => $DB->f("p_scenar"),
				"URLSCEN"=> $_SERVER["PHP_SELF"]."?str_search=".htmlspecialchars($DB->f("p_scenar"))."&cb_aut=1",
				"SEP" => "",
				"DESS" => "",
				"URLDESS" => "" ));
			}else {
				$t->set_var (array(
				"SCEN" => $DB->f("p_scenar"),
				"URLSCEN" => $_SERVER["PHP_SELF"]."?str_search=".htmlspecialchars($DB->f("p_scenar"))."&cb_aut=1",
				"SEP" => "/",
				"DESS" => $DB->f("p_dessin"),
				"URLDESS" => $_SERVER["PHP_SELF"]."?str_search=".htmlspecialchars($DB->f("p_dessin"))."&cb_aut=1"
				));
			}
			$t->parse ("RBlock", "ResultBlock",true);
		}
	}
	// Nb de réponses retournées par la query
	if ($totalRows > 0 ) {
		$t->set_var("NBREPONSES",$totalRows." album(s) trouvé(s) !");
		$navig = '<a href="'.sprintf("%s?pageNum=%d%s", $_SERVER["PHP_SELF"], max(0, $pageNum - 1), $queryString).'">&lt;&lt;</a>&nbsp;';
		for ($i=max($pageNum - 10, 0);$i < min($pageNum+10,$totalPages)+1;$i++) {
			if ($i == $pageNum){
				$navig.= '<b><a href="'.sprintf("%s?pageNum=%d%s", $_SERVER["PHP_SELF"], $i, $queryString).'">'.($i+1)."</a></b>&nbsp;";
			}else{
				$navig.= '<a href="'.sprintf("%s?pageNum=%d%s", $_SERVER["PHP_SELF"], $i, $queryString).'">'.($i+1)."</a>&nbsp;";
			}
		}
		$navig.= '<a href="'.sprintf("%s?pageNum=%d%s", $_SERVER["PHP_SELF"], min($totalPages, $pageNum + 1), $queryString).'">&gt;&gt;</a>';
		$t->set_var("NAVPAGE",$navig);
		$t->parse ("BBlock", "ButtonBlock",true);
	}else {
		$t->set_var("NBREPONSES","Aucun album trouvé, désolé ! <br>
					<a href='prop_album.php'>Proposer l'ajout d'un album</a>"
					);
	}
}

// variable caché du formulaire d'ajout pour la validation
$t->set_var(array(
"SEARCHSTR"=> stripslashes($str_search),
"NBROW"=>$totalRows,
"PAGENUM"=>$pageNum,
"MODESEARCH"=>$mode
));

// assigne la barre de login
$t->set_var (array(
"LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
));

// Check box
if ($str_search) {
	if ( $cb_album ) {
		$t->set_var("ALBCHECK","checked");
	}
}else {
	$t->set_var("ALBCHECK","checked");
}
if ($cb_serie) {
	$t->set_var("SERCHECK","checked");
}
if ($cb_aut) {
	$t->set_var("AUTCHECK","checked");
}
$t->set_var("PAGETITLE","BDOVORE.com : Recherche des albums dans la base");
$t->set_var("PAGEKEYWORD",$str_search);

// Valeur de recherche par défault
$t->set_var("DEFSEARCH","'".stripslashes(htmlentities($str_search,ENT_QUOTES))."'");
$t->parse("FORM_RECHERCHE","tpForm_Recherche");
$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBase");
