<?php



minAccessLevel(1);

// Mettre à jour les informations
if ($act=="insert")
{
    if ($_POST['txtPrenom'] != '')
    $long_name = $_POST['txtNom'].", ".$_POST['txtPrenom'];
    else
    $long_name = $_POST['txtNom'];

    $pseudo = notIssetOrEmpty($_POST['txtPseudo']) ?  $long_name : $_POST['txtPseudo'];

    $query = "
    INSERT INTO `bd_auteur` (
        `PSEUDO`,
        `NOM`,
        `PRENOM`
    ) VALUES (
        ".sqlise($pseudo,'text').",
        ".sqlise($_POST['txtNom'],'text').",
        ".sqlise($_POST['txtPrenom'],'text')."
    )";

    $DB->query ($query);
    // Creation d'un nouveau Template
    $t = new Template(BDO_DIR."public/templates");
    // fichier à utiliser
    $t->set_file("tpBody","auteurexpress.tpl");
    $t->set_var (array
    (
    "BODYONLOAD" => "window.close()"
    ));
    // assigne la barre de login
    $t->pparse("MyFinalOutput","tpBody");
}

// Afficher le formulaire pré - remplis
elseif($act=="")
{
    // Creation d'un nouveau Template
    $t = new Template(BDO_DIR."public/templates");
    // fichier à utiliser
    $t->set_file("tpBody","auteurexpress.tpl");
    $t->set_var (array
    (
    "URLACTION" => BDO_URL."admin/addauteur.php?act=insert"
    ));
    // assigne la barre de login
    $t->pparse("MyFinalOutput","tpBody");
}
