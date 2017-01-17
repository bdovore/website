<?php



include (BDO_DIR."inc/function.amazon.inc.php");

minAccessLevel(1);


// Mettre à jour les informations
if ($act=="update"){
    $nb = 0;

    foreach ($alb_id as $idtome){
        // Selection le numéro de l'edition en cours
        $query = "
        SELECT
            en.id_edition,
            en.img_couv
        FROM
            bd_tome t
            INNER JOIN bd_edition en ON t.ID_EDITION=en.ID_EDITION
        WHERE
            t.id_tome = ".$DB->escape($idtome)."
        ";
        $DB->query ($query);
        $DB->next_record();
        $idedition = $DB->f("id_edition");

        // Efface la couverture actuelle
        $oldfile = $DB->f("img_couv");
        @unlink (BDO_DIR."images/couv/".$oldfile);

        // détermine le nouveau nom
        $newfilename = "CV-".sprintf("%06d",$idtome)."-".sprintf("%06d",$idedition);

        // Copie le fichier dans le répertoire temporaire
        $new_filename = get_img_from_url($url_amz[$idtome],BDO_DIR."images/tmp/",$newfilename);

        // Déplace le fichier dans le répertoire couv
        rename (BDO_DIR."images/tmp/".$new_filename, BDO_DIR."images/couv/".$new_filename);

        // Met à jour bd_edition
        $query = "UPDATE bd_edition SET img_couv = ".sqlise($new_filename,'text')." WHERE id_edition = ".$DB->escape($idedition);
        $DB->query ($query);

        $nb++;
    }
    echo GetMetaTag(2,"$nb albums ont été traités.",(BDO_URL."admin/mu_couv.php?serie=".$serie));
}

// AFFICHER UNE FICHE SERIE
elseif($act==""){
    // Creation d'un nouveau Template
    $t = new Template(BDO_DIR."public/templates");
    // fichier à utiliser
    $t->set_file(array(
    "tpBody" => "admin.mu.couv.tpl",
    "tpBase" => "body.tpl"
    ));

    $t->set_var (array(
    "LOGINBARRE" => GetIdentificationBar(),
    "MENUBARRE" => admin_menu(),
    "URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
    ));

    if ($serie != ""){

        // récupère le infos liées à la série
        $query = "SELECT nom FROM bd_serie WHERE id_serie = ".$DB->escape($serie);
        $DB->query ($query);
        $DB->next_record();
        $t->set_var (array(
        "SERIE" => stripslashes($DB->f("nom")),
        "IDSERIE" => $serie,
        "NOUVTITRE" => stripslashes($DB->f("nom")).", Tome #tome#"
        ));

        // Affiche les couvertures
        $query = "
        SELECT
            t.id_tome,
            t.num_tome,
            t.titre,
            en.img_couv,
            en.isbn
        FROM
            bd_tome t
            INNER JOIN bd_edition en ON t.id_edition=en.id_edition
        WHERE
        t.id_serie = ".$DB->escape($serie)."
        ORDER BY t.num_tome";
        $DB->query ($query);
        // on déclare le block à utiliser
        $t->set_block('tpBody','AlbumsBlock','ABlock');
        // Stocke les résultats retournés dans un tableau pour eviter la deconnexion
        $compteur = 0;
        while ($DB->next_record()){
            $result_query[0][$compteur] = $DB->f("id_tome");
            $result_query[1][$compteur] = $DB->f("num_tome");
            $result_query[2][$compteur] = $DB->f("titre");
            $result_query[3][$compteur] = $DB->f("img_couv");
            $result_query[4][$compteur] = $DB->f("isbn");
            $compteur++;
        }

        //Affiche les différentes éléments
        for ($i=0; $i<$compteur; $i++){
            // Vérifie l'existence d'une image chez bdovore
            if (is_null($result_query[3][$i]) | ($result_query[3][$i]=='')){
                $url_image = BDO_URL_IMAGE."couv/default.png";
                $dimension="";
                $poids="";
            }else{
                $url_image = BDO_URL_IMAGE."couv/".$result_query[3][$i];
                $imageproperties = getimagesize(BDO_DIR."images/couv/".$result_query[3][$i]);
                $imagetype = $imageproperties[2];
                $imagelargeur = $imageproperties[0];
                $imagehauteur = $imageproperties[1];
                $dimension = $imagelargeur."x".$imagehauteur;
                $poids = round(filesize(BDO_DIR."images/couv/".$result_query[3][$i])/1024,2);
                $poids .="Ko";
                $imagename = $result_query[3][$i];
            }
            // Vérifie l'existence d'une couv chez amazon
            $url_page_amazon = BDO_PROTOCOL."://www.amazon.fr/exec/obidos/ASIN/".$result_query[4][$i];
            $url_img_amazon = get_couv_url($url_page_amazon,$result_query[4][$i]);
            if ($url_img_amazon == "") $url_img_amazon = BDO_URL."images/couv/default.png";

            // Affiche les données
            $t->set_var (array(
            "TOME" => $result_query[1][$i],
            "TITRE" => stripslashes($result_query[2][$i]),
            "IDTOME" =>  $result_query[0][$i],
            "URLIMAGE" => $url_image,
            "DIMENSION" => $dimension,
            "SIZE" => $poids,
            "IMAGENAME" => $imagename,
            "URLPAGEAMAZON" => $url_page_amazon,
            "URLIMGAMAZON" => $url_img_amazon
            ));
            $t->parse ("ABlock", "AlbumsBlock",true);
        }
    }
    $t->set_var (array(
    "ACTIONNAME" => "Mettre à Jour",
    "URLACTION" => BDO_URL."admin/mu_couv.php?act=update&serie=".$serie,
    "URLREFRESH" => BDO_URL."admin/mu_couv.php",
    "URLEDITSERIE" => BDO_URL."admin/adminseries.php?serie_id=".$serie
    ));

    // assigne la barre de login
    $t->parse("BODY","tpBody");
    $t->pparse("MyFinalOutput","tpBase");
}
