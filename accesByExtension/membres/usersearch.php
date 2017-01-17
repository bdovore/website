<?php



include (BDO_DIR."inc/queryfunction.php");

minAccessLevel(2);

// Défini les paramètres d'affichage

$query = "SELECT row_display FROM users WHERE user_id=".$DB->escape($_SESSION["UserId"]);
$DB->query($query);
$DB->next_record();

$pagetitle = "Mes albums : ";

$maxRows = $DB->f("row_display");
$pageNum = 0;

if (isset($_GET['pageNum'])) {
    $pageNum = $_GET['pageNum'];
}
$startRow = $pageNum * $maxRows;

// Creation d'un nouveau Template
$t = new Template(BDO_DIR."public/templates");

// fichier à utiliser
$t->set_file(array(
"tpMenuColl" => "menu_coll.tpl",
"tpBody" => "userspace.search.tpl",
"tpMenu" => "user.menu.tpl",
"tpBase" => "body.tpl"));

// on déclare les blocks contenus sur la feuille
$t->set_block('tpBody','ResultBlock','RBlock');
$t->set_block('tpBody','ButtonBlock','BBlock');

if ($del == 1) {
    //suppression d'une edition de la collection
    $delete = "delete from users_album where user_id=%d and id_edition=%d";
    $delete = sprintf($delete,$DB->escape($_SESSION["UserId"]),$id_edition);
    $DB->query($delete);
}

$nb = 0;
$flg_achat = 'N';

$Album = new QueryAlbum;
$Album->setUserMode($_SESSION["UserId"]);

$str_mode = "";
if ($search_mode) {
    $Album->setSearchMode($search_mode);
    $str_mode.="&search_mode=".$search_mode;
    //echo 'alors '.$search_mode;
}

if ($cb_pret ) {
    $Album->filterPret();
    $pret_check = "checked";
    $str_mode.="&cb_pret=1";
}
if ($cb_dedi) {
    $Album->filterDedicace('O');
    $dedi_check = "checked";
    $str_mode.="&cb_dedi=1";
}
if ($cb_comment) {
    $Album->filterNonComment();
    $cmt_check = "checked";
    $str_mode.="&cb_comment=1";
}
if ($cb_note) {
    $Album->filterNonNote();
    $not_check = "checked";
    $str_mode.="&cb_note=1";
}
if ($rb_mode ) {
    if ($rb_mode==1) {
        $t->set_var("MODE1CHECKED","checked");
        $Album->filterAchat('N');
        $str_mode .= "&rb_mode=1";
        $flg_achat = 'N';
    }
    else if ($rb_mode == 2) {
        $t->set_var("MODE2CHECKED","checked");
        $pagetitle.= "mes prets";
        $Album->filterPret();
        $str_mode .= "&rb_mode=2";
        $flg_achat = 'N';
    }
    else {
        $t->set_var("MODE3CHECKED","checked");
        $pagetitle.= "mes futurs achats";
        $Album->filterAchat('O');
        $str_mode .= "&rb_mode=3";
        $flg_achat = 'O';
    }
}
else {
    $t->set_var("MODE1CHECKED","checked");
    $Album->filterAchat('N');
    $str_mode .= "&rb_mode=1";
}
$strq= "";
if ($cb_album) {
    // ajout du filtre titre album
    $Album->filterTitle($DB->escape($str_search));
    $strq .= "&cb_album=1";
}
if ($cb_serie) {
    $Album->filterSerie($DB->escape($str_search));
    $strq .= "&cb_serie=1";
}
if ($cb_aut) {
    $Album->filterAuteur($DB->escape($str_search));
    $strq .= "&cb_aut=1";
}
if (isset($_GET['totalRows'])) {
    $totalRows = $_GET['totalRows'];
} else {
    $DB->query($Album->getQuery(1,0,0));
    //echo_pre($Album->getQuery(1,0,0));

    $DB->next_record();
    $totalRows = $DB->f("total");
}
$totalPages = ceil($totalRows/$maxRows)-1;
$queryString = "";

$queryString = sprintf("&totalRows=%d%s", $totalRows, $queryString);
$queryString .=  "&str_search=".$str_search.$strq;
$queryString .= $str_mode;


if ($ch_tri==1) $Album->setOrder("ser.nom, t.num_tome, t.titre");
if ($ch_tri==2) $Album->setOrder("t.titre, t.num_tome, ser.nom");
if ($ch_tri==3) $Album->setOrder("s.pseudo, d.pseudo, ser.nom, t.titre");

$DB->query($Album->getQuery(2,$startRow,$maxRows));
//echo_pre($Album->getQuery(2,$startRow,$maxRows));


if ($DB->nf() != 0 ) {
    while ($DB->next_record()) {
        $urldelete = $_SERVER["PHP_SELF"]."?del=1&id_tome=".$DB->f("id_tome")."&id_edition=".$DB->f("id_edition");
        if ($DB->f("flg_int") == 'O') {
            $lib_serie = stripslashes($DB->f("serie"))." - Intégrale";
        }
        else {
            if ($DB->f("flg_fini") == 2 ) {
                $lib_serie = stripslashes($DB->f("serie")). " (One shot)";
            }
            else {
                if ($DB->f("flg_type") == 1) {
                    $lib_serie = stripslashes($DB->f("serie"))." - Coffret";
                }
                else {
                    if ($DB->f("num_tome") == 0 || !$DB->f("num_tome")) {
                        $lib_serie = stripslashes($DB->f("serie"))." - HS";
                    }
                    else {
                        $lib_serie = stripslashes($DB->f("serie"))." n°".$DB->f("num_tome");
                    }
                }
            }
        }
        $t->set_var (array
        (
        //"URLALBUM" => '"#"'.' onclick="window.open('."'useralbum.php?id_tome=".$DB->f("id_tome")."&id_edition=".$DB->f("id_edition") ."','Album','width=580,height=530,scrollbars=1')".'"',
        "URLALBUM" => getAlbumUrl($DB->f("id_tome")),//"#"'.' onclick="window.open('."'membres/album.php?id_tome=".$DB->f("id_tome")."','Album','width=500,height=400,scrollbars=1')".'"',
        "TITRE" => stripslashes($DB->f("titre")),
        "SERIE" => $lib_serie,
        "EDITEUR" => $DB->f("editeur"),
        "DTEPARUTION" => dateParution($DB->f("dte_parution"),$DB->f("flag_dte_parution")),
        "URLSERIE"=> "userserie.php?id_serie=".$DB->f("id_serie"),
        "URLDELETE" => $urldelete."&str_search=".$str_search.$strq.$str_mode,
        "IDALBUM"=> $DB->f("id_tome")));

        // Assigne Auteur et Scenariste si necessaire
        if ($DB->f("id_scenar") == $DB->f("id_dessin")) {
            $t->set_var (array
            ("SCEN" => $DB->f("p_scenar"),
            "URLSCEN"=> $_SERVER["PHP_SELF"]."?str_search=".$DB->f("p_scenar")."&cb_aut=1&rb_mode=".$rb_mode,
            "SEP" => "",
            "DESS" => "",
            "URLDESS" => ""));
        }
        else {
            $t->set_var (array
            ("SCEN" => $DB->f("p_scenar"),
            "URLSCEN" => $_SERVER["PHP_SELF"]."?str_search=".$DB->f("p_scenar")."&cb_aut=1&rb_mode=".$rb_mode,
            "SEP" => "/",
            "DESS" => $DB->f("p_dessin"),
            "URLDESS" => $_SERVER["PHP_SELF"]."?str_search=".$DB->f("p_dessin")."&cb_aut=1&rb_mode=".$rb_mode ));
        }
        $t->parse ("RBlock", "ResultBlock",true);
    }
}
// Nb de réponses retournées par la query
if ($totalRows > 0 ) {
    $t->set_var("NBREPONSES",$totalRows." album(s) trouvé(s) !");
    $navig = '<a href="'.sprintf("%s?pageNum=%d%s", $_SERVER["PHP_SELF"], max(0, $pageNum - 1), $queryString).'">&lt;&lt;</a> ';
    for ($i=0;$i < $totalPages+1;$i++) {
        if ($i == $pageNum)
        {   $navig.= '<b><a href="'.sprintf("%s?pageNum=%d%s", $_SERVER["PHP_SELF"], $i, $queryString).'">'.($i+1)."</a></b> ";
        }else{
            $navig.= '<a href="'.sprintf("%s?pageNum=%d%s", $_SERVER["PHP_SELF"], $i, $queryString).'">'.($i+1)."</a> ";
        }
    }
    $navig.= '<a href="'.sprintf("%s?pageNum=%d%s", $_SERVER["PHP_SELF"], min($totalPages, $pageNum + 1), $queryString).'">&gt;&gt;</a>';
    $t->set_var("NAVPAGE",$navig);
    $t->parse ("BBlock", "ButtonBlock",true);
}
else {
    $t->set_var("NBREPONSES","Aucun album trouvé, désolé !");
}

// assigne la barre de login
$t->set_var (array
("LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
"URLSITEIMAGE" => BDO_URL_IMAGE,
"URLSITEFORUM" => BDO_URL_FORUM
));

// Check box

    if (( $cb_album ) or (!$cb_serie and !$cb_aut)) {
        $t->set_var("ALBCHECK","checked");
    }

if ($cb_serie) {$t->set_var("SERCHECK","checked");}
if ($cb_aut) {$t->set_var("AUTCHECK","checked");}

foreach (array(1=>'séries',2=>'albums',3=>'auteurs') as $id_tri=>$tri) {
    $option.= '<option value="'.$id_tri.'"';
    if ($ch_tri == $id_tri) {
        $option.= " selected";
    }
    $option.=">".htmlspecialchars($tri)."</option>";
}

$t->set_var("OPTIONS",$option);


$t->set_var(array(
"PRETCHECK" => $pret_check,
"DEDICHECK" => $dedi_check,
"CMTCHECK" => $cmt_check,
"NOTCHECK" => $not_check,
"RBMODE"=>$rb_mode,
"FLG_ACHAT" =>$flg_achat
));
// Valeur de recherche par défault
$t->set_var("DEFSEARCH","'".stripslashes(htmlspecialchars($str_search, ENT_QUOTES))."'");
$t->set_var("PAGETITLE",$pagetitle);
$t->parse("MENUCOLL","tpMenuColl");
$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->pparse("MyFinalOutput","tpBase");
