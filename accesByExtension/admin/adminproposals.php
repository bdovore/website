<?php



include (BDO_DIR."inc/function.cle.inc.php");
include (BDO_DIR."inc/class_bdovore.php");

minAccessLevel(1);


// Tableau pour les choix d'options
$opt_type[0][0] = 0;
$opt_type[0][1] = 'Album';
$opt_type[1][0] = 1;
$opt_type[1][1] = 'Coffret';

$opt_action[0] = "Ins�rer dans la collection";
$opt_action[1] = "Ins�rer comme achat futur";
$opt_action[2] = "Aucune";

$opt_status[0][0] = 0;
$opt_status[0][1] = "En cours";
$opt_status[1][0] = 2;
$opt_status[1][1] = "En pause";
$opt_status[2][0] = 3;
$opt_status[2][1] = "Aide requise";
$opt_status[3][0] = 4;
$opt_status[3][1] = "Aide apport�e";
//Options non n�cessaires ici et qui rendrait l'objet SELECT trop large dans le tableau recap des propals :
//$opt_status[3][0] = 1;
//$opt_status[3][1] = "Accept�e";
//$opt_status[3][0] = 98;
//$opt_status[3][1] = "Supprim�e par l'utilisateur";
//$opt_status[4][0] = 99;
//$opt_status[4][1] = "Supprim�e";

// V�rifie si il s'agit d'un update et si oui, modifie le act � la vol�e
$act = $_REQUEST["act"];
$update = $_REQUEST["chkUpdate"];
if ($update == 'O') {
	$act = "update";
}

$validationdelay = 21;//nbre de jours apr�s lesquels on ne valide pas (pour les parutions futures)
$datebeforevalid = "Ne pas valider les albums qui paraissent apr�s le " . date("d/m/Y", mktime(0, 0, 0, date("m"),date("d")+$validationdelay,date("Y"))) . " ($validationdelay jours)";



// LISTE LES PROPOSALS
if ($act==""){
	$titre_admin = "Nouveaux Albums en attente";
	if ($cle == ""){
		$cle=1;
	}
	if ($sort == "DESC"){
		$sort = " DESC";
	}else{
		$sort="";
	}
	// Selection des champs � afficher
	$clerep[1] = "id_proposal";
	$clerep[2] = "prop_dte";
	$clerep[3] = "user_id";
	$clerep[4] = "titre";
	$clerep[5] = "serie";

	$orderby = $clerep[$cle];

	$query = "SELECT id_proposal, p.user_id, u.username, prop_dte, titre, serie, status, dte_parution
		FROM users_alb_prop p, users u
		WHERE p.user_id = u.user_id AND
		      p.status <> 98 AND
			  p.status <> 99 AND
			  p.status <> 1 AND
			  p.PROP_TYPE='AJOUT'
		ORDER BY ".$orderby.$sort;

	$DB->query ($query);

	// Creation d'une nouvelle instance Fast Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier � utiliser
	$t->set_file(array(
	"tpBody" => "admin.proposals.tpl",
	"tpBase" => "body.tpl"
	));
	// on d�clare le block � utiliser
	$t->set_block('tpBody','PropBlock','PBlock');

	$t->set_var (array("DATEBEFOREVALID" => $datebeforevalid));

	//Liste les propositions
	while ($DB->next_record()){
		$color_status = "";
		switch ($DB->f("status")) {
			case "0":
				$color_status = "#E7CCBD";
				break;
			case "2":
				$color_status = "#FFDB70";
				break;
			case "3":
				$color_status = "#8374E7";
				break;
			case "4":
				$color_status = "#82FF70";
				break;
		}
		$stylevaliddelay = "";
		$date_parution = $DB->f ("dte_parution");
		$date_courante_21 = date("Y-m-d", mktime(0, 0, 0, date("d")+$validationdelay, date("m"), date("Y")));
		if ($date_parution > $date_courante_21){
			$stylevaliddelay = 'style="background-color: #FFDB70;" title="Parution le '.$DB->f ("dte_parution").'"';
		}
		$t->set_var (array(
		"TITRE_ADMIN" => $titre_admin,
		"COLOR_STATUS" => $color_status,
		"ID" => $DB->f ("id_proposal"),
		"DATE" => $DB->f ("prop_dte"),
		"USER" => $DB->f ("username"),
		"TITRE" => stripslashes($DB->f ("titre")),
		"SERIE" => stripslashes($DB->f ("serie")),
		"DATE_PARUTION" => $DB->f ("dte_parution"),
		"STYLE_VALIDDELAY" => $stylevaliddelay,
		"OPTIONSTATUS" => GetOption1Value($opt_status,$DB->f("status")),
		"URLEDIT" => BDO_URL."admin/adminproposals.php?act=valid&propid=".$DB->f ("id_proposal"),
		"URLDELETE" => BDO_URL."admin/adminproposals.php?act=supprim_list&propid=".$DB->f ("id_proposal")
		));
		$t->parse ("PBlock", "PropBlock",true);
	}

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

//SUPPRESSION DE PROPOSAL
elseif($act=="supprim")
{
	// V�rifie l'existence d'une couverture

	$query = "
	SELECT 
		user_id, 
		img_couv, 
		action, 
		notif_mail, 
		titre 
	FROM 
		users_alb_prop 
	WHERE id_proposal = ".$DB->escape($propid);
	
	$DB->query ($query);
	$DB->next_record();
	$prop_user = $DB->f("user_id");
	$prop_img = $DB->f("img_couv");
	$prop_action = $DB->f("action");
	$prop_titre = $DB->f("titre");
	$notif_mail = $DB->f("notif_mail");

	if ($prop_img != ''){
		$filename = $prop_img;
		if (file_exists(BDO_DIR."images/tmp/$filename")){
			unlink(BDO_DIR."images/tmp/$filename");
		}
	}
	//Effacement virtuel de l'album
	$query = "
	UPDATE users_alb_prop SET 
		`STATUS` = 99, 
		`VALIDATOR` = " . $DB->escape($_SESSION["UserId"]) . " , 
		`VALID_DTE` = NOW() 
	WHERE
		id_proposal=".$DB->escape($propid);
	
	$DB->query ($query);

	//Envoi d'un email
	$query = "SELECT email FROM users WHERE user_id = ".$DB->escape($prop_user);
	$DB->query ($query);
	$DB->next_record();
	$mail_adress = $DB->f("email");
	$mail_sujet = "Votre proposition d'ajout dans la base BDOVORE";
	$mail_entete = "From: no-reply@bdovore.com";
	$mail_text = stripslashes($_POST["txtMailRefus"])."\n\n";
	mail($mail_adress,$mail_sujet,$mail_text,$mail_entete);

	$query = "
	SELECT 
		id_proposal 
		FROM 
		users_alb_prop 
	WHERE 
		id_proposal > ".$DB->escape($propid)." 
		AND status = 0 
		AND prop_type = 'AJOUT' 
	ORDER BY id_proposal";
	
	$DB->query ($query);

	if ($DB->num_rows() > 0){
		$DB->next_record();
		$next_url = BDO_URL."admin/adminproposals.php?act=valid&propid=".$DB-> f('id_proposal');
	}else{
		$next_url = BDO_URL."admin/adminproposals.php";
	}
	//rouvre la page
	echo GetMetaTag(1,"La proposition a �t� effac�e",($next_url));
	exit;
}

//SUPPRESSION DE PROPOSAL DEPUIS LE TABLEAU RECAPITULATIF (pas d'envoi d'email)
elseif($act=="supprim_list")
{
	// V�rifie l'existence d'une couverture

	$query = "
	SELECT 
		user_id, 
		img_couv, 
		action, 
		notif_mail, 
		titre 
	FROM 
		users_alb_prop 
	WHERE 
		id_proposal = ".$DB->escape($propid);
	
	$DB->query ($query);
	$DB->next_record();
	$prop_user = $DB->f("user_id");
	$prop_img = $DB->f("img_couv");
	$prop_action = $DB->f("action");
	$prop_titre = $DB->f("titre");

	if ($prop_img != ''){
		$filename = $prop_img;
		if (file_exists(BDO_DIR."images/tmp/$filename")){
			unlink(BDO_DIR."images/tmp/$filename");
		}
	}
	//Effacement virtuel de l'album
	$query = "
	UPDATE users_alb_prop SET 
		`STATUS` = 99, 
		`VALIDATOR` = " . $DB->escape($_SESSION["UserId"]) . " , 
		`VALID_DTE` = NOW() 
	WHERE
		id_proposal=".$DB->escape($propid);
	$DB->query ($query);

	$query = "
	SELECT 
		id_proposal 
	FROM 
		users_alb_prop 
	WHERE
		id_proposal > ".$DB->escape($propid)." 
		AND status = 0 
		AND prop_type = 'AJOUT' 
		ORDER BY id_proposal";
	$DB->query ($query);

	if ($DB->num_rows() > 0){
		$DB->next_record();
		$next_url = BDO_URL."admin/adminproposals.php?act=valid&propid=".$DB-> f('id_proposal');
	}else{
		$next_url = BDO_URL."admin/adminproposals.php";
	}
	//rouvre la page
	echo GetMetaTag(1,"La proposition a �t� effac�e",($next_url));
	exit;
}

// AFFICHAGE D'UN PROPOSAL
elseif($act=="valid")
{


	$query = "
	SELECT 
		users_alb_prop.USER_ID, 
		users_alb_prop.ID_PROPOSAL, 
		users_alb_prop.ACTION, 
		users_alb_prop.TITRE, 
		users_alb_prop.NUM_TOME, 
		users_alb_prop.PRIX, 
		users_alb_prop.ID_SERIE, 
		users_alb_prop.SERIE AS ORISERIE, 
		bd_serie.NOM AS SERIE, 
		users_alb_prop.DTE_PARUTION, 
		users_alb_prop.ID_GENRE, 
		users_alb_prop.GENRE AS ORIGENRE,
		bd_genre.LIBELLE AS GENRE, 
		users_alb_prop.ID_EDITEUR, 
		users_alb_prop.EDITEUR AS ORIEDITEUR, 
		bd_editeur.NOM AS EDITEUR_NOM, 
		users_alb_prop.ID_SCENAR, 
		users_alb_prop.SCENAR AS ORISCENAR, 
		bd_auteur.PSEUDO AS PSEUDO_SCENAR, 
		users_alb_prop.ID_SCENAR_ALT, 
		users_alb_prop.SCENAR_ALT AS ORISCENARALT, 
		bd_auteur_3.PSEUDO AS PSEUDO_SCENAR_ALT, 
		users_alb_prop.ID_DESSIN, 
		users_alb_prop.DESSIN AS ORIDESSIN,
		bd_auteur_1.PSEUDO AS PSEUDO_DESSIN, 
		users_alb_prop.ID_DESSIN_ALT, 
		users_alb_prop.DESSIN_ALT AS ORIDESSINALT, 
		bd_auteur_4.PSEUDO AS PSEUDO_DESSIN_ALT, 
		users_alb_prop.ID_COLOR, 
		users_alb_prop.COLOR AS ORICOLOR, 
		bd_auteur_2.PSEUDO AS PSEUDO_COLOR, 
		users_alb_prop.ID_COLOR_ALT, 
		users_alb_prop.COLOR_ALT AS ORICOLORALT, 
		bd_auteur_5.PSEUDO AS PSEUDO_COLOR_ALT, 
		users_alb_prop.DESCRIB_EDITION, 
		users_alb_prop.ID_COLLECTION, 
		users_alb_prop.COLLECTION AS ORICOLLECTION, 
		bd_collection.NOM AS COLLECTION, 
		users_alb_prop.HISTOIRE, 
		users_alb_prop.IMG_COUV,
		users_alb_prop.FLG_INT, 
		users_alb_prop.FLG_TYPE, 
		users_alb_prop.FLG_TT, 
		users_alb_prop.EAN, 
		users_alb_prop.ISBN, 
		users_alb_prop.PRIX, 
		users_alb_prop.DESCRIB_EDITION, 
		users_alb_prop.CORR_COMMENT, 
		users_alb_prop.STATUS
	FROM (((((((((
		users_alb_prop 
		LEFT JOIN bd_serie ON users_alb_prop.ID_SERIE = bd_serie.ID_SERIE) 
		LEFT JOIN bd_genre ON users_alb_prop.ID_GENRE = bd_genre.ID_GENRE) 
		LEFT JOIN bd_editeur ON users_alb_prop.ID_EDITEUR = bd_editeur.ID_EDITEUR) 
		LEFT JOIN bd_auteur ON users_alb_prop.ID_SCENAR = bd_auteur.ID_AUTEUR) 
		LEFT JOIN bd_auteur AS bd_auteur_1 ON users_alb_prop.ID_DESSIN = bd_auteur_1.ID_AUTEUR) 
		LEFT JOIN bd_auteur AS bd_auteur_2 ON users_alb_prop.ID_COLOR = bd_auteur_2.ID_AUTEUR) 
		LEFT JOIN bd_collection ON users_alb_prop.ID_COLLECTION = bd_collection.ID_COLLECTION) 
		LEFT JOIN bd_auteur as bd_auteur_3 ON users_alb_prop.ID_SCENAR_ALT = bd_auteur_3.ID_AUTEUR) 
		LEFT JOIN bd_auteur as bd_auteur_4 ON users_alb_prop.ID_DESSIN_ALT = bd_auteur_4.ID_AUTEUR) 
		LEFT JOIN bd_auteur as bd_auteur_5 ON users_alb_prop.ID_COLOR_ALT = bd_auteur_5.ID_AUTEUR
	WHERE 
		users_alb_prop.ID_PROPOSAL =".$DB->escape($propid) ;
	$DB->query ($query);
	$DB->next_record();
	// Determine l'URL image
	if (is_null($DB->f("IMG_COUV")) | ($DB->f("IMG_COUV")=='')){
		$url_image = BDO_URL_COUV."default.png";
	}else{
		$url_image = BDO_URL_IMAGE."tmp/".$DB->f("IMG_COUV");
		$dim_image = imgdim($url_image);
	}

	$prop_user = $DB->f("USER_ID");
	$titre = stripslashes($DB->f("TITRE"));

	$color_status = "";
	switch ($DB->f("status")) {
		case "0":
			$color_status = "#FFFFFF";
			break;
		case "2":
			$color_status = "#FFDB70";
			break;
		case "3":
			$color_status = "#8374E7";
			break;
	}

	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier � utiliser
	$t->set_file(array(
	"tpBody" => "admin.valid.prop.tpl",
	"tpBase" => "body.tpl"
	));

	$t->set_var (array(
	"DATEBEFOREVALID" => $datebeforevalid,
	"PROPID" => stripslashes($DB->f("ID_PROPOSAL")),
	"TITRE" => stripslashes($DB->f("TITRE")),
	"CLTITRE" => ($DB->f("TITRE")!='' ? "flat" : "to_be_corrected"),
	"ORITITRE" => stripslashes($DB->f("TITRE")),
	"IDSERIE" => stripslashes($DB->f("ID_SERIE")),
	"CLIDSERIE" => (is_numeric($DB->f("ID_SERIE")) ? "flat" : "to_be_corrected"),
	"ORISERIE" => htmlentities(stripslashes($DB->f("ORISERIE"))),
	"TOME" => $DB->f("NUM_TOME"),
	"CLTOME" => "flat",
	"PRIX_VENTE" => $DB->f("PRIX"),
	"ISINT" => (($DB->f("FLG_INT")=='O') ? 'checked' : ''),
	"OPTTYPE" => GetOptionValue($opt_type,$DB->f("FLG_TYPE")),
	"IDGENRE" => $DB->f("ID_GENRE"),
	"CLIDGENRE" => (is_numeric($DB->f("ID_GENRE")) ? "flat" : "to_be_corrected"),
	"ORIGENRE" => htmlentities($DB->f("ORIGENRE")),
	"IDSCEN" => $DB->f("ID_SCENAR"),
	"CLIDSCEN" => (is_numeric($DB->f("ID_SCENAR")) ? "flat" : "to_be_corrected"),
	"ORISCENARISTE" => htmlentities($DB->f("ORISCENAR")),
	"IDSCENALT" => $DB->f("ID_SCENAR_ALT"),
	"CLIDSCENALT" => "flat",
	"ORISCENARISTEALT" => htmlentities($DB->f("ORISCENARALT")),
	"IDEDIT" => $DB->f("ID_EDITEUR"),
	"CLIDEDIT" => (is_numeric($DB->f("ID_EDITEUR")) ? "flat" : "to_be_corrected"),
	"ORIEDITEUR" => htmlentities($DB->f("ORIEDITEUR")),
	"IDDESS" => $DB->f("ID_DESSIN"),
	"CLIDDESS" => (is_numeric($DB->f("ID_DESSIN")) ? "flat" : "to_be_corrected"),
	"ORIDESSINATEUR" => htmlentities($DB->f("ORIDESSIN")),
	"IDDESSALT" => $DB->f("ID_DESSIN_ALT"),
	"CLIDDESSALT" => "flat",
	"ORIDESSINATEURALT" => htmlentities($DB->f("ORIDESSINALT")),
	"IDCOLOR" => $DB->f("ID_COLOR"),
	"CLIDCOLOR" => (is_numeric($DB->f("ID_COLOR")) ? "flat" : "to_be_corrected"),
	"ORICOLORISTE" => htmlentities($DB->f("ORICOLOR")),
	"IDCOLORALT" => $DB->f("ID_COLOR_ALT"),
	"CLIDCOLORALT" => "flat",
	"ORICOLORISTEALT" => htmlentities($DB->f("ORICOLORALT")),
	"IDCOLLEC" => $DB->f("ID_COLLECTION"),
	"CLIDCOLLEC" => (is_numeric($DB->f("ID_COLLECTION")) ? "flat" : "to_be_corrected"),
	"ORICOLLECTION" => htmlentities($DB->f("ORICOLLECTION")),
	"DTPAR" => $DB->f("DTE_PARUTION"),
	"EAN" => $DB->f("EAN"),
	"URLEAN" => "http://www.bdnet.com/".$DB->f("EAN")."/alb.htm",
	"ISEAN" => check_EAN($DB->f("EAN")) ? "" : "*",
	"ISBN" => $DB->f("ISBN"),
	"URLISBN" => "http://www.amazon.fr/exec/obidos/ASIN/".$DB->f("ISBN"),
	"ISISBN" => check_ISBN($DB->f("ISBN")) ? "" : "*",
	"PRIX" => $DB->f("PRIX"),
	"ISTT" => (($DB->f("FLG_TT") == 'O') ? 'checked' : ''),
	"CLDTPAR" => "flat",
	"URLIMAGE" => $url_image,
	"DIMIMAGE" => $dim_image,
	"HISTOIRE" => stripslashes($DB->f("HISTOIRE")),
	"SERIE" => is_null($DB->f("ID_SERIE")) ? htmlentities(stripslashes($DB->f("ORISERIE"))) : htmlentities(stripslashes($DB->f("SERIE"))),
	"CLSERIE" => ($DB->f("SERIE")==$DB->f("ORISERIE") ? "flat" : "has_changed"),
	"GENRE" => is_null($DB->f("ID_GENRE")) ? htmlentities($DB->f("ORIGENRE")) : htmlentities($DB->f("GENRE")),
	"CLGENRE" => ($DB->f("GENRE")==$DB->f("ORIGENRE") ? "flat" : "has_changed"),
	"SCENARISTE" => is_null($DB->f("ID_SCENAR")) ?  htmlentities($DB->f("ORISCENAR")) :htmlentities($DB->f("PSEUDO_SCENAR")),
	"CLSCENARISTE" => ($DB->f("PSEUDO_SCENAR")==$DB->f("ORISCENAR") ? "flat" : "has_changed"),
	"SCENARISTEALT" => is_null($DB->f("ID_SCENAR_ALT")) ?  htmlentities($DB->f("ORISCENARALT")) :htmlentities($DB->f("PSEUDO_SCENAR_ALT")),
	"CLSCENARISTEALT" => ($DB->f("PSEUDO_SCENAR_ALT")==$DB->f("ORISCENARALT") ? "flat" : "has_changed"),
	"DESSINATEUR" => is_null($DB->f("ID_DESSIN")) ?  htmlentities($DB->f("ORIDESSIN")) :htmlentities($DB->f("PSEUDO_DESSIN")),
	"CLDESSINATEUR" => ($DB->f("PSEUDO_DESSIN")==$DB->f("ORIDESSIN") ? "flat" : "has_changed"),
	"DESSINATEURALT" => is_null($DB->f("ID_DESSIN_ALT")) ?  htmlentities($DB->f("ORIDESSINALT")) :htmlentities($DB->f("PSEUDO_DESSIN_ALT")),
	"CLDESSINATEURALT" => ($DB->f("PSEUDO_DESSIN_ALT")==$DB->f("ORIDESSINALT") ? "flat" : "has_changed"),
	"COLORISTE" => is_null($DB->f("ID_COLOR")) ?  htmlentities($DB->f("ORICOLOR")) :htmlentities($DB->f("PSEUDO_COLOR")),
	"CLCOLORISTE" => ($DB->f("PSEUDO_COLOR")==$DB->f("ORICOLOR") ? "flat" : "has_changed"),
	"COLORISTEALT" => is_null($DB->f("ID_COLOR_ALT")) ?  htmlentities($DB->f("ORICOLORALT")) :htmlentities($DB->f("PSEUDO_COLOR_ALT")),
	"CLCOLORISTEALT" => ($DB->f("PSEUDO_COLOR_ALT")==$DB->f("ORICOLORALT") ? "flat" : "has_changed"),
	"EDITEUR" => is_null($DB->f("ID_EDITEUR")) ?  htmlentities($DB->f("ORIEDITEUR")) :htmlentities($DB->f("EDITEUR_NOM")),
	"CLEDITEUR" => ($DB->f("EDITEUR_NOM")==$DB->f("ORIEDITEUR") ? "flat" : "has_changed"),
	"COLLECTION" => is_null($DB->f("ID_COLLECTION")) ?  htmlentities($DB->f("ORICOLLECTION")) :htmlentities($DB->f("COLLECTION")),
	"CLCOLLECTION" => ($DB->f("COLLECTION")==$DB->f("ORICOLLECTION") ? "flat" : "has_changed"),
	"COMMENT" => stripslashes($DB->f("DESCRIB_EDITION")),
	"CORRCOMMENT" => $DB->f("CORR_COMMENT"),
	"OPTIONSTATUS" => GetOptionValue($opt_status,$DB->f("STATUS")),
	"COLOR_STATUS" => $color_status,
	"PROPACTION" => $DB->f("ACTION"),
	"ACTIONUTIL" => $opt_action[$DB->f("ACTION")],
	"ACTIONNAME" => "Valider",
	"URLACTION" => BDO_URL."admin/adminproposals.php?act=append&propid=$propid",
	"URLUTILVALID" => BDO_URL."admin/adminproposals.php?act=merge&propid=$propid",
	"URLCOMMENTCORR" => BDO_URL."admin/adminproposals.php?act=comment&propid=$propid",
	"URLDELETE" => BDO_URL."admin/adminproposals.php?act=supprim&propid=".$DB->f ("ID_PROPOSAL"),
	));

	// Exemple d'email en cas de suppression
	$mail_sujet = "Votre proposition d'ajout dans la base BDOVORE";
	$mail_body = "Bonjour, \n";
	$mail_body .= "Votre proposition ";
	$mail_body .= '"'.$titre.'"';
	$mail_body .= " a �t� refus�e par l'�quipe de correction. \n";
	$mail_body .= "- Les informations que vous avez fournies n'�taient pas suffisantes. \n";
	$mail_body .= "- La proposition d'un autre membre a �t� pr�f�r�e ou valid�e avant. \n";
	$mail_body .= "- Nous consid�rons que cet album n'a pas de rapport suffisamment proche � la bande dessin�e pour �tre int�gr� � la base de donn�es du site. \n";
	$mail_body .= "- Cet album figurait d�j� dans votre collection. \n";
	$mail_body .= "Si l'�dition par d�faut de cet album ne correspond pas � celle que vous poss�dez,";
	$mail_body .= "	d'autres �ditions sont peut-�tre d�j� pr�sentes dans la base et peuvent �tre s�lectionn�es en cliquant sur l'album en question depuis votre garde-manger (menu d�roulant [Mon �dition] des fiches album). \n";
	$mail_body .= "Si ce n'est pas le cas, vous pouvez faire une proposition de nouvelle �dition via ce m�me menu d�roulant.\n\n";
	$mail_body .= "Merci de votre compr�hension, \n";
	$mail_body .= "L'�quipe BDOVORE";
	$t->set_var (array(
	"SUJET_EMAIL" => $mail_sujet,
	"CORPS_EMAIL" => $mail_body
	));

	if ($DB->f("ID_SERIE") != 0){
		$t->set_var (
		"LIENEDITSERIE" , "<a href='".BDO_URL."admin/adminseries.php?serie_id=".stripslashes($DB->f("ID_SERIE"))."'><img src='".BDO_URL_IMAGE."edit.gif' width='18' height='13' border='0'></a>"
		);
	}
	// D�termine les albums ayant une syntaxe approchante
	$main_words = main_words(stripslashes($DB->f("TITRE")));
	if ($main_words[1][0] != ''){
		$query = "
		select 
			id_tome, 
			titre 
		from 
			bd_tome 
		where 
			titre like '%".$DB->escape($main_words[0][0])."%".$DB->escape($main_words[1][0])."%'
			or titre like '%".$DB->escape($main_words[1][0])."%".$DB->escape($main_words[0][0])."%' 
			LIMIT 0,30;";
	}else{
		$query = "
		select 
			id_tome, 
			titre 
		from 
			bd_tome 
		where 
			titre like '%".$DB->escape($main_words[0][0])."%' 
		LIMIT 0,30;";
	}
	$DB->query ($query);

	// on d�clare le block � utiliser
	$t->set_block('tpBody','CloseBlock','CLBlock');
	// on affiche
	while ($DB->next_record()){
		$t->set_var (array(
		"CLOSELINKS" => stripslashes($DB->f ("titre")),
		"URLCLOSELINKS" => BDO_URL."admin/adminalbums.php?alb_id=".$DB->f ("id_tome")
		));
		$t->parse ("CLBlock", "CloseBlock",true);
	}

	// R�cup�re l'adresse mail de l'utilisateur
	$query = "SELECT email, username FROM users WHERE user_id = ".$DB->escape($prop_user);
	$DB->query ($query);
	$DB->next_record();
	$mail_adress = $DB->f("email");
	$pseudo = $DB->f("username");

	$t->set_var (array(
	"ADRESSEMAIL" => $mail_adress,
	"MAILSUBJECT" => "Votre proposition BDovore : ".$titre,
	"MEMBRE" => $pseudo
	));

	// url suivant et pr�c�dent
	$query = "
	SELECT 
		id_proposal 
	FROM 
		users_alb_prop 
	WHERE 
		id_proposal <".$DB->escape($propid)." 
		AND status = 0 
		AND prop_type = 'AJOUT' 
	ORDER BY id_proposal desc
	";
	$DB->query ($query);

	if ($DB->num_rows() > 0){
		$DB->next_record();
		$prev_url = BDO_URL."admin/adminproposals.php?act=valid&propid=".$DB-> f('id_proposal');
		$t->set_var ("BOUTONPRECEDENT" , "<a href='".$prev_url."'><input type='button' value='Pr�c�dent' /></a>");
	}else{
		$t->set_var ("BOUTONPRECEDENT" , "<del>Pr�c�dent</del>");
	}

	$query = "
	SELECT 
		id_proposal 
	FROM 
		users_alb_prop 
	WHERE
		id_proposal >".$DB->escape($propid)." 
		AND status = 0 
		AND prop_type = 'AJOUT' 
	ORDER BY id_proposal
	";
	$DB->query ($query);

	if ($DB->num_rows() > 0){
		$DB->next_record();
		$next_url = BDO_URL."admin/adminproposals.php?act=valid&propid=".$DB-> f('id_proposal');
		$t->set_var ("BOUTONSUIVANT" , "<a href='".$next_url."'><input type='button' value='Suivant'></a>");
	}else{
		$t->set_var ("BOUTONSUIVANT" , "<del>Suivant</del>");
	}

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

// Validation du formulaire : Ajout dans la base et � la collection
elseif($act=="append")
{

	// R�cup�re l'utilisateur et l'image de couv

	$query = "
	SELECT 
		user_id, 
		img_couv, 
		action, 
		notif_mail, 
		url_bdnet 
	FROM 
		users_alb_prop 
	WHERE 
		id_proposal = ".$DB->escape($propid);
	$DB->query ($query);
	$DB->next_record();
	$prop_user = $DB->f("user_id");
	$prop_img = $DB->f("img_couv");
	$prop_action = $DB->f("action");
	$notif_mail = $DB->f("notif_mail");


	// n'ins�re dans bd_tome que s'il s'agit d'une nouvelle �dition
	if ($_POST['txtExistingTomeId'] == ''){
		// R�cup�re le genre de la s�rie
		$query = "SELECT id_genre FROM bd_serie WHERE id_serie = ".$DB->escape($_POST['txtSerieId']);
		$DB->query($query);
		$DB->next_record();

		// Ins�re l'information dans la table bd_tome
		$query_el = array(
		"TITRE" => sqlise($_POST['txtTitre'],'text'),
		"NUM_TOME" => sqlise($_POST['txtNumTome'],'int_null'),
		"ID_SERIE" => $DB->escape($_POST['txtSerieId']),
		"PRIX_BDNET" => sqlise($_POST['txtPrixVente'],'int_null'),
		"ID_GENRE" => $DB->escape($DB->f("id_genre")),
		
		"ID_SCENAR" => sqlise($_POST['txtScenarId'],'int'),
		"ID_SCENAR_ALT" => sqlise($_POST['txtScenarAltId'],'int'),
		"ID_DESSIN" => sqlise($_POST['txtDessiId'],'int'),
		"ID_DESSIN_ALT"=> sqlise($_POST['txtDessiAltId'],'int'),
		"ID_COLOR" => sqlise($_POST['txtColorId'],'int'),
		"ID_COLOR_ALT" => sqlise($_POST['txtColorAltId'],'int'),
		
		"HISTOIRE" => sqlise($_POST['txtHistoire'],'text'),
		"FLG_INT" => (($_POST['chkIntegrale'] == "checkbox") ? "'O'" : "'N'"),
		"FLG_TYPE" => $DB->escape($_POST['lstType'])
		);
		$query = insert_query("bd_tome",$query_el);
		$DB->query ($query);
		echo "Album ajout� dans la table bd_tome<br />";
		// r�cup�re la valeur de la derni�re insertion
		$lid_tome= mysql_insert_id();
		$nouv_edition = "O";

	}else{
		$lid_tome = $_POST['txtExistingTomeId'];
		$nouv_edition = "N";
	}

	// ins�re un champ dans la table bd_edition
	$query_el = array(
	"id_tome" => $lid_tome,
	"id_editeur" => $DB->escape($_POST['txtEditeurId']),
	"id_collection" => $DB->escape($_POST['txtCollecId']),
	"dte_parution" => sqlise($_POST['txtDateParution'],'text'),
	"flg_tt" => (($_POST['chkTT'] == "checkbox") ? "'O'" : "'N'"),
	"EAN" => sqlise($_POST['txtEAN'],'text'),
	"ISBN" => sqlise($_POST['txtISBN'],'text'),
	"COMMENT" => sqlise($_POST['txtCommentEdition'],'text'),
	"VALIDATOR" => $DB->escape($_SESSION["UserId"]),
	"VALID_DTE" => 'NOW()'
	);
	$query = insert_query("bd_edition",$query_el);
	$DB->query ($query);
	echo "Nouvelle �dition ins�r�e dans la table id_edition<br />";

	// r�cup�re la valeur de la derni�re insertion
	$lid_edition = mysql_insert_id();

	if ($nouv_edition == "O")
	{
		// renseigne cette edition comme defaut pour bd_tome
		$DB->query("UPDATE bd_tome SET ID_EDITION='" . $DB->escape($lid_edition) . "' WHERE id_tome=" . $DB->escape($lid_tome));
	}

	// Verifie la pr�sence d'une image � t�l�charger
	if (is_file($txtFileLoc) | (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $_POST['txtFileURL'], $url_ary))){
		if (is_file($txtFileLoc)){ // un fichier � uploader
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
			$newfilename = "CV-".sprintf("%06d",$lid_tome)."-".sprintf("%06d",$lid_edition);
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
			if ( empty($url_ary[4]) ){
				echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image incompl�te. Vous allez �tre redirig�.';
				exit();
			}
			$base_get = '/' . $url_ary[4];
			$port = ( !empty($url_ary[3]) ) ? $url_ary[3] : 80;
			// Connection au serveur h�bergeant l'image
			if ( !($fsock = @fsockopen($url_ary[2], $port, $errno, $errstr)) ){
				$error = true;
				echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image innacessible. Vous allez �tre redirig�.';
				exit();
			}

			// R�cup�re l'image
			@fputs($fsock, "GET $base_get HTTP/1.1\r\n");
			@fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
			@fputs($fsock, "Connection: close\r\n\r\n");

			unset($avatar_data);
			while( !@feof($fsock) ){
				$avatar_data .= @fread($fsock, 102400);
			}
			@fclose($fsock);

			// Check la validit� de l'image
			if (!preg_match('#Content-Length\: ([0-9]+)[^ /][\s]+#i', $avatar_data, $file_data1) || !preg_match('#Content-Type\: image/[x\-]*([a-z]+)[\s]+#i', $avatar_data, $file_data2)){
				$error = true;
				echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors du t�l�chargement de l\'image. Vous allez �tre redirig�.';
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
				echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Could not write avatar file to local storage. Please contact the board administrator with this message. Vous allez �tre redirig�.';
				exit();
			}

			// newfilemname
			if ( !($imgtype = check_image_type($avatar_filetype, $error)) ){
				exit;
			}
			$newfilename = "CV-".sprintf("%06d",$lid_tome)."-".sprintf("%06d",$lid_edition).$imgtype;

			// si le fichier existe, on l'efface
			if (file_exists(BDO_DIR."images/couv/$newfilename")){
				@unlink(BDO_DIR."images/couv/$newfilename");
			}

			// copie le fichier temporaire dans le repertoire image
			@copy($tmp_filename, BDO_DIR."images/couv/$newfilename");
			unlink($tmp_filename);

			$img_couv=$newfilename;
		}else{
			$img_couv='';
		}

		// met � jour la r�f�rence au fichier dans la table bd_edition
		$query = "UPDATE bd_edition SET";
		$query .= " `img_couv` = '".$DB->escape($img_couv)."'";
		$query .=" WHERE (`id_edition`=".$DB->escape($lid_edition).");";
		$DB->query($query);

		echo "Nouvelle image ins�r�e dans la base<br />";

	}else{
		// v�rifie si une image a �t� propos�e
		if (($prop_img != '') && ($_POST['chkDelete'] != 'checked')){// copie l'image dans les couvertures
			$newfilename = "CV-".sprintf("%06d",$lid_tome)."-".sprintf("%06d",$lid_edition);
			$strLen =strlen ($prop_img);
			$newfilename .= substr ($prop_img, $strLen - 4, $strLen);
			@copy(BDO_DIR."images/tmp/$prop_img", BDO_DIR."images/couv/$newfilename");
			@unlink(BDO_DIR."images/tmp/".$prop_img);

			// met � jours la r�f�rence au fichier dans la table bd_edition
			$query = "UPDATE bd_edition SET";
			$query .= " `img_couv` = '".$DB->escape($newfilename)."'";
			$query .=" WHERE (`id_edition`=".$DB->escape($lid_edition).");";
			$DB->query($query);

			echo "Image propos�e ins�r�e dans la base<br />";

		}
	}

	// On rajoute un redimensionnement si le correcteur l'a voulu

	if ($_POST["chkResize"] == "checked") {

		//Redimensionnement
		//*****************

		$max_size = 180;
		$imageproperties = getimagesize(BDO_DIR."images/couv/$newfilename");
		if ($imageproperties != false) {
			$imagetype = $imageproperties[2];
			$imagelargeur = $imageproperties[0];
			$imagehauteur = $imageproperties[1];

			//D�termine s'il y a lieu de redimensionner l'image
			if ((($imagelargeur > $imagehauteur) && ($imagehauteur > $maxsize)) || (($imagelargeur <= $imagehauteur) & ($imagelargeur > $max_size))) {

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
					$source = imagecreatefromgif(BDO_DIR."images/couv/$newfilename");
					break;

				case "2":
					$source = imagecreatefromjpeg(BDO_DIR."images/couv/$newfilename");
					break;

				case "3":
					$source = imagecreatefrompng(BDO_DIR."images/couv/$newfilename");
					break;

				case "6":
					$source = imagecreatefrombmp(BDO_DIR."images/couv/$newfilename");
					break;
			}

			imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_w, $new_h, $imagelargeur, $imagehauteur);

			switch ($imagetype) {
				case "2":
					unlink(BDO_DIR."images/couv/$newfilename");
					imagejpeg($new_image,BDO_DIR."images/couv/$newfilename",100);
					break;

				case "1":
				case "3":
				case "6":
					unlink(BDO_DIR."images/couv/$newfilename");
					$img_couv = substr($newfilename,0,strlen($newfilename)-3)."jpg";
					imagejpeg($new_image,BDO_DIR."images/couv/$img_couv",100);

					// met � jours la r�f�rence au fichier dans la table bd_edition
					$query = "UPDATE bd_edition SET";
					$query .= " `img_couv` = '".$DB->escape($img_couv)."'";
					$query .=" WHERE (`id_edition`=".$DB->escape($lid_edition).");";
					$DB->query($query);
			}

		}

		echo "$new_w, $new_h, $imagelargeur, $imagehauteur<br />";
		echo "Image redimensionn�e<br />";
	}

	// Ajoute l'album � la collection de l'utilisateur
	if ($prop_action != 2)
	{
		$query_el = array(
		"USER_ID" => $DB->escape($prop_user),
		"DATE_AJOUT" => 'NOW()',
		"FLG_ACHAT" => sqlise(($prop_action == 1 ? 'O' : 'N'),'text'),
		"ID_EDITION" => $DB->escape($lid_edition)
		);
		$query = insert_query("users_album",$query_el);
		$DB->query ($query);
		echo "Album ajout� dans la collection de l'utilisateur<br />";
	}

	//Efface le fichier de la base et passe le status de l'album � valider
	if ($prop_img != ''){
		if (file_exists(BDO_DIR."images/tmp/".$prop_img)){
			@unlink(BDO_DIR."images/tmp/".$prop_img);
		}
	}
	$query = "UPDATE users_alb_prop SET `STATUS` = 1, `VALIDATOR` = " . $DB->escape($_SESSION["UserId"]) . " , `VALID_DTE` = NOW() WHERE id_proposal=".$DB->escape($propid);
	$DB->query ($query);

	// Envoie un mail si n�cessaire pour pr�venir l'utilisateur
	if ($notif_mail==1){
		$mail_action[0] = "L'album a �t� ajout� � votre collection, comme demand�.\n\n";
		$mail_action[1] = "L'album a �t� ajout� dans vos achats futurs, comme demand�.\n\n";
		$mail_action[2] = "L'album n'a pas �t� ajout� � votre collection, comme demand�.";

		// R�cup�re l'adresse du posteur
		$query = "SELECT email FROM users WHERE user_id = ".$DB->escape($prop_user);
		$DB->query ($query);
		$DB->next_record();
		$mail_adress = $DB->f("email");
		$mail_sujet = "Ajout d'un album dans la base BDOVORE";
		$mail_entete = "From: no-reply@bdovore.com";
		$mail_text = "Bonjour, \n\n";
		$mail_text .="Votre proposition d'ajout � la base de donn�es de BDOVORE a �t� valid�e.\n\n";
		$mail_text .="Titre : ".$_POST['txtTitre']."\n";
		$mail_text .=$mail_action[$prop_action];
		$mail_text .="Merci pour votre participation\n\n";
		$mail_text .="L'�quipe BDOVORE";
		mail($mail_adress,$mail_sujet,$mail_text,$mail_entete);
		echo "Email de confirmation envoy�<br />";
	}

	$query = "SELECT id_proposal FROM users_alb_prop WHERE id_proposal > ".$DB->escape($propid)." AND status = 0 AND prop_type = 'AJOUT' ORDER BY id_proposal;";
	$DB->query ($query);

	if ($DB->num_rows() > 0){
		$DB->next_record();
		$next_url = BDO_URL."admin/adminproposals.php?act=valid&propid=".$DB-> f('id_proposal');
	}else{
		$next_url = BDO_URL."admin/adminalbums.php?alb_id=".$lid_tome;
	}

	echo GetMetaTag(1,"L'album a �t� ajout�",$next_url);
}


// Ajoute un album existant � la collection de l'utilisateur (contr�le javascript en amont du caract�re judicieux de l utilisation du form)
elseif($act=="merge")
{
	$idtome = $_POST['txtFutAlbId'];

	// R�cup�re l'utilisateur et l'image de couv

	$query = "SELECT user_id, action, notif_mail, url_bdnet FROM users_alb_prop WHERE id_proposal = ".$DB->escape($propid);
	$DB->query ($query);
	$DB->next_record();
	$prop_user = $DB->f("user_id");
	$prop_action = $DB->f("action");
	$notif_mail = $DB->f("notif_mail");


	// Ajoute l'album existant � la collection ou aux futurs achats de l'utilisateur

	// V�rifie la pr�sence de l'album existant dans la collection de l'utilisateur
	$query = "SELECT 
	bd_edition.id_tome 
	FROM users_album 
	INNER JOIN bd_edition ON bd_edition.ID_EDITION=users_album.ID_EDITION
	WHERE bd_edition.id_tome =".$DB->escape($idtome)." 
	AND users_album.user_id = ".$DB->escape($prop_user);
	$DB->query ($query);
	if ($DB->num_rows() > 0)
	{
		echo GetMetaTag(1,"Cet album est d�j� pr�sent dans la collection de l'utilisateur",BDO_URL."admin/adminproposals.php?act=valid&propid=".$propid);
		exit();
	}
	else
	{ // Ajoute l'album
		$query = "
		SELECT 
			t.TITRE, 
			en.ID_EDITION
		FROM 
			bd_tome t 
			INNER JOIN bd_edition en ON t.ID_EDITION=en.ID_EDITION
		WHERE 
			t.ID_TOME=".$DB->escape($idtome);

		$DB->query($query);
		$DB->next_record();
		// Assigne les variables
		$titre = stripslashes($DB->f("TITRE"));
		$id_edition = $DB->f("ID_EDITION");

		$query_el = array(
		"USER_ID" => $prop_user,
		"DATE_AJOUT" => 'NOW()',
		"FLG_ACHAT" => sqlise(($prop_action == 1 ? 'O' : 'N'),'text'),
		"ID_EDITION" => $id_edition
		);
		$query = insert_query("users_album",$query_el);
		$DB->query ($query);
		echo "L'album s�lectionn� a �t� ajout� � la collection de l'utilisateur<br />";

		// Archive la proposition
		$query = "
		UPDATE users_alb_prop SET 
		`STATUS` = 99, 
		`VALIDATOR` = " . $DB->escape($_SESSION["UserId"]) . " , 
		`VALID_DTE` = NOW() 
		WHERE id_proposal=".$DB->escape($propid);
		$DB->query ($query);

		// Envoie un mail si n�cessaire pour pr�venir l'utilisateur
		if ($notif_mail==1){
			$mail_action[0] = "Cet album a �t� plac� dans votre collection, comme demand�.\n\n";
			$mail_action[1] = "Cet album a �t� plac� dans vos achats futurs, comme demand�.\n\n";

			// R�cup�re l'adresse du posteur et compose l'email
			$query = "SELECT email FROM users WHERE user_id = ".$DB->escape($prop_user);
			$DB->query ($query);
			$DB->next_record();
			$mail_adress = $DB->f("email");
			$mail_sujet = "Ajout d'un album dans la base BDOVORE";
			$mail_entete = "From: no-reply@bdovore.com";
			$mail_text = "Bonjour, \n\n";
			$mail_text .="Proposition : ".$_POST['txtTitre']."\n";
			$mail_text .= "Votre proposition d'ajout � la base de donn�es n'a pas �t� accept�e car l'album en question y figurait d�ja. \n";
			$mail_text .=$mail_action[$prop_action];
			$mail_text .= "Si l'�dition par d�faut de cet album ne correspond pas � celle que vous poss�dez,
							d'autres �ditions sont peut-�tre d�j� pr�sentes dans la base et peuvent �tre
							s�lectionn�es en cliquant sur l'album en question depuis votre garde-manger (menu d�roulant [Mon �dition]
							des fiches album). Si ce n'est pas le cas, vous pouvez faire une proposition de nouvelle �dition via ce m�me
							menu d�roulant.\n\n";
			$mail_text .="L'�quipe BDOVORE";
			mail($mail_adress,$mail_sujet,$mail_text,$mail_entete);
			echo "Email d'information envoy� � l'utilisateur<br />";
		}

		// Pr�pare la redirection vers la proposition suivante
		$query = "SELECT id_proposal FROM users_alb_prop WHERE id_proposal > ".$DB->escape($propid)." AND status = 0 AND prop_type = 'AJOUT' ORDER BY id_proposal;";
		$DB->query ($query);
		if ($DB->num_rows() > 0){
			$DB->next_record();
			$next_url = BDO_URL."admin/adminproposals.php?act=valid&propid=".$DB-> f('id_proposal');
		}else{
			$next_url = BDO_URL."admin/adminalbums.php?alb_id=".$idtome;
		}
	}
	echo GetMetaTag(1,"Bien jou� !",$next_url);
}

//SAUVEGARDE
elseif ($act=="update")
{

	//instancie un nouvel objet proposition
	$propal = new Element("users_alb_prop");

	$propal->fetch($propid);
	//$propal->display();

	// Met � jour les diff�rents champs du formulaire
	$propal->field["ID_SERIE"] = $_REQUEST["txtSerieId"];
	$propal->field["SERIE"] = $_REQUEST["txtSerie"];
	$propal->field["TITRE"] = $_REQUEST["txtTitre"];
	$propal->field["FLG_TYPE"] = $_REQUEST["lstType"];
	$propal->field["NUM_TOME"] = $_REQUEST["txtNumTome"];
	$propal->field["FLG_INT"] = (($_REQUEST["chkIntegrale"] == "checkbox") ? "O" : "N");
	$propal->field["PRIX"] = $_REQUEST["txtPrixVente"];
	$propal->field["HISTOIRE"] = $_REQUEST["txtHistoire"];
	$propal->field["ID_GENRE"] = $_REQUEST["txtGenreId"];
	$propal->field["GENRE"] = $_REQUEST["txtGenre"];
	$propal->field["ID_SCENAR"] = $_REQUEST["txtScenarId"];
	$propal->field["SCENAR"] = $_REQUEST["txtScenar"];
	$propal->field["ID_SCENAR_ALT"] = $_REQUEST["txtScenarAltId"];
	$propal->field["SCENAR_ALT"] = $_REQUEST["txtScenarAlt"];
	$propal->field["ID_DESSIN"] = $_REQUEST["txtDessiId"];
	$propal->field["DESSIN"] = $_REQUEST["txtDessi"];
	$propal->field["ID_DESSIN_ALT"] = $_REQUEST["txtDessiAltId"];
	$propal->field["DESSIN_ALT"] = $_REQUEST["txtDessiAlt"];
	$propal->field["ID_COLOR"] = $_REQUEST["txtColorId"];
	$propal->field["COLOR"] = $_REQUEST["txtColor"];
	$propal->field["ID_COLOR_ALT"] = $_REQUEST["txtColorAltId"];
	$propal->field["COLOR_ALT"] = $_REQUEST["txtColorAlt"];
	$propal->field["ID_EDITEUR"] = $_REQUEST["txtEditeurId"];
	$propal->field["EDITEUR"] = $_REQUEST["txtEditeur"];
	$propal->field["ID_COLLECTION"] = $_REQUEST["txtCollecId"];
	$propal->field["COLLECTION"] = $_REQUEST["txtCollec"];
	$propal->field["ISBN"] = $_REQUEST["txtISBN"];
	$propal->field["EAN"] = $_REQUEST["txtEAN"];
	$propal->field["DTE_PARUTION"] = $_REQUEST["txtDateParution"];
	$propal->field["FLG_TT"] = (($_REQUEST["chkTT"] == "checkbox") ? "O" : "N");
	$propal->field["DESCRIB_EDITION"] = $_REQUEST["txtCommentEdition"];

	$propal->update();

	// Retourne sur la page proposition
	header("Location:".BDO_URL."admin/adminproposals.php?act=valid&propid=$propid");
	exit ();

	//SAUVEGARDE DES COMMENTAIRES CORRECTEUR
}
elseif ($act=="comment")
{
	// R�cup�re le commentaire, le nouveau status et la proposition en cours
	$comment = $_REQUEST["txtCommentCorr"];
	$propid = $_REQUEST["propid"];
	$status = $_REQUEST["cmbStatus"];

	// Met � jour la case commentaire

	$query = "UPDATE users_alb_prop SET corr_comment = '".$DB->escape($comment)."' WHERE id_proposal = '".$DB->escape($propid)."' ;";
	$DB->query ($query);

	// Met � jours le status
	if (($status != 1) && ($status != 99)) {
		$query = "UPDATE users_alb_prop SET 
		status = '".$DB->escape($status)."', 
		`VALIDATOR` = " . $DB->escape($_SESSION["UserId"]) . " , 
		`VALID_DTE` = NOW() 
		WHERE id_proposal = '".$DB->escape($propid)."'";
		$DB->query ($query);
	}else{
		echo "Le status n'a pas pu �tre mis � jour";
	}

	// Retourne sur la page proposition
	header("Location:".BDO_URL."admin/adminproposals.php?act=valid&propid=$propid");
	exit ();
}
