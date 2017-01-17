<?php


include (BDO_DIR."inc/db_phpBB.php");



//initialisation des variables de couleur
$color["NewUser"] = "#000000";
$color["NewPass1"] = "#000000";
$color["Newpass2"] = "#000000";
$color["NewEmail"] = "#000000";

//initialisation des valeurs par défaut
$default_username = $_POST["NewUser"];
$defaut_pass1 = $_POST["NewPass1"];
$defaut_pass2 = $_POST["Newpass2"];
$defaut_email = $_POST["NewEmail"];

// initialisation des messages d'erreur
$stringerror[0] ="";
$stringerror[1] = "Veuillez remplir les champs indiqués en rouge";
$stringerror[2] = "Le password choisit doit comprendre au moins 6 caractères et ne doit pas contenir d'espace";
$stringerror[3] = "Les mots de passe ne concordent pas";
$stringerror[4] = "veuillez entrer une adresse e-mail valide";
$stringerror[5] = "le nom d'utilisateur ne doit comporter que lettres / chiffres";
$stringerror[6] = "Le nom d'utilisateur est déjà utilisé";
$stringerror[7] = "Une erreur inconnue s'est produite : votre inscription n'a pu s'effectuer normalement";

$errornum = 0;

//traitement des exceptions en cas de post
$act = $_GET["act"];
if ($act=="post")
{//Détermine le nombre de champs vides
    $champvide = 0;
    reset ($_POST);
    while (list ($key, $val) = each ($_POST))
    {
        if ($val == "")
        {$champvide++;
        $color[$key] = "#FF0000";}
    }

    if ($champvide != 0)
    { $errornum=1;}
    //Verifie la validité du password
    if ((Checkpassword($defaut_pass1) != 1) && ($errornum == 0))
    {
        $errornum=2;
        $defaut_pass1="";
        $defaut_pass2="";
    }
    // Vérifie que les passwords concordent
    if (($defaut_pass1 != $defaut_pass2) && ($errornum == 0))
    {
        $errornum=3;
        $defaut_pass1="";
        $defaut_pass2="";
    }

    //Vérifie la validité de l'adresse e-mail
    if ((Checkmail($defaut_email) != 1) && ($errornum == 0))
    {
        $errornum=4;
        $color["NewEmail"] = "#FF0000";
    }
    //Vérifie la validité du login
    //Vérifie que le login ne comprend que des caractères authorisés et aucun espace
    if ((CheckChars($default_username) != true) && ($errornum == 0))
    {
        $errornum=5;
        $color["NewUser"] = "#FF0000";
    }

    // vérifie que login choisi n'est pas réservé et qu'il n'est pas déjà utilisé
    if ($errornum ==0)
    {

        $query ="SELECT username FROM users WHERE username='".$DB->escape($default_username)."'";
        $DB->query($query);
        if (AuthorisedLogin($default_username) != true or ($DB->nf() != 0))
        {
            $errornum=6;
            $color["NewUser"] = "#FF0000";
        }else{
            //on valide le nouvel utilisateur
            $query = "INSERT INTO users (username,password,level,email) VALUES ('".$DB->escape($default_username)."', '".md5($defaut_pass1)."', ";
            $query .= "2, '".$defaut_email."');";
            if ($DB->query($query) == true){
                //ajout dans le forum si besoin
                $DB_php = new DB_phpBB();
                $verif = "select count(*) nb from phpbb_users where username='".$DB->escape($default_username)."'";
                $DB_php->query($verif);
                $DB_php->next_record();
                if ($DB_php->f("nb") == 0) {
                    $query = "SELECT MAX(user_id) AS total
                                FROM phpbb_users";
                    $DB_php->query($query);
                    $DB_php->next_record();
                    $new_id = $DB_php->f("total")+ 1;
                    // insertion dans la table phpbb
                    $query = "insert into phpbb_users (
                    user_id , username,user_password, user_email, user_regdate ) values (
                    $new_id,'".$DB->escape($default_username)."', '".md5($defaut_pass1)."', '".$DB->escape($defaut_email)."'," . time() . "
                    )";
                    $DB_php->query($query);
                }
                $texte = "Inscription r&eacute;ussie sur le site <u>ainsi que sur le forum</u> (même identifiants de connexion).
                        <br />Vous allez être redirig&eacute; vers la page de connexion pour avoir accès à votre compte personnel. Vous pourrez ensuite commencer à g&eacute;rer votre collection.";
                echo GetMetaTag(15,$texte,(BDO_URL."membres/login.php"));
                exit();
            }
            $errornum=7;
        }
    }
}

// Génère la page avec le form de saisie
// Creation d'une nouvelle instance Fast Template
$t = new Template(BDO_DIR."public/templates");
// fichier à utiliser
$t->set_file(array(
"tpBody" => "inscription.tpl",
"tpMenu" => "menu.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBase" => "body.tpl"));

$errortext = $stringerror[$errornum];

$t->set_var (array
("USERNAME" => $default_username,
"PASSWORD1" => $defaut_pass1,
"PASSWORD2" => $defaut_pass2,
"NOM" => $default_nom,
"PRENOM" => $default_prenom,
"EMAIL" => $defaut_email,
"COLORNEWUSER"=> $color["NewUser"],
"COLORNEWPASS1"=> $color["NewPass1"],
"COLORNEWPASS2"=> $color["Newpass2"],
"COLORNEWEMAIL"=> $color["NewEmail"],
"ERRORTEXT" => $errortext));

// assigne la barre de login
$t->set_var (array
("LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
"URLSITEIMAGE" => BDO_URL_IMAGE,
"URLSITEFORUM" => BDO_URL_FORUM
));
$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBase");
