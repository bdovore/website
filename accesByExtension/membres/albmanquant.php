<?php



minAccessLevel(2);

// Emplacement des fonctions
function cv_date_bd($date) {
    $mois = substr(month_to_text((int)substr($date,5,2)),0,3).".";
    $annee =substr($date,0,4);
    return $mois." ".$annee;
}
// Début du code

$act = $_GET["act"];

if ($act=="excserie") {

    // on récupère l'id_serie
    $id_serie = intval($_GET["id"]);
    $prev = intval($_GET["prev"]);

    // on efface les anciennes références à la série
    $query = "DELETE FROM users_exclusions WHERE user_id = ".$DB->escape($_SESSION["UserId"])." AND id_serie = ".$DB->escape($id_serie);
    $DB->query($query);

    // on crée la nouvelle exclusion
    $query = "
    INSERT INTO users_exclusions (
    `user_id` ,`id_tome` ,`id_serie`
    ) VALUES (
    '".$DB->escape($_SESSION["UserId"])."', '0', '".$DB->escape($id_serie)."');";

    $DB->query($query);
    header("Location: ".$_SERVER['PHP_SELF']."?id_serie=".$prev);
    exit;
}

if ($act=="excalb") {

    // on récupère l'id_serie
    $id_serie = intval($_GET["id"]);
    $prev = intval($_GET["prev"]);
    //$sel_tome = $_GET["sel_tome"];

    // on insère les tomes cochés dans la table users_exclusion
    $sel_tome = $_POST["sel_tome"];
    if (count($sel_tome)>0){
        foreach ($sel_tome as $id_tome) {
            $query = "
            INSERT IGNORE INTO users_exclusions (
            `user_id` ,`id_tome` ,`id_serie`
            ) VALUES (
            '".$DB->escape($_SESSION["UserId"])."', '".$DB->escape($id_tome)."', '".$DB->escape($id_serie)."');";

            $DB->query($query);
        }
    }
    header("Location: ".$_SERVER['PHP_SELF']."?id_serie=".$prev);
    exit;
}

if ($act == "razserie") {

    // on récupère l'id_serie
    $id_serie = intval($_GET["id"]);
    $query = "DELETE FROM users_exclusions WHERE user_id = ".$DB->escape($_SESSION["UserId"])." AND id_serie = ".$DB->escape($id_serie);
    $DB->query($query);

    header("Location: ".$_SERVER['PHP_SELF']."?id_serie=".$id_serie);
    exit;
}
// annulation de l'exclusion pour une serie (tout albums)
if (isset($_POST['actAnnExclu']) and isset($_POST['id_serie'])) {

    // on récupère l'id_serie
    $id_serie = intval($_POST['id_serie']);
    $query = "DELETE FROM users_exclusions WHERE user_id = ".$DB->escape($_SESSION["UserId"])." AND id_serie = ".$DB->escape($id_serie);
    $DB->query($query);

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// variables générales

$clerep[1] = "t.titre";
$clerep[2] = "t.num_tome";
$clerep[3] = "en.dte_parution";

$cle = ( isset($_GET['cle']) ) ? intval($_GET['cle']) : 2;
if ($cle < 1 || $cle > 3) $cle = 2;
$sort = ( !strcasecmp($_GET['sort'],"DESC") ) ? "DESC" : "ASC";

// On rempli la liste déroulante avec les séries pour lesquelles il manque des albums


$query = "
SELECT DISTINCT
    user_serie.id_serie,
    user_serie.nom
FROM
    (
        SELECT DISTINCT
            s.id_serie,
            s.nom
        FROM
            users_album ua
            INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
            INNER JOIN bd_tome t ON t.id_tome = en.id_tome
            INNER JOIN bd_serie s ON t.ID_SERIE=s.ID_SERIE
        WHERE
            ua.user_id = ".$DB->escape($_SESSION["UserId"])."
            AND NOT EXISTS (
                        SELECT NULL FROM users_exclusions ues
                        WHERE s.id_serie=ues.id_serie
                        AND ues.id_tome = 0
                        AND ues.user_id = ".$DB->escape($_SESSION["UserId"])."
                    )
        ) user_serie
    INNER JOIN bd_tome t ON t.ID_SERIE=user_serie.ID_SERIE
    INNER JOIN bd_edition en ON t.ID_EDITION=en.ID_EDITION
WHERE
        NOT EXISTS (
            SELECT NULL
            FROM users_album ua
            INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
            WHERE
            ua.user_id = ".$DB->escape($_SESSION["UserId"])."
            AND t.id_tome=en.id_tome
        )
        AND NOT EXISTS (
            SELECT NULL
            FROM users_exclusions uet
            WHERE uet.user_id = ".$DB->escape($_SESSION["UserId"])."
            AND t.id_tome=uet.id_tome
        )
ORDER BY user_serie.nom
";

$DB->query($query);
// création de la liste
if (isset($_POST["lstSerie"])) {
    $id_serie = $_POST["lstSerie"];
}
elseif (isset($_GET["id_serie"] )){
    $id_serie = $_GET["id_serie"];
}
else {
    $id_serie = 0;
}
$i = 0;
$prev = 0;
while ($DB->next_record()) {
    $option.= '<option value="'.$DB->f("id_serie").'"';
    if ($flg_next) {
        $prev = $DB->f("id_serie");
        $flg_next = false;
    }
    if ($i == $id_serie || $id_serie == $DB->f("id_serie")) {
        $option .= " selected ";
        $id_serie = $DB->f("id_serie");
        $flg_next = true;
    }
    $option .= "> ".$DB->f("nom")."</option>";
}

// récuperation des séries avec exclusions
$query = "
SELECT bd_serie.nom, bd_serie.id_serie
FROM users_exclusions INNER JOIN bd_serie USING(id_serie)
WHERE users_exclusions.user_id =".$DB->escape($_SESSION["UserId"])."
GROUP BY bd_serie.id_serie
ORDER BY bd_serie.nom
";

$DB->query($query);
$optionExclu = '';
while ($DB->next_record()) {
    $optionExclu.= '<option value="'.$DB->f("id_serie").'"> '.$DB->f("nom")."</option>\n";
}


$query = "
SELECT
    t.id_tome,
    t.titre,
    t.num_tome,
    en.dte_parution
FROM
    bd_tome t
    INNER JOIN bd_edition en ON t.ID_EDITION=en.ID_EDITION
WHERE
    t.id_serie = '".$DB->escape($id_serie)."'
    AND
    NOT EXISTS (
        SELECT NULL
        FROM users_album ua
        INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
        WHERE
        ua.user_id = ".$DB->escape($_SESSION["UserId"])."
        AND t.id_tome=en.id_tome
    )
    AND NOT EXISTS (
        SELECT NULL
        FROM users_exclusions uet
        WHERE uet.user_id = ".$DB->escape($_SESSION["UserId"])."
        AND t.id_tome=uet.id_tome
    )
ORDER BY ".$clerep[$cle]. " ".$DB->escape($sort);

$DB->query ($query);


// Creation d'une nouvelle instance Fast Template
$t = new Template(BDO_DIR."public/templates");
// fichier à utiliser
$t->set_file(array(
"tpBody" => "user_albummanquant_lm.tpl",
"tpMenu" => "user.menu.tpl",
"tpMenuColl" => "menu_coll.tpl",
"tpBase" => "body.tpl"));
$t->set_var(array(
"OPTSOURCE" => $option,
"OPTSOURCEEXCLU" => $optionExclu
));
// on déclare le block à utiliser
$t->set_block('tpBody','DetailBlock','DBlock');


//Liste les nouveautés par mois
while ($DB->next_record())
{

    $t->set_var (array
    ( "TOME" => stripslashes($DB->f ("num_tome")),
    "TITRE" => stripslashes($DB->f ("titre")),
    "DTEPAR" => dateParution($DB->f ("dte_parution")),
    "IDTOME" => $DB->f ("id_tome"),
    "URLTITRE" => getAlbumUrl($DB->f("id_tome")),
    "SERID" => $id_serie
    ));
    //Affiche le block
    $t->parse ("DBlock", "DetailBlock",true);
}

// Converti les variable generales
$t->set_var (array
("URLEXCLUSERIE" => $_SERVER['PHP_SELF']."?act=excserie&id=".$id_serie."&prev=".$prev,
"ACTION1" => $_SERVER['PHP_SELF'],
"ACTIONEXCLU" => $_SERVER['PHP_SELF'],
"ACTION2" => $_SERVER['PHP_SELF']."?act=excalb&id=".$id_serie."&prev=".$prev,
"RAZSERIE" => $_SERVER['PHP_SELF']."?act=razserie&id=".$id_serie
));


// assigne la barre de login
$t->set_var (array
("LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
"PAGETITLE" => "Séries à compléter"));
$t->parse("BODY","tpBody");
$t->parse("MENUCOLL","tpMenuColl");
$t->parse("MENUBARRE","tpMenu");
$t->pparse("MyFinalOutput","tpBase");
