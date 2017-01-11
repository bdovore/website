<?php



minAccessLevel(1);

// LISTE LES PROPOSALS
if ($act=="")
{
	// Creation d'une nouvelle instance Fast Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "admin.add.from.url.tpl",
	"tpBase" => "body.tpl"));

	$t->set_var (array
	("URLACTION1" => BDO_URL."admin/adminfromurl.php?act=generate&source=bdnet",
	"URLACTION2" => BDO_URL."admin/adminfromurl.php?act=generate&source=amazon"));

	// assigne la barre de login
	$t->set_var (array
	("LOGINBARRE" => GetIdentificationBar(),
	"MENUBARRE" => admin_menu(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,));
	$t->parse("BODY","tpBody");
	$t->pparse("MyFinalOutput","tpBase");

}

//SUPPRESSION DE PROPOSAL

elseif($act=="generate")
{
	if($source=="bdnet")
	{
		//Récupère la page html
		$file_content = file_get_contents($_POST["txtURLBDNET"]);

		//titre
		$pointeur_b = strpos($file_content,"<TITLE>")+7;
		$pointeur_e = strpos($file_content," - (",$pointeur_b);
		$titre = substr($file_content,$pointeur_b,$pointeur_e-$pointeur_b);
		//Serie
		$pointeur_b = strpos($file_content,"<B>S&eacute;rie: </B>")+21;
		$pointeur_b = strpos($file_content,">",$pointeur_b)+1;
		$pointeur_e = strpos($file_content,"</A>",$pointeur_b);
		$serie = substr($file_content,$pointeur_b,$pointeur_e-$pointeur_b);
		// Dessinateur
		$pointeur_b = strpos($file_content,"<B>Dessinateur:</B>")+19;
		$pointeur_b = strpos($file_content,">",$pointeur_b)+1;
		$pointeur_e = strpos($file_content,"</A>",$pointeur_b);
		$dessin = substr($file_content,$pointeur_b,$pointeur_e-$pointeur_b);
		// Scenariste
		$pointeur_b = strpos($file_content,"<B>Sc&eacute;nariste:</B>")+25;
		$pointeur_b = strpos($file_content,">",$pointeur_b)+1;
		$pointeur_e = strpos($file_content,"</A>",$pointeur_b);
		$scenar = substr($file_content,$pointeur_b,$pointeur_e-$pointeur_b);
		// Genre
		$pointeur_b = strpos($file_content,"<B>Genre:</B>")+25;
		$pointeur_b = strpos($file_content,">",$pointeur_b)+1;
		$pointeur_e = strpos($file_content,"</A>",$pointeur_b);
		$genre = substr($file_content,$pointeur_b,$pointeur_e-$pointeur_b);
		// Editeur
		$pointeur_b = strpos($file_content,"<B>Editeur:</B>")+15;
		$pointeur_b = strpos($file_content,">",$pointeur_b)+1;
		$pointeur_e = strpos($file_content,"</A>",$pointeur_b);
		$editeur = substr($file_content,$pointeur_b,$pointeur_e-$pointeur_b);
		// Date Parution
		if (strpos($file_content,"Paru en: ")) {
			$pointeur_b = strpos($file_content,"Paru en: ")+9;
			$pointeur_e = strpos($file_content,"<",$pointeur_b);
			$dte_parution = cv_date(substr($file_content,$pointeur_b,$pointeur_e-$pointeur_b));
		}
		//URL_Image
		$pointeur_b = strpos($file_content,"<TD WIDTH=\"150\" VALIGN=\"MIDDLE\"><img src=\"")+42;
		$pointeur_e = strpos($file_content,"\"",$pointeur_b);
		$tmp_url = substr($file_content,$pointeur_b,$pointeur_e-$pointeur_b);
		if (strpos($file_content,"http")){
			$url_image = $tmp_url;
		}
		//Tome
		$pointeur_b = strpos($file_content,"tome ")+5;
		$pointeur_e = strpos($file_content,"<",$pointeur_b);
		$tome = substr($file_content,$pointeur_b,$pointeur_e-$pointeur_b);
	}else{
		echo "amazon";
	}


	$query = "INSERT INTO `users_alb_prop` (`USER_ID`,`PROP_DTE`, `PROP_TYPE`, `ACTION`, `NOTIF_MAIL`, `TITRE`,
	`NUM_TOME`, `ID_SERIE`, `SERIE`, `DTE_PARUTION`, `ID_GENRE`, `GENRE`, `ID_EDITEUR`, `EDITEUR`, `ID_SCENAR`,
	`SCENAR`, `ID_DESSIN`, `DESSIN`, `ID_COLOR`, `COLOR`, `ID_COLLECTION`, `COLLECTION`, `HISTOIRE`,`URL_BDNET`
	) VALUES (" . $DB->escape($_SESSION["UserId"]) . ", NOW(), 'AJOUT', 2, 0, ";
	$query .= sqlise($titre,'text').", ".sqlise($tome,'text').", NULL, ".sqlise($serie,'text').", ".sqlise($dte_parution,'text'). ", ";
	$query .= "NULL, ".sqlise($genre,'text').", NULL, ".sqlise($editeur,'text').", NULL, ".sqlise($scenar,'text').", ";
	$query .= "NULL, ".sqlise($dessin,'text').", NULL, NULL, ";
	$query .= "NULL, NULL, NULL, ".sqlise($_POST["txtURLBDNET"],'text').");";
	$DB->query ($query);
	//echo $query;
	// récupère la valeur de la dernière insertion
	$lid= mysql_insert_id();

	if (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $url_image, $url_ary))
	{ // un fichier à télécharger
		if ( empty($url_ary[4]) )
		{
			echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image incomplète. Vous allez être redirigé.';
			exit();
		}
		$base_get = '/' . $url_ary[4];
		$port = ( !empty($url_ary[3]) ) ? $url_ary[3] : 80;
		// Connection au serveur hébergeant l'image
		if ( !($fsock = @fsockopen($url_ary[2], $port, $errno, $errstr)) )
		{
			$error = true;
			echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image innacessible. Vous allez être redirigé.';
			exit();
		}

		// Récupère l'image
		@fputs($fsock, "GET $base_get HTTP/1.1\r\n");
		@fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
		@fputs($fsock, "Connection: close\r\n\r\n");

		unset($avatar_data);
		while( !@feof($fsock) )
		{
			$avatar_data .= @fread($fsock, 102400);
		}
		@fclose($fsock);

		// Check la validité de l'image
		if (!preg_match('#Content-Length\: ([0-9]+)[^ /][\s]+#i', $avatar_data, $file_data1) || !preg_match('#Content-Type\: image/[x\-]*([a-z]+)[\s]+#i', $avatar_data, $file_data2))
		{
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

		if ( $bytes_written != $avatar_filesize )
		{
			@unlink($tmp_filename);
			echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Could not write avatar file to local storage. Please contact the board administrator with this message. Vous allez être redirigé.';
			exit();
		}
		// newfilemname
		if ( !($imgtype = check_image_type($avatar_filetype, $error)) )
		{
			exit;
		}

		$new_filename = sprintf("tmpCV-%06d-01",$lid).$imgtype;

		// si le fichier existe, on l'efface
		if (file_exists(BDO_DIR."images/tmp/$new_filename"))
		{
			@unlink(BDO_DIR."images/tmp/$new_filename");
		}

		// copie le fichier temporaire dans le repertoire image
		@copy($tmp_filename, BDO_DIR."images/tmp/$new_filename");
		@unlink($tmp_filename);

		$img_couv=$new_filename;

		// met à jours la référence au fichier dans la base
		$query = "UPDATE users_alb_prop SET
		`img_couv` = '".$DB->escape($img_couv)."'
		WHERE (`id_proposal`=".$lid.");";
		$DB->query($query);
	}

	echo GetMetaTag(2,"L'album a été ajouté. Merci de le valider.",(BDO_URL."admin/adminproposals.php"));
}
