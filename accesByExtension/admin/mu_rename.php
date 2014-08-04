<?php



minAccessLevel(1);


// Mettre � jour les informations
if ($act=="update"){
	$nb = 0;
	
	foreach ($alb_id as $idtome){
		$nouv_titre = ereg_replace("#tome#", $num_tome[$idtome], $_POST["txtNouvTitre"]);
		$query = "UPDATE bd_tome SET titre = '".$DB->escape($nouv_titre)."' WHERE id_tome = ".$DB->escape($idtome);
		$DB->query ($query);
		$nb++;
	}
	echo GetMetaTag(2,"$nb albums ont �t� trait�s.",(BDO_URL."admin/mu_rename.php?serie=".$serie));
}

// AFFICHER UNE FICHE SERIE
elseif($act==""){
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier � utiliser
	$t->set_file(array(
	"tpBody" => "admin.mu.rename.tpl",
	"tpBase" => "body.tpl"
	));

	if ($serie != ""){
		

		// r�cup�re le infos li�es � la s�rie
		$query = "SELECT nom FROM bd_serie WHERE id_serie = ".$DB->escape($serie);
		$DB->query ($query);
		$DB->next_record();
		$t->set_var (array(
		"SERIE" => stripslashes($DB->f("nom")),
		"IDSERIE" => $serie,
		"NOUVTITRE" => stripslashes($DB->f("nom")).", Tome #tome#"
		));

		// R�cup�re les coloristes secondaires ayant travaill� sur la s�rie
		$query = "SELECT t.id_tome, t.num_tome, t.titre FROM bd_tome t WHERE t.flg_type = 0 AND t.flg_int = 'N' AND t.id_serie = ".$DB->escape($serie)." ORDER BY t.id_tome";
		$DB->query ($query);
		// on d�clare le block � utiliser
		$t->set_block('tpBody','AlbumsBlock','ABlock');
		//Affiche les diff�rentes �l�ments
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
	"ACTIONNAME" => "Mettre � Jour",
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
