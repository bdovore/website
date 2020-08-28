<?php

/**
 *
 * @author Tom
 *
 */
class Compte extends Bdo_Controller {

    /**
     */
    public function Index() {
        /*
         * Affichage des informations du compte ou création de compte
         */
        if (User::minAccesslevel(2)) {
            $user_id = getVal("user_id", "");
            $act=getVal("act","");
            $this->loadModel("User");
            if ($user_id != "" && $user_id != $_SESSION["userConnect"]->user_id) {
                //un username et un userid ont été passés via l'URL
                //On vérifie que l'utilisateur est authorisé à ouvrir cette page

                $this->User->set_dataPaste(array("user_id" =>$user_id ));
                $this->User->load();
                $currentstatus = $this->User->level;
                $username = $this->User->username;

                if ($currentstatus <= $_SESSION["userConnect"]->level) {
                    echo GetMetaTag(3, "Vous n'avez pas les authorisations n&eacute;cessaire pour afficher cette page.", (BDO_URL ));
                    exit();
                } else {
                    $profile_user_id = $user_id;
                    $profile_user_username = $username;
                }
            } else {
                $profile_user_id = $_SESSION["userConnect"]->user_id;
                $profile_user_username = $_SESSION["userConnect"]->username;
            }


// Mettre à jour les informations

            if ($act == "update") {
                // vérifie que ni nom, prénom ou email ne sont nuls
                if (postVal("txtemail") == '' or !Checkmail(postVal("txtemail"))) {
                    echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">' . "L'adresse email n'est pas valide. Vous allez etre redirig&eacute;.";
                } else {
                    // procède à la mise à jour
                    $this->User->set_dataPaste(array("user_id" =>$user_id,
                       "email" => postVal("txtemail"),
                        "birthday" => postVal("txtanniv"),
                        "OPEN_COLLEC" => postVal("lstOpenCollec"),
                        "ABT_NEWS" => (postVal("txtNewsletter") == "checked" ? "1" : "0")  ,
                        "location" => postVal("txtlocation"),
                        "CARRE_TYPE" => postVal("lstCarre")));
                    $this->User->update();
                    if (issetNotEmpty($this->User->error)) {
                        var_dump($this->User->error);
                        exit();
                    }
                    echo GetMetaTag(2, "Votre profil a &eacute;t&eacute; mis &agrave; jour.", (BDO_URL . "Compte"));
                }
            }


            // suppression du compte
            else if (($act == "delete") and (User::minAccesslevel(1) or $profile_user_id == $_SESSION["userConnect"]->user_id)) {
                $this->User->set_dataPaste(array("user_id" => $profile_user_id));
                $this->User->load();
                $useremail = $this->User->email;
                $username = $this->User->username;

                // si user correcteur ou admin

                User::deleteAllDataForUser($profile_user_id);
                $this->User->logout();
                $mail_adress = $useremail;
                $mail_sujet = "BDOVORE - suppression de votre compte";
                $mail_entete = "From: BDoVore <no-reply@bdovore.com>";
                $mail_text = "Bonjour " . $username . ", \n\n";
                $mail_text .="Votre compte BDoVore a &eactue;t&eactue; supprim&eactue;.\n\n";
                $mail_text .="Votre compte sur le forum BDoVore n'a pas &eacute;t&eacute; supprim&eacute;.\nSi vous le d&eacute;sirez, merci de nous faire part des raisons ayant motiv&eacute;es votre d&eacute;part.\n\n";
                $mail_text .="Nous respectons votre d&eacute;cision et esperons vous revoir tr&eacute;s bientot.\n\n";
                $mail_text .="L'&eacute;quipe BDOVORE";

                mail($mail_adress, $mail_sujet, $mail_text, $mail_entete);

                $mail_adress = 'tomlameche@gmail.com';
                $mail_sujet = "BDOVORE - suppression de compte";
                $mail_entete = "From: BDoVore <no-reply@bdovore.com>";
                $mail_text = "id : " . $profile_user_id . ", \n";
                $mail_text .= "username : " . $username . ", \n";
                $mail_text .= "email : " . $useremail . ", \n";

                mail($mail_adress, $mail_sujet, $mail_text, $mail_entete);

                echo "Votre profil BDoVore a &eacute;t&eacute; supprim&eacute; d&eacute;finitivement.";
            }


// Mofidifcation du mot de passe
            else if ($act == "newpass") {
                // control du mot de pass et udpate
                    $newpass1 = postVal("txtpass1");
                    $newpass2 = postVal("txtpass2");
                    $validpassword = Checkpassword($newpass1);
                    if ($validpassword != 1) {
                        echo GetMetaTag(5, $validpassword . ' Vous allez etre redirig&eacute;.', (BDO_URL . "Compte"));
                    } elseif (($newpass1 != $newpass2) and ($validpassword == 1)) {
                        echo GetMetaTag(5, "Les mots de passes ne sont pas identiques. Vous allez etre redirig&eacute;", (BDO_URL . "Compte"));
                    } elseif (($validpassword == 1) and ($newpass1 === $newpass2)) {
                        $this->User->set_dataPaste(array(
                            "user_id" => intval($profile_user_id),
                            "password" => md5($newpass1)
                        ));
                        $this->User->update();

                        echo GetMetaTag(2, "Votre mot de passe est modifi&eacute;.", (BDO_URL . "Compte"));
                    }

            }
            // Afficher le formulaire pré-remplis
            elseif ($act == "") {
                //récupère les données utilisateur dans la base de données
                $this->User->set_dataPaste(array("user_id" =>$profile_user_id ));
                $this->User->load();

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


                $this->view->set_var(array
                    ("USERID" => $profile_user_id,
                    "UTILISATEUR" => $this->User->username,
                    "EMAIL" => $this->User->email,
                    "LOCATION" => $this->User->location,
                    "BIRTHDAY" => $this->User->birthday,
                    "YESISSELECTED" => ($this->User->OPEN_COLLEC == 'Y' ? 'Selected' : ''),
                    "NOISSELECTED" => ($this->User->OPEN_COLLEC == 'N' ? 'Selected' : ''),
                    "URLCOLLEC" => BDO_URL . 'guest?user=' . encodeUserId($profile_user_id),
                    "OPTIONDISPLAY" => GetOptionValue($my_options, $this->User->row_display),
                    "OPTIONDISPLAYSERIE" => GetOptionValue($other_options, $this->User->rowserie),
                    "IS_NEWSLETTER" => ($this->User->ABT_NEWS == 1 ? 'Checked' : ''),
                    "PICTURE" => $pathimage,
                    "OPTIONCARRE" => GetOptionValue($carre_options, $this->User->CARRE_TYPE)));

                $this->view->set_var("PAGETITLE", "BDOVORE.com : Mon profil");
                $this->view->layout = "iframe";
                $this->view->render();
            }
        }
    }

    public function Inscription() {
        $mobile = getVal("mobile",""); // check if mobile device detected
        $source = getVal("source","");
        //initialisation des variables de couleur
        $color["NewUser"] = "#000000";
        $color["NewPass1"] = "#000000";
        $color["Newpass2"] = "#000000";
        $color["NewEmail"] = "#000000";

//initialisation des valeurs par défaut
        $default_username = postVal("NewUser");
        $defaut_pass1 = postVal("NewPass1");
        $defaut_pass2 = postVal("Newpass2");
        $defaut_email = postVal("NewEmail");

// initialisation des messages d'erreur
        $stringerror[0] = "";
        $stringerror[1] = "Veuillez remplir les champs indiqu&eacute;s en rouge";
        $stringerror[2] = "Le password choisit doit comprendre au moins 6 caracteres et ne doit pas contenir d'espace";
        $stringerror[3] = "Les mots de passe ne concordent pas";
        $stringerror[4] = "veuillez entrer une adresse e-mail valide";
        $stringerror[5] = "le nom d'utilisateur ne doit comporter que lettres / chiffres";
        $stringerror[6] = "Le nom d'utilisateur est d&eacute;j&agrave; utilis&eacute;";
        $stringerror[7] = "Une erreur inconnue s'est produite : votre inscription n'a pu s'effectuer normalement";

        $errornum = 0;

//traitement des exceptions en cas de post
        $act = getVal("act");
        if ($act == "post") {//Détermine le nombre de champs vides
            $champvide = 0;
            reset($_POST);
            while (list ($key, $val) = each($_POST)) {
                if ($val == "") {
                    $champvide++;
                    $color[$key] = "#FF0000";
                }
            }

            if ($champvide != 0) {
                $errornum = 1;
            }
            //Verifie la validité du password
            if ((Checkpassword($defaut_pass1) != 1) && ($errornum == 0)) {
                $errornum = 2;
                $defaut_pass1 = "";
                $defaut_pass2 = "";
            }
            // Vérifie que les passwords concordent
            if (($defaut_pass1 != $defaut_pass2) && ($errornum == 0)) {
                $errornum = 3;
                $defaut_pass1 = "";
                $defaut_pass2 = "";
            }

            //Vérifie la validité de l'adresse e-mail
            if ((Checkmail($defaut_email) != 1) && ($errornum == 0)) {
                $errornum = 4;
                $color["NewEmail"] = "#FF0000";
            }
            //Vérifie la validité du login
            //Vérifie que le login ne comprend que des caractères authorisés et aucun espace
            if ((CheckChars($default_username) != true) && ($errornum == 0)) {
                $errornum = 5;
                $color["NewUser"] = "#FF0000";
            }

            // vérifie que login choisi n'est pas réservé et qu'il n'est pas déjà utilisé
            if ($errornum == 0) {
                $user = New user();
                $user->load("c", " WHERE LCASE(username)= LCASE('" . Db_Escape_String($default_username) . "')");

                if (AuthorisedLogin($default_username) != true or (issetNotEmpty($user->user_id))) {
                    $errornum = 6;
                    $color["NewUser"] = "#FF0000";
                } else {
                    //on valide le nouvel utilisateur
                    $user->set_dataPaste(array(
                        "username" => Db_Escape_String($default_username),
                        "password" => md5($defaut_pass1),
                        "email" => $defaut_email,
                        "level" => 2
                    ));
                    $user->update();

                    if (notIssetOrEmpty($user->error)) {
                        //ajout dans le forum si besoin
                       $user->setForumAccount($default_username, $defaut_pass1, $defaut_email);
                        $this->view->set_var(array("validInscription" => 1));
                       
                    } else
                    {
                        $this->set_var(array("ERROR" => $user->error));
                    }
                    $errornum = 7;
                }
            }
        }



        $errortext = $stringerror[$errornum];

        $this->view->set_var(array
            ("USERNAME" => $default_username,
            "PASSWORD1" => $defaut_pass1,
            "PASSWORD2" => $defaut_pass2,
            "NOM" => $default_nom,
            "PRENOM" => $default_prenom,
            "EMAIL" => $defaut_email,
            "COLORNEWUSER" => $color["NewUser"],
            "COLORNEWPASS1" => $color["NewPass1"],
            "COLORNEWPASS2" => $color["Newpass2"],
            "COLORNEWEMAIL" => $color["NewEmail"],
            "ERRORTEXT" => $errortext,
            "source" => $source));
        // set frame
        $frame = "iframe";
       /* $url_referer = parse_url($_SERVER["HTTP_REFERER"]);
        $domaine = $url_referer['host'];
        $url_host =  parse_url(BDO_URL);
        if ($domaine != $url_host['host']) {
            $frame = "default";
        }
        if ($mobile == "T") $frame = "default";*/
        
        //$this->view->layout = "$frame";
        $this->view->render();
    }

    public function forgotPass() {
        $email = getVal("email");
        $this->view->layout = "iframe";
        if ($email=="ok")
    {//initialise la procédure de renvoi
        $user_username = postVal("txtusername");
        $user_email = postVal("txtemail");
                $this->loadModel("User");
                $this->User->load("c", " WHERE username= '".Db_Escape_String($user_username)."' and email = '".Db_Escape_String($user_email)."'");

        //Verifie qu'un nom a été retourné par la query
        if (notIssetOrEmpty($this->User->user_id))
        {
            $this->view->addAlertPage("L'utilisateur n'existe pas ou l'adresse e-mail est erron&eacute;e !");
                        $this->view->addPhtmlFile('alert', 'BODY');

        }
                else {

        //génère un nouveau mot de passe et l'envoie à l'utilisateur


        $newpassword = passgen(8);
                $this->User->set_dataPaste(array("user_id" => $this->User->user_id, "password" =>md5($newpassword) ));
                $this->User->update();

                if (issetNotEmpty($this->User->error)) {
                    var_dump($this->User->error);
                    exit;
                }

        //Prépare l'email à envoyer
        $textemail = "Bonjour,\n\n";
        $textemail .= "Suite à votre demande, votre mot de passe pour accéder à www.bdovore.com a été changé.\n";
        $textemail .= "Votre nouveau mot de passe est :\n\n";
        $textemail .= "$newpassword\n\n";
        $textemail .= "N'oubliez pas de changer votre mot de passe dans votre profil lors de votre prochain login.\n";
        $textemail .= "Amicalement\n";


        mail($user_email,"Votre nouveau mot de passe",$textemail);

        echo  "Votre nouveau mot de passe a &eacute;t&eacute; envoy&eacute;. Vous pouvez fermer cette fenêtre.";
        exit();
                }
    }

                $this->view->render();

    }

}

?>
