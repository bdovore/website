<?php



minAccessLevel(1);


// Tableau pour les choix d'options du status des series
$opt_status[0][0] = 0;
$opt_status[0][1] = 'Finie';
$opt_status[1][0] = 1;
$opt_status[1][1] = 'En cours';
$opt_status[2][0] = 2;
$opt_status[2][1] = 'One Shot';
$opt_status[3][0] = 3;
$opt_status[3][1] = 'Interrompue/Abandonnée';

$act = $_GET["act"];

// Mettre à jour les informations
if ($act=="update"){
	
	$query = "
	UPDATE bd_serie SET
		`nom` = '".$DB->escape($_POST['txtSerie'])."',
		`id_genre` = ".$DB->escape($_POST['txtGenreId']).",
		`flg_fini` = '".$DB->escape($_POST['chkFini'])."',
		`nb_tome` = ".sqlise($_POST['txtNbTome'],'int').",
		`tri` = '".$DB->escape($_POST['txtTri'])."',
		`histoire` ='".$DB->escape($_POST['txtSerieHist'])."'
	WHERE
		`id_serie`=".$DB->escape($_POST["txtSerieId"])."
	";
	$DB->query($query);
	echo $DB->affected_rows()." lignes modifiées dans la table bd_serie.<br />";
	$query = "UPDATE bd_tome SET id_genre = ".$DB->escape($_POST['txtGenreId']). " WHERE (`id_serie`=".$DB->escape($_POST["txtSerieId"]).");";
	$DB->query($query);
	echo $DB->affected_rows()." lignes modifiées dans la table bd_tome.<br />";

	echo '<META http-equiv="refresh" content="1; URL=javascript:history.go(-1)">'."Mise à jour effectuée";
}

// EFFACEMENT D'UN ALBUM
elseif($act=="delete"){
	if ($conf == "ok")
	{
		$query = "SELECT * FROM bd_tome WHERE id_serie= ".$DB->escape($idserie);
		$DB->query ($query);
		$nb_tome = $DB->num_rows();
		if ($nb_tome > 0)
			exit('La série contient encore '.$nb_tome.' album(s). Suppression interdite.');

		$query = "SELECT * FROM serie_comment WHERE id_serie= ".$DB->escape($idserie);
		$DB->query ($query);
		$nb_tome = $DB->num_rows();
		if ($nb_tome > 0)
			exit('La série contient encore '.$nb_tome.' commentaire(s). Suppression interdite.');

		$query = "SELECT * FROM users_exclusions WHERE id_serie= ".$DB->escape($idserie);
		$DB->query ($query);
		$nb_tome = $DB->num_rows();
		if ($nb_tome > 0)
			exit('La série contient encore '.$nb_tome.' exclusion(s) d\'utilisateur. Suppression interdite.');

		$query = "DELETE FROM bd_serie WHERE id_serie= ".$DB->escape($idserie)." LIMIT 1";
		$DB->query ($query);
		echo $DB->affected_rows()." ligne modifiée dans la table bd_serie.<br />";
		echo '<META http-equiv="refresh" content="2; URL='.BDO_URL.'admin/index.php">La serie a été effacée de la base.';
		exit();

	}
	else
	{// Affiche la demande de confirmation
		echo 'Etes-vous sûr de vouloir effacer la série n. '.$idserie.' ? <a href="'.BDO_URL.'admin/adminseries.php?act=delete&conf=ok&idserie='.$idserie.'">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';

		exit();
	}
}
// AFFICHE UN FORMULAIRE VIDE
elseif($act=="new"){
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpAdminSerieDetail" => "admin.serie.detail.tpl",
	"tpBody" => "admin.serie.tpl",
	"tpBase" => "body.tpl"
	));

	$t->set_var (array(
	"NBALBUMS" => "0",
	"NBAUTEURS" => "0",
	"NBNOTES" => "0",
	"NBCOMMENTS" => "0",
	"STYLE_NOTATION" => "",
	"OPTSTATUS" => GetOptionValue($opt_status,1),
	"URLDELETE" => "javascript:alert('Désactivé');",
	"ACTIONNAME" => "Enregistrer",
	"URLEDITGENRE" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
	"URLACTION" => BDO_URL."admin/adminseries.php?act=append"
	));
	// assigne la barre de login
	$t->set_var (array(
	"LOGINBARRE" => GetIdentificationBar(),
	"MENUBARRE" => admin_menu(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
	));
	$t->parse("BODY","tpBody");
	$t->parse("ADMINSERIEDETAIL","tpAdminSerieDetail");
	$t->pparse("MyFinalOutput","tpBase");
}

// INSERE UNE NOUVELLE SERIE DANS LA BASE
elseif($act=="append"){
	if ($_POST['txtTri'] == ''){
		$tri = substr(trim(clean_article(stripslashes($_POST['txtSerie']))),0,3);
	}else{
		$tri = $_POST['txtTri'];
	}
	
	$query = "
	INSERT INTO `bd_serie` (
	`NOM`, `ID_GENRE`, `FLG_FINI`, `NB_TOME`, `TRI`, `HISTOIRE`
	) VALUES (
	'".$DB->escape($_POST['txtSerie'])."', 
	".$DB->escape($_POST['txtGenreId']).", 
	'".$DB->escape($_POST['chkFini'])."', 
	".sqlise($_POST['txtNbTome'],'int').", 
	".sqlise($tri,'text_simple').", 
	".sqlise($_POST['txtSerieHist'],'text_simple')."
	) 
	";
	$DB->query ($query);
	$lid= mysql_insert_id();
	echo GetMetaTag(2,"La série a été ajouté",(BDO_URL."admin/adminseries.php?serie_id=".$lid));
}

// AFFICHER UNE SERIE
elseif($act==""){
	$champ_form_style = 'champ_form_desactive';
	
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpAdminSerieDetail" => "admin.serie.detail.tpl",
	"tpBody" => "admin.serie.tpl",
	"tpMenu" => "menuadmin.tpl",
	"tpBase" => "body.tpl"
	));
	// Selectionne les albums présents dans la série
	$query = "SELECT id_tome, titre FROM bd_tome WHERE id_serie=".$DB->escape($serie_id);
	$DB->query ($query);
	$nb_tome = $DB->num_rows();
	// on rempli avec les albums
	$t->set_block('tpBody','AlbBlock','ABlock');
	while ($DB->next_record()){
		$t->set_var (array(
		"CHAMPFORMSTYLE_EDIT" => $champ_form_style,
		"ALB_LIEN" => BDO_URL."admin/adminalbums.php?alb_id=".$DB->f("id_tome"),
		"ALB_TITRE" => stripslashes($DB->f("titre"))
		));
		$t->parse ("ABlock", "AlbBlock",true);
	}

	// Selectionne les auteurs ayant travaillé pour la série
	$query = "SELECT distinct id_auteur, pseudo 
	FROM 
	bd_auteur, bd_tome 
	WHERE id_serie = ".$DB->escape($serie_id)." 
	and (id_scenar = id_auteur or id_dessin = id_auteur);";
	$DB->query ($query);
	$nb_auteur = $DB->num_rows();
	// on rempli avec les albums
	$t->set_block('tpBody','AutBlock','AuBlock');
	while ($DB->next_record()){
		$t->set_var (array(
		"AUT_LIEN" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_auteur"),
		"AUT_TITRE" => stripslashes($DB->f("pseudo"))
		));
		$t->parse ("AuBlock", "AutBlock",true);
	}

	//récupère les données dans la base
	$query = "
	SELECT 
		s.ID_SERIE, 
		s.NOM, 
		s.ID_GENRE, 
		g.LIBELLE, 
		s.NOTE, 
		s.FLG_FINI, 
		s.NB_TOME, 
		s.TRI, 
		s.HISTOIRE
    FROM 
    	bd_serie s 
    	INNER JOIN bd_genre g ON s.ID_GENRE = g.ID_GENRE
    WHERE 
    	s.id_serie=".$DB->escape($serie_id);
	$DB->query ($query);
	$DB->next_record();

	//affichage du message de notification de note/commentaire de membre sur la serie
	$note = $DB->f("NOTE");
	$warning_note = "";
	if ($note == '0'){
		$warning_note = '<div>Aucun membre n\'a noté/commenté la série.</div>';
	}else{
		$warning_note = '<div class="b">Des membres ont noté/commenté la série.</div>';
	}

	$t->set_var (array(
	"IDSERIE" => $DB->f("ID_SERIE"),
	"SERIE" => stripslashes($DB->f("NOM")),
	"TRI" => $DB->f("TRI"),
	"IDGENRE" => $DB->f("ID_GENRE"),
	"GENRE" => htmlentities($DB->f("LIBELLE")),
	"NOTE" => $DB->f("NOTE"),
	"WARNING_NOTE" => $warning_note,
	"HISTOIRE_SERIE" => $DB->f("HISTOIRE"),
	"OPTSTATUS" => GetOptionValue($opt_status,$DB->f("FLG_FINI")),
	"NBTOME" => $DB->f("NB_TOME"),
	"NBALBUMS" => $nb_tome,
	"NBAUTEURS" => $nb_auteur,
	"URLDELETE" => BDO_URL."admin/adminseries.php?act=delete&idserie=".$DB->f("ID_SERIE"),
	"ACTIONNAME" => "Valider les Modifications",
	"URLEDITGENRE" => BDO_URL."admin/admingenres.php?genre_id=".$DB->f("ID_GENRE"),
	"URLMASSDETAIL" => BDO_URL."admin/mu_detail.php?serie=".$DB->f("ID_SERIE"),
	"URLMASSUPDATE" => BDO_URL."admin/mu_serie.php?serie=".$DB->f("ID_SERIE"),
	"URLMASSRENAME" => BDO_URL."admin/mu_rename.php?serie=".$DB->f("ID_SERIE"),
	"URLMASSCOUV" => BDO_URL."admin/mu_couv.php?serie=".$DB->f("ID_SERIE"),
	"URLAJOUTALB" => BDO_URL."admin/adminalbums.php?act=newfserie&id_serie=".$DB->f("ID_SERIE"),
	"URLACTION" => BDO_URL."admin/adminseries.php?act=update"
	));
	// assigne la barre de login
	$t->set_var (array(
	"LOGINBARRE" => GetIdentificationBar(),
	"MENUBARRE" => admin_menu(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
	));
	$t->parse("BODY","tpBody");
	$t->parse("ADMINSERIEDETAIL","tpAdminSerieDetail");
	$t->pparse("MyFinalOutput","tpBase");
}
