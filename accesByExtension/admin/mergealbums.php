<?php



minAccessLevel(1);

$error_msg[0] = "Album � supprimer non d�fini";
$error_msg[1] = "Album � garder non d�fini";
$error_msg[2] = "Album � garder et album � supprimer identiques";

// Fusionne les albums
if ($act=="merge"){
    // v�rifie que source_id et dest_id ont �t� definis
    if ((is_null($dest_id)) | ($dest_id=="")){
        header("Location:".BDO_URL."admin/mergealbums.php?source_id=$source_id&error=1");
    }
    if ((is_null($source_id)) | ($source_id=="")){
        header("Location:".BDO_URL."admin/mergealbums.php?dest_id=$dest_id&error=0");
    }
    if ($source_id==$dest_id){
        header("Location:".BDO_URL."admin/mergealbums.php?source_id=$source_id&dest_id=$dest_id&error=2");
    }
    if ($conf=="ok"){

        // R�cup�re la valeur de l'album � mettre � jour
        $query = q_editionDefaultByIdTome($DB->escape($dest_id));

        $DB->query($query);
        $DB->next_record();
        $id_serie = $DB->f("id_serie");
        $id_editeur = $DB->f("id_editeur");
        $id_collection = $DB->f("id_collection");
        $id_scenar = $DB->f("id_scenar");
        $id_dessin = $DB->f("id_dessin");
        $id_genre = $DB->f("id_genre");
        $id_edition = $DB->f("id_edition");

        // Met � jour les commentaires
        $DB->query("UPDATE IGNORE users_comment SET `id_tome` = ".$DB->escape($dest_id)." WHERE `id_tome`=".$DB->escape($source_id));
        echo "Nombre de records modifi�es dans la table users_comment : ".$DB->affected_rows()."<br />";

        // Met � jour les carres
        $DB->query("UPDATE IGNORE users_list_carre SET `id_tome` = ".$DB->escape($dest_id) ." WHERE `id_tome`=".$DB->escape($source_id));
        echo "Nombre de records modifi�es dans la table users_list_carre : ".$DB->affected_rows()."<br />";

        // Met � jour les exclusions
        $DB->query("UPDATE IGNORE users_exclusions SET `id_tome` = ".$DB->escape($dest_id) ." WHERE `id_tome`=".$DB->escape($source_id));
        echo "Nombre de records modifi�es dans la table users_exclusions : ".$DB->affected_rows()."<br />";

        // Fusionne les albums (restera ensuite � fusionner les �ditions redondantes, cf. mergeeditions)
        $DB->query("UPDATE IGNORE bd_edition SET `id_tome` = ".$DB->escape($dest_id) ." WHERE `id_tome`=".$DB->escape($source_id));
        echo "Nombre de records modifi�es dans la table bd_edition : ".$DB->affected_rows()."<br />";

        // Efface les �ditions et les couvertures correspondantes
        //$query = "SELECT id_edition, img_couv FROM bd_edition WHERE id_tome=" . $DB->escape($source_id);
        //$DB->query ($query);
        //while ($DB->next_record()){
        //  $DB->f("img_couv");
        //  if ($DB->f("img_couv") != ''){
        //      $filename = $DB->f("img_couv");
        //      if (file_exists(BDO_DIR."images/couv/$filename")){
        //          @unlink(BDO_DIR."images/couv/$filename");
        //          echo "Couverture effac�e pour l'�dition N�".$DB->f("id_edition")."<br />";
        //      }
        //  }
        //}

        //$DB->query("DELETE users_album.* FROM users_album INNER JOIN bd_edition USING(id_edition)
        //WHERE bd_edition.`id_tome`=".$DB->escape($source_id));
        //echo "Nombre de records supprim�s dans la table users_album : ".$DB->affected_rows()."<br />";

        // vide la table bd_edition
        $query = "DELETE FROM bd_edition WHERE id_tome =" . $DB->escape($source_id);
        $DB->query ($query);
        echo 'R�f�rence(s) � l\'album supprim�e(s) dans la table bd_edition<br />';

        $query = "DELETE FROM bd_tome WHERE id_tome=" . $DB->escape($source_id);
        $DB->query ($query);
        echo 'R�f�rence(s) � l\'album supprim�e(s) dans la table bd_tome<br />';


        echo '<META http-equiv="refresh" content="4; URL='.BDO_URL.'admin/index.php">Les albums ont �t� fusionn�s.';
    }
    else
    {
        // Demande de confirmation
        echo 'Etes-vous s&ucirc;r de vouloir fusionner les albums n�'.$source_id.' et '.$dest_id.'? <a href="'.BDO_URL.'admin/mergealbums.php?act=merge&conf=ok&source_id='.$source_id.'&dest_id='.$dest_id.'">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
        exit();
    }
}

// AFFICHER UN ALBUM
elseif($act==""){

    // Creation d'un nouveau Template
    $t = new Template(BDO_DIR."public/templates");
    // fichier � utiliser
    $t->set_file(array(
    "tpBody" => "merge.albums.tpl",
    "tpBase" => "body.tpl"
    ));

    // REMPLISSAGE PARTIE GAUCHE
    if ((!is_null($source_id)) & ($source_id!='')){
        // r�cup�re le nombre d'utilisateurs
        $nb_comments1 = countUserBy("tomeComment",$source_id);

        // r�cup�re les donn�es principales
        $query = q_tome("t.id_tome = ".$DB->escape($source_id));

        $DB->query ($query);
        $DB->next_record();
        // Determine l'URL image
        if (is_null($DB->f("img_couv")) | ($DB->f("img_couv")=='')){
            $url_image1 = BDO_URL_IMAGE."couv/default.png";
        }else{
            $url_image1 = BDO_URL_IMAGE."couv/".$DB->f("img_couv");
        }
        $t->set_var (array(
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
        "SOURCEID" => $DB->f("id_tome"),
        "NBUSERS1" => $nb_users1,
        "NBCOMMENT1" => $nb_comments1
        ));
    }else{
        $t->set_var (array(
        "NBUSERS1" => "0",
        "NBCOMMENT1" => "0"
        ));
    }

    //REMPLISSAGE DE LA PARTIE DROITE
    if ((!is_null($dest_id)) & ($dest_id!='')){
        // r�cup�re le nombre d'utilisateurs
        $nb_users2 = countUserBy("tome",$dest_id);

        // r�cup�re le nombre de commentaires
        $nb_comments2 = countUserBy("tomeComment",$dest_id);

        // r�cup�re les donn�es principales
        $query = q_tome("t.id_tome = ".$DB->escape($dest_id));

        $DB->query ($query);
        $DB->next_record();
        // Determine l'URL image
        if (is_null($DB->f("img_couv")) | ($DB->f("img_couv")=='')){
            $url_image2 = BDO_URL_IMAGE."couv/default.png";
        }else{
            $url_image2 = BDO_URL_IMAGE."couv/".$DB->f("img_couv");
        }
        $t->set_var (array(
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
        "DESTID" => $DB->f("id_tome"),
        "NBUSERS2" => $nb_users2,
        "NBCOMMENT2" => $nb_comments2
        ));
    }else{
        $t->set_var (array(
        "NBUSERS2" => "0",
        "NBCOMMENT2" => "0"
        ));
    }
    // Message d'erreur
    if (!is_null($error)){
        $t->set_var ("ERRORMESSAGE",$error_msg[$error]);
    }
    // variables mises� jour dans tous les cas
    $t->set_var (array(
    "URLREFRESH" => BDO_URL."admin/mergealbums.php",
    "URLECHANGE" => BDO_URL."admin/mergealbums.php?source_id=$dest_id&dest_id=$source_id",
    "URLFUSION" => BDO_URL."admin/mergealbums.php?act=merge&source_id=$source_id&dest_id=$dest_id"
    ));
    // assigne la barre de login
    $t->set_var (array(

    "URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
    ));
    $t->parse("BODY","tpBody");
    $t->pparse("MyFinalOutput","tpBase");
}
