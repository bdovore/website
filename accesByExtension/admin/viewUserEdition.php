<?php


minAccessLevel(1);

ob_start();

set_time_limit(360);

mysql_connect(BDO_DB_HOST, BDO_DB_USER, BDO_DB_PWD);
mysql_select_db(BDO_DB_SID);

include_once (BDO_DIR . "inc/relation.inc.php");

echo '<h2>Utilisateurs possédant l\'édition</h2>';

if (isset($_POST['valeur']))
{
	$id_edition = $_POST['valeur'];
}
else if (isset($_GET['id_edition']))
{
	$id_edition = $_GET['id_edition'];
}
else {
	$id_edition = "";
}

echo '<form name="formrelation" method="post">
ID de l\'edition : <input type="text" name="valeur" value="' . $id_edition . '">
<br /><input type="submit" name="execformuseredition" value="Voir les utilisateurs">
</div>
</form>';


if (isset($_POST['execformuseredition']))
{
	echo "<a href='".BDO_URL."admin/admineditions.php?edition_id=".$id_edition."'>Retour à la fiche édition</a><br />";
		
	$query = "SELECT SQL_CALC_FOUND_ROWS
		`users`.`username`, `users`.`email` , `users_album`.`date_ajout` 
		FROM `users` INNER JOIN users_album USING (user_id) 
		WHERE `users_album`.`id_edition`= '".mysql_real_escape_string($_POST['valeur'])."'

		LIMIT 0,50";

	
	$resultat = mysql_query($query);
	$a_obj = array();
	while ($obj = mysql_fetch_object($resultat)) {
		$a_obj[] = $obj;
	}
	$nbr = mysql_num_rows($resultat);

	$resCount = mysql_query('SELECT FOUND_ROWS() as nb');
	$rowCount = mysql_fetch_assoc($resCount);
	$nbrtotal = $rowCount['nb'];

	if (0 < $nbr)
	{echo '<br />' . $nbr . '/'.$nbrtotal.' utilisateur(s).
	<br />Recherche limitée à 50 utilisateurs.';

	tableOfFetchObj($a_obj);
	}
}




// dossier contenant les templates
$t = new Template(BDO_DIR."public/templates");
// fichier à utiliser
$t->set_file(array(
"tpForm_Recherche" => "form_recherche.tpl",
"tpControl" => "control.tpl",
"tpBody" => "body.tpl"
));

$t->set_var("PAGE_OB",ob_get_clean());

$t->set_var("MENUBARRE",admin_menu());
$t->set_var("LOGINBARRE",GetIdentificationBar());
$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);

$t->parse("BODY","tpControl");
$t->parse("FORM_RECHERCHE","tpForm_Recherche");
$t->pparse("MyFinalOutput","tpBody");