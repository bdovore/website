<?php



minAccessLevel(1);

$act = $_GET["act"];
$edition_id = intval($_GET["edition_id"]);

// Mettre � jour les informations
if ($act=="update"){
	if (is_file($_FILES["txtFileLoc"]["tmp_name"])){// un fichier � uploader
		$txtFileLoc = $_FILES["txtFileLoc"]["tmp_name"];
		$imageproperties = getimagesize($txtFileLoc);
		$imagetype = $imageproperties[2];
		$imagelargeur = $imageproperties[0];
		$imagehauteur = $imageproperties[1];
		// v�rifie le type d'image
		if (($imagetype != 1) and ($imagetype != 2)){
			echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Seul des fichiers JPEG ou GIF peuvent �tre charg�s. Vous allez �tre redirig�.';
			exit();
		}
		$uploaddir = BDO_DIR."images/couv/";
		$newfilename = "CV-".sprintf("%06d",$_POST["txtTomeId"])."-".sprintf("%06d",$_POST["txtEditionId"]);
		if (($imagetype == 1)){
			$newfilename .=".gif";
		}else{
			$newfilename .=".jpg";
		}
		if(!copy($txtFileLoc,$uploaddir.$newfilename)){
			echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors de l\'envoi de l\'image au serveur. Vous allez �tre redirig�.';
			exit();
		}else{
			$img_couv=$newfilename;
		}
	}
	else if (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $_POST['txtFileURL'], $url_ary)){ // un fichier � t�l�charger
		$newfilename = "CV-".sprintf("%06d",$_POST["txtTomeId"])."-".sprintf("%06d",$_POST["txtEditionId"]);
		$newfilename = get_img_from_url($_POST['txtFileURL'],BDO_DIR."images/tmp/",$newfilename);
		$tmp_filename = BDO_DIR."images/tmp/".$newfilename;

		// si le fichier existe, on l'efface
		if (file_exists(BDO_DIR."images/couv/$newfilename")){
			@unlink(BDO_DIR."images/couv/$newfilename");
		}

		// copie le fichier temporaire dans le repertoire image
		@copy($tmp_filename, BDO_DIR."images/couv/$newfilename");
		@unlink($tmp_filename);

		$img_couv=$newfilename;
	}else{
		$img_couv='';
	}
	
	if($_POST['FLAG_DTE_PARUTION'] != "1") $_POST['txtDateParution'] = completeDate($_POST['txtDateParution']);
	else $_POST['txtDateParution'] = '';

	$query = "
	UPDATE bd_edition SET
	`dte_parution` = ".(($_POST['txtDateParution'] == "") ? "NULL" : "'".$_POST['txtDateParution']."'").",
	`flag_dte_parution` = ".(($_POST['FLAG_DTE_PARUTION'] == "1") ? "'1'" : "NULL").",
	`id_editeur` = ".$DB->escape($_POST['txtEditeurId']).",
	`id_collection` = ".$DB->escape($_POST['txtCollecId']).",
	`ean` = ".sqlise($_POST['txtEAN'],'text').",
	`isbn` = ".sqlise($_POST['txtISBN'],'text').",
	`comment` = ".sqlise($_POST['txtComment'],'text').",
	`flg_tt` = ".(($_POST['chkTT'] == "checkbox") ? "'O'" : "'N'").",
	`validator` = " .  $DB->escape($_SESSION["UserId"]).",
	`valid_dte` = NOW()
	";

	// v�rifie si la couverture a �t� chang�e
	if ($img_couv != ''){
		$query .= ", `img_couv` = '".$DB->escape($img_couv)."'";
	}
	$query .=" WHERE `id_edition`=".$DB->escape($_POST["txtEditionId"]);
	$DB->query($query);
	echo 'Mise � jour effectu�e dans la table bd_edition<br />';


	// On rajoute un redimensionnement si le correcteur l'a voulu
	if ($_POST["chkResize"] == "checked") {
		$id_edition = intval($_POST["txtEditionId"]);
		resize_edition_image($id_edition, BDO_DIR."images/couv/");
	}

	$redirection = BDO_URL."admin/adminalbums.php?alb_id=".$_POST["txtTomeId"];
	echo GetMetaTag(1,"L'�dition a �t� mise � jour",$redirection);
	exit();

}elseif($act=="delete"){// EFFACEMENT D'UNE EDITION
	if ($_GET["conf"] == "ok")
	{

		// Determine s'il y a lieu d'effacer l'image
		$query = "SELECT id_tome, img_couv FROM bd_edition WHERE id_edition = ".$DB->escape($edition_id);
		$DB->query($query);
		$DB->next_record();
		$url_img = $DB->f("img_couv");
		$id_tome = $DB->f("id_tome");
		if ($url_img != ''){
			$filename = $url_img;
			if (file_exists(BDO_DIR."images/couv/$filename")){
				unlink(BDO_DIR."images/couv/$filename");
				echo "Couverture effac�e<br />";
			}
		}

		// Efface l'�dition de la base
		$query = "DELETE FROM bd_edition WHERE id_edition = ".$DB->escape($edition_id)." LIMIT 1";
		$DB->query ($query);
		$redirection = BDO_URL."admin/adminalbums.php?alb_id=".$id_tome;
		echo GetMetaTag(1,"L'�dition a �t� �ffac�e de la base",$redirection);
		exit();

	}
	else{// Affiche la demande de confirmation
		echo 'Etes-vous s&ucirc;r de vouloir effacer l\'�dition n. '.$edition_id.' ? <a href="'.BDO_URL.'admin/admineditions.php?act=delete&conf=ok&edition_id='.$edition_id.'">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
		exit();
	}

}elseif($act=="autorize"){// ACTIVATION D'UNE EDITION

	// Commence par activer l'�dition dans la base
	$query = "
		UPDATE bd_edition SET 
		`prop_status` = '1', 
		`validator` =" . $DB->escape($_SESSION["UserId"]) . ", 
		`valid_dte` = NOW() 
		WHERE id_edition = '".$DB->escape($edition_id)."';";
	$DB->query($query);
	$DB->next_record();


	// redirection vers album
	$query = "
		SELECT 
			id_tome
		FROM 
			bd_edition 
		WHERE 
			id_edition=".$DB->escape($edition_id);

	$DB->query($query);
	$DB->next_record();

	echo GetMetaTag(1,"L'�dition a �t� activ�e",BDO_URL."admin/adminalbums.php?alb_id=".$DB->f("id_tome"));
	exit();

}


// AFFICHE UN FORMULAIRE VIDE
elseif($act=="new"){
	// determine si une r�f�rence d'album a �t� pass�
	if (isset($_GET["alb_id"])){
		$alb_id = intval($_GET["alb_id"]);

		$query = "SELECT titre FROM bd_tome WHERE id_tome = ".$DB->escape($alb_id);
		$DB->query($query);
		$DB->next_record();
		$alb_titre = $DB->f("titre");
	}else{
		$alb_titre = '';
		$alb_id = '';
	}

	$url_image = BDO_URL."images/couv/default.png";
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier � utiliser
	$t->set_file(array(
	"tpAdminEditionCouv" => "admin.edition.couv.tpl",
	"tpAdminEditionDetail" => "admin.edition.detail.tpl",
	"tpBody" => "admin.edition.tpl",
	"tpBase" => "body.tpl"
	));

	$t->set_var (array(
	"URLIMAGE" => $url_image,
	"NBUSERS" => "0",
	"IDTOME" => $alb_id,
	"TITRE" => $alb_titre,
	"URLDELETE" => "javascript:alert('D�sactiv�');",
	"ACTIONNAME" => "Enregistrer",
	"URLACTION" => BDO_URL."admin/admineditions.php?act=append"
	));
	// assigne la barre de login
	$t->set_var (array(
	"LOGINBARRE" => GetIdentificationBar(),
	"MENUBARRE" => admin_menu(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
	));
	$t->parse("BODY","tpBody");
	$t->parse("ADMINEDITIONDETAIL","tpAdminEditionDetail");
	$t->parse("ADMINEDITIONCOUV","tpAdminEditionCouv");
	$t->pparse("MyFinalOutput","tpBase");
}

// INSERE UNE NOUVELLE EDITION DANS LA BASE
elseif($act=="append"){
	$id_tome = $_POST['txtTomeId'];

	$_POST['txtDateParution'] = completeDate($_POST['txtDateParution']);
	if ($_POST['txtDateParution'] == "")
		$flag_dte_par = 1;
	else
		$flag_dte_par = (($_POST['FLAG_DTE_PARUTION'] == "1") ? "'1'" : "NULL");


	$query_el = array(
	"id_tome" => $DB->escape($_POST['txtTomeId']),
	"id_editeur" => $DB->escape($_POST['txtEditeurId']),
	"id_collection" => $DB->escape($_POST['txtCollecId']),
	"dte_parution" => sqlise($_POST['txtDateParution'],'text'),
	"flag_dte_parution" => $flag_dte_par,
	"ean" => sqlise($_POST['txtEAN'],'text'),
	"isbn" => sqlise($_POST['txtISBN'],'text'),
	"comment" => sqlise($_POST['txtComment'],'text'),
	"flg_tt" => (($_POST['chkTT'] == "checkbox") ? "'O'" : "'N'"),
	"validator" => $_SESSION["UserId"],
	"valid_dte" => 'NOW()'
	);
	$query = insert_query("bd_edition",$query_el);
	$DB->query ($query);

	// r�cup�re la valeur de la derni�re insertion
	$lid= mysql_insert_id();

	// Verifie la pr�sence d'une image � t�l�charger
	if (is_file($_FILES["txtFileLoc"]["tmp_name"])){ // un fichier � uploader
		$txtFileLoc = $_FILES["txtFileLoc"]["tmp_name"];
		$imageproperties = getimagesize($txtFileLoc);
		$imagetype = $imageproperties[2];
		$imagelargeur = $imageproperties[0];
		$imagehauteur = $imageproperties[1];
		// v�rifie le type d'image
		if (($imagetype != 1) and ($imagetype != 2)){
			echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Seul des fichiers JPEG ou GIF peuvent �tre charg�s. Vous allez �tre redirig�.';
			exit();
		}
		$uploaddir = BDO_DIR."images/couv/";
		$newfilename = "CV-".sprintf("%06d",$id_tome)."-".sprintf("%06d",$lid);
		if (($imagetype == 1)){
			$newfilename .=".gif";
		}else{
			$newfilename .=".jpg";
		}
		if(!copy($txtFileLoc,$uploaddir.$newfilename)){
			echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors de l\'envoi de l\'image au serveur. Vous allez �tre redirig�.';
			exit();
		}else{
			$img_couv=$newfilename;
		}
	}else if (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $_POST['txtFileURL'], $url_ary)){ // un fichier � t�l�charger
		$newfilename = "CV-".sprintf("%06d",$id_tome)."-".sprintf("%06d",$lid);
		$new_filename = get_img_from_url($_POST['txtFileURL'],BDO_DIR."images/tmp/",$newfilename);
		$tmp_filename = BDO_DIR."images/tmp/".$new_filename;

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

	if ($img_couv != '') {
		// met � jours la r�f�rence au fichier dans la table bd_edition
		$query = "
		UPDATE bd_edition SET
		`img_couv` = '".$DB->escape($img_couv)."'
		WHERE (`id_edition`=".$DB->escape($lid).")
		";
		$DB->query($query);
	}

	// On rajoute un redimensionnement si le correcteur l'a voulu
	if ($_POST["chkResize"] == "checked") {
		resize_edition_image($lid, BDO_DIR."images/couv/");
	}

	echo GetMetaTag(2,"L'�dition a �t� ajout�e",(BDO_URL."admin/adminalbums.php?alb_id=".$id_tome));
}

// AFFICHER UNE EDITION
elseif($act==""){

	// r�cup�rer le nombres dutilisateurs avec cette edition dans leur collection
	$query = "SELECT count(user_id) as nb_tome FROM users_album WHERE id_edition = ".$DB->escape($edition_id);
	$DB->query ($query);
	$DB->next_record();

	$nbusers = $DB->f("nb_tome");

	// R�cup�re l'adresse mail de l'utilisateur
	$query = "
	SELECT 
		e.id_edition, 
		t.id_tome, 
		e.user_id, 
		u.username, 
		u.email, 
		e.prop_dte, 
		t.titre, 
		s.nom serie
	FROM 
		bd_edition e, 
		bd_tome t, 
		bd_serie s, 
		users u
	WHERE 
		e.user_id = u.user_id 
		AND e.prop_status = 0 
		AND e.id_tome = t.id_tome 
		and t.id_serie = s.id_serie 
		AND e.id_edition = ".$DB->escape($edition_id);

	$DB->query ($query);
	$DB->next_record();
	$mail_adress = $DB->f("email");
	$mailsubject = "Votre proposition de nouvelle �dition pour l'album : ".$DB->f("titre");
	$pseudo = $DB->f("username");
	$DB->query ($query);
	$DB->next_record();

	// r�cup�re les donn�es principales
	$query = q_edition("en.id_edition = ".$DB->escape($edition_id));
	$DB->query ($query);
	$DB->next_record();

	// Determine l'URL image
	if (is_null($DB->f("img_couv")) | ($DB->f("img_couv")=='')){
		$url_image = BDO_URL_COUV."default.png";
	}else{
		$url_image = BDO_URL_COUV.$DB->f("img_couv");
		$dim_image = imgdim("$url_image");
	}
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier � utiliser
	$t->set_file(array(
	"tpAdminEditionCouv" => "admin.edition.couv.tpl",
	"tpAdminEditionDetail" => "admin.edition.detail.tpl",
	"tpBody" => "admin.edition.tpl",
	"tpBase" => "body.tpl"
	));
	// d�termine s'il est possible d'effacer cet album
	if (($DB->f("id_edition")==$DB->f("id_edition_default")) | ($nbusers > 0)){
		$url_delete = "javascript:alert('Impossible d\'effacer cette �dition');";
	}else{
		$url_delete = BDO_URL."admin/admineditions.php?act=delete&edition_id=".$edition_id;
	}
	// Activation de l'�dition
	if ($DB->f("prop_status") == 0) {
		$actionautorise = "<a href=\"".BDO_URL."admin/admineditions.php?act=autorize&edition_id=".$edition_id."\">Activer cette �dition</a>";
		$contactuser = "propos�e par <a href=\"mailto:".$mail_adress."?subject=".$mailsubject."\" style=\"font-weight: bold;\">".$pseudo."</a> (".$mail_adress.")<br />";
	}

	$t->set_var (array(
	"IDTOME" => $DB->f("id_tome"),
	"IDEDITION" => $edition_id,
	"TITRE" => stripslashes($DB->f("titre")),
	"IDEDIT" => $DB->f("id_editeur"),
	"EDITEUR" => htmlentities($DB->f("enom")),
	"IDCOLLEC" => $DB->f("id_collection"),
	"COLLECTION" => htmlentities($DB->f("cnom")),
	"DTPAR" => $DB->f("dte_parution"),
	"CHKFLAG_DTE_PARUTION" => (($DB->f("flag_dte_parution")==1) ? 'CHECKED' : ''),

	"COMMENT" => stripslashes($DB->f("comment")),
	"ISTT" => (($DB->f("flg_tt") == 'O') ? 'checked' : ''),
	"FLGDEF" => (($DB->f("id_edition")==$DB->f("id_edition_default") ? 'O' : '')),
	"EAN" => $DB->f("ean"),
	"URLEAN" => "http://www.bdnet.com/".$DB->f("ean")."/alb.htm",
	"ISBN" => $DB->f("isbn"),
	"URLISBN" => "http://www.amazon.fr/exec/obidos/ASIN/".$DB->f("isbn"),
	"URLIMAGE" => $url_image,
	"DIMIMAGE" => $dim_image,
	"NBUSERS" => $nbusers,
	"VIEWUSEREDITION" => "<a href='".BDO_URL."admin/viewUserEdition.php?id_edition=".$edition_id."'>(voir les utilisateurs)</a>",
	"ACTIONAUTORIZE" => $actionautorise,
	"CONTACTUSER" => $contactuser,
	"URLDELETE" => $url_delete,
	"URLFUSION" => BDO_URL."admin/mergealbums.php?source_id=".$DB->f("id_tome"),
	"URLFUSIONEDITION" => BDO_URL."admin/mergeeditions.php?source_id=".$edition_id,
	"URLEDITEDIT" => BDO_URL."admin/adminediteurs.php?editeur_id=".$DB->f("id_editeur"),
	"URLEDITCOLL" => BDO_URL."admin/admincollections.php?collec_id=".$DB->f("id_collection"),
	"ACTIONNAME" => "Valider les Modifications",
	"URLACTION" => BDO_URL."admin/admineditions.php?act=update"
	));
	if($DB->f("dte_parution") == "0000-00-00"){
		$t->set_var ("PARUTION_0","to_be_corrected");
	}

	// assigne la barre de login
	$t->set_var (array(
	"LOGINBARRE" => GetIdentificationBar(),
	"MENUBARRE" => admin_menu(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
	));
	$t->parse("BODY","tpBody");
	$t->parse("ADMINEDITIONDETAIL","tpAdminEditionDetail");
	$t->parse("ADMINEDITIONCOUV","tpAdminEditionCouv");
	$t->pparse("MyFinalOutput","tpBase");
}

function resize_edition_image($id_edition,$imagedir) {
	//Redimensionnement
	//*****************
	global $DB;

	// cherche les infos de cette �dition
	$query = "SELECT id_tome, img_couv FROM bd_edition WHERE id_edition = ". $DB->escape($id_edition);
	$DB->query($query);
	$DB->next_record();
	$id_tome = $DB->f("id_tome");
	$url_img = $DB->f("img_couv");


	if ($url_img == ''){
		echo "error : no image in database<br/>";
	} else {
		$newfilename = $url_img;

		$max_size = 180;

		//if ($_SERVER["SERVER_NAME"] != 'localhost')
		$imageproperties = getimagesize($imagedir.$newfilename);
		//else $imageproperties = false;

		if ($imageproperties != false)
		{
			$imagetype = $imageproperties[2];
			$imagelargeur = $imageproperties[0];
			$imagehauteur = $imageproperties[1];

			//D�termine s'il y a lieu de redimensionner l'image
			if ((($imagelargeur > $imagehauteur) && ($imagehauteur > $max_size)) || (($imagelargeur <= $imagehauteur) & ($imagelargeur > $max_size))) {

				if ($imagelargeur < $imagehauteur) {
					// image de type panorama : on limite la largeur � 128
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
					$source = imagecreatefromgif($imagedir.$newfilename);
					break;

				case "2":
					$source = imagecreatefromjpeg($imagedir.$newfilename);
					break;

				case "3":
					$source = imagecreatefrompng($imagedir.$newfilename);
					break;

				case "6":
					$source = imagecreatefrombmp($imagedir.$newfilename);
					break;
			}

			imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_w, $new_h, $imagelargeur, $imagehauteur);

			switch ($imagetype) {
				case "2":
					unlink($imagedir.$newfilename);
					imagejpeg($new_image,$imagedir.$newfilename,100);
					break;

				case "1":
				case "3":
				case "6":
					unlink($imagedir.$newfilename);
					$img_couv = substr($newfilename,0,strlen($newfilename)-3)."jpg";
					imagejpeg($new_image,$imagedir.$img_couv,100);


					// met � jours la r�f�rence au fichier dans la table bd_edition
					$query = "UPDATE bd_edition SET";
					$query .= " `img_couv` = '".$DB->escape($img_couv)."'";
					$query .=" WHERE (`id_edition`=".$DB->escape($id_edition).")";
					$DB->query($query);
			}
		} else
		{
			echo "error : no image properties <br/>";
		}

		echo "$new_w, $new_h, $imagelargeur, $imagehauteur<br />";
		echo "Image redimensionn�e<br />";
	}
}
