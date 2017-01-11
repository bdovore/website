<?php





$link_album = "<a href='#' onclick=".'"'."window.open('". BDO_URL."membres/album.php?id_tome=%d','Album','width=500,height=400,scrollbars=1');return false;".'"'.">";
$link_serie = "<a href='serie.php?id_serie=%d'>";
// variables générales
$nb = 30;
$first = $_GET["first"];
if ($first=='') {$first = 0;}

$cle = $_GET["cle"];
if ($cle == "")
{$cle=1;}

$sort = $_GET["sort"];
if ($sort != "DESC") {
    $sort = " ASC";
} else {
    $sort=" DESC";
}

$type = $_GET["type"];
if ($type == "" || ($type != "EDITION" && $type != "CORRECTION") ) {
    $type = "AJOUT";
}

if ($type == "EDITION") {
    $clerep[1] = "s.id_serie";
    $clerep[2] = "u.username";
    $clerep[3] = "en.prop_dte";
    $waitstatus = "en.prop_status IN (0,2,3,4)";
    $query = "
    select
        count(1) nb
    from
        bd_edition en
        INNER JOIN users u ON en.user_id = u.user_id
    where
        en.prop_status IN (0,2,3,4)
    ";
    $DB->query($query);
    $DB->next_record();
    $nb_alb = $DB->f("nb");

    $query = "
    SELECT
        en.id_edition,
        t.id_tome,
        t.num_tome,
        en.user_id,
        u.username,
        en.prop_dte,
        t.titre,
        s.nom serie,
        s.id_serie
    FROM
        bd_edition en
        INNER JOIN users u ON en.user_id = u.user_id
        INNER JOIN bd_tome t ON en.id_tome = t.id_tome
        INNER JOIN bd_serie s ON s.id_serie = t.id_serie
    WHERE
        en.prop_status IN (0,2,3,4)
    ORDER BY ".$clerep[$cle]. " ".$DB->escape($sort)."
    LIMIT ".$DB->escape($first)." , ".$DB->escape($nb);
}
else {
    $clerep[1] = "uap.serie";
    $clerep[2] = "u.username";
    $clerep[3] = "uap.prop_dte";
    $waitstatus = "(status = 0 OR status = 2 OR status = 3 OR status = 4)";
    $query = "
    select
        count(1) nb
    from
        users_alb_prop uap
        INNER JOIN users u ON uap.user_id = u.user_id
    where
        uap.status IN (0,2,3,4)
        and uap.prop_type = '".$DB->escape($type)."'
    ";
    $DB->query($query);
    $DB->next_record();
    $nb_alb = $DB->f("nb");

    $query = "
    select
        uap.serie,
        uap.id_serie,
        uap.titre,
        uap.id_tome,
        uap.prop_dte,
        u.username,
        uap.num_tome
    from
        users_alb_prop uap
        INNER JOIN users u ON uap.user_id = u.user_id
    where
        uap.status IN (0,2,3,4)
        and uap.prop_type = '".$DB->escape($type)."'
    ORDER BY ".$clerep[$cle]. " ".$DB->escape($sort)."
    LIMIT ".$DB->escape($first)." , ".$DB->escape($nb);
}

$DB->query ($query);

// Creation d'une nouvelle instance Fast Template
$t = new Template(BDO_DIR."public/templates");
// fichier à utiliser
$t->set_file(array(
"tpBody" => "listpropal.tpl",
"tpMenu" => "menu.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBase" => "body.tpl"));

// on déclare le block à utiliser
$t->set_block('tpBody','DetailBlock','DBlock');

//Liste les nouveautés par mois
while ($DB->next_record())
{

    if ($DB->f("id_serie") <> "") {
        $serie = sprintf($link_serie,$DB->f("id_serie")).stripslashes($DB->f("serie"))."</a>";

    } else {
        $serie = stripslashes($DB->f("serie"));
    }
    if ($DB->f("id_tome") <> "") {
        $titre = sprintf($link_album,$DB->f("id_tome")).stripslashes($DB->f("titre"))."</a>";

    } else {
        $titre = stripslashes($DB->f("titre"));
    }
    $t->set_var (array
    ( "SERIE" => $serie,
    "TOME" => stripslashes($DB->f ("num_tome")),
    "TITRE" => $titre,
    "DTEPAR" => $DB->f ("prop_dte"),
    "AUTEUR" => $DB->f ("username")
    ));
    //Affiche le block
    $t->parse ("DBlock", "DetailBlock",true);
}

// block pour les liens vers les autres listes
$t->set_block('tpBody','LinksBlock','LBlock');

$typeautre = array("AJOUT", "CORRECTION", "EDITION");
$typeencours = array("AJOUT" => "d'ajout",
"CORRECTION" => "de corrections",
"EDITION" => "d'ajout d'édition");
$texteautre = array("AJOUT" => "Demandes d'ajout",
"CORRECTION" => "Demandes de corrections",
"EDITION" => "Demandes d'ajout d'édition");

// titre de la liste + liens vers les autres listes
$t->set_var(array(
"TYPEENCOURS" => $typeencours[$type],
"TYPE" => $type
));
foreach($typeautre as $value) {
    if ($value <> $type) {
        $t->set_var(array(
        "TYPEAUTRE" => $value,
        "AJOUTCORRECTION" => $texteautre[$value],
        ));
        $t->parse ("LBlock", "LinksBlock",true);
    }
}

// Converti les variable generales
$t->set_var (array
("BARRENAVIGATION" => GetNavigationBar($first,$nb,$nb_alb,BDO_URL."listpropal.php?&type=".$type."&cle=".$cle."&sort=".$sort),
"DUREE" => $duree,
"ACTION" => $_SERVER['PHP_SELF'],
"NBTOTAL" => $nb_alb
));

// assigne la barre de login
$t->set_var (array
("LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
"PAGETITLE" => "Proposition en cours..."));

$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBase");
