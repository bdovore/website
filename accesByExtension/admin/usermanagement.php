<?php



minAccessLevel(0);

// MODIFICATION D'UN PROFIL

if ($act=="viewprofil")
{//Edite le profil d'un utilisateur
}

// MODIFICATION D'UN STATUS

elseif ($act=="viewstatus")
{
	if ($status == "ok")
	{//modification physique de la base

		$newstatus = $_POST["cmblevel"];
		$DB->query ("UPDATE users SET level=".$DB->escape($newstatus)." WHERE user_id=".$DB->escape($userid));
		$sendemail = $_POST["cmbemail"];
		if ($sendemail == 1)
		{// Envoie un email
			echo "email envoyé";
		}
		echo GetMetaTag(2,"Mise à jour effectuée",(BDO_URL."admin/usermanagement.php"));;
		exit();
	}


	$DB->query ("SELECT username, level FROM users WHERE user_id=".$DB->escape($iduser));
	$DB->next_record();

	$currentstatus = $DB->f ("level");
	// Vérifie si un utilisateur n'essaie pas de modifier un utilisateur plus haut placé
	if ($currentstatus <= $_SESSION["UserLevel"])
	{
		exit("Vous n'êtes pas autorisé à effectuer cette opération");
	}
	$username = $DB->f ("username");
	$userstatus = $user_status[$currentstatus];

	// Determine les choix possibles pour le modérateur
	$optionvalue[0][0]=99;
	$optionvalue[0][1]="D&eacute;sactiv&eacute;";
	$optionvalue[1][0]=2;
	$optionvalue[1][1]="Membre";

	if (minAccessLevel(0,false))
	{
		$optionvalue[2][0]=1;
		$optionvalue[2][1]="Mod&eacute;rateur";
	}
	$options = GetOptionValue($optionvalue,$currentstatus);

	{// Affichage du formulaire
		// Creation d'un nouveau Template
		$t = new Template(BDO_DIR."public/templates");
		// fichier à utiliser
		$t->set_file(array(
		"tpBody" => "userchgstatus.tpl",
		"tpBase" => "body.tpl"));

		$t->set_var (array
		("UTILISATEUR" => $username,
		"CURRENTSTATUS" => $userstatus,
		"USERID" => $iduser,
		"OPTIONS" => $options));

		// assigne la barre de login
		$t->set_var (array
		("LOGINBARRE" => GetIdentificationBar(),
		"MENUBARRE" => admin_menu(),
		"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,));
		$t->parse("BODY","tpBody");
		$t->pparse("MyFinalOutput","tpBase");
		exit();
	}
}

// EFFACE UN UTILISATEUR
elseif($act=="delete")
{
	if ($conf == "ok")
	{
		$query = "DELETE FROM users WHERE user_id = ".$DB->escape($userid)." LIMIT 1";
		$DB->query ($query);
		$redirection = BDO_URL."admin/usermanagement.php";
		echo '<META http-equiv="refresh" content="2; URL='.$redirection.'">L\'utilisateur a été effacé de la base.';
		exit();
	}
	else
	{// Affiche la demande de confirmation
		echo 'Etes-vous s&ucirc;r de vouloir effacer l\'utilisateur n. '.$iduser.' ? <a href="../admin/usermanagement.php?act=delete&conf=ok&userid='.$iduser.'">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
		exit();
	}

}


// LISTE LES UTILISATEURS
elseif ($act=="")
{
	if ($cle == "")
	$cle=1;
	if ($sort == "DESC")
	{
		$sort = " DESC";
	}else
	{
		$sort="";
	}

	// Selection des utilisateurs à afficher
	$clerep[1] = "user_id";
	$clerep[2] = "username";
	$clerep[3] = "nom";
	$clerep[4] = "level";
	$clerep[5] = "nb_connect";
	$clerep[6] = "last_connect";
	$orderby = $clerep[$cle];

	$query = "select count(*) nb from users";
	$DB->query ($query);
	$DB->next_record();
	$nb_user = $DB->f("nb");
	$limPage = 50;
	$totalPage = ceil($nb_user/ $limPage) + 1;
	$query = "SELECT user_id, username, level, nb_connect, last_connect FROM users ORDER BY ".$orderby.$sort;
	if (!$page) {
		$page = 1;

	}
	$debut = (($page - 1)*$limPage);
	$query.= " limit ".$debut.", ".$limPage;

	$DB->query ($query);

	// Creation d'une nouvelle instance Fast Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "usermanagement.tpl",
	"tpBase" => "body.tpl"));
	// on déclare le block à utiliser
	$t->set_block('tpBody','UsersBlock','UBlock');

	//Liste les users
	while ($DB->next_record())
	{
		$user_id = $DB->f ("user_id");
		$userlevel = $DB->f ("level");
		$userstatus = $user_status[$userlevel];

		$nbconnect = $DB->f ("nb_connect");
		$lastconnect = $DB->f ("last_connect");

		if ($_SESSION["UserLevel"] < $userlevel)
		{
			$username = '<a href="'.BDO_URL.'membres/profil.php?user_id='.$user_id.'">'.$DB->f ("username").'</a>';
			$userstatus = '<a href="'.BDO_URL.'admin/usermanagement.php?act=viewstatus&iduser='.$user_id.'">'.$user_status[$userlevel].'</a>';
		}
		else
		{
			$username = $DB->f ("username");
			$userstatus = $user_status[$userlevel];
		}
		if (minAccessLevel(0,false))
		{$urldelete = './usermanagement.php?act=delete&iduser='.$user_id;
		}else{$urldelete = "";}

		$t->set_var (array
		("ID" => $user_id,
		"USERNAME" => $username,
		"STATUS" => $userstatus,
		"NBVISITES"=> $nbconnect,
		"LASTVISIT"=> $lastconnect,
		"URLDELETE" => $urldelete));

		$t->parse ("UBlock", "UsersBlock",true);
	}
	$nav = "";
	for ($i=1;$i < $totalPage;$i++) {
		$nav.= "<a href='".$_SERVER["PHP_SELF"]."?page=$i&id_serie=$id_serie'>";
		if ($i == $page) {
			$nav.="<strong>$i</strong></a>&nbsp;";
		}
		else {
			$nav.= "$i</a>&nbsp;";
		}
	}
	$t->set_var("NAVBLOCK",$nav);
	// assigne la barre de login
	$t->set_var (array
	("LOGINBARRE" => GetIdentificationBar(),
	"MENUBARRE" => admin_menu(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,));
	$t->parse("BODY","tpBody");
	$t->pparse("MyFinalOutput","tpBase");
}
