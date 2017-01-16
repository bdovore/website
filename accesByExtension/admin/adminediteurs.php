<?php



minAccessLevel(1);

// Mettre à jour les informations
if ($act=="update")
{

    $query = "UPDATE bd_editeur SET";
    $query .= " `nom` = '".$_POST['txtNomEditeur']."', ";
    $query .= " `url_site` = '".$_POST['txtUrlSite']."'";
    $query .=" WHERE (`id_editeur`=".$_POST["txtIdEditeur"].");";
    $DB->query($query);
    echo '<META http-equiv="refresh" content="1; URL=javascript:history.go(-1)">'."Mise à jour effectuée";
}

// EFFACEMENT D'UN ALBUM
elseif($act=="delete")
{
    if ($conf == "ok")
    {
        $query = "DELETE FROM bd_editeur WHERE id_editeur= ".$DB->escape($editeur_id)." LIMIT 1";
        $DB->query ($query);
        $redirection = BDO_URL."admin/index.php";
        echo '<META http-equiv="refresh" content="2; URL='.$redirection.'">L\'éditeur a été effacée de la base.';
        exit();
    }
    else
    {// Affiche la demande de confirmation
        echo 'Etes-vous sûr de vouloir effacer l\'éditeur n. '.$editeur_id.' ? <a href="'.BDO_URL.'admin/adminediteurs.php?act=delete&conf=ok&editeur_id='.$editeur_id.'">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
        exit();
    }
}
// AFFICHE UN FORMULAIRE VIDE
elseif($act=="new")
{
    // Creation d'un nouveau Template
    $t = new Template(BDO_DIR."public/templates");
    // fichier à utiliser
    $t->set_file(array(
    "tpBody" => "admin.editeur.tpl",
    "tpBase" => "body.tpl"));
    //echo $query;
    //echo htmlspecialchars($DB->f("id_color"));
    $t->set_var (array
    ("NBCOLLEC" => "0",
    "URLDELETE" => "javascript:alert('Désactivé');",
    "URLADDCOLLEC" => "javascript:alert('Désactivé');",
    "URLFUSION" => "javascript:alert('Désactivé');",
    "ACTIONNAME" => "Enregistrer",
    "URLACTION" => BDO_URL."admin/adminediteurs.php?act=append"
    ));
    // assigne la barre de login
    $t->set_var (array
    ("LOGINBARRE" => GetIdentificationBar(),
    "MENUBARRE" => admin_menu(),
    "URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,));
    $t->parse("BODY","tpBody");
    $t->pparse("MyFinalOutput","tpBase");
}

// INSERE UN NOUVEL ALBUM DANS LA BASE
elseif($act=="append")
{

    $query = "
    INSERT INTO `bd_editeur` (
        `NOM`,
        `URL_SITE`
    )   VALUES (
        '".$DB->escape($_POST['txtNomEditeur'])."',
        '".$DB->escape($_POST['txtUrlSite'])."'
     )
     ";
    $DB->query ($query);
    $lid= mysql_insert_id();

    // Insère un collection <N/A> pour cet éditeur
    $query_el = array("nom" => sqlise("<N/A>",'text'),
    "id_editeur" => $lid
    );
    $query = insert_query("bd_collection",$query_el);
    $DB->query ($query);
    echo GetMetaTag(2,"L'éditeur a été ajouté",(BDO_URL."admin/adminediteurs.php?editeur_id=".$lid));
}

// AFFICHER UN EDITEUR
elseif($act=="")
{

    // Creation d'un nouveau Template
    $t = new Template(BDO_DIR."public/templates");
    // fichier à utiliser
    $t->set_file(array(
    "tpBody" => "admin.editeur.tpl",
    "tpMenu" => "menuadmin.tpl",
    "tpBase" => "body.tpl"));

    // Selectionne les collections liées à lediteur
    $query = "SELECT id_collection, nom FROM bd_collection WHERE id_editeur=".$DB->escape($editeur_id);
    $DB->query ($query);
    $nb_collec = $DB->num_rows();
    // on rempli avec les albums
    $t->set_block('tpBody','ColBlock','CBlock');
    while ($DB->next_record())
    {
        $t->set_var (array
        ("COL_LIEN" => BDO_URL."admin/admincollections.php?collec_id=".$DB->f("id_collection"),
        "COL_TITRE" => htmlentities(stripslashes($DB->f("nom")))
        ));
        $t->parse ("CBlock", "ColBlock",true);
    }

    //récupère les données editeur dans la base de donnée
    $query = "SELECT ID_EDITEUR, NOM, URL_SITE FROM bd_editeur WHERE id_editeur=".$DB->escape($editeur_id);
    $DB->query ($query);
    $DB->next_record();
    $t->set_var (array
    ("IDEDITEUR" => $DB->f("ID_EDITEUR"),
    "NOM" => $DB->f("NOM"),
    "URLWEBSITE" => $DB->f("URL_SITE"),
    "NBCOLLEC" => $nb_collec,
    "URLDELETE" => BDO_URL."admin/adminediteurs.php?act=delete&editeur_id=".$DB->f("ID_EDITEUR"),
    "URLFUSION" => BDO_URL."admin/mergeediteurs.php?source_id=".$DB->f("ID_EDITEUR"),
    "URLADDCOLLEC" => BDO_URL."admin/admincollections.php?act=new&editeur_id=".$DB->f("ID_EDITEUR"),
    "ACTIONNAME" => "Valider les Modifications",
    "URLACTION" => BDO_URL."admin/adminediteurs.php?act=update"));
    // assigne la barre de login
    $t->set_var (array
    ("LOGINBARRE" => GetIdentificationBar(),
    "URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,));
    $t->parse("BODY","tpBody");
    $t->parse("MENUBARRE","tpMenu");
    $t->pparse("MyFinalOutput","tpBase");
}
