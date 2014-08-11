<?php 	



include (BDO_DIR."inc/function.cle.inc.php");

minAccessLevel(1);

// Tableau pour les choix d'options
$opt_type[0][0] = 0;
$opt_type[0][1] = 'Album';
$opt_type[1][0] = 1;
$opt_type[1][1] = 'Coffret';

$opt_action[0] = "Insérer dans la collection";
$opt_action[1] = "Insérer comme achat futur";
$opt_action[2] = "Aucune";


// LISTE LES PROPOSALS
if ($act==""){
	if ($cle == "")
	{
		$cle=1;
	}
	if ($sort == "DESC")
	{
		$sort = " DESC";
	}
	else
	{
		$sort="";
	}

	// Selection des champs à afficher
	$clerep[1] = "id_edition";
	$clerep[2] = "prop_dte";
	$clerep[3] = "user_id";
	$clerep[4] = "titre";
	$clerep[5] = "serie";

	$orderby = $clerep[$cle];

	$query = "
	SELECT 
		e.id_edition, 
		t.id_tome id_proposal, 
		e.user_id, 
		u.username, 
		e.prop_dte, 
		t.titre, 
		s.nom serie
	FROM 
		bd_edition e, 
		bd_tome t, 
		bd_serie s , 
		users u
	WHERE 
		e.user_id = u.user_id 
		AND e.prop_status = 0 
		AND e.id_tome = t.id_tome 
		and t.id_serie = s.id_serie 
	ORDER BY ".$orderby.$sort;

	$DB->query ($query);

	// Creation d'une nouvelle instance Fast Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "admin.proposals.editions.tpl",
	"tpBase" => "body.tpl"));
	// on déclare le block à utiliser
	$t->set_block('tpBody','PropBlock','PBlock');

	$validationdelay = 21;//nbre de jours après lesquels on ne valide pas (pour les parutions futures)
	$datebeforevalid = "Ne pas valider les éditions qui paraissent après le " . date("d/m/Y", mktime(0, 0, 0, date("m"),date("d")+$validationdelay,date("Y"))) . " ($validationdelay jours)";
	$t->set_var (array("DATEBEFOREVALID" => $datebeforevalid));

	//Liste les propositions
	while ($DB->next_record()){
		$t->set_var (array(
		"ID" => $DB->f ("id_proposal"),
		"DATE" => $DB->f ("prop_dte"),
		"USER" => $DB->f ("username"),
		"TITRE" => stripslashes($DB->f ("titre")),
		"SERIE" => stripslashes($DB->f ("serie")),
		"URLEDIT" => BDO_URL."admin/adminalbums.php?alb_id=".$DB->f ("id_proposal"),
		"URLDELETE" => "javascript:alert('Impossible d\'effacer cette proposition depuis cet écran');")
		);
		$t->parse ("PBlock", "PropBlock",true);
	}

	// assigne la barre de login
	$t->set_var (array(
	"LOGINBARRE" => GetIdentificationBar(),
	"MENUBARRE" => admin_menu(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,)
	);
	$t->parse("BODY","tpBody");
	$t->pparse("MyFinalOutput","tpBase");
}

//SUPPRESSION DE PROPOSAL

elseif($act=="supprim"){
	if($status=="ok"){
		//suppression de la news

		// Vérifie l'existence d'une couverture

		$query = "SELECT user_id, img_couv, action, notif_mail, titre FROM users_alb_prop WHERE id_proposal = ".$DB->escape($propid);
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
				@unlink(BDO_DIR."images/tmp/$filename");
			}
		}
		//Effacement virtuel de l'album
		$query = "UPDATE users_alb_prop SET `STATUS` = 99, `VALIDATOR` = " . $DB->escape($_SESSION["UserId"]) . " , `VALID_DTE` = NOW() WHERE id_proposal=".$DB->escape($propid);
		$DB->query ($query);

		//Vérifie si un email doit être envoyé
		if ($notif_mail==1){// Récupère l'adresse du posteur
			$query = "SELECT email FROM users WHERE user_id = $prop_user;";
			$DB->query ($query);
			$DB->next_record();
			$mail_adress = $DB->f("email");
			$mail_sujet = "Ajout d'un album dans la base BDOVORE";
			$mail_entete = "From: no-reply@bdovore.com";
			$mail_text = "Bonjour, \n\n";
			$mail_text .="Votre proposition à la base de donnée de BDOVORE n'a pas été prise en compte.\n\n";
			$mail_text .="Titre : ".$prop_titre."\n\n";
			$mail_text .="Cet album figurait sans doute dans notre base ou l'information fournie n'était pas suffisante.\n\n";
			$mail_text .="Merci pour votre compréhension\n\n";
			$mail_text .="L'équipe BDOVORE";
			mail($mail_adress,$mail_sujet,$mail_text,$mail_entete);
		}

		$query = "SELECT id_proposal FROM users_alb_prop WHERE id_proposal > $propid AND status = 0 AND prop_type = 'AJOUT' ORDER BY id_proposal;";
		$DB->query ($query);

		if ($DB->num_rows() > 0){
			$DB->next_record();
			$next_url = BDO_URL."admin/adminproposals.php?act=valid&propid=".$DB-> f('id_proposal');
		}else{
			$next_url = BDO_URL."admin/adminproposals.php";
		}

		//rouvre la page
		echo GetMetaTag(1,"La proposition a été effacée",($next_url));
		exit;
	}else{
		// affiche la confirmation de la demande d'effacement
		echo 'Etes-vous s&ucirc;r de vouloir effacer la proposition n&deg;'.$propid.'  ?   <a href="adminproposals.php?act=supprim&propid='.$propid.'&status=ok">oui</a>
	  	- <a href="javascript:history.go(-1)">non</a>';
		exit();
	}
}
