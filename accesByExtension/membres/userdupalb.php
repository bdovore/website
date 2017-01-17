<?php



include (BDO_DIR."inc/queryfunction.php");

minAccessLevel(2);

// Creation d'un nouveau Template
$t = new Template(BDO_DIR."public/templates");

// Vérification s'il s'agit d'un ordre d'ajout
if ($act == "insert")
{
    //Situation d'insert
    /* modif id_scenar
    $request = "INSERT INTO users_album (user_id, id_tome,id_scenar, id_dessin,id_genre,
    id_editeur, id_collection, id_serie, date_ajout, flg_achat, id_edition )
    SELECT ".$DB->escape($_SESSION["UserId"]).", t.id_tome, t.id_scenar, t.id_dessin, s.id_genre,
    e.id_editeur, e.id_collection,t.id_serie,CURRENT_TIMESTAMP(),'N', e.id_edition
    FROM bd_tome t, bd_serie s, bd_edition e
    WHERE t.id_serie = s.id_serie AND e.id_edition = ".$DB->escape($btnDefEdit)." AND e.id_tome = t.id_tome;";
    */
    $request = "INSERT INTO users_album (user_id, date_ajout, flg_achat, id_edition )
        VALUES ( ".$DB->escape($_SESSION["UserId"]).",CURRENT_TIMESTAMP(),'N',".$DB->escape($btnDefEdit).")";

    $DB->query($request);
    // Selectionne le template de non ajout
    $t->set_file(array(
    "tpBody" => "user_dup_alb_conf.tpl"));

    // Complète les variables
    $t->set_var(array(
    "WNDTITLE" => "Confirmation"
    ));
    $t->set_var("URLSITE",BDO_URL);
    $t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
    $t->set_var("URLSITEFORUM",BDO_URL_FORUM);
    $t->pparse("Output","tpBody");

    exit();

}

$request = "
SELECT DISTINCT
    e.id_edition,
    ed.nom enom,
    c.nom cnom,
    e.dte_parution,
    e.flag_dte_parution,
    e.img_couv
FROM
    bd_edition e,
    bd_editeur ed,
    bd_collection c
WHERE
    e.id_tome = ".$DB->escape($id_tome)."
    AND e.id_edition NOT IN (
        SELECT u.id_edition
        FROM users_album u
        INNER JOIN bd_edition en ON u.id_edition=en.id_edition
        WHERE u.user_id=".$DB->escape($_SESSION["UserId"])."
        AND en.id_tome =".$DB->escape($id_tome)."
    )
    AND c.id_collection = e.id_collection
    AND c.id_editeur = ed.id_editeur
    AND e.prop_status = 1
";
$DB->query($request);

// Vérifie si l'utilisateur possède toutes les collections
if ($DB->nf() == 0) {
    // Selectionne le template de non ajout
    $t->set_file(array(
    "tpBody" => "user_nodup_alb.tpl"));

    // Complète les variables
    $t->set_var(array(
    "WNDTITLE" => "Aucune édition à ajouter"
    ));
}else{
    // Dans le cas contraire, présente le formulaire d'ajout avec les éditions disponibles
    // Selectionne le template d'ajout
    $t->set_file(array(
    "tpBody" => "user_dup_alb.tpl"));

    // Selection du block édition
    $t->set_block('tpBody','EditionBlock','EBlock');
    $ischecked = "checked";

    while ($DB->next_record())
    {
        // Determine l'URL image
        if (is_null($DB->f("img_couv")) | ($DB->f("img_couv")==''))
        {
            $url_image = BDO_URL_IMAGE."couv/default.png";
        }else{
            $url_image = BDO_URL_IMAGE."couv/".$DB->f("img_couv");
        }
        // Affiche le résultat
        $t->set_var (array
        ("EDITEUR" => stripslashes($DB->f("enom")),
        "COLLECTION" => htmlentities(stripslashes($DB->f("cnom"))),
        "DTPAR" => dateParution($DB->f("dte_parution"),$DB->f("flag_dte_parution")),
        "BTNVALUE" => $DB->f("id_edition"),
        "URLIMAGE"=> $url_image,
        "ISCHECKED" => $ischecked
        ));
        $t->parse ("EBlock", "EditionBlock",true);
        $ischecked = "";
    }

    // Remplissage des variables

    $t->set_var(array(
    "WNDTITLE" => $DB->f("titre"),
    "ACTIONNAME" => "Ajouter",
    "URLACTION" => BDO_URL."membres/userdupalb.php?act=insert"
    ));

}
$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
$t->set_var("URLSITEFORUM",BDO_URL_FORUM);

$t->pparse("Output","tpBody");
