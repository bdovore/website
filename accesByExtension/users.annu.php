<?php



include (BDO_DIR."inc/queryfunction.php");

$lim = 50;
$clerep[1] = "username";

$select = "select count(*) as nbrusers from users u ";

$pseudo = (isset($_POST["pseudo"])) ? $_POST["pseudo"] : $_GET["pseudo"];

if ($pseudo) {
	if ( CheckChars($pseudo) ) {
		if (get_magic_quotes_gpc ()) {
			$pseudo = stripslashes($pseudo);
		}
		$pseudo = mysql_real_escape_string($pseudo); //no sql injection
	} else {
		unset($pseudo);
	}
}

$cle = 1;//$_GET["cle"];
$sort = $_GET["sort"];
$page = $_GET["page"];

//pseudo has been cleaned
if ($pseudo) {
	$select .= "where upper(u.username) like upper('".$DB->escape($pseudo)."%') ";
}

$DB->query($select);
$DB->next_record();
$nbrusers = $DB->f("nbrusers");
$nb_page = $nbrusers/$lim + 1;

$DB->free();

$select = "select u.username, u.user_id, u.open_collec from users u ";

// cleaned above
if ($pseudo) {
	$select .= "where upper(u.username) like upper('".$DB->escape($pseudo)."%') ";
}

$select .= "group by u.user_id, username ";
if (!$cle) {
	$cle = 1;
	$sort = "ASC";
}
if (strcasecmp($sort,"ASC") && strcasecmp($sort,"DESC")) {
	$sort = "ASC";
}
$select .= "order by ".$clerep[$cle]." ".$DB->escape($sort)." ";
if (!$page) {
	$page = 1;
}
$select .= "limit ".(($page - 1 )*$lim).", ".$DB->escape($lim);

$DB->query($select);

// Creation d'une nouvelle instance Fast Template
$t = new Template(BDO_DIR."public/templates");

// fichier à utiliser
$t->set_file(array(
"tpBody" => "annuaire.tpl",
"tpMenu" => "menu.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBase" => "body.tpl")
);
$t->set_block('tpBody','UsersBlock','UBlock');

while($DB->next_record()) {
	$users_table[$DB->f("user_id")] = array("username" => $DB->f("username"), "open_collec" => $DB->f("open_collec"));
}
$DB->free();

if (!empty($users_table)){
	foreach ($users_table as $user_id => $user) {
		if ($user["open_collec"] == 'Y') {
			$username = '<a href="guest.php?user='.encodeUserId($user_id).'">'.$user["username"]."</a>";
		} else {
			$username = $user["username"];
		}

		$t->set_var(array("USERNAME" => $username));
		$t->parse("UBlock", "UsersBlock",true);
	}
}

if($pseudo) {
	$t->set_var("PSEUDO","&pseudo=" .$pseudo);
}

$nav = "";

for ($i = 1; $i < $nb_page; $i++) {
	$nav.= '<a href="users.annu.php?cle='.$cle.'&sort='.$sort.'&page='.$i.'&pseudo='.$pseudo.'">';
	if ($i == $page) {
		$nav.= "<strong>$i</strong></a> ";
	}
	else {
		$nav.= "$i</a> ";
	}
}
$t->set_var("NAVBLOCK",$nav);

// assigne la barre de login
$t->set_var (array(
"LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
"PAGETITLE" => "L'annuaire des collectionneurs")
);
$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBase");
