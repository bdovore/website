<?php



include (BDO_DIR."inc/queryfunction.php");

if (isset($_SESSION["UserId"]))
{
    $DB->query("select rowserie from users where user_id =".$_SESSION["UserId"]);
    $DB->next_record();
    $limPage = $DB->f("rowserie");
}else{
    $limPage = 5;
}


// **************** nombre d'album à afficher par page : 5

// **********************************************

// Creation d'un nouveau Template
$t = new Template(BDO_DIR."public/templates");
// fichier à utiliser
$t->set_file(array(
"tpBody" => "strip.tpl",
"tpMenu" => "menu.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBase" => "body.tpl"));

$t->set_block('tpBody','AlbBlock','ABlock');
$query = "select count(*) nb from edito where typ = 'STRIP'";
$DB->query($query);
$DB->next_record();
$nb_strip = $DB->f("nb");
if (!$page) {
    $page = 1;
}

$query = "select ed_text from edito where typ = 'STRIP' order by dte_maj ";
$query .= "limit ".(($page - 1)*2).", 2";
$DB->query($query);

$t->set_block('tpBody','ImgBlock','IBlock');
while ($DB->next_record()) {
    $t->set_var("IMGSTRIP",$DB->f("ed_text"));
    $t->parse("IBlock", "ImgBlock",true);
}


$totalPage = ceil($nb_strip/ 2) + 1;
$nav = "";
for ($i=1;$i < $totalPage;$i++) {
    $nav.= "<a href='".$_SERVER["PHP_SELF"]."?page=$i'>";
    if ($i == $page) {
        $nav.="<strong>$i</strong></a>&nbsp;";
    }
    else {
        $nav.= "$i</a>&nbsp;";
    }
}
$t->set_var("NAVPAGE",$nav);


// assigne la barre de login
$t->set_var (array
("LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
"URLSITEIMAGE" => BDO_URL_IMAGE,
"URLSITEFORUM" => BDO_URL_FORUM,
"PAGETITLE" =>"Les Aventures des Bdovore, par Feyd, Latruffe et Tomlameche",
"PAGEKEYWORD" => $keyword));
$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBase");
