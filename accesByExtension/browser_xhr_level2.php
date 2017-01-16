<?php


header('Content-Type: text/html; charset=ISO-8859-15');

include_once (BDO_DIR."inc/bdovore.php");



include (BDO_DIR."inc/queryfunction.php");

mysql_connect(BDO_DB_HOST, BDO_DB_USER, BDO_DB_PWD);
mysql_select_db(BDO_DB_SID);

// defintion des variables
$maxRows = 25;

$pagetitle = "BDovore.com - Bandes dessinées par ";

$pageNum = getVal('pageNum',0);
$rb_browse = getVal('rb_browse','ser');

$lev2_id = getVal('lev2_id','toto');

$lev_id = getVal('lev_id');



$startRow = $pageNum * $maxRows;


            $a_serie = explode('#',$lev2_id);
            $lev2_id = $a_serie[0];


// URL des pages d'edition
$url_edit = array(
"ALBUM"=>"./admin/adminalbums.php?alb_id=%d",
"SERIE"=>"./admin/adminseries.php?serie_id=%d",
"GENRE"=>"./admin/admingenres.php?genre_id=%d",
"COLLECTION"=>"./admin/admincollections.php?collec_id=%d",
"AUTEUR" => "./admin/adminauteurs.php?auteur_id=%d",
"EDITEUR"=> "./admin/adminediteurs.php?editeur_id=%d"
);

if ($rb_browse == 'ser') {

    $query_album = "
    select SQL_CALC_FOUND_ROWS
    id_tome id,
    CONCAT(
    CASE
        when bd_tome.flg_int = 'O'  then 'Intégrale '
        when bd_tome.flg_type = 1 then 'Coffret '
        when bd_serie.flg_fini = 2 then 'One shot'
        else IFNULL(concat('Tome ',bd_tome.num_tome),'HS')
    end, ': ', bd_tome.titre) as titre
    from
        bd_tome INNER JOIN bd_serie USING(id_serie)
    where
    bd_tome.id_serie = $lev_id
    order by num_tome, titre
    ";

    // URL album
    $act_url = ' onclick="window.open('."'./membres/album.php?id_tome=%d','Album','width=550,height=600,scrollbars=1')".';return false;"';
    $url_alb = "#";
    // URL d'edition
    $edit_lev3 ="<a href='".$url_edit["ALBUM"]."' target =_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0 /></a>";
}
elseif ($rb_browse == 'aut') {

    $query_album = "
    select SQL_CALC_FOUND_ROWS
        id_tome id,
        CONCAT(
        CASE
        when bd_tome.flg_int = 'O' then 'Intégrale '
        when bd_tome.flg_type = 1 then 'Coffret '
        when bd_serie.flg_fini = 2 then 'One shot'
        else IFNULL(concat('Tome ',bd_tome.num_tome),'HS')
        end,': ', bd_tome.titre) as titre
    from
        bd_tome
        INNER JOIN bd_serie USING(id_serie)
    where
        bd_tome.id_serie =".$lev2_id."
        and (
            bd_tome.id_dessin = ".mysql_real_escape_string($lev_id)."
            or bd_tome.id_scenar = ".mysql_real_escape_string($lev_id)."
            or bd_tome.id_color = ".mysql_real_escape_string($lev_id)."
            or bd_tome.id_scenar_alt = ".mysql_real_escape_string($lev_id)."
            or bd_tome.id_dessin_alt = ".mysql_real_escape_string($lev_id)."
            or bd_tome.id_color_alt = ".mysql_real_escape_string($lev_id)."
            )
    order by num_tome, titre
    ";

    // URL album
    $act_url = ' onclick="window.open('."'./membres/album.php?id_tome=%d','Album','width=550,height=600,scrollbars=1')".';return false;"';
    $url_alb = "#";
    // URL d'edition
    $edit_lev3 ="<a href='".$url_edit["ALBUM"]."' target =_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0 /></a>";
}
elseif ($rb_browse == 'edit'){

    $query_album = "
    select SQL_CALC_FOUND_ROWS distinct
        bd_serie.id_serie id,
        bd_serie.nom as titre
    from
    bd_edition
        INNER JOIN bd_tome USING(id_tome)
        INNER JOIN bd_serie USING(id_serie)
    where
        bd_edition.id_editeur=" . mysql_real_escape_string($lev_id) . "
        and bd_edition.id_collection=" . mysql_real_escape_string($lev2_id) . "
    order by bd_serie.nom
    ";

    // URL affichage
    $url_alb = "serie.php?id_serie=%d";
    $act_url = "";
    // URL d'edition
    $edit_lev3 = "<a href='".$url_edit["SERIE"]."' target =_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0></a>";
}
elseif ($rb_browse == 'genr') {

    $query_album = "
    select SQL_CALC_FOUND_ROWS
        distinct
        bd_serie.id_serie id,
        bd_serie.nom as titre
    from
        bd_serie
    where
        bd_serie.id_genre=" . mysql_real_escape_string($lev_id) . "
        and bd_serie.nom like '" . $lev2_id . "%'
    order by titre
    ";
    $url_alb = "serie.php?id_serie=%d";
    $act_url = "";
    // URL d'edition
    $edit_lev3 ="<a href='".$url_edit["SERIE"]."' target =_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0></a>";
}


$t = new Template(BDO_DIR."public/templates");
$t->set_file(array(
"tpBody" => "browser_xhr_level2.tpl",
));

// on déclare les blocks contenus sur la feuille
$t->set_block('tpBody','DataBlock','DBlock');


//echo_pre($query_album . " LIMIT ".mysql_real_escape_string($startRow).",".mysql_real_escape_string($maxRows));

if ($rb_browse != "ser") {
    $recAlbum = mysql_query($query_album . " LIMIT ".mysql_real_escape_string($startRow).",".mysql_real_escape_string($maxRows));

    $resCount = mysql_query('SELECT FOUND_ROWS() as nb');
    $rowCount = mysql_fetch_assoc($resCount);
    $totalRows = $rowCount['nb'];

    while ($rowAlbum = mysql_fetch_assoc($recAlbum)) {
        $t->set_var(array(
        "WSPACER"=>"1px",
        "HSPACER"=>"1px",
        "IMGNAVIG"=>"spacer.gif",
        "URLEVEL"=>sprintf($url_alb,$rowAlbum['id']),
        "NAMELEVEL"=>htmlspecialchars(stripslashes($rowAlbum['titre'])),
        "ACTLEVEL"=>sprintf($act_url,$rowAlbum['id']),
        "LEVSIGN" =>"A".$rowAlbum['id']
        ));
        if (minAccessLevel(1,false)) {
            $t->set_var("URLEDIT", sprintf( $edit_lev3,$rowAlbum['id']));
        }
        $t->parse("DBlock","DataBlock",true);
    }
}
else {
    // browse par série : seulement 2 niveaux
    // aJOUT DE LA PREmière ligne Fiche Série
    $t->set_var(array(
    "WSPACER"=>"16px",
    "HSPACER"=>"1px",
    "IMGNAVIG"=>"spacer.gif",
    "URLEVEL"=> "serie.php?id_serie=".$lev_id,
    "NAMELEVEL"=> '- Fiche Série -',
    "ACTLEVEL"=> "",
    "URLEDIT" =>"",
    "LEVSIGN" =>""
    ));
    $t->parse("DBlock","DataBlock",true);
    $recAlbum = mysql_query($query_album . " LIMIT ".mysql_real_escape_string($startRow).",".mysql_real_escape_string($maxRows));

    $resCount = mysql_query('SELECT FOUND_ROWS() as nb');
    $rowCount = mysql_fetch_assoc($resCount);
    $totalRows = $rowCount['nb'];

    while ($rowAlbum = mysql_fetch_assoc($recAlbum)) {
        $t->set_var(array(
        "WSPACER"=>"32px",
        "HSPACER"=>"1px",
        "IMGNAVIG"=>"spacer.gif",
        "URLEVEL"=>sprintf($url_alb,$rowAlbum['id']),
        "NAMELEVEL"=>htmlspecialchars(stripslashes($rowAlbum['titre'])),
        "ACTLEVEL"=>sprintf($act_url,$rowAlbum['id']),
        "LEVSIGN" =>"A".$rowAlbum['id']
        ));
        if (minAccessLevel(1,false)) {
            $t->set_var("URLEDIT", sprintf( $edit_lev3,$rowAlbum['id']));
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
    $totalrowhtml = 'lignes de '.($startRow+1).' à ';
    $totalrowhtml .= ($startRow+$maxRows > $totalRows) ? $totalRows : ($startRow+$maxRows);
    $totalrowhtml .= ' ( sur '.$totalRows.' lignes )';

    $totalPages = ceil($totalRows/$maxRows)-1;

    if ($pageNum > 0)
    {
        $url = '<a href=\'javascript:SubmitXhr("","onglet_div_xhr_1L' . $lev_id  . (($rb_browse!='ser') ? ( '_2L'.$lev2_id) : '') . '","browser_xhr_level2.php?rb_browse=' . $rb_browse . '&lev_id=' . $lev_id . '&lev2_id=' . $lev2_id . '&pageNum='.max(0, $pageNum - 1).'",false)\' >Pr&eacute;c&eacute;dent</a>';
        $t->set_var("URLPREVPAGE",$url);
    }
    else
    $t->set_var("URLPREVPAGE",'');

    if ($pageNum < $totalPages)
    {
        $url = '<a href=\'javascript:SubmitXhr("","onglet_div_xhr_1L' . $lev_id . (($rb_browse!='ser') ? ( '_2L'.$lev2_id) : '') . '","browser_xhr_level2.php?rb_browse=' . $rb_browse . '&lev_id=' . $lev_id . '&lev2_id=' . $lev2_id . '&pageNum='.min($totalPages, $pageNum + 1).'",false)\' >Suivant</a>';
        $t->set_var("URLNEXTPAGE",$url);
    }
    else
    $t->set_var("URLNEXTPAGE",'');

    $t->set_var("TOTALROW", $totalrowhtml);
}
else
{
    $t->set_var("URLPREVPAGE",'');
    $t->set_var("URLNEXTPAGE",'');
    $t->set_var("TOTALROW",'Aucune ligne de résultat !');
}


$t->set_var("PAGETITLE",$pagetitle);
$t->set_var("ACTBROWSER",$_SERVER["PHP_SELF"]."?");
$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
$t->set_var (array("LOGINBARRE" => GetIdentificationBar()));

$t->parse("BODY","tpBody");
$t->pparse("MyFinalOutput","tpBody");
