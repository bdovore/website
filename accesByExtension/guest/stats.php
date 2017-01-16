<?php


include_once (BDO_DIR."inc/bdovore.php");



include (BDO_DIR."inc/queryfunction.php");


// V�rifie qu'un parametre a �t� pass�
if (!isset($user)){
    if (issetNotEmpty($_SESSION["UserId"])) {
        $user = encodeUserId($_SESSION["UserId"]);
    }else {
        echo GetMetaTag(3,"Erreur lors du chargement de cette page : vous allez �tre redirig�.",(BDO_URL."index.php"));
        exit();
    }
}


$ori_user = $user;
$user = decodeUserId($user);

if ($user <> $_SESSION["UserId"] ) {
    // verifie que l'utilisateur a autorise la mise en ligne de sa collection
    $username = openCollection($user);
}else {
    $username = $_SESSION["UserName"];
}

// pr�pare le template
$t = new Template(BDO_DIR."public/templates");
$t->set_file(array(
"tpBody" => "guest_stats.tpl",
"tpMenu" => "menu.tpl",
"tpMenuGuest" => "menu_guest.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBase" => "body.tpl"
));

// envoie les variables de la page
$t->set_var (array(
"PAGETITLE" => "Bdovore.com : visitez la collection de $username",
"USERNAME" => $username,
"USERID" => $ori_user
));

// Envoie les info g�n�rales et publie la page
$t->set_var (array(
"LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
));

$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUGUEST","tpMenuGuest");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBase");
