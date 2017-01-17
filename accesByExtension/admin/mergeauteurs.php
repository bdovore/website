<?php



minAccessLevel(1);


$error_msg[0] = "Auteur à supprimer non défini";
$error_msg[1] = "Auteur à garder non défini";
$error_msg[2] = "Auteur à garder et auteur à fusionner identiques";


// Mettre à jour les informations


if ($act=="merge")
{
    // vérifie que source_id et dest_id ont été defini
    if ((is_null($dest_id)) | ($dest_id==""))
    {header("Location:".BDO_URL."admin/mergeauteurs.php?source_id=$source_id&error=1");
    }
    if ((is_null($source_id)) | ($source_id==""))
    {header("Location:".BDO_URL."admin/mergeauteurs.php?dest_id=$dest_id&error=0");
    }

    if ($source_id==$dest_id)
    {header("Location:".BDO_URL."admin/mergeauteurs.php?source_id=$source_id&dest_id=$dest_id&error=2");
    }

    if ($conf=="ok")
    {

        // Met à jour l'information contenue dans la base de données
        $modif =0;
        // scenar
        $query = "UPDATE bd_tome SET id_scenar = ".$DB->escape($dest_id)." where id_scenar = ".$DB->escape($source_id);
        $DB->query($query);
        $modif += $DB->affected_rows();
        // scenar_alt
        $query = "UPDATE bd_tome SET id_scenar_alt = ".$DB->escape($dest_id)." where id_scenar_alt = ".$DB->escape($source_id);
        $DB->query($query);
        $modif += $DB->affected_rows();
        //dessin
        $query = "UPDATE bd_tome SET id_dessin = ".$DB->escape($dest_id)." where id_dessin = ".$DB->escape($source_id);
        $DB->query($query);
        $modif += $DB->affected_rows();
        //dessin_alt
        $query = "UPDATE bd_tome SET id_dessin_alt = ".$DB->escape($dest_id)." where id_dessin_alt = ".$DB->escape($source_id);
        $DB->query($query);
        $modif += $DB->affected_rows();
        //color
        $query = "UPDATE bd_tome SET id_color = ".$DB->escape($dest_id)." where id_color = ".$DB->escape($source_id);
        $DB->query($query);
        $modif += $DB->affected_rows();
        //color_alt
        $query = "UPDATE bd_tome SET id_color_alt = ".$DB->escape($dest_id)." where id_color_alt = ".$DB->escape($source_id);
        $DB->query($query);
        $modif += $DB->affected_rows();

        echo "Nombre de records modifiées dans la table bd_tome : ".$modif ."<br>";

        // Supprime l'ancien auteur
        $query = "DELETE FROM bd_auteur WHERE id_auteur = ".$DB->escape($source_id);
        $DB->query($query);
        echo "Nombre de records modifiées dans la table bd_auteur : ".$DB->affected_rows()."<br>";

        $redirection = BDO_URL."admin/index.php";
        echo '<META http-equiv="refresh" content="4; URL='.$redirection.'">Les auteurs ont été fusionnés.';

    }else{
        // Demande de confirmation

        echo 'Etes-vous s&ucirc;r de vouloir fusionner les auteurs n°'.$source_id.' et '.$dest_id.'? <a href="'.BDO_URL.'admin/mergeauteurs.php?act=merge&conf=ok&source_id='.$source_id.'&dest_id='.$dest_id.'">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
        exit();
    }
}

// AFFICHER
elseif($act=="")
{

    // Creation d'un nouveau Template
    $t = new Template(BDO_DIR."public/templates");
    // fichier à utiliser
    $t->set_file(array(
    "tpBody" => "merge.auteurs.tpl",
    "tpBase" => "body.tpl"));

    // REMPLISSAGE PARTIE GAUCHE
    if ((!is_null($source_id)) & ($source_id!=''))
    {
        // récupère le nombre d'utilisateurs
        $nb_users1 = countTomeBy("auteur",$source_id);

        // récupère les données principales
        $query= "
        select id_auteur, pseudo, prenom, nom, flg_scenar, flg_dessin, flg_color, comment, dte_nais, dte_deces, nationalite
        from bd_auteur
        where id_auteur = ".$DB->escape($source_id);

        $DB->query ($query);
        $DB->next_record();
        $t->set_var (array
        ("AUTEURID1" => $DB->f("id_auteur"),
        "AUTEUR1" => htmlentities(stripslashes($DB->f("pseudo"))),
        "PRENOM1" => htmlentities(stripslashes($DB->f("prenom"))),
        "NOM1" => htmlentities(stripslashes($DB->f("nom"))),
        "DTENAISS1" => $DB->f("dte_nais"),
        "DTEDECES1" => $DB->f("dte_deces"),
        "NATION1" => htmlentities(stripslashes($DB->f("nationalite"))),
        "ISSCEN1" => $DB->f("flg_scenar")==1 ? checked : '',
        "ISDESS1" => $DB->f("flg_dessin")==1 ? checked : '',
        "ISCOLOR1" => $DB->f("flg_color")==1 ? checked : '',
        "COMMENT1" => htmlentities(stripslashes($DB->f("comment"))),
        "URLEDITEDIT1" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_auteur"),
        "SOURCEID" => $DB->f("id_auteur"),
        "NBUSERS1" => $nb_users1
        ));
    }else{
        $t->set_var (array
        ("NBUSERS1" => "0",
        "URLEDITEDIT1" => "javascript:alert('Désactivé')"
        ));
    }

    //REMPLISSAGE DE LA PARTIE DROITE
    if ((!is_null($dest_id)) & ($dest_id!=''))
    {
        // récupère le nombre d'utilisateurs

        $nb_users2 = countTomeBy("auteur",$dest_id);

        // récupère les données principales
        $query= "
        select id_auteur, pseudo, prenom, nom, flg_scenar, flg_dessin, flg_color, comment, dte_nais, dte_deces, nationalite
        from bd_auteur where id_auteur = ".$DB->escape($dest_id);

        $DB->query ($query);
        $DB->next_record();

        $t->set_var (array
        ("AUTEURID2" => $DB->f("id_auteur"),
        "AUTEUR2" => htmlentities(stripslashes($DB->f("pseudo"))),
        "PRENOM2" => htmlentities(stripslashes($DB->f("prenom"))),
        "NOM2" => htmlentities(stripslashes($DB->f("nom"))),
        "DTENAISS2" => $DB->f("dte_nais"),
        "DTEDECES2" => $DB->f("dte_deces"),
        "NATION2" => htmlentities(stripslashes($DB->f("nationalite"))),
        "ISSCEN2" => $DB->f("flg_scenar")==1 ? checked : '',
        "ISDESS2" => $DB->f("flg_dessin")==1 ? checked : '',
        "ISCOLOR2" => $DB->f("flg_color")==1 ? checked : '',
        "COMMENT2" => htmlentities(stripslashes($DB->f("comment"))),
        "URLEDITEDIT2" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_auteur"),
        "DESTID" => $DB->f("id_auteur"),
        "NBUSERS2" => $nb_users2
        ));
    }else{
        $t->set_var (array
        ("NBUSERS2" => "0",
        "URLEDITEDIT2" => "javascript:alert('Désactivé')"
        ));
    }
    // Message d'erreur
    if (!is_null($error))
    {
        $t->set_var ("ERRORMESSAGE",$error_msg[$error]);
    }


    // variables misesà jour dans tous les cas
    $t->set_var (array
    ("URLREFRESH" => BDO_URL."admin/mergeauteurs.php",
    "URLECHANGE" => BDO_URL."admin/mergeauteurs.php?source_id=$dest_id&dest_id=$source_id",
    "URLFUSION" => BDO_URL."admin/mergeauteurs.php?act=merge&source_id=$source_id&dest_id=$dest_id"
    ));

    // assigne la barre de login
    $t->set_var (array
    ("LOGINBARRE" => GetIdentificationBar(),
    "MENUBARRE" => admin_menu(),
    "URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
    "URLSITEFORUM" => BDO_URL_FORUM
    ));
    $t->parse("BODY","tpBody");
    $t->pparse("MyFinalOutput","tpBase");
}
