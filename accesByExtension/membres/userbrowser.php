<?php


include_once (BDO_DIR."inc/bdovore.php");



include (BDO_DIR."inc/queryfunction.php");


minAccessLevel(2);

// defintion des variables

$letter = array ('A', 'B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

$maxRows_RecAuteur = 30;
$pageNum_RecAuteur = 0;
if (isset($_GET['pageNum_RecAuteur'])) {
    $pageNum_RecAuteur = $_GET['pageNum_RecAuteur'];
}
$startRow_RecAuteur = $pageNum_RecAuteur * $maxRows_RecAuteur;


mysql_select_db(BDO_DB_SID, $bdovore);
$filter = "";
$pret_check = "";
$dedi_check = "";
$cmt_check = "";
$not_check = "";
$string_filter = "";
$pagetitle = "Ma collection : ";
if ($cb_pret ) {
    $filter .= " and flg_pret='O'";
    $pret_check = "checked";
    $string_filter.="&cb_pret=1";
}
if ($cb_dedi) {
    $filter .= " and flg_dedicace='O'";
    $dedi_check = "checked";
    $string_filter.="&cb_dedi=1";
}
if ($cb_comment) {
    $filter .= " and c.comment is null";
    $cmt_check = "checked";
    $string_filter.="&cb_comment=1";
}
if ($cb_note) {
    $filter .= " and (c.note = 0 or c.note is null )";
    $not_check = "checked";
    $string_filter.="&cb_note=1";
}

if ($rb_browse == 'ser' || !$rb_browse) {
    $query_RecAuteur = "
    SELECT distinct
    s.id_serie id, s.nom name
    FROM
    users_album u
    INNER JOIN bd_edition en ON u.id_edition=en.id_edition
    INNER JOIN bd_tome t ON en.id_tome=t.id_tome
    INNER JOIN bd_serie s ON t.id_serie=s.id_serie
    left outer join users_comment c on c.user_id = u.user_id and    c.id_tome = en.id_tome
    WHERE
    u.user_id =".mysql_real_escape_string($_SESSION["UserId"])."
    and flg_achat = 'N' ".$filter;
    $pagetitle.= "série ";

    if ($let) {
        if (strlen($let) < 4) {
            $query_RecAuteur .= " and LCASE(s.tri) like LCASE('".mysql_real_escape_string($let)."%') ";
        }
        else {
            $query_RecAuteur .= " and LCASE(s.nom) like LCASE('%".mysql_real_escape_string($let)."%') ";
        }
    }
    $query_RecAuteur.=" ORDER BY s.tri, s.nom ASC ";

    $query_album = "
    select
        t.id_tome,
        CONCAT(CASE when t.flg_int = 'O'
        then 'Intégrale '
        when t.flg_type = 1
        then 'Coffret '
        else IFNULL(concat('Tome ',t.num_tome),'HS') end,': ', t.titre) as titre,
        u.id_edition
    from
        users_album u
        INNER JOIN bd_edition en ON u.id_edition=en.id_edition
        INNER JOIN bd_tome t ON en.id_tome=t.id_tome
        left outer join users_comment c on c.user_id = u.user_id and    c.id_tome = en.id_tome
    where
        t.id_serie=".mysql_real_escape_string($lev_id)."
        and u.user_id =".mysql_real_escape_string($_SESSION["UserId"])."
        and u.flg_achat='N' ".$filter."
    order by t.num_tome, t.titre ";

    // URL album
    $act_url = ' onclick="window.open('."'./membres/album.php?id_tome=%d&id_edition=%d','Album','width=500,height=400,scrollbars=1')".'"';
    $url_alb = "";
    // URL d'edition
    $edit_lev1 = "";
    $edit_lev2 = "<a href='".$url_edit["SERIE"]."' target =_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0></a>";
    $edit_lev3 ="<a href='".$url_edit["ALBUM"]."' target =_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0></a>";


}
elseif ($rb_browse == 'aut') {
    $pagetitle.= "mes auteurs ";
    if ($let) {
        $filtre_aut = " and LCASE(pseudo) like LCASE('".mysql_real_escape_string($let)."%') ";
    } else {
        $filtre_aut = "";
    }
    $query_RecAuteur = "
        SELECT distinct
        a.ID_AUTEUR id,
        a.PSEUDO name
    FROM
        users_album u
        INNER JOIN bd_edition en ON u.id_edition=en.id_edition
        INNER JOIN bd_tome t ON en.id_tome=t.id_tome
        INNER JOIN bd_auteur a ON t.id_dessin=a.id_auteur
    where
        u.user_id =".mysql_real_escape_string($_SESSION["UserId"])."
        and u.flg_achat='N' $filtre_aut

    union

    SELECT distinct
        a.ID_AUTEUR id,
        a.PSEUDO name
    FROM
        users_album u
        INNER JOIN bd_edition en ON u.id_edition=en.id_edition
        INNER JOIN bd_tome t ON en.id_tome=t.id_tome
        INNER JOIN bd_auteur a ON t.id_scenar=a.id_auteur
    where
        u.user_id =".mysql_real_escape_string($_SESSION["UserId"])."
        and u.flg_achat='N' $filtre_aut
    group by id, name";

    $query_RecAuteur.=" ORDER BY name ASC ";
    $query_niv2 = "
    select distinct
        s.id_serie id,
        s.nom name
    from
        users_album u
        INNER JOIN bd_edition en ON u.id_edition=en.id_edition
        INNER JOIN bd_tome t ON en.id_tome=t.id_tome
        INNER JOIN bd_serie s ON s.id_serie=t.id_serie
        left outer join users_comment c on c.user_id = u.user_id and    c.id_tome = en.id_tome
    where
    (t.id_dessin = ".mysql_real_escape_string($lev_id)." or t.id_scenar = ".mysql_real_escape_string($lev_id).")
    and u.user_id =".mysql_real_escape_string($_SESSION["UserId"])."
    and flg_achat ='N'
    ".$filter." order by name
    ";


    $query_album = "
    select
        t.id_tome,
        CONCAT(CASE when t.flg_int = 'O'
        then 'Intégrale '
        when t.flg_type = 1
        then 'Coffret '
        else IFNULL(concat('Tome ',t.num_tome),'HS') end,': ', t.titre) as titre,
        u.id_edition
    from
        users_album u
        INNER JOIN bd_edition en ON u.id_edition=en.id_edition
        INNER JOIN bd_tome t ON en.id_tome=t.id_tome
        left outer join users_comment c on c.user_id = u.user_id and    c.id_tome = en.id_tome
    where
        t.id_serie=".mysql_real_escape_string($lev2_id)."
        and u.user_id =".mysql_real_escape_string($_SESSION["UserId"])."
        and u.flg_achat='N' ".$filter."
    order by t.num_tome, t.titre ";


}
elseif ($rb_browse == 'edit'){
    $pagetitle."mes éditeurs ";
    $query_RecAuteur = "
    SELECT distinct
        e.ID_EDITEUR id,
        e.NOM name
    FROM
        users_album u
        INNER JOIN bd_edition en ON u.id_edition=en.id_edition
        INNER JOIN bd_collection col ON en.id_collection=col.id_collection
        INNER JOIN bd_editeur e ON col.id_editeur=e.id_editeur
        left outer join users_comment c on c.user_id = u.user_id and    c.id_tome = en.id_tome
    where
        u.user_id =".mysql_real_escape_string($_SESSION["UserId"])."
        and flg_achat ='N' ".$filter;
    if ($let) {
        $query_RecAuteur .= " and LCASE(e.NOM) like LCASE('".mysql_real_escape_string($let)."%') ";
    }
    $query_RecAuteur.=" ORDER BY e.NOM ASC ";

    $query_niv2 = "
    select
        distinct col.id_collection id,
        col.nom name
    from
        users_album u
        INNER JOIN bd_edition en ON u.id_edition=en.id_edition
        INNER JOIN bd_collection col ON en.id_collection=col.id_collection
        left outer join users_comment c on c.user_id = u.user_id and    c.id_tome = en.id_tome
    where
        col.id_editeur = ".mysql_real_escape_string($lev_id)."
        and u.user_id =".mysql_real_escape_string($_SESSION["UserId"])."
        and u.flg_achat ='N' ".$filter."
        order by name";

        $query_album = "
    select
        t.id_tome,
        CONCAT(s.nom,' : ',CASE when t.flg_int = 'O'
        then 'Intégrale '
        when t.flg_type = 1
        then 'Coffret '
        else IFNULL(concat('Tome ',t.num_tome),'HS') end,': ', t.titre) as titre,
        u.id_edition
    from
        users_album u
        INNER JOIN bd_edition en ON u.id_edition=en.id_edition
        INNER JOIN bd_tome t ON en.id_tome=t.id_tome
        INNER JOIN bd_serie s ON s.id_serie=t.id_serie
        left outer join users_comment c on c.user_id = u.user_id and    c.id_tome = en.id_tome
    where
        en.id_collection = ".mysql_real_escape_string($lev2_id)."
        and u.user_id =".mysql_real_escape_string($_SESSION["UserId"])."
        and u.flg_achat='N' ".$filter."
        order by s.nom,t.num_tome, t.titre ";

}
elseif ($rb_browse == 'genr') {
    $pagetitle.= "mes genres ";
    $query_RecAuteur = "
    SELECT distinct
        g.ID_GENRE id,
        g.LIBELLE name
    FROM
        users_album u
        INNER JOIN bd_edition en ON u.id_edition=en.id_edition
        INNER JOIN bd_tome t ON en.id_tome=t.id_tome
        INNER JOIN bd_serie s ON s.id_serie=t.id_serie
        INNER JOIN bd_genre g ON s.id_genre=g.id_genre
    left outer join users_comment c on c.user_id = u.user_id and    c.id_tome = en.id_tome
    where
    u.user_id =".mysql_real_escape_string($_SESSION["UserId"])."
    and u.flg_achat ='N' ".$filter;

    if ($let) {
        $query_RecAuteur .= " and LCASE(LIBELLE) like LCASE('".mysql_real_escape_string($let)."%') ";
    }
    $query_RecAuteur.=" ORDER BY LIBELLE ASC ";

    $query_niv2 = "
    select distinct
        s.id_serie id,
        s.nom name
    from
        users_album u
        INNER JOIN bd_edition en ON u.id_edition=en.id_edition
        INNER JOIN bd_tome t ON en.id_tome=t.id_tome
        INNER JOIN bd_serie s ON s.id_serie=t.id_serie
        left outer join users_comment c on c.user_id = u.user_id and    c.id_tome = en.id_tome
    where
        s.id_genre = ".mysql_real_escape_string($lev_id)."
        and u.user_id =".mysql_real_escape_string($_SESSION["UserId"])."
        and flg_achat ='N' ".$filter."
    order by name";

        $query_album = "
    select
        t.id_tome,
        CONCAT(CASE when t.flg_int = 'O'
        then 'Intégrale '
        when t.flg_type = 1
        then 'Coffret '
        else IFNULL(concat('Tome ',t.num_tome),'HS') end,': ', t.titre) as titre,
        u.id_edition
    from
        users_album u
        INNER JOIN bd_edition en ON u.id_edition=en.id_edition
        INNER JOIN bd_tome t ON en.id_tome=t.id_tome
                left outer join users_comment c on c.user_id = u.user_id and    c.id_tome = en.id_tome
    where
        t.id_serie = ".mysql_real_escape_string($lev2_id)."
        and u.user_id =".mysql_real_escape_string($_SESSION["UserId"])."
        and u.flg_achat='N' ".$filter."
        order by t.num_tome, t.titre ";

}


$query_limit_RecAuteur = $query_RecAuteur. " LIMIT ".$startRow_RecAuteur.",".$maxRows_RecAuteur;
//echo_pre($query_limit_RecAuteur);

$RecAuteur = mysql_query($query_limit_RecAuteur, $bdovore) or die(mysql_error());
//$row_RecAuteur = mysql_fetch_assoc($RecAuteur);

$t = new Template(BDO_DIR."public/templates");
$t->set_file(array(
"tpMenuColl" => "menu_coll.tpl",
"tpBody" => "userbrowser.tpl",
"tpMenu" => "user.menu.tpl",
"tpBase" => "body.tpl"));

// on déclare les blocks contenus sur la feuille
$t->set_block('tpBody','NavBlock','NBlock');
$t->set_block('tpBody','DataBlock','DBlock');

if (isset($_GET['totalRows_RecAuteur'])) {
    $totalRows_RecAuteur = $_GET['totalRows_RecAuteur'];
} else {
    $all_RecAuteur = mysql_query($query_RecAuteur, $bdovore) or die(mysql_error()) ;
    $totalRows_RecAuteur = mysql_num_rows($all_RecAuteur);
}
$totalPages_RecAuteur = ceil($totalRows_RecAuteur/$maxRows_RecAuteur)-1;


$queryString_RecAuteur = "";
if (!empty($HTTP_SERVER_VARS['QUERY_STRING'])) {
    $params = explode("&", $HTTP_SERVER_VARS['QUERY_STRING']);
    $newParams = array();
    foreach ($params as $param) {
        if (stristr($param, "pageNum_RecAuteur") == false &&
        stristr($param, "totalRows_RecAuteur") == false &&

        stristr($param, "lev_id") == false &&
        stristr($param, "lev2_id") == false &&
        stristr($param, "rb_browse") == false &&
        stristr($param, "let") == false) {
            array_push($newParams, $param);
        }
    }
    if (count($newParams) != 0) {
        $queryString_RecAuteur = "&" . implode("&", $newParams);
    }
}
$queryString_RecAuteur = sprintf("&totalRows_RecAuteur=%d%s", $totalRows_RecAuteur, $queryString_RecAuteur);
if (!$rb_browse or $rb_browse == 'ser') {
    $t->set_var("SERCHECK","checked");
}
if($rb_browse=='aut') {
    $t->set_var("AUTCHECK","checked");
}
if ($rb_browse == 'genr') { $t->set_var("GENRCHECK","checked");}
if ($rb_browse == 'edit') { $t->set_var("EDITCHECK","checked");}

$query_string = "";
if ($rb_browse) {
    $query_string .= "rb_browse=".$rb_browse;
} else {
    $rb_browse = "ser";
    $query_string .= "rb_browse=".$rb_browse;
}
if ($let) {
    $query_string .="&let=".$let;
}
$queryString_RecAuteur .="&".$query_string;

$t->set_var("URLLETTER",$_SERVER["PHP_SELF"]."?".$query_string."&user=".$code_user);
$t->set_var("LETTER","Tous");
$t->set_var("VLETTER","");
$t->parse("NBlock","NavBlock",true);
foreach ($letter as $let_c) {
    $t->set_var("URLLETTER",$_SERVER["PHP_SELF"]."?".$query_string."&let=".$let_c);
    $t->set_var("LETTER",$let_c);
    $t->set_var("VLETTER",$let_c);
    $t->parse("NBlock","NavBlock",true);
}
if ($rb_browse != "ser") {
    while ($row_RecAuteur = mysql_fetch_assoc($RecAuteur)) {
        if ($lev_id == $row_RecAuteur['id']) {
            $pagetitle.= " ".htmlspecialchars($row_RecAuteur['name']);
            $img = "aro_3_2.gif";
        }
        else {
            $img = "aro_3_1.gif";
        }
        $t->set_var(array(
        "WSPACER"=>"0",
        "HSPACER"=>"0",
        "IMGNAVIG"=>$img,
        "URLEVEL"=>sprintf("%s?lev_id=%d%s",$_SERVER["PHP_SELF"],$row_RecAuteur['id'],$queryString_RecAuteur."&pageNum_RecAuteur=".$pageNum_RecAuteur).$string_filter,
        "NAMELEVEL"=>htmlspecialchars($row_RecAuteur['name']),
        "ACTLEVEL"=>"",
        "LEVSIGN" =>"L1".$row_RecAuteur['id']
        ));
        $t->parse("DBlock","DataBlock",true);
        if ($lev_id == $row_RecAuteur['id']) {
            $recLev2 = mysql_query($query_niv2,$bdovore);
            while ($row_niv2 = mysql_fetch_assoc($recLev2)) {
                if ($lev2_id == $row_niv2['id']) {
                    $img = "aro_3_2.gif";
                    $pagetitle.= " - ".htmlspecialchars(stripslashes($row_niv2['name']));
                }
                else {
                    $img = "aro_3_1.gif";
                }
                $t->set_var(array(
                "WSPACER"=>"16",
                "HSPACER"=>"1",
                "IMGNAVIG"=>$img,
                "URLEVEL"=>sprintf ("%s?lev_id=%d%s",$_SERVER["PHP_SELF"],$row_RecAuteur['id'],$queryString_RecAuteur."&pageNum_RecAuteur=".$pageNum_RecAuteur."&lev2_id=".$row_niv2['id']),
                "NAMELEVEL"=>htmlspecialchars(stripslashes($row_niv2['name'])),
                "ACTLEVEL"=>"",
                "LEVSIGN" =>"2L".$row_niv2['id']
                ));
                $t->parse("DBlock","DataBlock",true);
                if ($lev2_id == $row_niv2['id']) {
                    $recAlbum = mysql_query($query_album,$bdovore);
                    while ($rowAlbum = mysql_fetch_assoc($recAlbum)) {
                        $t->set_var(array(
                        "WSPACER"=>"32",
                        "HSPACER"=>"1",
                        "IMGNAVIG"=>"spacer.gif",
                        "URLEVEL"=>"",
                        "NAMELEVEL"=>htmlspecialchars(stripslashes($rowAlbum['titre'])),
                        "ACTLEVEL"=>'onclick="window.open('."'./useralbum.php?id_tome=".$rowAlbum[id_tome]."&id_edition=".$rowAlbum[id_edition]."','Album','width=580,height=530,scrollbars=1')".'"',
                        "LEVSIGN" =>"A".$rowAlbum[id_tome]
                        ));
                        $t->parse("DBlock","DataBlock",true);
                    }
                }
            }
        }
    }
}
else {
    // browse par série : seulement 2 niveaux
    while ($row_RecAuteur = mysql_fetch_assoc($RecAuteur)) {
        if ($lev_id == $row_RecAuteur['id']) {
            $img = "aro_3_2.gif";
            $pagetitle.=htmlspecialchars($row_RecAuteur['name']);
        }
        else {
            $img = "aro_3_1.gif";
        }
        $t->set_var(array(
        "WSPACER"=>"0",
        "HSPACER"=>"0",
        "IMGNAVIG"=>$img,
        "URLEVEL"=>$_SERVER["PHP_SELF"]."?lev_id=".$row_RecAuteur['id'].$queryString_RecAuteur."&pageNum_RecAuteur=".$pageNum_RecAuteur.$string_filter,
        "NAMELEVEL"=>htmlspecialchars(stripslashes($row_RecAuteur['name'])),
        "ACTLEVEL"=>"",
        "LEVSIGN" =>"L1".$row_RecAuteur['id']
        ));
        $keyword.=htmlspecialchars($row_RecAuteur['name']).",";

        if (minAccessLevel(0,false))
        {
            $t->set_var("URLEDIT", sprintf($edit_lev1,$row_RecAuteur['id']));
        }

        $t->parse("DBlock","DataBlock",true);
        if ($lev_id == $row_RecAuteur['id']) {
            //echo $query_album;
            $recAlbum = mysql_query($query_album,$bdovore);
            while ($rowAlbum = mysql_fetch_assoc($recAlbum)) {
                $t->set_var(array(
                "WSPACER"=>"32",
                "HSPACER"=>"1",
                "IMGNAVIG"=>"spacer.gif",
                "URLEVEL"=>"",
                "NAMELEVEL"=>htmlspecialchars(stripslashes($rowAlbum['titre'])),
                "ACTLEVEL"=>'onclick="window.open('."'./useralbum.php?id_tome=".$rowAlbum[id_tome]."&id_edition=".$rowAlbum[id_edition]."','Album','width=580,height=530,scrollbars=1')".'"',
                "LEVSIGN" =>"A".$rowAlbum[id_tome]
                ));

                $t->parse("DBlock","DataBlock",true);
            }
        }
    }



}
$t->set_var("PAGETITLE",$pagetitle);
$t->set_var("ACTBROWSER",$_SERVER["PHP_SELF"]);
$t->set_var("URLPREVPAGE",sprintf("%s?pageNum_RecAuteur=%d%s", $_SERVER["PHP_SELF"], max(0, $pageNum_RecAuteur - 1), $queryString_RecAuteur));
$t->set_var("URLNEXTPAGE",sprintf("%s?pageNum_RecAuteur=%d%s", $_SERVER["PHP_SELF"], min($totalPages_RecAuteur, $pageNum_RecAuteur + 1), $queryString_RecAuteur));

$t->set_var(array(
"PRETCHECK" => $pret_check,
"DEDICHECK" => $dedi_check,
"CMTCHECK" => $cmt_check,
"NOTCHECK" => $not_check
));
$t->set_var (array
("LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
"URLSITEIMAGE" => BDO_URL_IMAGE,
"URLSITEFORUM" => BDO_URL_FORUM
));

$t->parse("MENUCOLL","tpMenuColl");
$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->pparse("MyFinalOutput","tpBase");
