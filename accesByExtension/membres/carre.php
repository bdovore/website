<?php




minAccessLevel(2);

// Emplacement des fonctions
function cv_date_bd($date) {
	$mois = substr(month_to_text((int)substr($date,5,2)),0,3).".";
	$annee =substr($date,0,4);
	return $mois." ".$annee;
}


//validation
if ($act=='submit')
{
	// Efface la liste actuelle de l'utilisteur
	$query = "DELETE FROM users_list_carre WHERE user_id = ".$DB->escape($_SESSION["UserId"]);
	$DB->query ($query);

	// Insère la nouvelle liste
	for ($i=1; $i <= 9; $i++)
	{
		$champs = "txtAlbum".$i."Id";
		if ($_POST[$champs] != '' & $_POST[$champs] != 0)
		{
			$query = "INSERT IGNORE INTO `users_list_carre` (
			`user_id`, `id_tome`, `rang`
			) VALUES (
			".$DB->escape($_SESSION["UserId"]).", ".$DB->escape($_POST[$champs]).", ".$DB->escape($i)." 
			)";
			$DB->query ($query);
		}
	}
	echo GetMetaTag(1,"Vos modifications ont été prises en compte.",BDO_URL."membres/userhome.php");

}
// Affichage de la liste existante
else if ($act=='')
{
	$query = "
	SELECT 
		u.rang, 
		u.id_tome, 
		t.titre 
	FROM 
		users_list_carre u, 
		bd_tome t 
	WHERE 
		u.id_tome = t.id_tome 
		AND u.user_id = ".$DB->escape($_SESSION["UserId"])." 
	ORDER BY rang";
	
	$DB->query ($query);

	// Creation d'une nouvelle instance Fast Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "user_carre.tpl",
	"tpMenu" => "user.menu.tpl",
	"tpMenuColl" => "menu_coll.tpl",
	"tpBase" => "body.tpl"));

	// on déclare le block à utiliser
	$t->set_block('tpBody','DetailBlock','DBlock');

	$i=1;
	//Liste les auteurs actuellement dans la base
	while ($DB->next_record())
	{
		$t->set_var (array
		("TOMEID" => $DB->f ("id_tome"),
		"TITRE" => stripslashes($DB->f ("titre")),
		"ID" => $i
		));
		//Affiche le block
		$t->parse ("DBlock", "DetailBlock",true);
		$i++;
	}

	// Affiche les champs vides
	while ($i <= 9)
	{
		$t->set_var (array
		("TOMEID" => '',
		"TITRE" => '',
		"ID" => $i
		));
		//Affiche le block
		$t->parse ("DBlock", "DetailBlock",true);
		$i++;
	}

	// variables générales
	$t->set_var (array
	("ACTIONNAME" => 'Enregistrer',
	"URLACTION" => BDO_URL.'membres/carre.php?act=submit',
	));

	// assigne la barre de login
	$t->set_var (array
	("LOGINBARRE" => GetIdentificationBar(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
	"PAGETITLE" => "Mes auteurs favoris"));
	$t->parse("BODY","tpBody");
	$t->parse("MENUBARRE","tpMenu");
	$t->parse("MENUCOLL","tpMenuColl");
	$t->pparse("MyFinalOutput","tpBase");
}
