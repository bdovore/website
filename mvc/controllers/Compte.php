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
    }

    public function Inscription() {
        //initialisation des variables de couleur
        $color["NewUser"] = "#000000";
        $color["NewPass1"] = "#000000";
        $color["Newpass2"] = "#000000";
        $color["NewEmail"] = "#000000";

//initialisation des valeurs par d�faut
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
        if ($act == "post") {//D�termine le nombre de champs vides
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
            //Verifie la validit� du password
            if ((Checkpassword($defaut_pass1) != 1) && ($errornum == 0)) {
                $errornum = 2;
                $defaut_pass1 = "";
                $defaut_pass2 = "";
            }
            // V�rifie que les passwords concordent
            if (($defaut_pass1 != $defaut_pass2) && ($errornum == 0)) {
                $errornum = 3;
                $defaut_pass1 = "";
                $defaut_pass2 = "";
            }

            //V�rifie la validit� de l'adresse e-mail
            if ((Checkmail($defaut_email) != 1) && ($errornum == 0)) {
                $errornum = 4;
                $color["NewEmail"] = "#FF0000";
            }
            //V�rifie la validit� du login
            //V�rifie que le login ne comprend que des caract�res authoris�s et aucun espace
            if ((CheckChars($default_username) != true) && ($errornum == 0)) {
                $errornum = 5;
                $color["NewUser"] = "#FF0000";
            }

            // v�rifie que login choisi n'est pas r�serv� et qu'il n'est pas d�j� utilis�
            if ($errornum == 0) {
                $user = New user();
                $user->load("c"," WHERE username='" . Db_Escape_String($default_username)."'" );
                
                if (AuthorisedLogin($default_username) != true or (issetNotEmpty($user->USER_ID))) {
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
                        $user->setForumAccount(Db_Escape_String($default_username), $defaut_pass1, $defaut_email);
                        
                        $texte = "Inscription r&eacute;ussie sur le site <u>ainsi que sur le forum</u> (meme identifiants de connexion).
                        <br />Vous pouvez fermer cette fenêtre et vous connecter avec votre identifiant et mot de passe !";
                        //echo GetMetaTag(15, $texte, (BDO_URL . "compte"));
                        exit();
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
            "ERRORTEXT" => $errortext));
        $this->view->layout = "iframe";
        $this->view->render();
    }

}

?>
