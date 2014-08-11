<?php
/*
user=175047 OK
user=899 KO
*/



include_once (BDO_DIR."inc/bdovore.php");

include (BDO_DIR."inc/db_phpBB.php");


include (BDO_DIR."inc/queryfunction.php");

// Début des fonctions
function gen_img_title($id_tome, $URL, $AltTitle){
	return '<a href="#"><img src="'.$URL.'" width="100" height="130" alt="'.$AltTitle.'" onclick="window.open(\''.BDO_URL.'membres/album.php?id_tome='.$id_tome.'\',\'Album\',\'width=500,height=400,scrollbars=1\')" border = "0"></a>';
}


// Vérifie qu'un parametre a été passé
if (!isset($user)){
	if (issetNotEmpty($_SESSION["UserId"])){
		$user = encodeUserId($_SESSION["UserId"]);
	}else{
		echo GetMetaTag(3,"Erreur lors du chargement de cette page : vous allez être redirigé.",(BDO_URL."index.php"));
		exit();
	}
}
$ori_user = $user;
$user = decodeUserId($user);

if ($user <> $_SESSION["UserId"] ){
	// vérifie que l'utilisateur a autorisé la mise en ligne de sa collection
	$username = openCollection($user);
}else{
	$username = $_SESSION["UserName"];
}

$query = "select carre_type from users where user_id=" . $DB->escape($user);
$DB->query ($query);
$DB->next_record();
$carre_type = $DB->f("carre_type");

// prépare le template
$t = new Template(BDO_DIR."public/templates");
$t->set_file(array(
"tpBody" => "guest.tpl",
"tpMenu" => "menu.tpl",
"tpMenuGuest" => "menu_guest.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBase" => "body.tpl"
));

// Charge les statistisques
$query = "
select 
count(*) as countofalb, 
count(distinct t.id_serie) as countofserie
from 
	users_album u 
	INNER JOIN bd_edition en ON en.id_edition = u.id_edition 
	INNER JOIN bd_tome t ON t.id_tome = en.id_tome 
where 
	u.user_id=" . $DB->escape($user) . " 
	and u.flg_achat='N'
";

$DB->query ($query);
$DB->next_record();
$nbtomes = $DB->f("countofalb");
$nbseries = $DB->f("countofserie");

// Futurs achats
$query = "select count(*) as nbfutursachats from users_album u where user_id=" . $DB->escape($user) . " and flg_achat='O'";
$DB->query ($query);
$DB->next_record();
$nbfuturs_achats = $DB->f("nbfutursachats");

// Inégrales
$query = "
select
	count(*) as nbintegrale 
from 
	users_album u 
	INNER JOIN bd_edition en ON en.id_edition = u.id_edition 
	INNER JOIN bd_tome t ON t.id_tome = en.id_tome 
where 
	u.user_id=" . $DB->escape($user) . " 
	and t.flg_int = 'O' 
	and flg_achat='N'
";
$DB->query ($query);
$DB->next_record();
$nbintegrales = $DB->f("nbintegrale");

// coffrets
$query = "
select 
	count(*) as nbcoffret 
from 
	users_album u 
	INNER JOIN bd_edition en ON en.id_edition = u.id_edition 
	INNER JOIN bd_tome t ON t.id_tome = en.id_tome 
where 
	u.user_id=" . $DB->escape($user) . " 
	and t.flg_type = '1' 
	and flg_achat='N'
";

$DB->query ($query);
$DB->next_record();
$nbcoffrets = $DB->f("nbcoffret");

// Selections des 10 albums les mieux notés
if ($carre_type == 0){
	$query = "
	SELECT 
		uc.note, 
		t.id_tome, 
		t.titre, 
		en.img_couv 
	FROM 
		users_album ua
		INNER JOIN bd_edition en ON en.id_edition = ua.id_edition 
		INNER JOIN users_comment uc ON  uc.id_tome = en.id_tome AND uc.user_id = ua.user_id
		INNER JOIN bd_tome t ON t.id_tome = en.id_tome 
	WHERE 
		ua.user_id=" . $DB->escape($user) . " 
		and ua.flg_achat='N' 
	ORDER BY uc.note desc 
	LIMIT 0,10";
}else{
	$query = "
	select 
		ulc.rang, 
		t.id_tome, 
		t.titre, 
		en.img_couv 
	from 
		users_list_carre ulc 
		INNER JOIN bd_tome t ON t.id_tome = ulc.id_tome  
		INNER JOIN bd_edition en ON en.id_edition = t.id_edition 
	where 
		ulc.user_id=" . $DB->escape($user) . " 
	ORDER BY ulc.rang 
	limit 0,9
	";
}
$DB->query ($query);

// Rempli le tableau avec les couv
$i=1;
while ($DB->next_record()){
	$html_image = gen_img_title($DB->f("id_tome"),BDO_URL_IMAGE.'couv/'.$DB->f("img_couv"),stripslashes($DB->f("titre")));
	$t->set_var("ALB".$i, $html_image);
	$i++;
}

// Selection des 4 derniers achats
$query = "
select 
	t.titre, 
	en.img_couv, 
	t.id_tome 
from
	users_album ua 
	INNER JOIN bd_edition en ON en.id_edition = ua.id_edition 
	INNER JOIN bd_tome t ON t.id_tome = en.id_tome 
where 
	ua.user_id=" . $DB->escape($user) . "
	and ua.flg_achat='N' 
	 
order by IFNULL(ua.date_achat,ua.date_ajout) desc 
limit 0,4
";
$DB->query ($query);

// Rempli le tableau avec les couv
$i=1;
while ($DB->next_record()){
	$html_image = gen_img_title($DB->f("id_tome"),BDO_URL_IMAGE.'couv/'.$DB->f("img_couv"),stripslashes($DB->f("titre")));
	$t->set_var("LASTPURCH".$i, $html_image);
	$i++;
}

// Récupère les contributions
$query = "SELECT count(*) as nbprop FROM users_alb_prop WHERE prop_type = 'AJOUT' and user_id=" . $DB->escape($user);
$DB->query ($query);
$DB->next_record();
$user_prop_alb = $DB->f("nbprop");

$query = "SELECT count(*) as nbprop FROM users_alb_prop WHERE prop_type = 'CORRECTION' and user_id=" . $DB->escape($user);
$DB->query ($query);
$DB->next_record();
$user_prop_corr = $DB->f("nbprop");

// Renvoie les infos de l'utilisateur
$DB_php = new DB_phpBB();
$query = "SELECT user_regdate, user_posts FROM phpbb_users WHERE username = ".sqlise($username,'text');
$DB_php->query ($query);
$DB_php->next_record();
$user_regdate = $DB_php->f("user_regdate");
$user_posts = $DB_php->f("user_posts");

// envoie les variables de la page
$t->set_var(array(
"PAGETITLE" => "Bdovore.com : visitez la collection de $username",
"IDUSER" => $user,
"DTEARRIVEE" => $user_regdate,
"NBPOSTS" => $user_posts,
"NBPROPS" => $user_prop_alb,
"NBCORRECTIONS" => $user_prop_corr,
"USERNAME" => $username,
"DTEARRIVEE" => strftime('%d-%m-%Y',$user_regdate),
"NBALBUMS" => $nbtomes,
"NBINTEGRALES" => $nbintegrales,
"NBCOFFRETS" => $nbcoffrets,
"NBSERIES" => $nbseries,
"NBACHATS" => $nbfuturs_achats,
"URLBROWSER"=> BDO_URL.'guestbrowser.php?user='.$ori_user,
"USERID" => $ori_user,
"URLCOLLEC" => "www.bdovore.com/guest.php?user=".$ori_user
));

$userid = $_GET["user"];
if($userid == ""){
	$t->set_var("ADDTHIS","<img src='".BDO_URL."images/site/lg-addthis-fr.gif' width='125' height='16' alt='Partager cette page' style='border:0'/>");
}

// Envoie les info générales et publie la page
$t->set_var(array(
"LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
));

$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUGUEST","tpMenuGuest");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBase");
