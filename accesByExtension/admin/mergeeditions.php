<?php



minAccessLevel(1);

$error_msg[0] = "Edition à supprimer non définie";
$error_msg[1] = "Edition à garder non définie";
$error_msg[2] = "Edition à garder et à supprimer identiques";

// Fusionne les editions
if ($act=="merge"){
    // vérifie que source_id et dest_id ont été definis
    if ((is_null($dest_id)) | ($dest_id=="")){
        header("Location:".BDO_URL."admin/mergeeditions.php?source_id=$source_id&error=1");
    }
    if ((is_null($source_id)) | ($source_id=="")){
        header("Location:".BDO_URL."admin/mergeeditions.php?dest_id=$dest_id&error=0");
    }
    if ($source_id==$dest_id){
        header("Location:".BDO_URL."admin/mergeeditions.php?source_id=$source_id&dest_id=$dest_id&error=2");
    }
    if ($conf=="ok"){

        // Récupère les données de l'edition à mettre à jour (de destination)
        $query = "
        SELECT
            t.id_tome,
            s.id_serie,
            s.id_genre,
            en.id_edition,
            c.id_collection,
            c.id_editeur,
            t.id_scenar,
            t.id_dessin
        from
            bd_edition en
            INNER JOIN bd_tome t ON t.id_tome = en.id_tome
            INNER JOIN bd_serie s ON t.id_serie = s.id_serie
            INNER JOIN bd_collection c ON en.id_collection = c.id_collection
        WHERE
            en.id_edition = ".$DB->escape($dest_id)."
        ";

        $DB->query($query);
        $DB->next_record();

        $id_serie = $DB->f("id_serie");
        $id_editeur = $DB->f("id_editeur");
        $id_collection = $DB->f("id_collection");
        $id_scenar = $DB->f("id_scenar");
        $id_dessin = $DB->f("id_dessin");
        $id_genre = $DB->f("id_genre");
        $id_tome = $DB->f("id_tome");

        // Efface les éditions et les couvertures correspondantes

        $query = "SELECT id_edition, img_couv FROM bd_edition WHERE id_edition = ".$DB->escape($source_id);
        $DB->query ($query);
        while ($DB->next_record()){
            $DB->f("img_couv");
            if ($DB->f("img_couv") != ''){
                $filename = $DB->f("img_couv");
                if (file_exists(BDO_DIR."images/couv/$filename")){
                    @unlink(BDO_DIR."images/couv/$filename");
                    echo "Couverture effacée pour l'édition N°".$DB->f("id_edition")."<br />";
                }
            }
        }
        $query = "UPDATE IGNORE users_album SET id_edition=".$DB->escape($dest_id)." WHERE id_edition = ".$DB->escape($source_id);
        $DB->query ($query);
        echo 'Référence(s) à l\'édition modifiée(s) dans la table users_album<br />';

        $query = "delete from users_album where id_edition = ".$DB->escape($source_id);
        $DB->query ($query);
        echo 'Référence(s) à l\'édition supprimée(s) dans la table users_album (sécurité au cas ou un utilisateur possède les deux éditions fusionnées)<br />';

        // vide la table bd_edition
        $query = "DELETE FROM bd_edition WHERE id_edition = ".$DB->escape($source_id)." LIMIT 1";
        $DB->query ($query);
        echo 'Référence(s) à l\'album supprimée(s) dans la table bd_edition<br />';


        $redirection = BDO_URL."admin/admineditions.php?edition_id=".$dest_id;
        echo '<META http-equiv="refresh" content="4; URL='.$redirection.'">Les éditions ont été fusionnées.';
    }else{
        // Demande de confirmation
        echo 'Etes-vous s&ucirc;r de vouloir fusionner les éditions n°'.$source_id.' et '.$dest_id.'? <a href="'.BDO_URL.'admin/mergeeditions.php?act=merge&conf=ok&source_id='.$source_id.'&dest_id='.$dest_id.'">Oui</a> - <a href="javascript:history.go(-1)">Non</a><br />Si l\'édition à supprimer est l\'édition par défaut, n\'oubliez pas de redéfinir une édition par défaut à l\'album en question.';
        exit();
    }
}

// AFFICHER L'INTERFACE DE FUSION DES EDITIONS
elseif($act==""){

    // Creation d'un nouveau Template
    $t = new Template(BDO_DIR."public/templates");
    // fichier à utiliser
    $t->set_file(array(
    "tpBody" => "merge.editions.tpl",
    "tpBase" => "body.tpl"
    ));

    // REMPLISSAGE PARTIE GAUCHE
    if ((!is_null($source_id)) & ($source_id!='')){
        // récupère le nombre d'utilisateurs
        $nb_users1 = countUserBy("edition", $source_id);

        // récupère les données principales
        $query = q_edition("en.id_edition=".$DB->escape($source_id));

        $DB->query ($query);
        $DB->next_record();
        // Determine l'URL image
        if (is_null($DB->f("img_couv")) | ($DB->f("img_couv")=='')){
            $url_image1 = BDO_URL_IMAGE."couv/default.png";
        }else{
            $url_image1 = BDO_URL_IMAGE."couv/".$DB->f("img_couv");
        }
        $t->set_var (array(
        "EDITIONID1" => $DB->f("id_edition"),
        "EAN1" => $DB->f("ean"),
        "ISBN1" => $DB->f("isbn"),
        "TOMEID1" => $DB->f("id_tome"),
        "TITRE1" => htmlentities(stripslashes($DB->f("titre"))),
        "IDSERIE1" => $DB->f("id_serie"),
        "SERIE1" => htmlentities(stripslashes($DB->f("s_nom"))),
        "TOME1" => $DB->f("num_tome"),
        "IDGENRE1" => $DB->f("id_genre"),
        "GENRE1" => htmlentities(stripslashes($DB->f("libelle"))),
        "IDSCENAR1" => $DB->f("id_scenar"),
        "SCENAR1" => htmlentities(stripslashes($DB->f("scpseudo"))),
        "IDSCENARALT1" => $DB->f("id_scenar_alt"),
        "SCENARALT1" => htmlentities(stripslashes($DB->f("scapseudo"))),
        "IDEDIT1" => $DB->f("id_editeur"),
        "EDIT1" => htmlentities(stripslashes($DB->f("enom"))),
        "IDDESS1" => $DB->f("id_dessin"),
        "DESS1" => htmlentities(stripslashes($DB->f("depseudo"))),
        "IDDESSALT1" => $DB->f("id_dessin_alt"),
        "DESSALT1" => htmlentities(stripslashes($DB->f("deapseudo"))),
        "IDCOLOR1" => $DB->f("id_color"),
        "COLOR1" => htmlentities(stripslashes($DB->f("copseudo"))),
        "IDCOLORALT1" => $DB->f("id_color_alt"),
        "COLORALT1" => htmlentities(stripslashes($DB->f("coapseudo"))),
        "IDCOLL1" => $DB->f("id_collection"),
        "COLL1" => htmlentities(stripslashes($DB->f("cnom"))),
        "DTEPAR1" => $DB->f("dte_parution"),
        "URLIMAGE1" => $url_image1,
        "HISTOIRE1" => htmlentities(stripslashes($DB->f("histoire"))),
        "DESCRIPTED1" => htmlentities(stripslashes($DB->f("comment"))),
        "SOURCEID" => $DB->f("id_edition"),
        "NBUSERS1" => $nb_users1,
        ));
    }else{
        $t->set_var ("NBUSERS1","0");
    }

    //REMPLISSAGE DE LA PARTIE DROITE
    if ((!is_null($dest_id)) & ($dest_id!='')){
        // récupère le nombre d'utilisateurs
        $nb_users2 = countUserBy("edition", $dest_id);

        // récupère les données principales
        $query = q_edition("en.id_edition=".$DB->escape($dest_id));

        $DB->query($query);
        $DB->next_record();
        // Determine l'URL image
        if (is_null($DB->f("img_couv")) | ($DB->f("img_couv")=='')){
            $url_image2 = BDO_URL_IMAGE."couv/default.png";
        }else{
            $url_image2 = BDO_URL_IMAGE."couv/".$DB->f("img_couv");
        }
        $t->set_var (array(
        "EDITIONID2" => $DB->f("id_edition"),
        "EAN2" => $DB->f("ean"),
        "ISBN2" => $DB->f("isbn"),
        "TOMEID2" => $DB->f("id_tome"),
        "TITRE2" => htmlentities(stripslashes($DB->f("titre"))),
        "IDSERIE2" => $DB->f("id_serie"),
        "SERIE2" => htmlentities(stripslashes($DB->f("s_nom"))),
        "TOME2" => $DB->f("num_tome"),
        "IDGENRE2" => $DB->f("id_genre"),
        "GENRE2" => htmlentities(stripslashes($DB->f("libelle"))),
        "IDSCENAR2" => $DB->f("id_scenar"),
        "SCENAR2" => htmlentities(stripslashes($DB->f("scpseudo"))),
        "IDSCENARALT2" => $DB->f("id_scenar_alt"),
        "SCENARALT2" => htmlentities(stripslashes($DB->f("scapseudo"))),
        "IDEDIT2" => $DB->f("id_editeur"),
        "EDIT2" => htmlentities(stripslashes($DB->f("enom"))),
        "IDDESS2" => $DB->f("id_dessin"),
        "DESS2" => htmlentities(stripslashes($DB->f("depseudo"))),
        "IDDESSALT2" => $DB->f("id_dessin_alt"),
        "DESSALT2" => htmlentities(stripslashes($DB->f("deapseudo"))),
        "IDCOLOR2" => $DB->f("id_color"),
        "COLOR2" => htmlentities(stripslashes($DB->f("copseudo"))),
        "IDCOLORALT2" => $DB->f("id_color_alt"),
        "COLORALT2" => htmlentities(stripslashes($DB->f("coapseudo"))),
        "IDCOLL2" => $DB->f("id_collection"),
        "COLL2" => htmlentities(stripslashes($DB->f("cnom"))),
        "DTEPAR2" => $DB->f("dte_parution"),
        "URLIMAGE2" => $url_image2,
        "HISTOIRE2" => htmlentities(stripslashes($DB->f("histoire"))),
        "DESCRIPTED2" => htmlentities(stripslashes($DB->f("comment"))),
        "DESTID" => $DB->f("id_edition"),
        "NBUSERS2" => $nb_users2,
        ));
    }else{
        $t->set_var ("NBUSERS2","0");
    }
    // Message d'erreur
    if (!is_null($error)){
        $t->set_var ("ERRORMESSAGE",$error_msg[$error]);
    }
    // variables mises à jour dans tous les cas
    $t->set_var (array(
    "URLEDITION1" => BDO_URL."admin/admineditions.php?edition_id=".$source_id,
    "URLEDITION2" => BDO_URL."admin/admineditions.php?edition_id=".$dest_id,
    "URLREFRESH" => BDO_URL."admin/mergeeditions.php",
    "URLECHANGE" => BDO_URL."admin/mergeeditions.php?source_id=$dest_id&dest_id=$source_id",
    "URLFUSION" => BDO_URL."admin/mergeeditions.php?act=merge&source_id=$source_id&dest_id=$dest_id"
    ));
    // assigne la barre de login
    $t->set_var (array(
    "LOGINBARRE" => GetIdentificationBar(),
    "MENUBARRE" => admin_menu(),
    "URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
    "URLSITEFORUM" => BDO_URL_FORUM
    ));
    $t->parse("BODY","tpBody");
    $t->pparse("MyFinalOutput","tpBase");
}
