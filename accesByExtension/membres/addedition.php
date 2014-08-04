<?php



minAccessLevel(2);

// Emplacement des fonctions
function cv_date_bd($date) {
	$mois = substr(month_to_text((int)substr($date,5,2)),0,3).".";
	$annee =substr($date,0,4);
	return $mois." ".$annee;
}


// récupère les variables postées
$id_editeur = $_POST["txtEditeurId"];
$editeur = $_POST["txtEditeur"];
$id_collection = $_POST["txtCollecId"];
$collection = $_POST["txtCollec"];
$dte_parution = $_POST["txtDateParution"];
if($dte_parution == "0000-00-00"){
	$date = "";
}else{
	$date = $_POST['txtDateParution'];
}
$isbn = $_POST["txtISBN"];
$ean = $_POST["txtEAN"];
$is_eo = $_POST["chkEO"];
$is_tt = $_POST["chkTT"];
$file_url = $_POST["txtFileURL"];
$describ = $_POST["txtDescrib"];

// insertion de la nouvelle édition en tant que proposition
if ($act=="append"){

	$id_tome = $_POST['txtTomeId'];

	$query_el = array(
	"id_tome" => $DB->escape($_POST['txtTomeId']),
	"id_editeur" => $DB->escape($_POST['txtEditeurId']),
	"id_collection" => $DB->escape($_POST['txtCollecId']),
	"dte_parution" => sqlise($date,'text'),
	"flg_eo" => (($_POST['chkEO'] == "checked") ? "'O'" : "'N'"),
	"flg_tt" => (($_POST['chkTT'] == "checked") ? "'O'" : "'N'"),
	"comment" => sqlise($_POST['txtDescrib'],'text'),
	"user_id" => $_SESSION["UserId"],
	"prop_dte" => "NOW()",
	"prop_status" => 0,
	"ISBN" => sqlise($_POST['txtISBN'],'text'),
	"EAN" => sqlise($_POST['txtEAN'],'text')
	);
	$query = insert_query("bd_edition",$query_el);
	$DB->query ($query);

	// récupère la valeur de la dernière insertion
	$lid= mysql_insert_id();

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
			$newfilename = "CV-".sprintf("%06d",$id_tome)."-".sprintf("%06d",$lid);
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
		}
		else if (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $_POST['txtFileURL'], $url_ary)){ // un fichier à télécharg ?????A	?	$ier
			$newfilename = "CV-".sprintf("%06d",$id_tome)."-".sprintf("%06d",$lid);
			$new_filename = get_img_from_url($_POST['txtFileURL'],BDO_DIR."images/tmp/",$newfilename);
			$tmp_filename = BDO_DIR."images/tmp/".$new_filename;

			// si le fichier existe, on l'efface
			if (file_exists(BDO_DIR."images/couv/$new_filename")){
				unlink(BDO_DIR."images/couv/$new_filename");
			}

			// copie le fichier temporaire dans le repertoire image
			copy($tmp_filename, BDO_DIR."images/couv/$new_filename");
			unlink($tmp_filename);
			$img_couv=$new_filename;
		}else{
			$img_couv='';
		}

		// met à jour la référence au fichier dans la table bd_edition
		$query = "
		UPDATE bd_edition SET
			`img_couv` = '".$DB->escape($img_couv)."'
		WHERE
			`id_edition`=".$DB->escape($lid);
		$DB->query($query);
	}
	// N'ajoute pas automatiquement l'édition mais se contente dé confirmer qu'elle a été proposée
	echo GetMetaTag(3,"La nouvelle édition a bien été proposée pour être ajoutée à la base de données BDovore. Elle sera prochainement traitée part l'équipe de validation et votre collection sera mise à jour en conséquence. Merci de votre contribution à la base BDovore.","javascript:window.close()");
}

// Affichage du formulaire
if ($act==''){
	// variables générales
	// Creation d'une nouvelle instance Fast Template
	$t = new Template(BDO_DIR."public/templates");

	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "user.add.edition.tpl"));

	// Converti les variable generales
	$t->set_var (array(
	"IDTOME" => $id_tome,
	"IDEDIT" => $id_editeur,
	"EDITEUR" => stripslashes($editeur),
	"IDCOLLEC" => $id_collection,
	"COLLECTION" => stripslashes($collection),
	"DTPAR" => $dte_parution,
	"ISEO" => $is_eo,
	"ISTT" => $is_tt,
	"ORIURL" => $file_url,
	"ISBN" => $isbn,
	"EAN" => $ean,
	"DESCRIB" => stripslashes($describ),
	"ERRORMESSAGE" => $error_message,
	"ACTIONNAME" => "Valider",
	"URLACTION" => BDO_URL."membres/addedition.php?act=append",
	));

	// assigne la barre de login
	$t->set_var (array(
	"LOGINBARRE" => GetIdentificationBar(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
	));
	$t->pparse("MyFinalOutput","tpBody");
}
