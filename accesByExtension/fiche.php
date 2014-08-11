<?php


include_once (BDO_DIR."inc/bdovore.php");

include (BDO_DIR."inc/queryfunction.php");


// vérifie qu'une variable a été passée
$alb_id = $_GET["alb_id"];

// prépare le template
$t = new Template(BDO_DIR."public/templates");
$t->set_file(array(
"tpBase" => "public_fiche.tpl"
));

// Charge les statistisques
$query = q_tome("t.id_tome = ".$DB->escape($alb_id));

$DB->query ($query);
$DB->next_record();
$url_couv = $DB->f("img_couv");
$scenar = stripslashes($DB->f("scpseudo"));
$dessin = stripslashes($DB->f("depseudo"));
$color = stripslashes($DB->f("copseudo"));

// affichage de la série
if ($DB->f("flg_fini")==2) {
	$titre = stripslashes($DB->f("titre"))." (One-Shot)";
	$serie = "";
	$lbl_serie = "&nbsp";
}else{
	$titre = stripslashes($DB->f("titre"));
	$serie = stripslashes($DB->f("s_nom"));
	$lbl_serie = "Série :";
}

//affichage de l'éditeur
if ($DB->f("cnom")!="<N/A>")
$editeur = stripslashes($DB->f("enom"))." - ".stripslashes($DB->f("cnom"));
else
$editeur = stripslashes($DB->f("enom"));

//affichage de l'eiteur seul
$shortedit = stripslashes($DB->f("enom"));

// affichage de la date de parution
$dte_parution = dateParution($DB->f("dte_parution"),$DB->f("flag_dte_parution"));

$url_scenar = BDO_URL.'browser.php?rb_browse=aut&lev_id='.$DB->f("id_scenar")."&let=".
htmlspecialchars($DB->f("scpseudo"));
$url_dessin = BDO_URL.'browser.php?rb_browse=aut&lev_id='.$DB->f("id_dessin")."&let=".
htmlspecialchars($DB->f("depseudo"));
$url_color = BDO_URL.'browser.php?rb_browse=aut&lev_id='.$DB->f("id_color")."&let=".
htmlspecialchars($DB->f("copseudo"));
$url_serie = BDO_URL."serie.php?id_serie=".$DB->f("id_serie");
$url_titre = BDO_URL."membres/album.php?id_tome=".$alb_id;

$url_achat = BDO_URL."membres/add_fiche_album.php?act=add&id_tome=".$DB->f("id_tome")."&flg_achat=O&id_edition=".$DB->f("id_edition");

// Rempli le template
$t->set_var (array(
"TITRE" => $titre,
"URLTITRE" => $url_titre,
"URLSERIE" => $url_serie,
"SCENARISTE" => $scenar,
"URLSCENAR" => $url_scenar,
"DESSINATEUR" => $dessin,
"URLDESSIN" => $url_dessin,
"COLORISTE" => $color,
"URLCOLOR" => $url_color,
"SERIE" => $serie,
"LBLSERIE" => $lbl_serie,
"DTEPARUTION" => $dte_parution,
"EDITEUR" => $editeur,
"SHORTEDIT" => $shortedit,
"URLCOUV" => BDO_URL_COUV.$url_couv,
"URLACHATFUTUR" => $url_achat
));


// Envoie les info générales et publie la page
$css_sheets = array("fiche.css");
$t->set_var (array(
"URLSITE" => BDO_URL,
"URLSITEIMAGE" => BDO_URL_IMAGE,
"DOCTITRE" => "Fiche ".$titre,
"CSSSTYLE" => gen_css_links($css_sheets)
));

$t->pparse("MyFinalOutput","tpBase");
