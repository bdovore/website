<?php



minAccessLevel(1);


$error_msg[0] = "Album à supprimer non défini";
$error_msg[1] = "Album à garder non défini";
$error_msg[2] = "Album à garder et album à supprimer identiques";


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
		$query = "UPDATE bd_edition SET id_editeur = ".$DB->escape($dest_id)." where id_editeur = ".$DB->escape($source_id);
		$DB->query($query);
		echo "Nombre de records modifiées dans la table bd_tome : ".$DB->affected_rows()."<br />";

		// Met à jour la table collection
		$query = "UPDATE bd_collection SET id_editeur = ".$DB->escape($dest_id)." where id_editeur = ".$DB->escape($source_id);
		$DB->query($query);
		echo "Nombre de records modifiées dans la table bd_collection : ".$DB->affected_rows()."<br />";

		// Supprime l'ancien editeur
		$query = "DELETE FROM bd_editeur WHERE id_editeur = $source_id;";
		$DB->query($query);
		echo "Nombre de records modifiées dans la table bd_editeur : ".$DB->affected_rows()."<br />";

		$redirection = BDO_URL."admin/index.php";
		echo '<META http-equiv="refresh" content="4; URL='.$redirection.'">Les éditeurs ont été fusionnés.';

	}else{
		// Demande de confirmation

		echo 'Etes-vous sûr de vouloir fusionner les editeurs n°'.$source_id.' et '.$dest_id.'? <a href="'.BDO_URL.'admin/mergeediteurs.php?act=merge&conf=ok&source_id='.$source_id.'&dest_id='.$dest_id.'">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
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
	"tpBody" => "merge.editeurs.tpl",
	"tpBase" => "body.tpl"));

	// REMPLISSAGE PARTIE GAUCHE
	if ((!is_null($source_id)) & ($source_id!=''))
	{
		// récupère le nombre d'utilisateurs
		$nb_users1 = countUserBy("editeur",$source_id);		
		
		// récupère les données principales
		$query= "select id_editeur, nom, url_site from bd_editeur where id_editeur = ".$DB->escape($source_id);
		$DB->query ($query);
		$DB->next_record();
		$t->set_var (array
		("EDITEURID1" => $DB->f("id_editeur"),
		"URLEDITEDIT1" => BDO_URL."admin/adminediteurs.php?editeur_id=".$DB->f("id_editeur"),
		"EDITEUR1" => htmlentities(stripslashes($DB->f("nom"))),
		"URLSITE1" => $DB->f("url_site"),
		"SOURCEID" => $DB->f("id_editeur"),
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
		$nb_users2 = countUserBy("editeur",$dest_id);
		
		// récupère les données principales
		$query= "select id_editeur, nom, url_site from bd_editeur where id_editeur =".$DB->escape($dest_id);
		$DB->query ($query);
		$DB->next_record();

		$t->set_var (array
		("EDITEURID2" => $DB->f("id_editeur"),
		"URLEDITEDIT2" => BDO_URL."admin/adminediteurs.php?editeur_id=".$DB->f("id_editeur"),
		"EDITEUR2" => htmlentities(stripslashes($DB->f("nom"))),
		"URLSITE2" => $DB->f("url_site"),
		"DESTID" => $DB->f("id_editeur"),
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
	("URLREFRESH" => BDO_URL."admin/mergeediteurs.php",
	"URLECHANGE" => BDO_URL."admin/mergeediteurs.php?source_id=$dest_id&dest_id=$source_id",
	"URLFUSION" => BDO_URL."admin/mergeediteurs.php?act=merge&source_id=$source_id&dest_id=$dest_id"
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
