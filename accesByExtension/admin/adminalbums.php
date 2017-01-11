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

// Tableau pour les choix d'options
$opt_type[0][0] = 0;
$opt_type[0][1] = 'Album';
$opt_type[1][0] = 1;
$opt_type[1][1] = 'Coffret';


$act = $_GET["act"];

// Mettre à jour les informations
if ($act=="update"){

	$query = "UPDATE bd_tome SET
	`titre` = '".$DB->escape($_POST['txtTitre'])."',
	`num_tome` = ".($_POST['txtNumTome']=='' ? "NULL" :  "'".$DB->escape($_POST['txtNumTome']). "'").",
	`id_serie` = ".$DB->escape($_POST['txtSerieId']).",
	`prix_bdnet` = ".($_POST['txtPrixVente']=='' ? "NULL" :  "'".$DB->escape($_POST['txtPrixVente']). "'").",
	`id_scenar` = '".sqlise($_POST['txtScenarId'],'int')."',
	`id_dessin` = '".sqlise($_POST['txtDessiId'],'int')."',
	`id_scenar_alt` = '".sqlise($_POST['txtScenarAltId'],'int')."',
	`id_dessin_alt` = '".sqlise($_POST['txtDessiAltId'],'int')."',
	`id_color_alt` = '".sqlise($_POST['txtColorAltId'],'int')."',
	`id_color` = '".sqlise($_POST['txtColorId'],'int')."',
	`flg_int` = ".(($_POST['chkIntegrale'] == "checkbox") ? "'O'" : "'N'").",
	`flg_type` = ".$DB->escape($_POST['lstType']).",
	`histoire` = '".$DB->escape($_POST['txtHistoire'])."'
	WHERE `id_tome`=".$DB->escape($_POST["txtTomeId"]);
	$DB->query($query);

	// Met à jour l'information sur la série
	$query = "SELECT id_serie, id_genre FROM bd_serie WHERE id_serie = ".$DB->escape($_POST['txtSerieId']);
	$DB->query($query);
	$DB->next_record();

	$id_genre = $DB->f("id_genre");

	$query = "UPDATE bd_tome SET";
	$query .= " id_genre = $id_genre";
	$query .= " WHERE `id_tome`=".$DB->escape($_POST["txtTomeId"]);
	$DB->query($query);

	// définie l'édition à utiliser par défaut
	$query = "
	UPDATE bd_tome SET
		id_edition = " . $DB->escape($_POST['btnDefEdit']) . "
	WHERE
		`id_tome`=".$_POST["txtTomeId"];
	$DB->query($query);

	echo '<META http-equiv="refresh" content="1; URL=javascript:history.go(-1)">'."Mise à jour effectuée";
}


// EFFACEMENT D'UN ALBUM
elseif($act=="delete")
{
	if ($conf == "ok")
	{
		//Revérifie que c'est bien l'administrateur qui travaille
		if (minAccessLevel(1))
		{
			// Efface les éditions et les couvertures correspondantes
			$query = "SELECT id_edition, img_couv FROM bd_edition WHERE id_tome =".$DB->escape($idtome);
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
			$query = "DELETE FROM bd_edition WHERE id_tome=" . $DB->escape($idtome);
			$DB->query ($query);
			echo 'Référence(s) à l\'album supprimée(s) dans la table bd_edition<br />';

			$query = "DELETE FROM bd_tome WHERE id_tome=" . $DB->escape($idtome);
			$DB->query ($query);

			$redirection = BDO_URL."index.php";
			echo '<META http-equiv="refresh" content="1; URL='.$redirection.'">L\'album a été effacé de la table bd_tome.';
			exit();
		}
	}
	else
	{
		// Affiche la demande de confirmation
		echo 'Etes-vous sûr de vouloir effacer l\'album n. '.$idtome.' ? <a href="'.BDO_URL.'admin/adminalbums.php?act=delete&conf=ok&idtome='.$idtome.'">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
		exit();
	}
}
elseif($act=="new")
{
	// AFFICHE UN FORMULAIRE VIDE
	$url_image = BDO_URL."images/couv/default.png";
	$champ_form_style = 'champ_form_desactive';
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpAdminEditionCouv" => "admin.edition.couv.tpl",
	"tpAdminEditionDetail" => "admin.edition.detail.tpl",
	"tpAdminAlbumDetail" => "admin.album.detail.tpl",
	"tpAdminSerieDetail" => "admin.serie.detail.tpl",
	"tpBody" => "admin.album.tpl",
	"tpBase" => "body.tpl"
	));

	$t->set_var (array(
	"CHAMPFORMSTYLE" => $champ_form_style,
	"URLIMAGE" => $url_image,
	"OPTTYPE" => GetOptionValue($opt_type,0),
	"NBUSERS" => "0",
	"NBUSERS2" => "0",
	"URLSERIE" => "javascript:alert('Désactivé');",
	"URLDELETE" => "javascript:alert('Désactivé');",
	"URLFUSION" => "javascript:alert('Désactivé');",
	"ACTIONNAME" => "Enregistrer",
	"URLEDITSERIE" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
	"URLEDITGENRE" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
	"URLEDITSCEN" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
	"URLEDITDESS" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
	"URLEDITDESSALT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
	"URLEDITCOLOR" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
	"URLEDITCOLORALT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
	"URLEDITEDIT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
	"URLEDITCOLL" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
	"URLEDITCOLLALT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
	"URLACTION" => BDO_URL."admin/adminalbums.php?act=append"
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
	$t->parse("ADMINALBUMDETAIL","tpAdminAlbumDetail");
	$t->parse("ADMINEDITIONDETAIL","tpAdminEditionDetail");
	$t->parse("ADMINEDITIONCOUV","tpAdminEditionCouv");
	$t->pparse("MyFinalOutput","tpBase");
}


// AFFICHE UN FORMULAIRE prérempli
elseif($act=="newfserie"){
	$url_image = BDO_URL."images/couv/default.png";
	$champ_form_style = 'champ_form_desactive';
	$champ_form_style_newfserie = 'champ_form_desactive_newfserie';
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpAdminEditionCouv" => "admin.edition.couv.tpl",
	"tpAdminEditionDetail" => "admin.edition.detail.tpl",
	"tpAdminAlbumDetail" => "admin.album.detail.tpl",
	"tpAdminSerieDetail" => "admin.serie.detail.tpl",
	"tpBody" => "admin.album.tpl",
	"tpBase" => "body.tpl"
	));

	$id_serie = $_GET["id_serie"];

	$query = q_tome("t.id_serie='".$DB->escape($id_serie)."'"
	,"ORDER BY t.num_tome DESC LIMIT 1");


	$DB->query($query);
	$DB->next_record();

	$t->set_var (array(
	"CHAMPFORMSTYLE" => $champ_form_style,
	"CHAMPFORMSTYLE_NEWFSERIE" => $champ_form_style_newfserie,
	"URLIMAGE" => $url_image,
	"OPTTYPE" => GetOptionValue($opt_type,0),
	"NBUSERS" => "0",
	"NBUSERS2" => "0",
	"TOME" => $DB->f("num_tome")+1,
	"IDSERIE" => $DB->f("id_serie"),
	"SERIE" => htmlentities(stripslashes($DB->f("s_nom"))),
	"IDSCEN" => $DB->f("id_scenar"),
	"SCENARISTE" => htmlentities($DB->f("scpseudo")),
	"IDSCENALT" => $DB->f("id_scenar_alt"),
	"SCENARISTEALT" => ($DB->f("id_scenar_alt")==0 )? "" : htmlentities($DB->f("scapseudo")),
	"IDDESS" => $DB->f("id_dessin"),
	"DESSINATEUR" => htmlentities($DB->f("depseudo")),
	"IDDESSALT" => $DB->f("id_dessin_alt"),
	"DESSINATEURALT" => ($DB->f("id_dessin_alt")==0 )? "" : htmlentities($DB->f("deapseudo")),
	"IDCOLOR" => $DB->f("id_color"),
	"COLORISTE" => htmlentities($DB->f("copseudo")),
	"IDCOLORALT" => $DB->f("id_color_alt"),
	"COLORISTEALT" => ($DB->f("id_color_alt")==0 )? "" : htmlentities($DB->f("coapseudo")),
	"IDEDIT" => $DB->f("id_editeur"),
	"EDITEUR" => htmlentities(stripslashes($DB->f("enom"))),
	"IDCOLLEC" => $DB->f("id_collection"),
	"COLLECTION" => htmlentities(stripslashes($DB->f("cnom"))),
	"URLSERIE" => "javascript:alert('Désactivé');",
	"URLDELETE" => "javascript:alert('Désactivé');",
	"URLFUSION" => "javascript:alert('Désactivé');",
	"ACTIONNAME" => "Enregistrer",
	"URLEDITSERIE" => BDO_URL."admin/adminseries.php?serie_id=".$DB->f("id_serie"),
	"URLEDITGENRE" => BDO_URL."admin/admingenres.php?genre_id=".$DB->f("id_genre"),
	"URLEDITSCEN" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_scenar"),
	"URLEDITDESS" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_dessin"),
	"URLEDITCOLOR" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_color"),
	"URLEDITSCENALT" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_scenar_alt"),
	"URLEDITDESSALT" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_dessin_alt"),
	"URLEDITCOLORALT" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_color_alt"),
	"URLEDITEDIT" => BDO_URL."admin/adminediteurs.php?editeur_id=".$DB->f("id_editeur"),
	"URLEDITCOLL" => BDO_URL."admin/admincollections.php?collec_id=".$DB->f("id_collection"),
	"URLEDITCOLLALT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
	"URLACTION" => BDO_URL."admin/adminalbums.php?act=append"
	));

	// assigne la barre de login
	$t->set_var (array(
	"LOGINBARRE" => GetIdentificationBar(),
	"MENUBARRE" => admin_menu(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,));
	$t->parse("BODY","tpBody");
	$t->parse("ADMINSERIEDETAIL","tpAdminSerieDetail");
	$t->parse("ADMINALBUMDETAIL","tpAdminAlbumDetail");
	$t->parse("ADMINEDITIONDETAIL","tpAdminEditionDetail");
	$t->parse("ADMINEDITIONCOUV","tpAdminEditionCouv");
	$t->pparse("MyFinalOutput","tpBase");
}


// INSERE UN NOUVEL ALBUM DANS LA BASE
elseif($act=="append"){

	$query = "
	INSERT INTO `bd_tome`
	(`TITRE`, `NUM_TOME`, `PRIX_BDNET`, `ID_SERIE`,
	`ID_GENRE`, `ID_SCENAR`, `ID_SCENAR_ALT`, `ID_DESSIN`,
	`ID_DESSIN_ALT`, `ID_COLOR`, `ID_COLOR_ALT`,
	`HISTOIRE`, `FLG_INT`, `FLG_TYPE`
	) VALUES (
	'".$DB->escape($_POST['txtTitre'])."',
	".($_POST['txtNumTome']=='' ? "NULL" :  "'".$DB->escape($_POST['txtNumTome']). "'").",
	".($_POST['txtPrixVente']=='' ? "NULL" :  "'".$DB->escape($_POST['txtPrixVente']). "'").",
	'".$_POST['txtSerieId']."',
	'".$DB->escape($_POST['txtGenreId'])."',

	'".sqlise($_POST['txtScenarId'],'int')."',
	'".sqlise($_POST['txtScenarAltId'],'int')."',
	'".sqlise($_POST['txtDessiId'],'int')."',
	'".sqlise($_POST['txtDessiAltId'],'int')."',
	'".sqlise($_POST['txtColorId'],'int')."',
	'".sqlise($_POST['txtColorAltId'],'int')."',

	".($_POST['txtHistoire']=='' ? "NULL" : "'".$DB->escape($_POST['txtHistoire'])."'").",
	".(($_POST['chkIntegrale'] == "checkbox") ? "'O'" : "'N'").",
	".$DB->escape($_POST['lstType'])."
	)";

	$DB->query ($query);
	// récupère la valeur de la dernière insertion
	$lid_tome = mysql_insert_id();

	// met à jour le genre en fonction de la série sélectionnée
	$query = "SELECT id_genre FROM bd_serie WHERE id_serie = ".$DB->escape($_POST['txtSerieId']);
	$DB->query($query);
	$DB->next_record();

	$id_genre = $DB->f("id_genre");

	$query = "
	UPDATE bd_tome SET
	id_genre = ".$DB->escape($id_genre)."
	WHERE `id_tome`=".$DB->escape($lid_tome);
	$DB->query($query);

	// insère un champ dans la table id_edition

	$_POST['txtDateParution'] = completeDate($_POST['txtDateParution']);
	if ($_POST['txtDateParution'] == "")
		$flag_dte_par = 1;
	else
		$flag_dte_par = (($_POST['FLAG_DTE_PARUTION'] == "1") ? "'1'" : "NULL");

	$query_el = array(
	"id_tome" => $DB->escape($lid_tome),
	"id_editeur" => $DB->escape($_POST['txtEditeurId']),
	"id_collection" => $DB->escape($_POST['txtCollecId']),
	"dte_parution" => sqlise($_POST['txtDateParution'],'text'),
	"flag_dte_parution" => $flag_dte_par,
	"flg_tt" => (($_POST['chkTT'] == "checkbox") ? "'O'" : "'N'"),
	"ean" => sqlise($_POST['txtEAN'],'text'),
	"isbn" => sqlise($_POST['txtISBN'],'text'),
	"comment" => sqlise($_POST['txtComment'],'text'),
	"validator" => $_SESSION["UserId"],
	"valid_dte" => 'NOW()'
	);
	$query = insert_query("bd_edition",$query_el);
	$DB->query ($query);

	// récupère la valeur de la dernière insertion
	$lid_edition = mysql_insert_id();

	// renseigne cette edition comme defaut pour bd_tome
	$DB->query("UPDATE bd_tome SET ID_EDITION='" . $DB->escape($lid_edition) . "' WHERE id_tome=" . $DB->escape($lid_tome));

	// Verifie la présence d'une image à télécharger
	if (is_file($txtFileLoc) | (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $_POST['txtFileURL'], $url_ary))){
		if (is_file($txtFileLoc)){ // un fichier à uploader
			$imageproperties = getimagesize($txtFileLoc);
			$imagetype = $imageproperties[2];
			$imagelargeur = $imageproperties[0];
			$imagehauteur = $imageproperties[1];
			// vérifie le type d'image
			if (($imagetype != 1) and ($imagetype != 2)){
				echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Seul des fichiers JPEG ou GIF peuvent être chargés. Vous allez être redirigé.';
				exit();
			}
			$uploaddir = BDO_DIR."images/couv/";
			$newfilename = "CV-".sprintf("%06d",$lid_tome)."-".sprintf("%06d",$lid_edition);
			if (($imagetype == 1)){
				$newfilename .=".gif";
			}else{
				$newfilename .=".jpg";
			}
			if(!copy($txtFileLoc,$uploaddir.$newfilename)){
				echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors de l\'envoi de l\'image au serveur. Vous allez être redirigé.';
				exit();
			}else{
				$img_couv=$newfilename;
			}
		}else if (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $_POST['txtFileURL'], $url_ary)){ // un fichier à télécharger
			if ( empty($url_ary[4]) ){
				echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image incomplète. Vous allez être redirigé.';
				exit();
			}
			$base_get = '/' . $url_ary[4];
			$port = ( !empty($url_ary[3]) ) ? $url_ary[3] : 80;
			// Connection au serveur hébergeant l'image
			if ( !($fsock = @fsockopen($url_ary[2], $port, $errno, $errstr)) ){
				$error = true;
				echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image innacessible. Vous allez être redirigé.';
				exit();
			}

			// Récupère l'image
			@fputs($fsock, "GET $base_get HTTP/1.1\r\n");
			@fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
			@fputs($fsock, "Connection: close\r\n\r\n");

			unset($avatar_data);
			while( !@feof($fsock) ){
				$avatar_data .= @fread($fsock, 102400);
			}
			@fclose($fsock);

			// Check la validité de l'image
			if (!preg_match('#Content-Length\: ([0-9]+)[^ /][\s]+#i', $avatar_data, $file_data1) || !preg_match('#Content-Type\: image/[x\-]*([a-z]+)[\s]+#i', $avatar_data, $file_data2)){
				$error = true;
				echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors du téléchargement de l\'image. Vous allez être redirigé.';
				exit();
			}
			$avatar_filesize = $file_data1[1];
			$avatar_filetype = $file_data2[1];
			$avatar_data = substr($avatar_data, strlen($avatar_data) - $avatar_filesize, $avatar_filesize);
			$tmp_path = BDO_DIR.'images/tmp';
			$tmp_filename = tempnam($tmp_path, uniqid(rand()) . '-');
			$fptr = @fopen($tmp_filename, 'wb');
			$bytes_written = @fwrite($fptr, $avatar_data, $avatar_filesize);
			@fclose($fptr);
			if ( $bytes_written != $avatar_filesize ){
				@unlink($tmp_filename);
				echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Could not write avatar file to local storage. Please contact the board administrator with this message. Vous allez être redirigé.';
				exit();
			}
			// newfilemname
			if ( !($imgtype = check_image_type($avatar_filetype, $error)) ){
				exit;
			}

			$new_filename = "CV-".sprintf("%06d",$lid_tome)."-".sprintf("%06d",$lid_edition).$imgtype;

			// si le fichier existe, on l'efface
			if (file_exists(BDO_DIR."images/couv/$new_filename")){
				@unlink(BDO_DIR."images/couv/$new_filename");
			}

			// copie le fichier temporaire dans le repertoire image
			@copy($tmp_filename, BDO_DIR."images/couv/$new_filename");
			@unlink($tmp_filename);
			$img_couv=$new_filename;
		}else{
			$img_couv='';
		}
		if ($_POST["chkResize"] == "checked" && $img_couv != '') {
			//Redimensionnement
			$max_size = 180;
			$imageproperties = getimagesize(BDO_DIR."images/couv/$img_couv");
			if ($imageproperties != false) {
				$imagetype = $imageproperties[2];
				$imagelargeur = $imageproperties[0];
				$imagehauteur = $imageproperties[1];

				//Détermine s'il y a lieu de redimensionner l'image
				if ((($imagelargeur > $imagehauteur) && ($imagehauteur > $maxsize)) || (($imagelargeur <= $imagehauteur) & ($imagelargeur > $max_size))) {
					if ($imagelargeur < $imagehauteur) {
						// image de type panorama : on limite la largeur à 128
						$new_w = $max_size;
						$new_h = round($imagehauteur * $max_size / $imagelargeur);
					}else {
						// imahe de type portrait : on limite la hauteur au maxi
						$new_h = $max_size;
						$new_w = round($imagelargeur * $max_size / $imagehauteur);
					}
				}else{
					$new_h = $imagehauteur;
					$new_w = $imagelargeur;
				}
				$new_image = imagecreatetruecolor($new_w, $new_h);
				switch ($imagetype) {
					case "1":
						$source = imagecreatefromgif(BDO_DIR."images/couv/$img_couv");
						break;
					case "2":
						$source = imagecreatefromjpeg(BDO_DIR."images/couv/$img_couv");
						break;
					case "3":
						$source = imagecreatefrompng(BDO_DIR."images/couv/$img_couv");
						break;
					case "6":
						$source = imagecreatefrombmp(BDO_DIR."images/couv/$img_couv");
						break;
				}
				imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_w, $new_h, $imagelargeur, $imagehauteur);
				switch ($imagetype) {
					case "2":
						unlink(BDO_DIR."images/couv/$img_couv");
						imagejpeg($new_image,BDO_DIR."images/couv/$img_couv",100);
						break;
					case "1":
					case "3":
					case "6":
						unlink(BDO_DIR."images/couv/$img_couv");
						$img_couv = substr($img_couv,0,strlen($img_couv)-3)."jpg";
						imagejpeg($new_image,BDO_DIR."images/couv/$img_couv",100);
				}
			}
			echo "$new_w, $new_h, $imagelargeur, $imagehauteur<br />";
			echo "Image redimensionnée<br />";
		}

		// met à jours la référence au fichier dans la table bd_edition
		$query = "UPDATE bd_edition SET";
		$query .= " `img_couv` = '".$DB->escape($img_couv)."'";
		$query .=" WHERE `id_edition`=".$DB->escape($lid_edition);
		$DB->query($query);
	}
	echo GetMetaTag(2,"L'album a été ajouté",(BDO_URL."admin/adminalbums.php?alb_id=".$lid_tome));
}


// AFFICHER UN ALBUM
elseif($act==""){

	// récupère le nombre d'utilisateurs
	$query = "
	SELECT COUNT(DISTINCT(users_album.user_id)) AS countusers
	FROM users_album
	INNER JOIN bd_edition USING(ID_EDITION)
	WHERE bd_edition.ID_TOME=" . $DB->escape($alb_id);
	$DB->query ($query);
	$DB->next_record();
	$nb_users = $DB->f("countusers");

	// récupère le nombre de commentaires
	$query = "select count(user_id) as countcomments from users_comment where id_tome=" . $DB->escape($alb_id);
	$DB->query ($query);
	$DB->next_record();
	$nb_comments = $DB->f("countcomments");

	// récupère les données principales
	$query = q_tome("t.id_tome=".$DB->escape($alb_id));

	$DB->query ($query);
	$DB->next_record();

	$id_edition_default = $DB->f("id_edition");

	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpAdminSerieDetail" => "admin.serie.detail.tpl",
	"tpAdminAlbumDetail" => "admin.album.detail.tpl",
	"tpAdminEditionDetail" => "admin.edition.detail.tpl",
	"tpBody" => "admin.edit.album.tpl",
	"tpBase" => "body.tpl"
	));

	$champ_form_style = 'champ_form_desactive';

	// détermine s'il est possible d'effacer cet album
	if (($nb_users==0) & ($nb_comments==0)){
		$url_delete = BDO_URL."admin/adminalbums.php?act=delete&idtome=".$DB->f("id_tome");
	}else{
		$url_delete = "javascript:alert('Impossible');";
	}

	$t->set_var (array(
	"CHAMPFORMSTYLE" => $champ_form_style,
	"IDTOME" => $DB->f("id_tome"),
	"TITRE" => stripslashes($DB->f("titre")),
	"IDSERIE" => $DB->f("id_serie"),
	"SERIE" => htmlentities(stripslashes($DB->f("s_nom"))),
	"TRI" => $DB->f("tri"),
	"IDGENRE" => $DB->f("id_genre"),
	"GENRE" => htmlentities($DB->f("libelle")),
	"OPTSTATUS" => GetOptionValue($opt_status,$DB->f("flg_fini")),
	"NBTOME" => $DB->f("nb_tome"),
	"HISTOIRE_SERIE" => $DB->f("histoire_serie"),
	"TOME" => $DB->f("num_tome"),
	"PRIX_VENTE" => $DB->f("prix_bdnet"),
	"IDSCEN" => $DB->f("id_scenar"),
	"SCENARISTE" => htmlentities($DB->f("scpseudo")),
	"IDSCENALT" => $DB->f("id_scenar_alt"),
	"SCENARISTEALT" => ($DB->f("id_scenar_alt")==0 )? "" : htmlentities($DB->f("scapseudo")),
	"IDDESS" => $DB->f("id_dessin"),
	"DESSINATEUR" => htmlentities($DB->f("depseudo")),
	"IDDESSALT" => $DB->f("id_dessin_alt"),
	"DESSINATEURALT" => ($DB->f("id_dessin_alt")==0 )? "" : htmlentities($DB->f("deapseudo")),
	"IDCOLOR" => $DB->f("id_color"),
	"COLORISTE" => htmlentities($DB->f("copseudo")),
	"IDCOLORALT" => $DB->f("id_color_alt"),
	"COLORISTEALT" => ($DB->f("id_color_alt")==0 )? "" : htmlentities($DB->f("coapseudo")),
	"HISTOIRE" => stripslashes($DB->f("histoire")),
	"ISINT" => (($DB->f("flg_int")=='O') ? 'checked' : ''),
	"OPTTYPE" => GetOptionValue($opt_type,$DB->f("flg_type")),
	"NBUSERS" => $nb_users,
	"NBUSERS2" => $nb_comments,
	"URLDELETE" => $url_delete,
	"URLFUSION" => BDO_URL."admin/mergealbums.php?source_id=".$DB->f("id_tome"),
	"URLSPLIT" => BDO_URL."admin/admin.split.php?alb_id=".$DB->f("id_tome"),
	"URLFUSIONDELETE" => BDO_URL."admin/admin.delete.php?alb_id=".$DB->f("id_tome"),
	"URLEDITSERIE" => BDO_URL."admin/adminseries.php?serie_id=".$DB->f("id_serie"),
	"URLEDITGENRE" => BDO_URL."admin/admingenres.php?genre_id=".$DB->f("id_genre"),
	"URLEDITSCEN" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_scenar"),
	"URLEDITDESS" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_dessin"),
	"URLEDITCOLOR" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_color"),
	"URLEDITSCENALT" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_scenar_alt"),
	"URLEDITDESSALT" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_dessin_alt"),
	"URLEDITCOLORALT" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_color_alt"),
	"URLEDITEDIT" => BDO_URL."admin/adminediteurs.php?editeur_id=".$DB->f("id_editeur"),
	"URLEDITCOLL" => BDO_URL."admin/admincollections.php?collec_id=".$DB->f("id_collection"),
	"ACTIONNAME" => "Valider les Modifications",
	"URLACTION" => BDO_URL."admin/adminalbums.php?act=update"
	));

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
		}else{
			$url_image = BDO_URL."images/couv/".$DB->f("img_couv");
		}

		// Détermine si l'édition a été validée ou non
		if ($DB->f("prop_status") == 1){
			$bgcolor = "";
		}else{
			$bgcolor = 'style ="background-color: #ff8282;"';
		}

		// Affiche le résultat
		$t->set_var (array(
		"EDITEUR" => stripslashes($DB->f("enom")),
		"COLLECTION" => htmlentities(stripslashes($DB->f("cnom"))),
		"IMGTT" => (($DB->f("flg_tt") == 'O') ? BDO_URL_IMAGE.'site/ic_TT.gif' : BDO_URL_IMAGE.'site/ic_TT_nb.gif'),
		"DTPAR" => dateParution($DB->f("dte_parution"),$DB->f("flag_dte_parution")),
		"BTNVALUE" => $DB->f("id_edition"),
		"URLEDITEDITION" => BDO_URL."admin/admineditions.php?edition_id=".$DB->f("id_edition"),
		"ISCHECKED" => (($DB->f("id_edition") == $id_edition_default) ? 'checked' : ''),
		"URLIMAGE"=> $url_image,
		"BGCOLOR" => $bgcolor
		));
		$t->parse ("EBlock", "EditionBlock",true);
	}

	$t->set_var (array(
	"NBEDITIONS" => $DB->num_rows(),
	"URLAJOUTEDITION" => BDO_URL."admin/admineditions.php?act=new&alb_id=".$alb_id
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
	$t->parse("ADMINALBUMDETAIL","tpAdminAlbumDetail");
	$t->parse("ADMINEDITIONDETAIL","tpAdminEditionDetail");
	$t->pparse("MyFinalOutput","tpBase");
}
