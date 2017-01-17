<?php


include_once (BDO_DIR."inc/bdovore.php");



include (BDO_DIR."inc/queryfunction.php");


// Vérifie qu'un parametre a été passé
if (!isset($user)){
    if (issetNotEmpty($_SESSION["UserId"])) {
        $user = encodeUserId($_SESSION["UserId"]);
    }else {
        echo GetMetaTag(3,"Erreur lors du chargement de cette page : vous allez être redirigé.",(BDO_URL."index.php"));
        exit();
    }
}

$ori_user = $user;
$user_id = decodeUserId($ori_user);

if ($user_id <> $_SESSION["UserId"] ) {
    // verifie que l'utilisateur a autorise la mise en ligne de sa collection
    $username = openCollection($user_id);
}else {
    $username = $_SESSION["UserName"];
}


// variables générales
$nb = 10;
if ($first=='') {$first = 0;}

// Compte les achats
$query = "SELECT count(*) as fut_achat FROM users_album WHERE user_id=" . $DB->escape($user_id) . " AND flg_achat='O'";
$DB->query ($query);
$DB->next_record();
$num_fa = $DB->f("fut_achat");

if ($num_fa == 0) $message_err = "Pas d'achats prévus";

// Récupère les 20 derniers achats
$query = "
SELECT
    t.id_tome,
    t.titre,
    CASE flg_int
    when 'O'
    then 'Intégrale'
    else (
        CASE t.flg_type
        when 1
        then 'Coffret'
        else t.num_tome
        end
    ) end num_tome,
    s.id_serie,
    s.nom nom_serie,
    u.comment,
    scen.pseudo pseudo_scen,
    dess.pseudo pseudo_dess,
    e.nom nom_editeur,
    col.nom nom_collec,
    g.libelle genre,
    ed.img_couv,
    ed.isbn
FROM
    users_album u
    LEFT JOIN bd_edition ed ON u.id_edition = ed.ID_EDITION
    LEFT JOIN bd_tome t ON ed.id_tome = t.ID_TOME
    LEFT JOIN bd_auteur scen ON t.ID_SCENAR = scen.ID_AUTEUR
    LEFT JOIN bd_auteur dess ON t.ID_DESSIN = dess.ID_AUTEUR
    LEFT JOIN bd_editeur e ON ed.id_editeur = e.ID_EDITEUR
    LEFT JOIN bd_collection col ON ed.id_collection = col.ID_COLLECTION
    LEFT JOIN bd_serie s ON t.id_serie = s.ID_SERIE
    LEFT JOIN bd_genre g ON s.id_genre = g.ID_GENRE
WHERE
    u.user_id=" . $DB->escape($user_id) . "
    AND u.flg_achat='O'
ORDER BY s.nom, t.num_tome, t.flg_type
LIMIT ".$DB->escape($first).", ".$DB->escape($nb);

$DB->query ($query);
// prépare le template
$t = new Template(BDO_DIR."public/templates");
$t->set_file(array(
"tpBody" => "guest_futurs_achats.tpl",
"tpMenu" => "menu.tpl",
"tpMenuGuest" => "menu_guest.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBase" => "body.tpl"));

// on déclare le block à utiliser
$t->set_block('tpBody','AlbBlock','ABlock');

//Liste les futurs achats
while ($DB->next_record())
{
    // Affichage du tome
    if ($DB->f("num_tome") == '')
    {
        $tome = "One-Shot";
    }else{
        $tome = $DB->f("num_tome");
    }

    // Détermine si la collection est disponible
    if ($DB->f("nom_collec") == '<N/A>')
    {
        $combi = $DB->f("nom_editeur");
    }else{
        $combi = $DB->f("nom_editeur")." - ".$DB->f("nom_collec");
    }

    // Genère la remarque
    if ($DB->f("comment") != '')
    {
        $comment = nl2br(stripslashes($DB->f("comment")));
    }
    else {
        $comment = "Aucune";
    }
    if ($DB->f("isbn")) {
        $amazon = BDO_PROTOCOL."://www.amazon.fr/exec/obidos/ASIN/".$DB->f("isbn")."/bdovorecom-21/";
    }
    else {
        $amazon = BDO_PROTOCOL."://www.amazon.fr/exec/obidos/external-search?tag=bdovorecom-21&keyword=".htmlspecialchars(stripslashes($DB->f("serie")))."%20".htmlspecialchars(stripslashes($DB->f("titre")))."&mode=books-fr";

    }
    $t->set_var (array
    (
    "SERIE" => stripslashes($DB->f("nom_serie")),
    "ALBTITLE" => stripslashes($DB->f("titre")),
    "NUMTOME" => $tome,
    "ALBGENRE" => $DB->f("genre"),
    "ALBSCENAR" => $DB->f("pseudo_scen"),
    "ALBDESSIN" => $DB->f("pseudo_dess"),
    "ALBEDITEUR" => $combi,
    "COUVALBUM" => BDO_URL_IMAGE."couv/".$DB->f("img_couv"),
    "IDTOME" => $DB->f("id_tome"),
    "URLSERIE" => BDO_URL."serie.php?id_serie=".$DB->f("id_serie"),
    "REMARQUE" => $comment,
    "URLAMAZON" => $amazon
    ));

    $t->parse ("ABlock", "AlbBlock",true);
}

// envoie les variables de la page
$t->set_var (array
(
"PAGETITLE" => "Bdovore.com : visitez la collection de $username",
"PASDALBUM" => $message_err,
"BARRENAVIGATION" => GetNavigationBar($first,$nb,$num_fa,BDO_URL."guest/futurs_achats?user=".$ori_user),
"USERNAME" => $username,
"USERID" => $ori_user
));

// Envoie les info générales et publie la page
$t->set_var (array
("LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,));

$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUGUEST","tpMenuGuest");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBase");
