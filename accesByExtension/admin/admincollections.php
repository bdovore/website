<?php



minAccessLevel(1);

// Mettre à jour les informations
if ($act=="update"){

	$query = "
	UPDATE bd_collection SET
		`nom` = '".$DB->escape($_POST['txtNomColl'])."',
		`id_editeur` = ".$DB->escape($_POST['txtEditeurId'])."
	WHERE
		`id_collection`=".$DB->escape($_POST["txtIdColl"])."
	";
	$DB->query($query);
	echo '<META http-equiv="refresh" content="1; URL=javascript:history.go(-1)">'."Mise à jour effectuée";
}

// EFFACEMENT D'UNE COLLECTION
elseif($act=="delete")
{
	if ($conf == "ok")
	{
		$query = "DELETE FROM bd_collection WHERE id_collection=".$DB->escape($collec_id);
		$DB->query ($query);
		$redirection = BDO_URL."admin/index.php";
		echo '<META http-equiv="refresh" content="2; URL='.$redirection.'">La collection a été effacée de la base.';
		exit();
	}
	else
	{
		// Affiche la demande de confirmation
		echo 'Etes-vous s&ucirc;r de vouloir effacer la collection  n. '.$collec_id.' ? <a href="'.BDO_URL.'admin/admincollections.php?act=delete&conf=ok&collec_id='.$collec_id.'">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
		exit();
	}
}
// AFFICHE UN FORMULAIRE VIDE
elseif($act=="new"){
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "admin.collec.tpl",
	"tpBase" => "body.tpl"
	));
	if (!is_null($editeur_id)){// Un éditeur a été passé dans l'URL

		$query = "SELECT id_editeur, nom FROM bd_editeur WHERE id_editeur = ".$DB->escape($editeur_id);
		$DB->query ($query);
		$DB->next_record();

		$t->set_var (array(
		"IDEDITEUR" => $DB->f("id_editeur"),
		"EDITEUR" => htmlentities(stripslashes($DB->f("nom"))),
		));
	}

	$t->set_var (array(
	"NBCOLALB" => "0",
	"URLDELETE" => "javascript:alert('Désactivé');",
	"ACTIONNAME" => "Enregistrer",
	"URLEDITEDIT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
	"URLACTION" => BDO_URL."admin/admincollections.php?act=append"
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

// INSERE UN NOUVEL ALBUM DANS LA BASE
elseif($act=="append"){

	$query = "INSERT INTO `bd_collection` (`NOM`, `ID_EDITEUR`)";
	$query .= " VALUES ('".$DB->escape($_POST['txtNomColl'])."', ";
	$query .= $_POST['txtEditeurId'].");";
	$DB->query ($query);
	$lid= mysql_insert_id();
	echo GetMetaTag(2,"La collection a été ajouté",(BDO_URL."admin/admincollections.php?collec_id=".$lid));
}

// AFFICHER UNE COLLECTION
elseif($act==""){

	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "admin.collec.tpl",
	"tpBase" => "body.tpl"
	));

	// on compte le nombre d'albums dans la collection
	$query = "SELECT count(distinct(bd_tome.ID_TOME)) as numofalb
	FROM bd_tome
	INNER JOIN bd_edition ON bd_tome.ID_EDITION=bd_edition.ID_EDITION
	WHERE bd_edition.id_collection=".$DB->escape($collec_id);
	$DB->query ($query);
	$DB->next_record();
	$nb_albums = $DB->f("numofalb");

	//récupère les données
	$query = "
	SELECT
		c.id_collection,
		c.nom,
		c.id_editeur,
		e.nom as editeurname
	FROM
		bd_collection c
		INNER JOIN bd_editeur e ON c.id_editeur = e.id_editeur
	WHERE
		c.id_collection=".$DB->escape($collec_id);

	$DB->query ($query);
	$DB->next_record();
	$t->set_var (array(
	"IDCOLL" => $DB->f("id_collection"),
	"NOM" => htmlentities(stripslashes($DB->f("nom"))),
	"IDEDITEUR" => $DB->f("id_editeur"),
	"EDITEUR" => htmlentities(stripslashes($DB->f("editeurname"))),
	"NBCOLALB" => $nb_albums,
	"URLDELETE" => BDO_URL."admin/admincollections.php?act=delete&collec_id=".$DB->f("id_collection"),
	"ACTIONNAME" => "Valider les Modifications",
	"URLEDITEDIT" => BDO_URL."admin/adminediteurs.php?editeur_id=".$DB->f("id_editeur"),
	"URLACTION" => BDO_URL."admin/admincollections.php?act=update"
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
