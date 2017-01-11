<?php



minAccessLevel(1);

// Fusionne les albums et transfère les éditions cochées
if($act=="update"){
    $old_idtome = $_POST["txtTomeId"];
    $new_idtome = $_POST["txtTome2Id"];
    if ($_POST["txtTome2Id"] == ""){
        echo GetMetaTag(2, "L'ID de l'album à conserver n'a pas été précisé.",(BDO_URL."admin/adminalbums.php?alb_id=".$old_idtome));
        exit();
    }
    $nb = 0;

    // Récupère la valeur de l'album à mettre à jour
    $query = "
    SELECT
        t.id_serie,
        c.id_editeur,
        en.id_collection,
        t.id_scenar,
        t.id_dessin,
        s.id_genre,
        en.id_edition
    FROM
        bd_tome t
        INNER JOIN bd_edition en ON en.id_edition = t.id_edition
        INNER JOIN bd_serie s ON s.id_serie = t.id_serie
        INNER JOIN bd_collection c ON c.id_collection = en.id_collection
    WHERE
        t.id_tome = ".$DB->escape($new_idtome);

    $DB->query($query);
    $DB->next_record();
    $id_serie = $DB->f("id_serie");
    $id_editeur = $DB->f("id_editeur");
    $id_collection = $DB->f("id_collection");
    $id_scenar = $DB->f("id_scenar");
    $id_dessin = $DB->f("id_dessin");
    $id_genre = $DB->f("id_genre");
    $id_edition = $DB->f("id_edition");

    foreach ($chkEdition as $idedition){
        // Modifie les couvertures
        $old_filename = $txtCouv[$idedition];
        if ($old_filename == ""){
            $new_filename = "";
        }else{
            $new_filename = "CV-".sprintf("%06d",$new_idtome)."-".sprintf("%06d",$idedition).substr($old_filename,-4);
            rename(BDO_DIR."images/couv/".$old_filename, BDO_DIR."images/couv/".$new_filename);
        }

        // Transfère les éditions sélectionnées sous le nouvel album
        $query = "
        UPDATE bd_edition SET
            id_tome = ".$DB->escape($new_idtome).",
            img_couv = ".sqlise($new_filename,'text_simple')."
        WHERE id_edition =". $idedition ;


        $DB->query($query);
        echo "Nombre de records modifiées dans la table bd_edition : ".$DB->affected_rows()."<br />";

    }

    // Met à jour les commentaires
    $DB->query("UPDATE IGNORE users_comment SET `id_tome` = ".$DB->escape($new_idtome)." WHERE `id_tome`=".$DB->escape($old_idtome));
    echo "Nombre de records modifiées dans la table users_comment : ".$DB->affected_rows()."<br />";

    // Met à jour les carres
    $DB->query("UPDATE IGNORE users_list_carre SET `id_tome` = ".$DB->escape($new_idtome) ." WHERE `id_tome`=".$DB->escape($old_idtome));
    echo "Nombre de records modifiées dans la table users_list_carre : ".$DB->affected_rows()."<br />";

    // Met à jour les exclusions
    $DB->query("UPDATE IGNORE users_exclusions SET `id_tome` = ".$DB->escape($new_idtome) ." WHERE `id_tome`=".$DB->escape($old_idtome));
    echo "Nombre de records modifiées dans la table users_exclusions : ".$DB->affected_rows()."<br />";

    // Efface les éditions et les couvertures correspondantes
    $query = "SELECT id_edition, img_couv FROM bd_edition WHERE id_tome=".$DB->escape($old_idtome);
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

    // vide la table bd_edition
    $DB->query ("DELETE FROM bd_edition WHERE id_tome=". $DB->escape($old_idtome));
    echo 'Référence(s) à l\'album supprimée(s) dans la table bd_edition<br />';

    $DB->query ("DELETE FROM bd_tome WHERE id_tome=". $DB->escape($old_idtome));
    echo 'Référence(s) à l\'album supprimée(s) dans la table bd_tome<br />';

    $DB->query("DELETE users_album.* FROM users_album INNER JOIN bd_edition USING(id_edition)
    WHERE bd_edition.`id_tome`=".$DB->escape($old_idtome));
    echo "Nombre de records supprimés dans la table users_album : ".$DB->affected_rows()."<br />";

    echo GetMetaTag(2,"Fusion effectuée.",(BDO_URL."admin/adminalbums.php?alb_id=".$DB->escape($new_idtome)));
}

// AFFICHER UN ALBUM
elseif($act==""){

    // récupère les données principales
    $query = q_tome("t.id_tome = ".$DB->escape($alb_id));
    $DB->query ($query);
    $DB->next_record();

    // Détermine l'affichage des infos
    $scenaristes1 = ($DB->f("id_scenar_alt") == 0) ? stripslashes($DB->f("scpseudo")) : stripslashes($DB->f("scpseudo"))." / ".stripslashes($DB->f("scapseudo"));
    $dessinateurs1 = ($DB->f("id_dessin_alt") == 0) ? stripslashes($DB->f("depseudo")) : stripslashes($DB->f("scdeeudo"))." / ".stripslashes($DB->f("deapseudo"));
    $coloristes1 = ($DB->f("id_color_alt") == 0) ? stripslashes($DB->f("copseudo")) : stripslashes($DB->f("codeeudo"))." / ".stripslashes($DB->f("coapseudo"));
    $edcollec1 = ($DB->f("cnom") == "<N/A>") ? stripslashes($DB->f("enom")) : stripslashes($DB->f("enom"))." / ".stripslashes($DB->f("cnom"));
    // Creation d'un nouveau Template
    $t = new Template(BDO_DIR."public/templates");
    // fichier à utiliser
    $t->set_file(array(
    "tpBody" => "admin.delete.album.tpl",
    "tpBase" => "body.tpl"
    ));
    $t->set_var (array(
    "IDTOME" => $DB->f("id_tome"),
    "TITRE" => stripslashes($DB->f("titre")),
    "TOME" => $DB->f("num_tome"),
    "SERIE" => stripslashes($DB->f("s_nom")),
    "SCENARISTES" => $scenaristes1,
    "DESSINATEURS" => $dessinateurs1,
    "COLORISTES" => $coloristes1,
    "EDCOLLEC" => $edcollec1,
    ));

    if ($report_id != "")
    {
        // récupère les données sur le nouveau tome
        $query = q_tome("t.id_tome = ".$DB->escape($report_id));
        $DB->query ($query);
        $DB->next_record();

        // Détermine l'affichage des infos
        $scenaristes2 = ($DB->f("id_scenar_alt") == 0) ? stripslashes($DB->f("scpseudo")) : stripslashes($DB->f("scpseudo"))." / ".stripslashes($DB->f("scapseudo"));
        $dessinateurs2 = ($DB->f("id_dessin_alt") == 0) ? stripslashes($DB->f("depseudo")) : stripslashes($DB->f("scdeeudo"))." / ".stripslashes($DB->f("deapseudo"));
        $coloristes2 = ($DB->f("id_color_alt") == 0) ? stripslashes($DB->f("copseudo")) : stripslashes($DB->f("codeeudo"))." / ".stripslashes($DB->f("coapseudo"));
        $edcollec2 = ($DB->f("cnom") == "<N/A>") ? stripslashes($DB->f("enom")) : stripslashes($DB->f("enom"))." / ".stripslashes($DB->f("cnom"));

        $t->set_var (array(
        "IDTOME2" => $DB->f("id_tome"),
        "TITRE2" => stripslashes($DB->f("titre")),
        "TOME2" => $DB->f("num_tome"),
        "SERIE2" => stripslashes($DB->f("s_nom")),
        "SCENARISTES2" => $scenaristes2,
        "DESSINATEURS2" => $dessinateurs2,
        "COLORISTES2" => $coloristes2,
        "EDCOLLEC2" => $edcollec2,
        ));

        // Affiche les informations relatives aux différentes éditions
        $query = q_AllEditionByIdTome($DB->escape($report_id));
        $DB->query ($query);

        // on déclare le block à utiliser
        $t->set_block('tpBody','Edition2Block','E2Block');

        //Affiche les différentes éditions
        while ($DB->next_record()){
            // Determine l'URL image
            if (is_null($DB->f("img_couv")) | ($DB->f("img_couv")=='')){
                $url_image = BDO_URL."images/couv/default.png";
                $url_couv = "";
            }else{
                $url_image = BDO_URL."images/couv/".$DB->f("img_couv");
                $url_couv = $DB->f("img_couv");
            }
            // Affiche le résultat
            $t->set_var (array(
            "EDITEUR2" => stripslashes($DB->f("enom")),
            "COLLECTION2" => htmlentities(stripslashes($DB->f("cnom"))),
            "DTPAR2" => $DB->f("dte_parution"),
            "URLIMAGE2"=> $url_image,
            ));
            $t->parse ("E2Block", "Edition2Block",true);
        }
        $nb_editions2 = $DB->num_rows();
    }

    // Affiche les informations relatives aux différentes éditions
    $query = q_AllEditionByIdTome($DB->escape($alb_id));
    $DB->query ($query);

    // on déclare le block à utiliser
    $t->set_block('tpBody','EditionBlock','EBlock');

    //Affiche les différentes éditions
    while ($DB->next_record()){
        // Determine l'URL image
        if (is_null($DB->f("img_couv")) | ($DB->f("img_couv")=='')){
            $url_image = BDO_URL."images/couv/default.png";
            $url_couv = "";
        }else{
            $url_image = BDO_URL."images/couv/".$DB->f("img_couv");
            $url_couv = $DB->f("img_couv");
        }
        // Affiche le résultat
        $t->set_var (array(
        "EDITEUR" => stripslashes($DB->f("enom")),
        "COLLECTION" => htmlentities(stripslashes($DB->f("cnom"))),
        "IMGTT" => (($DB->f("flg_tt") == 'O') ? BDO_URL_IMAGE.'site/ic_TT.gif' : BDO_URL_IMAGE.'site/ic_TT_nb.gif'),
        "DTPAR" => $DB->f("dte_parution"),
        "IDEDITION" => $DB->f("id_edition"),
        "URLEDITEDITION" => BDO_URL."admin/admineditions.php?edition_id=".$DB->f("id_edition"),
        "URLIMAGE"=> $url_image,
        "URLCOUV" => $url_couv
        ));
        $t->parse ("EBlock", "EditionBlock",true);
    }

    $t->set_var (array(
    "NBEDITIONS" => $DB->num_rows(),
    "NBEDITIONS2" => $nb_editions2,
    "REFRESHPAGE" => "admin.delete.php?alb_id=".$alb_id,
    "URLRETOURFICHE" => BDO_URL."admin/adminalbums.php?alb_id=".$DB->f("id_tome"),
    "ACTIONNAME" => "Transférer les éditions et effacer l'album",
    "URLACTION" => BDO_URL."admin/admin.delete.php?act=update"
    ));

    // assigne la barre de login
    $t->set_var (array(
    "LOGINBARRE" => GetIdentificationBar(),
    "MENUBARRE" => admin_menu(),
    "URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
    ));
    $t->parse("BODY","tpBody");
    $t->pparse("MyFinalOutput","tpBase");
}
