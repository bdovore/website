<?php



include (BDO_DIR."inc/queryfunction.php");

minAccessLevel(2);

$t = new Template(BDO_DIR."public/templates");
$t->set_file(array(
"tpBody" => "addalbum.tpl",
"tpMenu" => "menu.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBase" => "body.tpl"));


$nb_add = 0;
$nb_rejet = 0;
$nb_up = 0;
if ($cb_achat) {
    $achat= 'O';
}
else {
    $achat = 'N';
}
if ($sel_alb) {
    $select_verif = "
    select
        ua.flg_achat t
    from
        users_album ua
        INNER JOIN bd_edition en ON en.id_edition = ua.id_edition
    where
        ua.user_id=".$DB->escape($_SESSION["UserId"])." and en.id_tome = %d";
/* modif
    $insert_new = "
    insert into users_album (user_id, id_tome,id_scenar, id_dessin,id_genre,
     id_editeur, id_collection,id_serie, date_ajout,flg_achat, id_edition )
     select
         ".$DB->escape($_SESSION["UserId"]).",
         t.id_tome,
         t.id_scenar,
         t.id_dessin,
         s.id_genre,
         c.id_editeur,
         c.id_collection,
         s.id_serie,
         CURRENT_TIMESTAMP(),
         '".$DB->escape($achat)."',
         en.id_edition
     from
        bd_tome t
        INNER JOIN bd_edition en ON t.id_edition = en.id_edition
        INNER JOIN bd_serie s ON t.id_serie = s.id_serie
        INNER JOIN bd_collection c ON en.id_collection = c.id_collection
     where
         t.id_tome =%d
    ";
*/
    $insert_new = "
    insert into users_album (user_id, date_ajout, flg_achat, id_edition )
     select
         ".$DB->escape($_SESSION["UserId"]).",
         CURRENT_TIMESTAMP(),
         '".$DB->escape($achat)."',
         en.id_edition
     from
        bd_tome t
        INNER JOIN bd_edition en ON t.id_edition = en.id_edition
     where
         t.id_tome =%d
    ";

    $update_new = "
    update
        users_album ua
        INNER JOIN bd_edition en ON en.id_edition = ua.id_edition
    set
        ua.flg_achat = 'N',
        ua.date_achat = CURRENT_TIMESTAMP()
    where
        en.id_tome=%d
        and ua.user_id=".$DB->escape($_SESSION["UserId"]);

    foreach ($sel_alb as $id_tome)
    {
        $cur_select = sprintf($select_verif, $DB->escape($id_tome));
        $DB->query($cur_select);
        if ($DB->num_rows() > 0) {
            $DB->next_record();
            if ($DB->f("t") == "O" && $achat == "N") {
                $cur_select = sprintf($update_new, $DB->escape($id_tome));
                $DB->query($cur_select);
                $nb_up ++;
            }
            else {
                // cas où l'album est déjà dans la collection
                $nb_rejet ++;
            }
        }
        else {
            $cur_select = sprintf($insert_new, $DB->escape($id_tome));
            $DB->query($cur_select);
            $nb_add ++;
        }
    }

    if ($nb_add > 0 ) {
        $mes = "Vous avez ajouté ".$nb_add." album(s) dans votre collection !";
    }
    else  {
        $mes = "Aucun album ajouté !";
    }
    if ($nb_rejet > 0 ) {
        $mes.=" <br />".$nb_rejet." déjà présent(s) dans votre collection !";
    }
    if ($nb_up > 0){
        $mes .="<br />".$nb_up." futur(s) achat(s) mis à jour !";
    }

}
else {
    $mes = "Aucun album sélectionné !";

}
$t->set_var("PAGETITLE", "BDOVORE.com : ajouter des albums");
// ajout de la query string dans le lien vers la fenêtre de recherche
if ($src == "serie") {
    $query_string = "../serie.php?id_serie=$id_serie&page=$page";
}
else {
    $query_string = "search.php?str_search=$str_search&pageNum=$numPage&totalRows=$numRow&$modeSearch=1";
}
$t->set_var("QUERYSTRING",$query_string);
$t->set_var ("LOGINBARRE", GetIdentificationBar());
$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
$t->set_var("URLSITEFORUM",BDO_URL_FORUM);
$t->set_var("MESSAGE",$mes);
$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBase");
