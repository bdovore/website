<?php




minAccessLevel(2);


if ($user_id != "" && $user_id != $_SESSION["UserId"])
{
    //un username et un userid ont été passés via l'URL
    //On vérifie que l'utilisateur est authorisé à ouvrir cette page

    $DBTemp = new DB_Sql;
    $DBTemp->query("SELECT level,username FROM users WHERE (user_id=".$DB->escape($user_id).");");
    $DBTemp->next_record();
    $currentstatus = $DBTemp->f ("level");
    $username = $DBTemp->f ("username");

    if ($currentstatus <= $_SESSION["UserLevel"])
    {
        echo GetMetaTag(3,"Vous n'avez pas les authorisations nécessaire pour afficher cette page.",(BDO_URL."index.php"));
        exit();
    }else{
        $profile_user_id = $user_id;
        $profile_user_username = $username;
    }
}
else {
    $profile_user_id = $_SESSION["UserId"];
    $profile_user_username = $_SESSION["UserName"];
}


// Mettre à jour les informations

if ($act=="update")
{
    // vérifie que ni nom, prénom ou email ne sont nuls
    if ($_POST["txtemail"]=='' or !Checkmail($_POST["txtemail"]))
    {
        echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">'."L'adresse email n'est pas valide. Vous allez être redirigé.";
    }
    else
    {
        // procède à la mise à jour

        $query = "UPDATE users SET `email`='".$DB->escape($_POST["txtemail"])."', ";
        $query .= "`birthday`='".$DB->escape($_POST["txtanniv"])."', ";
        $query .= "`open_collec`='".$DB->escape($_POST["lstOpenCollec"])."', ";
        $query .= "`row_display`=".$DB->escape($_POST["lstRowDisplay"]).", ";
        $query .= "`rowserie`=".$DB->escape($_POST["lstRowSerie"]).", ";
        $query .= "`abt_news`= '".($_POST["txtNewsletter"]=="checked" ? "1" : "0")."', ";
        $query .= "`location`='".$DB->escape($_POST["txtlocation"])."', ";
        $query .= "`carre_type`='".$DB->escape($_POST["lstCarre"])."' WHERE (user_id=".$DB->escape($profile_user_id).")";
        $DB->query($query);
        echo GetMetaTag(1,"Votre profil a &eacute;t&eacute; mis à jour.",(BDO_URL."index.php"));
    }
}


// suppression du compte
else if (($act=="delete") and (minAccessLevel() or $profile_user_id==$_SESSION["UserId"]))
{
    $DB->query("SELECT username,email FROM users WHERE user_id=".$DB->escape($profile_user_id));
    $DB->next_record();
    $useremail = $DB->f("email");
    $username = $DB->f("username");

    // si user correcteur ou admin

    $DB->query("UPDATE `bd_edition` SET `USER_ID`=NULL WHERE `user_id`='".$DB->escape($profile_user_id)."'");
    $DB->query("UPDATE `bd_edition` SET `VALIDATOR`=NULL WHERE `VALIDATOR`='".$DB->escape($profile_user_id)."'");
    $DB->query("UPDATE `newsletter` SET `USR_CREA`=NULL WHERE `USR_CREA`='".$DB->escape($profile_user_id)."'");
    $DB->query("UPDATE `newsletter` SET `USR_MODIF`=NULL WHERE `USR_MODIF`='".$DB->escape($profile_user_id)."'");
    $DB->query("UPDATE `users_alb_prop` SET `VALIDATOR`=NULL WHERE `VALIDATOR`='".$DB->escape($profile_user_id)."'");

    // dans tout les cas
    $DB->query("DELETE FROM `serie_comment` WHERE `user_id`='".$DB->escape($profile_user_id)."'");
    $DB->query("DELETE FROM `users_album` WHERE `user_id`='".$DB->escape($profile_user_id)."'");
    $DB->query("DELETE FROM `users_exclusions` WHERE `user_id`='".$DB->escape($profile_user_id)."'");
    $DB->query("DELETE FROM `users_list_aut` WHERE `user_id`='".$DB->escape($profile_user_id)."'");
    $DB->query("DELETE FROM `users_list_carre` WHERE `user_id`='".$DB->escape($profile_user_id)."'");
    $DB->query("DELETE FROM `users_comment` WHERE `USER_ID`='".$DB->escape($profile_user_id)."'");
    $DB->query("DELETE FROM `users_alb_prop` WHERE `USER_ID`='".$DB->escape($profile_user_id)."'");
    $DB->query("DELETE FROM `users` WHERE `user_id`='".$DB->escape($profile_user_id)."'");

    $mail_adress = $useremail;
    $mail_sujet = "BDOVORE - suppression de votre compte";
    $mail_entete = "From: BDoVore <no-reply@bdovore.com>";
    $mail_text = "Bonjour ".$username.", \n\n";
    $mail_text .="Votre compte BDoVore a été supprimé.\n\n";
    $mail_text .="Votre compte sur le forum BDoVore n'a pas été supprimé.\nSi vous le désirez, merci de nous faire part des raisons ayant motivées votre départ.\n\n";
    $mail_text .="Nous respectons votre décision et espérons vous revoir très bientôt.\n\n";
    $mail_text .="L'équipe BDOVORE";

    mail($mail_adress,$mail_sujet,$mail_text,$mail_entete);

    $mail_adress = 'laurent.mignot@gmail.com';
    $mail_sujet = "BDOVORE - suppression de compte";
    $mail_entete = "From: BDoVore <no-reply@bdovore.com>";
    $mail_text = "id : ".$profile_user_id.", \n";
    $mail_text .= "username : ".$username.", \n";
    $mail_text .= "email : ".$useremail.", \n";

    mail($mail_adress,$mail_sujet,$mail_text,$mail_entete);

    echo GetMetaTag(1,"Votre profil BDoVore a &eacute;t&eacute; supprim&eacute; d&eacute;finitivement.",(BDO_URL."index.php"));
}


// Mofidifcation du mot de passe
else if ($act=="newpass")
{
    if ($status=="ok")
    {// control du mot de pass et udpate
        $newpass1 = $_POST["txtpass1"];
        $newpass2 = $_POST["txtpass2"];
        $validpassword = Checkpassword($newpass1);
        if ($validpassword != 1)
        {
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">'.$validpassword.' Vous allez être redirigé.';
        }
        elseif (($newpass1 != $newpass2) and ($validpassword == 1))
        {
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Les mots de passe ne concordent pas. Vous allez être redirigé.';
        }
        elseif (($validpassword == 1) and ($newpass1 == $newpass2))
        {
            $query = "UPDATE users SET `password`='".md5($newpass1)."' WHERE (user_id=".$DB->escape($profile_user_id).")";
            $DB->query($query);
            $act="";
            echo GetMetaTag(1,"Mise à jour effectuée avec succès.",(BDO_URL."membres/profil.php"));
        }
    }
    else
    {// affichage du formulaire de saisie de mot de passe
        // Creation d'un nouveau Template
        $t = new Template(BDO_DIR."public/templates");
        // fichier à utiliser
        $t->set_file(array(
        "tpMenuColl" => "menu_coll.tpl",
        "tpBody" => "newpassword.tpl",
        "tpMenu" => "user.menu.tpl",
        "tpBase" => "body.tpl"));


        //complète le path image
        $t->set_var (array
        ("UTILISATEUR" => $profile_user_username,
        "USERID" => $profile_user_id,
        ));





        $t->set_var("PAGETITLE","BDOVORE.com : Changer de mot de passe");
        // assigne la barre de login
        $t->set_var (array
        ("LOGINBARRE" => GetIdentificationBar(),
        "URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,));
        $t->parse("MENUCOLL","tpMenuColl");
        $t->parse("BODY","tpBody");
        $t->parse("MENUBARRE","tpMenu");
        $t->pparse("MyFinalOutput","tpBase");


    }
}


// Upload image
elseif ($act=="image")
{
    if ($status=="ok")
    {//post de l'image
        if (!is_file($imgfile))
        {// pas de fichier selectionné
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors du chargement du fichier. Votre fichier excède peut-être la limite requise. Vous allez être redirigé.';
            exit();
        }
        $imageproperties = getimagesize($imgfile);
        $imagetype = $imageproperties[2];
        $imagelargeur = $imageproperties[0];
        $imagehauteur = $imageproperties[1];
        // vérifie le type d'image
        if (($imagetype != 1) and ($imagetype != 2))
        {
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Seul des fichiers JPEG ou GIF peuvent être chargés. Vous allez être redirigé.';
            exit();
        }
        // vérifie les dimensions de l'image
        if (($imagelargeur > 120) or ($imagehauteur > 150))
        {
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">L\'image ne respecte pas la limite de taille. Vous allez être redirigé.';
            exit();
        }
        // copie le fichier sur le serveur

        $uploaddir = BDO_URL_IMAGE."trombi/";
        $newfilename = "avatar".$profile_user_id;
        if (($imagetype == 1))
        {
            $newfilename .=".gif";
        }else{
            $newfilename .=".jpg";
        }
        if(!copy($imgfile,$uploaddir.$newfilename))
        {
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors de l\'envoi au serveur. Vous allez être redirigé.';
            exit();
        }else{

            $query = "UPDATE users SET `image`='".$DB->escape($newfilename)."' WHERE (user_id=".$DB->escape($profile_user_id).")";
            $DB->query($query);
            echo GetMetaTag(3,"Mise à jour effectuée avec succès.",(BDO_URL."pages/profil.php"));
            exit();
        }
    }
    else
    {
        // affichage du form d'upload
        // Creation d'un nouveau Template
        $t = new Template(BDO_DIR."public/templates");
        // fichier à utiliser
        $t->set_file(array(
        "tpMenuColl" => "menu_coll.tpl",
        "tpBody" => "uploadfile.tpl",
        "tpMenu" => "user.menu.tpl",
        "tpBase" => "body.tpl"));

        $t->set_var (array
        ("USERNAME" => $profile_user_username,
        "USERID" => $profile_user_id,
        "URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,));

        $t->set_var("PAGETITLE","BDOVORE.com : mon Avatar");
        // assigne la barre de login
        $t->set_var (array
        ("LOGINBARRE" =>        GetIdentificationBar()));
        $t->parse("MENUCOLL","tpMenuColl");
        $t->parse("BODY","tpBody");
        $t->parse("MENUBARRE","tpMenu");
        $t->pparse("MyFinalOutput","tpBase");
        exit();
    }
}

// Afficher le formulaire pré - remplis
elseif($act=="")
{
    //récupère les données utilisateur dans la base de donnée
    $query= "SELECT username, email, birthday, location, open_collec, image, row_display, rowserie, abt_news, carre_type FROM users
    WHERE user_id = ".$DB->escape($profile_user_id);
    $DB->query ($query);
    $DB->next_record();

    //crée le tableau d'options
    $my_options[0][0] = 10;
    $my_options[0][1] = 10;
    $my_options[1][0] = 20;
    $my_options[1][1] = 20;
    $my_options[2][0] = 30;
    $my_options[2][1] = 30;

    $other_options[0][0] = 5;
    $other_options[0][1] = 5;
    $other_options[1][0] = 10;
    $other_options[1][1] = 10;
    $other_options[2][0] = 15;
    $other_options[2][1] = 15;

    $carre_options[0][0] = "0";
    $carre_options[0][1] = "Automatique";
    $carre_options[1][0] = "1";
    $carre_options[1][1] = "Manuel";

    // Creation d'un nouveau Template
    $t = new Template(BDO_DIR."public/templates");
    // fichier à utiliser
    $t->set_file(array(
    "tpMenuColl" => "menu_coll.tpl",
    "tpBody" => "profil.tpl",
    "tpMenu" => "user.menu.tpl",
    "tpBase" => "body.tpl"));

    //Recupère et complète le path image
    $imagename = $DB->f("image");
    $pathimage = BDO_URL_IMAGE."trombi/".$imagename;

    $t->set_var (array
    ("USERID" => $profile_user_id,
    "UTILISATEUR" => $DB->f("username"),
    "EMAIL" => $DB->f("email"),
    "LOCATION" => $DB->f("location"),
    "BIRTHDAY"=> $DB->f("birthday"),
    "YESISSELECTED"=> ($DB->f("open_collec") == 'Y' ? 'Selected':''),
    "NOISSELECTED"=> ($DB->f("open_collec") == 'N' ? 'Selected':''),
    "URLCOLLEC"=> BDO_URL.'guest.php?user='.encodeUserId($profile_user_id),
    "OPTIONDISPLAY"=> GetOptionValue($my_options,$DB->f("row_display")),
    "OPTIONDISPLAYSERIE"=> GetOptionValue($other_options,$DB->f("rowserie")),
    "IS_NEWSLETTER" => ($DB->f("abt_news") == 1 ? 'Checked':''),
    "PICTURE"=> $pathimage,
    "OPTIONCARRE" => GetOptionValue($carre_options,$DB->f("carre_type"))));

    $t->set_var("PAGETITLE","BDOVORE.com : Mon profil");
    // assigne la barre de login
    $t->set_var (array
    ("LOGINBARRE" => GetIdentificationBar(),
    "URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,));
    $t->parse("MENUCOLL","tpMenuColl");
    $t->parse("BODY","tpBody");
    $t->parse("MENUBARRE","tpMenu");
    $t->pparse("MyFinalOutput","tpBase");
}
