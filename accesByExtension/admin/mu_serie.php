<?php



minAccessLevel(1);
exit ('en travaux. Voir Thanaos.');

// Mettre à jour les informations
if ($act=="update"){
	
	$execute_query1 = false;
	$execute_query2 = false;
	$execute_query3 = false;
	$query1 = "UPDATE bd_tome u SET ";
	$query2 = "UPDATE bd_edition u, bd_tome t SET ";
	$query3 = "UPDATE users_album u SET ";
	$addendum = "";

	// Scénariste
	if ($_POST["txtScenarId"]!=""){
		$execute_query1 = true;
		$execute_query3 = true;
		$addendum = "u.id_scenar = ".$DB->escape($_POST["txtScenarId"]).", ";
		$query1 .= $addendum;
		$query3 .= $addendum;
	}

	// Scénariste Secondaire
	if ($_POST["txtScenarAltId"]!=""){
		$execute_query1 = true;
		$addendum = "u.id_scenar_alt = ".$DB->escape($_POST["txtScenarAltId"]).", ";
		$query1 .= $addendum;
	}

	// Dessinateur
	if ($_POST["txtDessiId"]!=""){
		$execute_query1 = true;
		$execute_query3 = true;
		$addendum = "u.id_dessin = ".$DB->escape($_POST["txtDessiId"]).", ";
		$query1 .= $addendum;
		$query3 .= $addendum;
	}

	// Dessinateur secondaire
	if ($_POST["txtDessiAltId"]!="")
	{
		$execute_query1 = true;
		$addendum = "u.id_dessin_alt = ".$DB->escape($_POST["txtDessiAltId"]).", ";
		$query1 .= $addendum;
	}

	// Coloriste
	if ($_POST["txtColorId"]!=""){
		$execute_query1 = true;
		$addendum = "u.id_color = ".$DB->escape($_POST["txtColorId"]).", ";
		$query1 .= $addendum;
	}

	// Coloriste Secondaire
	if ($_POST["txtColorAltId"]!=""){
		$execute_query1 = true;
		$addendum = "u.id_color = ".$DB->escape($_POST["txtColorAltId"]).", ";
		$query1 .= $addendum;
	}

	// Editeur
	if ($_POST["txtEditeurId"]!=""){
		$execute_query1 = true;
		$execute_query2 = true;
		$execute_query3 = true;
		$addendum = "u.id_editeur = ".$DB->escape($_POST["txtEditeurId"]).", ";
		$query1 .= $addendum;
		$query2 .= $addendum;
		$query3 .= $addendum;
	}

	// Collection
	if ($_POST["txtCollecId"]!=""){
		$execute_query1 = true;
		$execute_query2 = true;
		$execute_query3 = true;
		$addendum = "u.id_collection = ".$DB->escape($_POST["txtCollecId"]).", ";
		$query1 .= $addendum;
		$query2 .= $addendum;
		$query3 .= $addendum;
	}

	// Execute les queries
	if ($execute_query1 == true){
		$query1 = substr($query1,0,-2);
		$query1 .= " WHERE id_serie = ".$DB->escape($serie);

		$DB->query ($query1);
		echo $DB->affected_rows()." lignes modifiées dans la table bd_tome.<br>";
	}

	if ($execute_query2 == true){
		$query2 = substr($query2,0,-2);
		$query2 .= " WHERE t.id_tome = u.id_tome AND t.id_serie = ".$DB->escape($serie);
		
		$DB->query ($query2);
		echo $DB->affected_rows()." lignes modifiées dans la table bd_edition.<br>";
	}
	/* modif
	if ($execute_query3 == true){
		$query3 = substr($query3,0,-2);
		$query3 .= " WHERE id_serie = $serie;";
		
		$DB->query ($query3);
		echo $DB->affected_rows()." lignes modifiées dans la table users_album.<br>";
	}
	*/
	
	echo GetMetaTag(1,"Terminé",BDO_URL."admin/mu_serie.php?serie=".$serie);
}

// AFFICHER UNE FICHE SERIE
elseif($act==""){
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "admin.mu.serie.tpl",
	"tpBase" => "body.tpl"
	));

	if ($serie != ""){
		
		// récupère le infos liées à la série
		$query = "SELECT nom FROM bd_serie WHERE id_serie = ".$DB->escape($serie);
		$DB->query ($query);
		$DB->next_record();
		$t->set_var (array(
		"SERIE" => $DB->f("nom"),
		"IDSERIE" => $serie
		));

		// Récupère les scénaristes ayant travaillé sur la série
		$query = "SELECT DISTINCT a.id_auteur, a.pseudo FROM bd_tome t, bd_auteur a WHERE t.id_scenar = a.id_auteur AND t.id_serie = ".$DB->escape($serie);
		$DB->query ($query);
		// on déclare le block à utiliser
		$t->set_block('tpBody','Scen1Block','S1Block');
		//Affiche les différentes éléments
		while ($DB->next_record()){
			$t->set_var (array(
			"IDSCENARISTE1" => $DB->f("id_auteur"),
			"SCENARISTE1" => htmlentities(stripslashes($DB->f("pseudo"))),
			"URLSCENARISTE1" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_auteur")
			));
			$t->parse ("S1Block", "Scen1Block",true);
		}

		// Récupère les scénaristes secondaires ayant travaillé sur la série
		$query = "SELECT DISTINCT a.id_auteur, a.pseudo FROM bd_tome t, bd_auteur a WHERE t.id_scenar_alt = a.id_auteur AND t.id_serie = ".$DB->escape($serie);
		$DB->query ($query);
		// on déclare le block à utiliser
		$t->set_block('tpBody','Scen2Block','S2Block');
		//Affiche les différentes éléments
		while ($DB->next_record()){
			$t->set_var (array(
			"IDSCENARISTE2" => $DB->f("id_auteur"),
			"SCENARISTE2" => htmlentities(stripslashes($DB->f("pseudo"))),
			"URLSCENARISTE2" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_auteur")
			));
			$t->parse ("S2Block", "Scen2Block",true);
		}

		// Récupère les dessinateurs ayant travaillé sur la série
		$query = "SELECT DISTINCT a.id_auteur, a.pseudo FROM bd_tome t, bd_auteur a WHERE t.id_dessin = a.id_auteur AND t.id_serie = ".$DB->escape($serie);
		$DB->query ($query);
		// on déclare le block à utiliser
		$t->set_block('tpBody','Dess1Block','D1Block');
		//Affiche les différentes éléments
		while ($DB->next_record()){
			$t->set_var (array(
			"IDDESSINATEUR1" => $DB->f("id_auteur"),
			"DESSINATEUR1" => htmlentities(stripslashes($DB->f("pseudo"))),
			"URLDESSINATEUR1" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_auteur")
			));
			$t->parse ("D1Block", "Dess1Block",true);
		}

		// Récupère les dessinateurs secondaires ayant travaillé sur la série
		$query = "SELECT DISTINCT a.id_auteur, a.pseudo FROM bd_tome t, bd_auteur a WHERE t.id_dessin_alt = a.id_auteur AND t.id_serie = ".$DB->escape($serie);
		$DB->query ($query);
		// on déclare le block à utiliser
		$t->set_block('tpBody','Dess2Block','D2Block');
		//Affiche les différentes éléments
		while ($DB->next_record()){
			$t->set_var (array(
			"IDDESSINATEUR2" => $DB->f("id_auteur"),
			"DESSINATEUR2" => htmlentities(stripslashes($DB->f("pseudo"))),
			"URLDESSINATEUR2" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_auteur")
			));
			$t->parse ("D2Block", "Dess2Block",true);
		}

		// Récupère les coloristes ayant travaillé sur la série
		$query = "SELECT DISTINCT a.id_auteur, a.pseudo FROM bd_tome t, bd_auteur a WHERE t.id_color = a.id_auteur AND t.id_serie = ".$DB->escape($serie);
		$DB->query ($query);
		// on déclare le block à utiliser
		$t->set_block('tpBody','Color1Block','C1Block');
		//Affiche les différentes éléments
		while ($DB->next_record()){
			$t->set_var (array(
			"IDCOLORISTE1" => $DB->f("id_auteur"),
			"COLORISTE1" => htmlentities(stripslashes($DB->f("pseudo"))),
			"URLCOLORISTE1" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_auteur")
			));
			$t->parse ("C1Block", "Color1Block",true);
		}

		// Récupère les coloristes secondaires ayant travaillé sur la série
		$query = "SELECT DISTINCT a.id_auteur, a.pseudo FROM bd_tome t, bd_auteur a WHERE t.id_color_alt = a.id_auteur AND t.id_serie = ".$DB->escape($serie);
		$DB->query ($query);
		// on déclare le block à utiliser
		$t->set_block('tpBody','Color2Block','C2Block');
		//Affiche les différentes éléments
		while ($DB->next_record()){
			$t->set_var (array(
			"IDCOLORISTE2" => $DB->f("id_auteur"),
			"COLORISTE2" => htmlentities(stripslashes($DB->f("pseudo"))),
			"URLCOLORISTE2" => BDO_URL."admin/adminauteurs.php?auteur_id=".$DB->f("id_auteur")
			));
			$t->parse ("C2Block", "Color2Block",true);
		}

		// Récupère les editeurs ayant publié la série
		$query = "
		SELECT DISTINCT 
			er.id_editeur, 
			er.nom 
		FROM 
			bd_tome t 
			INNER JOIN bd_edition en ON t.id_tome = en.id_tome 
			INNER JOIN bd_collection c ON en.id_collection = c.id_collection 
			INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur 
			
		WHERE 
			t.id_serie = ".$DB->escape($serie);
		$DB->query ($query);
		// on déclare le block à utiliser
		$t->set_block('tpBody','EditeurBlock','EBlock');
		//Affiche les différentes éléments
		while ($DB->next_record()){
			$t->set_var (array(
			"IDEDITEUR" => $DB->f("id_editeur"),
			"EDITEUR" => stripslashes($DB->f("nom")),
			"URLEDITEUR" => BDO_URL."admin/adminediteurs.php?editeur_id=".$DB->f("id_editeur")
			));
			$t->parse ("EBlock", "EditeurBlock",true);
		}

		// Récupère les collections sous lesquels la série a été éditée
		$query = "
		SELECT DISTINCT 
			c.id_collection, 
			c.nom 
		FROM
			bd_tome t 
			INNER JOIN bd_edition en ON t.id_tome = en.id_tome 
			INNER JOIN bd_collection c ON en.id_collection = c.id_collection
		WHERE 
			t.id_serie = ".$DB->escape($serie);
		
		$DB->query ($query);
		// on déclare le block à utiliser
		$t->set_block('tpBody','CollectionBlock','ColBlock');
		//Affiche les différentes éléments
		while ($DB->next_record()){
			$t->set_var (array(
			"IDCOLLECTION" => $DB->f("id_collection"),
			"COLLECTION" => htmlentities(stripslashes($DB->f("nom"))),
			"URLCOLLECTION" => BDO_URL."admin/admincollections.php?collec_id=".$DB->f("id_collection")
			));
			$t->parse ("ColBlock", "CollectionBlock",true);
		}
	}

	$t->set_var (array(
	"ACTIONNAME" => "Mettre à Jour",
	"URLACTION" => BDO_URL."admin/mu_serie.php?act=update&serie=".$serie,
	"URLREFRESH" => BDO_URL."admin/mu_serie.php",
	"URLEDITSERIE" => BDO_URL."admin/adminseries.php?serie_id=".$serie
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
