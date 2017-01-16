<?php




// Par d�faut, cette page ferme la session en cours et consid�re l'utilisateur comme public
if (isset($_SESSION["UserName"]))
{
	session_destroy();
}


//Password Oubli�
if ($act=="forgotpass")
{
	if ($email=="ok")
	{//initialise la proc�dure de renvoie
		$user_username = $_POST["txtusername"];
		$user_email = $_POST["txtemail"];

		$query = "SELECT user_id FROM users WHERE username = '".$DB->escape($user_username)."' AND email='".$DB->escape($user_email)."'";
		$DB->query($query);
		//Verifie qu'un nom a �t� retourn� par la query
		if ($DB->nf() != 1)
		{
			echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">L\'utilisateur n\'existe pas ou l\'adresse e-mail est erron�e.';
			exit();
		}

		//g�n�re un nouveau mot de passe et l'envoie � l'utilisateur
		$DB->next_record();
		$current_user = $DB->f("user_id");
		$newpassword = passgen(8);
		$query = "UPDATE users SET password = '".md5($newpassword)."' WHERE user_id = ".$DB->escape($current_user);
		$DB->query($query);

		//Pr�pare l'email � envoyer
		$textemail = "Bonjour,\n\n";
		$textemail .= "Suite � votre demande, votre mot de passe pour acc�der � www.bdovore.com a �t� chang�.\n";
		$textemail .= "Votre nouveau mot de passe est :\n\n";
		$textemail .= "$newpassword\n\n";
		$textemail .= "N'oubliez pas de changer votre mot de passe dans votre profil lors de votre prochain login.\n";
		$textemail .= "Amicalement\n";


		mail($user_email,"Votre nouveau mot de passe",$textemail);

		echo GetMetaTag(3,"Votre nouveau mot de passe a �t� envoy�",(BDO_URL."index.php"));
		exit();
	}
	else
	{//Affiche le formulaire de saisie
		// Creation d'un nouveau template
		$t = new Template(BDO_DIR."public/templates");
		// fichier � utiliser
		$t->set_file(array(
		"tpBody" => "forgotpass.tpl",
		"tpMenu" => "user.menu.tpl",
		"tpBase" => "body.tpl"));

		// assigne la barre de login
		$t->set_var (array
		("LOGINBARRE" => GetIdentificationBar(),
		"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,));
		$t->parse("BODY","tpBody");
		$t->parse("MENUBARRE","tpMenu");
		$t->pparse("MyFinalOutput","tpBase");
	}
}
//affiche le formulaire de login
elseif ($act=="")
{
	$log = $_GET["log"];
	// Verification que le login a �t� rempli
	if ($log == 1)
	{
		$user_login = $_POST["txtlogin"];
		$user_password = $_POST["txtmot2pass"];
		$user_askcookie = $_POST["chkvisit"];

		if ($user_login == "") {
			header("Location:".BDO_URL."membres/login.php?errornum=1");
			exit ();
		}

		// Verification que le login existe
		$query = "SELECT user_id FROM users WHERE username ='".$DB->escape($user_login)."' AND level <98;";
		$DB->query($query);
		if ($DB->nf() != 1) {
			header("Location:".BDO_URL."membres/login.php?errornum=1");
			exit ();
		}
		//v�rifie que le password est OK
		$query = "SELECT user_id,level FROM users WHERE username ='".$DB->escape($user_login)."' AND password='".md5($user_password)."' AND level <98;";
		$DB->query($query);
		if ($DB->nf() != 1) {
			header("Location:".BDO_URL."membres/login.php?errornum=2");
			exit ();
		}

		$DB->next_record();

		$_SESSION["UserLevel"] = $DB->f("level");
		$_SESSION["UserId"] = $DB->f("user_id");
		$_SESSION["UserName"] = $user_login;

		//met � jour les information de la base
		$query = "UPDATE users Set nb_connect = (nb_connect + 1), last_connect = NOW() WHERE user_id=" . $DB->escape($_SESSION["UserId"]);
		$DB->query ($query);
		if ($user_askcookie ==1)
		{//d�fini les param�tres de cookie

			setcookie ("username",$user_login,time()+31104000,"/");
			setcookie ("pass",md5($user_password),time()+31104000,"/");
		}
		header("Location:".BDO_URL."index.php");
	}
	// Affichage du formulaire de login
	else
	{
		$errortype[0] = "La session a expir�. Veuillez vous identifier � nouveau";
		$errortype[1] = "Utilisateur Inconnu.";
		$errortype[2] = "Mot de passe erron�";
		$errortype[5] = "Veuillez vous identifier";
		if ($errornum =="") {
			$errornum = 5;
		}
		$errorstring = $errortype[$errornum];

		// Creation d'un nouveau template
		$t = new Template(BDO_DIR."public/templates");
		// fichier � utiliser
		$t->set_file(array(
		"tpBody" => "login.tpl",
		"tpMenu" => "menu.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
		"tpBase" => "body.tpl"));

		$t->set_var (array
		("ERREURTYPE" => $errorstring));

		$t->set_var("PAGETITLE","BDOVORE.com : login");
		// assigne la barre le navigation NEWS
		$t->set_var (array
		("BARRENEWS" => GetNavigationBar($first,$nb,$nbtotal,"./shownews.php")));
		// assigne la barre de login
		$t->set_var (array
		("LOGINBARRE" => GetIdentificationBar(),
		"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,));
		$t->parse("BODY","tpBody");
		$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUBARREUSER","tpMenuUser");
		$t->pparse("MyFinalOutput","tpBase");
	}
}

