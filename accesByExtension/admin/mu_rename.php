<?php



minAccessLevel(1);


// Mettre à jour les informations
if ($act=="update"){
	$nb = 0;
	
	foreach ($alb_id as $idtome){
		$nouv_titre = ereg_replace("#tome#", $num_tome[$idtome], $_POST["txtNouvTitre"]);
		$query = "UPDATE bd_tome SET titre = '".$DB->escape($nouv_titre)."' WHERE id_tome = ".$DB->escape($idtome);
		$DB->query ($query);
		$nb++;
	}
	echo GetMetaTag(2,"$nb albums ont été traités.",(BDO_URL."admin/mu_rename.php?serie=".$serie));
}

// AFFICHER UNE FICHE SERIE
elseif($act==""){
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "admin.mu.rename.tpl",
	"tpBase" => "body.tpl"
	));

	if ($serie != ""){
		

		// récupère le infos liées à la série
		$query = "SELECT nom FROM bd_serie WHERE id_serie = ".$DB->escape($serie);
		$DB->query ($query);
		$DB->next_record();
		$t->set_var (array(
		"SERIE" => stripslashes($DB->f("nom")),
		"IDSERIE" => $serie,
		"NOUVTITRE" => stripslashes($DB->f("nom")).", Tome #tome#"
		));

		// Récupère les coloristes secondaires ayant travaillé sur la série
		$query = "SELECT t.id_tome, t.num_tome, t.titre FROM bd_tome t WHERE t.flg_type = 0 AND t.flg_int = 'N' AND t.id_serie = ".$DB->escape($serie)." ORDER BY t.id_tome";
		$DB->query ($query);
		// on déclare le block à utiliser
		$t->set_block('tpBody','AlbumsBlock','ABlock');
		//Affiche les différentes éléments
		while ($DB->next_record()){
			$t->set_var (array(
			"TOME" => $DB->f("num_tome"),
			"TITRE" => stripslashes($DB->f("titre")),
			"IDTOME" =>  $DB->f("id_tome")
			));
			$t->parse ("ABlock", "AlbumsBlock",true);
		}
	}

	$t->set_var (array(
	"ACTIONNAME" => "Mettre à Jour",
	"URLACTION" => BDO_URL."admin/mu_rename.php?act=update&serie=".$serie,
	"URLREFRESH" => BDO_URL."admin/mu_rename.php",
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
