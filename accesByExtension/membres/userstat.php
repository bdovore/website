<?php



include (BDO_DIR."inc/queryfunction.php");

minAccessLevel(2);

$t = new Template(BDO_DIR."public/templates");
$t->set_file(array(
"tpMenuColl" => "menu_coll.tpl",
"tpBody" => "userstat.tpl",
"tpMenu" => "user.menu.tpl",
"tpBase" => "body.tpl"));


// Selections des scénaristes
$query = "
SELECT
    count(distinct(t.id_tome)) as nbtome,
    a.pseudo libelle
from
    users_album ua
    INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
    INNER JOIN bd_tome t ON en.id_tome=t.id_tome
    INNER JOIN bd_auteur a ON a.id_auteur=t.id_scenar
where
    ua.user_id = " . $DB->escape($_SESSION["UserId"]) . "
    and ua.flg_achat = 'N'
group by a.pseudo
order by nbtome desc
";
$DB->query ($query);
// on déclare le block à utiliser
$t->set_block('tpBody','ScenarBlock','SBlock');
//Liste les news
while ($DB->next_record())
{
    $t->set_var (array
    ("SCENAR" => stripslashes($DB->f("libelle")),
    "NBBYSCENAR" => $DB->f("nbtome")));
    $t->parse ("SBlock", "ScenarBlock",true);
}

// Selections des dessinateurs
$query = "
SELECT
    count(distinct(t.id_tome)) as nbtome,
    a.pseudo libelle
from
    users_album ua
    INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
    INNER JOIN bd_tome t ON en.id_tome=t.id_tome
    INNER JOIN bd_auteur a ON a.id_auteur=t.id_dessin
where
    ua.user_id = " . $DB->escape($_SESSION["UserId"]) . "
    and ua.flg_achat = 'N'
group by a.pseudo
order by nbtome desc
";
$DB->query ($query);

// on déclare le block à utiliser
$t->set_block('tpBody','DessinBlock','DBlock');
//Liste les news
while ($DB->next_record())
{
    $t->set_var (array
    ("DESSIN" => stripslashes($DB->f("libelle")),
    "NBBYDESSIN" => $DB->f("nbtome")));
    $t->parse ("DBlock", "DessinBlock",true);
}
$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
$t->set_var("IDUSER",encodeUserId($_SESSION["UserId"]));


$t->set_var (
"LOGINBARRE", GetIdentificationBar());


$t->parse("MENUCOLL","tpMenuColl");
$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->pparse("MyFinalOutput","tpBase");
