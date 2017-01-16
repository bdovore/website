<?php



minAccessLevel(1);


$error_msg[0] = "Genre à supprimer non défini";
$error_msg[1] = "Genre à garder non défini";
$error_msg[2] = "Genre à garder et à supprimer identiques";


// Mettre à jour les informations


if ($act=="merge")
{
    // vérifie que source_id et dest_id ont été defini
    if ((is_null($dest_id)) | ($dest_id==""))
    {header("Location:".BDO_URL."admin/mergeediteurs.php?source_id=$source_id&error=1");
    }
    if ((is_null($source_id)) | ($source_id==""))
    {header("Location:".BDO_URL."admin/mergeediteurs.php?dest_id=$dest_id&error=0");
    }

    if ($source_id==$dest_id)
    {header("Location:".BDO_URL."admin/mergeediteurs.php?source_id=$source_id&dest_id=$dest_id&error=2");
    }

    if ($conf=="ok")
    {

        // Met à jour l'information contenue dans la base de données
        $query = "UPDATE bd_tome SET id_genre = ".$DB->escape($dest_id)." where id_genre = ".$DB->escape($source_id);
        $DB->query($query);
        echo "Nombre de records modifiées dans la table bd_tome : ".$DB->affected_rows()."<br>";

        // Met à jour l'information contenue dans la base de données
        $query = "UPDATE bd_serie SET id_genre = ".$DB->escape($dest_id)." where id_genre = ".$DB->escape($source_id);
        $DB->query($query);
        echo "Nombre de records modifiées dans la table bd_serie : ".$DB->affected_rows()."<br>";

        // Supprime l'ancien genre
        $query = "DELETE FROM bd_genre WHERE id_genre = ".$DB->escape($source_id);
        $DB->query($query);
        echo "Nombre de records modifiées dans la table bd_genre : ".$DB->affected_rows()."<br>";

        $redirection = BDO_URL."admin/index.php";
        echo '<META http-equiv="refresh" content="4; URL='.$redirection.'">Les genres ont été fusionnés.';

    }else{
        // Demande de confirmation
        echo 'Etes-vous s&ucirc;r de vouloir fusionner les genres n°'.$source_id.' et '.$dest_id.'? <a href="'.BDO_URL.'admin/mergegenres.php?act=merge&conf=ok&source_id='.$source_id.'&dest_id='.$dest_id.'">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
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
    "tpBody" => "merge.genres.tpl",
    "tpBase" => "body.tpl"));

    // REMPLISSAGE PARTIE GAUCHE
    if ((!is_null($source_id)) & ($source_id!=''))
    {
        // récupère le nombre d'utilisateurs
        $nb_users1 = countUserBy("genre",$source_id);

        // récupère les données principales
        $query= "select id_genre, libelle from bd_genre where id_genre = ".$DB->escape($source_id);
        $DB->query ($query);
        $DB->next_record();
        $t->set_var (array
        ("GENREID1" => $DB->f("id_genre"),
        "URLEDITEDIT1" => BDO_URL."admin/admingenres.php?genre_id=".$DB->f("id_genre"),
        "GENRE1" => htmlentities(stripslashes($DB->f("libelle"))),
        "SOURCEID" => $DB->f("id_genre"),
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
        $nb_users2 = countUserBy("genre",$dest_id);

        // récupère les données principales
        $query= "select id_genre, libelle from bd_genre where id_genre = ".$DB->escape($dest_id);
        $DB->query ($query);
        $DB->next_record();

        $t->set_var (array
        ("GENREID2" => $DB->f("id_genre"),
        "URLEDITEDIT2" => BDO_URL."admin/admingenres.php?genre_id=".$DB->f("id_genre"),
        "GENRE2" => htmlentities(stripslashes($DB->f("libelle"))),
        "DESTID" => $DB->f("id_genre"),
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
    ("URLREFRESH" => BDO_URL."admin/mergegenres.php",
    "URLECHANGE" => BDO_URL."admin/mergegenres.php?source_id=$dest_id&dest_id=$source_id",
    "URLFUSION" => BDO_URL."admin/mergegenres.php?act=merge&source_id=$source_id&dest_id=$dest_id"
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
