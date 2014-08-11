<?php



minAccessLevel(1);

// Mettre à jour les informations
if ($act=="update"){

	$query = "UPDATE bd_genre SET
	`libelle` = '".$DB->escape($_POST['txtGenre'])."'
	WHERE (`id_genre`=".$DB->escape($_POST["txtIdGenre"]).");";
	$DB->query($query);
	echo '<META http-equiv="refresh" content="1; URL=javascript:history.go(-1)">'."Mise à jour effectuée";
}

// EFFACEMENT D'UN GENRE
elseif($act=="delete")
{
	if ($conf == "ok")
	{
			$query = "DELETE FROM bd_genre WHERE id_genre= ".$DB->escape($genre_id)." LIMIT 1";
			$DB->query ($query);
			$redirection = BDO_URL."admin/index.php";
			echo '<META http-equiv="refresh" content="2; URL='.$redirection.'">Le genre a été effacé de la base.';
			exit();
	}
	else
	{// Affiche la demande de confirmation
		echo 'Etes-vous s&ucirc;r de vouloir effacer le genre n. '.$genre_id.' ? <a href="'.BDO_URL.'admin/admingenres.php?act=delete&conf=ok&genre_id='.$genre_id.'">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
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
	"tpBody" => "admin.genre.tpl",
	"tpBase" => "body.tpl"
	));

	$t->set_var (array(
	"NBSERIES" => "0",
	"URLDELETE" => "javascript:alert('Désactivé');",
	"URLFUSION" => "javascript:alert('Désactivé');",
	"ACTIONNAME" => "Enregistrer",
	"URLACTION" => BDO_URL."admin/admingenres.php?act=append"
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

// INSERE UN NOUVEAU GENRE DANS LA BASE
elseif($act=="append"){

	$query = "INSERT INTO `bd_genre` (`LIBELLE`)";
	$query .= " VALUES ('".$DB->escape($_POST['txtGenre'])."')";
	$DB->query ($query);
	$lid= mysql_insert_id();
	echo GetMetaTag(2,"Le nouveau genre a été ajouté",(BDO_URL."admin/admingenres.php?genre_id=".$lid));
}

// AFFICHER UN GENRE
elseif($act==""){

	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "admin.genre.tpl",
	"tpBase" => "body.tpl"
	));
	// Compte les albums pour lesquels les auteurs ont travaillé
	$query = "select count(*) as nbseries from bd_serie where id_genre =".$DB->escape($genre_id);
	$DB->query ($query);
	$DB->next_record();
	$nb_serie = $DB->f("nbseries");

	//récupère les données utilisateur dans la base de donnée
	$query = "SELECT id_genre, libelle FROM bd_genre WHERE id_genre=".$DB->escape($genre_id);
	$DB->query ($query);
	$DB->next_record();
	$t->set_var (array(
	"IDGENRE" => $DB->f("id_genre"),
	"GENRE" => htmlentities(stripslashes($DB->f("libelle"))),
	"NBSERIES" => $nb_serie,
	"URLDELETE" => BDO_URL."admin/admingenres.php?act=delete&genre_id=".$DB->f("id_genre"),
	"URLFUSION" => BDO_URL."admin/mergegenres.php?source_id=".$DB->f("id_genre"),
	"ACTIONNAME" => "Valider les Modifications",
	"URLACTION" => BDO_URL."admin/admingenres.php?act=update"
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
