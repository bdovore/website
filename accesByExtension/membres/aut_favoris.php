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
	$query = "DELETE FROM users_list_aut WHERE user_id = ".$DB->escape($_SESSION["UserId"]);
	$DB->query ($query);

	// Insère la nouvelle liste
	for ($i=0; $i <= 20; $i++)
	{
		$champs = "txtAuteur".$i."Id";
		if ($_POST[$champs] != '' & $_POST[$champs] != 0)
		{
			$query = "
			INSERT IGNORE INTO `users_list_aut` (
			`user_id`, `id_auteur`
			) VALUES (
			".$DB->escape($_SESSION["UserId"]).", ".$DB->escape($_POST[$champs])."
			)";
			$DB->query ($query);
		}
	}
	echo GetMetaTag(1,"Vos modifications ont été prises en compte.",BDO_URL."membres/actualites.php");

}
// Affichage de la liste existante
else if ($act=='')
{

	$query = "
	SELECT
		u.id_auteur,
		a.pseudo
	FROM
		users_list_aut u,
		bd_auteur a
	WHERE
		u.id_auteur = a.id_auteur
		AND u.user_id = ".$DB->escape($_SESSION["UserId"]);
	$DB->query ($query);

	// Creation d'une nouvelle instance Fast Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "aut_favoris.tpl",
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
		("IDAUTEUR" => $DB->f ("id_auteur"),
		"AUTEUR" => stripslashes($DB->f ("pseudo")),
		"ID" => $i
		));
		//Affiche le block
		$t->parse ("DBlock", "DetailBlock",true);
		$i++;
	}

	// Affiche les champs vides
	while ($i <= 20)
	{
		$t->set_var (array
		("IDAUTEUR" => '',
		"AUTEUR" => '',
		"ID" => $i
		));
		//Affiche le block
		$t->parse ("DBlock", "DetailBlock",true);
		$i++;
	}

	// variables générales
	$t->set_var (array
	("ACTIONNAME" => 'Enregistrer',
	"URLACTION" => BDO_URL.'membres/aut_favoris.php?act=submit',
	));

	// assigne la barre de login
	$t->set_var (array
	("LOGINBARRE" => GetIdentificationBar(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
	"PAGETITLE" => "Mes auteurs favoris"));
	$t->parse("BODY","tpBody");
	$t->parse("MENUCOLL","tpMenuColl");
	$t->parse("MENUBARRE","tpMenu");
	$t->pparse("MyFinalOutput","tpBase");
}
