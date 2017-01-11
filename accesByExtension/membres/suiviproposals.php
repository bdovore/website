<?php




minAccessLevel(2);

// Tableau pour les choix d'options

$opt_source[0][0] = 0;
$opt_source[0][1] = 'Albums';
$opt_source[1][0] = 1;
$opt_source[1][1] = 'Editions';
$opt_source[2][0] = 2;
$opt_source[2][1] = 'Corrections';

$opt_filtre[0][0] = -1;
$opt_filtre[0][1] = 'Tous';
$opt_filtre[1][0] = 0;
$opt_filtre[1][1] = 'En attente';
$opt_filtre[2][0] = 1;
$opt_filtre[2][1] = 'Valid&eacute';
$opt_filtre[3][0] = 99;
$opt_filtre[3][1] = 'Refus&eacute;';
$opt_filtre[4][0] = 98;
$opt_filtre[4][1] = 'Effac&eacute; par l\'utilisateur';

$act = $_GET["act"];
$propid = intval($_GET["propid"]);

if (isset($_GET['lstSource'])) $_POST['lstSource'] = $_GET['lstSource'];

// LISTE LES PROPOSALS
if ($act==""){
	if ($cle == ""){
		$cle=1;
	}
	if ($sort == "DESC"){
		$sort = " DESC";
	}else{
		$sort="";
	}
	if ($_POST['lstSource']==2){
		$criteria = "CORRECTION";
		$choix_source = 2;
	}else if ($_POST['lstSource']==1){
		$criteria = "EDITION";
		$choix_source = 1;
	}else{
		$criteria = "AJOUT";
		$choix_source = 0;
	}
	if ($_POST['lstFiltre']==0) {
		if ($_POST['lstSource']==1){
			$filtre = " AND (prop_status = 0 OR prop_status = 2 OR prop_status = 3 OR prop_status = 4)";
		}else{
			$filtre = " AND (status = 0 OR status = 2 OR status = 3 OR status = 4)";
		}
		$choix_filtre = $_POST['lstFiltre'];
	} elseif ($_POST['lstFiltre']!=-1 & $_POST['lstFiltre']!=""){
		if ($_POST['lstSource']==1){
			$filtre = " AND prop_status = ".$DB->escape($_POST['lstFiltre']);
		}else{
			$filtre = " AND status = ".$DB->escape($_POST['lstFiltre']);
		}
		$choix_filtre = $_POST['lstFiltre'];
	} else{
		$filtre = "";
		$choix_filtre = -1;
	}

	// Selection des valeurs à afficher
	$clerep[1] = "prop_dte";
	$clerep[2] = "titre";

	$status[0] = "En attente";
	$status[1] = "Valid&eacute;";
	$status[2] = "En attente";
	$status[3] = "En attente";
	$status[4] = "En attente";
	$status[99] = "Refus&eacute;";
	$status[98] = "Effac&eacute; par l\'utilisateur";

	$orderby = $clerep[$cle];

	// Requete sur les données à afficher
	if ($_POST['lstSource']==1){
		$query = "SELECT e.id_edition, e.prop_dte, e.user_id, e.prop_status, t.id_tome, t.titre
			FROM bd_edition e, bd_tome t
			WHERE e.user_id=".$DB->escape($_SESSION["UserId"])." AND t.id_tome=e.id_tome $filtre
			ORDER BY ".$orderby.$sort;

		$DB->query ($query);
	}else{
		$query = "
		SELECT id_proposal,
			prop_dte,
			titre,
			status
		FROM
			users_alb_prop
		WHERE
			user_id=".$DB->escape($_SESSION["UserId"])."
			AND prop_type = '".$DB->escape($criteria)."'
			$filtre
		ORDER BY ".$orderby.$sort;

		$DB->query ($query);
	}

	// Creation d'une nouvelle instance Fast Template
	$t = new Template(BDO_DIR."public/templates");

	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "user.suivi.prop.tpl",
	"tpMenu" => "user.menu.tpl",
	"tpMenuColl" => "menu_coll.tpl",
	"tpBase" => "body.tpl"
	));

	// on déclare le block à utiliser
	$t->set_block('tpBody','PropBlock','PBlock');

	//Attribue les données à afficher
	while ($DB->next_record()){
		if ($_POST['lstSource']==1){
			$t->set_var (array(
			"DATE" => $DB->f ("prop_dte"),
			"TITRE" => stripslashes($DB->f ("titre")),
			"STATUS" => $status[$DB->f ("prop_status")],
			"URLACTION" => $status[$DB->f ("prop_status")],
			"URLDELETE"=> ($DB->f ("prop_status")==0) ? BDO_URL."membres/suiviproposals.php?act=supprim&type=".$criteria."&propid=".$DB->f ("id_edition") : "javascript:alert('Impossible');"
			));
		}else{
			$t->set_var (array(
			"DATE" => $DB->f ("prop_dte"),
			"TITRE" => stripslashes($DB->f ("titre")),
			"STATUS" => $status[$DB->f ("status")],
			"URLACTION" => $status[$DB->f ("status")],
			"URLDELETE"=> ($DB->f ("status")==0) ? BDO_URL."membres/suiviproposals.php?act=supprim&type=".$criteria."&propid=".$DB->f ("id_proposal") : "javascript:alert('Impossible');"
			));
		}
		$t->parse ("PBlock", "PropBlock",true);
	}
	$t->set_var (array(
	"OPTIONSOURCE" => GetOptionValue($opt_source,$choix_source),
	"OPTIONFILTRE" => GetOptionValue($opt_filtre,$choix_filtre)
	));

	// assigne la barre de login
	$t->set_var("PAGETITLE","BDovore.com : mes propositions");
	$t->set_var (array(
	"LOGINBARRE" => GetIdentificationBar(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
	));
	// COMPTAGE ALBUMS ET CORRECTIONS

	$select = "select prop_type, count(*) nb from users_alb_prop where status = 0 group by prop_type;";
	$DB->query($select);

	while ($DB->next_record()) {
		$prop_type = $DB->f("prop_type");
		$nb[$prop_type] = $DB->f("nb");
	}

	if ($nb["AJOUT"] == '') {
		$nb["AJOUT"] = 0;
	}
	if ($nb["CORRECTION"] == '') {
		$nb["CORRECTION"] = 0;
	}

	$t->set_var(array(
	"NBAJOUT" => $nb["AJOUT"],
	"NBCORRECT" => $nb["CORRECTION"]
	));
	// COMPTAGE EDITIONS
	$select = "select count(*) nbedition from bd_edition e, users u where e.prop_status = 0 AND e.user_id = u.user_id";
	$DB->query($select);
	$DB->next_record();
	$nbedition = $DB->f("nbedition");
	if ($nbedition == '') {
		$nbedition = 0;
	}
	$t->set_var(array(
	"NBEDITION" => $nbedition
	));
	$t->parse("BODY","tpBody");
	$t->parse("MENUBARRE","tpMenu");
	$t->parse("MENUCOLL","tpMenuColl");
	$t->pparse("MyFinalOutput","tpBase");
}

//SUPPRESSION DE PROPOSAL
elseif($act=="supprim")
{
	$status = $_GET["status"];
	$type = $_GET["type"];

	if($status=="ok"){// Supression Confirmée

		if ($type == 'EDITION'){// Définie les valeurs dans le cas d'une édition

			$query = "SELECT e.user_id, e.img_couv, e.id_tome, t.titre FROM bd_edition e, bd_tome t
			WHERE e.id_edition = ".$DB->escape($propid)." AND t.id_tome = e.id_tome;";
			$DB->query ($query);
			$DB->next_record();
			$prop_user = $DB->f("user_id");
			$prop_img = $DB->f("img_couv");
			$prop_action = $DB->f("action");
			$prop_titre = $DB->f("titre");
		}else {// Définie les valeurs dans le cas d'un ajout d'album ou d'une correction

			$query = "SELECT user_id, img_couv, titre FROM users_alb_prop WHERE id_proposal = ".$DB->escape($propid);
			$DB->query ($query);
			$DB->next_record();
			$prop_user = $DB->f("user_id");
			$prop_img = $DB->f("img_couv");
			$prop_action = $DB->f("action");
			$prop_titre = $DB->f("titre");
		}

		// Vérifie que l'utilisateur essaie bien d'effacer un album qu'il a proposé
		if ($_SESSION["UserId"] != $prop_user){
			echo GetMetaTag(3,"Vous ne pouvez pas supprimer la proposition d'un autre membre.",(BDO_URL."index.php"));
			exit();
		}
		if ($prop_img != ''){
			$filename = $prop_img;
			if (file_exists(BDO_DIR."images/tmp/$filename")){
				@unlink(BDO_DIR."images/tmp/$filename");
			}
		}

		//Effacement virtuel de l'album
		if ($type=='EDITION'){
			$query = "UPDATE bd_edition SET `PROP_STATUS` = 98, `VALIDATOR`=".$DB->escape($_SESSION["UserId"])." , `VALID_DTE` = NOW() WHERE id_edition=".$DB->escape($propid);
			$DB->query ($query);
		}else{
			$query = "UPDATE users_alb_prop SET `STATUS` = 98, `VALIDATOR`=".$DB->escape($_SESSION["UserId"])." , `VALID_DTE` = NOW() WHERE id_proposal=".$DB->escape($propid);
			$DB->query ($query);
		}

		//rouvre la page
		echo GetMetaTag(2,"La proposition a été effacée",(BDO_URL."membres/suiviproposals.php?cle=1&sort=DESC"));
		exit;
	}
	else
	{
		// affiche la confirmation de la demande d'effacement
		echo 'Etes-vous s&ucirc;r de vouloir effacer la proposition ?   <a href="'.BDO_URL.'membres/suiviproposals.php?act=supprim&type='.$type.'&propid='.$propid.'&status=ok">oui</a>
	  	- <a href="javascript:history.go(-1)">non</a>';
		exit();
	}
}
