<?php



// Emplacement des fonctions
function cv_date_bd($date) {
	$mois = substr(month_to_text((int)substr($date,5,2)),0,3).".";
	$annee =substr($date,0,4);
	return $mois." ".$annee;
}

minAccessLevel(2);

// variables générales
$nb = 20;
if ($first=='') {$first = 0;}

$clerep[1] = "g.libelle";
$clerep[2] = "s.nom";
$clerep[3] = "en.dte_parution";
$clerep[4] = "sc.pseudo";

// Tableau pour les choix d'options
// Type de recherche
$opt_source[0][0] = 0;
$opt_source[0][1] = 'Mes Séries';
$opt_source[1][0] = 1;
$opt_source[1][1] = 'Mes Auteurs Favoris';
$opt_source[2][0] = 2;
$opt_source[2][1] = 'Intégrales/Coffrets';
// Echelle de temps
$opt_duree[0][0] = 1;
$opt_duree[0][1] = '1 mois';
$opt_duree[1][0] = 3;
$opt_duree[1][1] = '3 mois';
$opt_duree[2][0] = 6;
$opt_duree[2][1] = '6 mois';
$opt_duree[3][0] = 12;
$opt_duree[3][1] = '1 an';


if ($cle == "")
{
	$cle=1;
}

if ($sort == "")
{
	$sort = " ASC";
}
else
{
	$sort=" ".$sort;
}

// vérifie si la page est un refresh ou si on arrive directement du form
if ($duree == '')
{// on viens du form : il faut récupérer les paramétres nb de pages, pages en cours et mois à explorer
	$duree = ($_POST["lstDuree"] != '' ? $_POST["lstDuree"] : 1);
}

if ($lstSource == '')
{// on viens du form : il faut récupérer les paramétres nb de pages, pages en cours et mois à explorer
	$lstSource = ($_POST["lstSource"] != '' ? $_POST["lstSource"] : 0);
}

// Récupère le nombre d'albums
if ($lstSource == 0)
{
	$query = "
	SELECT SQL_CALC_FOUND_ROWS
		t.id_tome, 
		t.titre, 
		t.num_tome, 
		en.dte_parution, 
		s.nom s_nom,
		g.libelle,
		t.id_serie,
		sc.pseudo scenar, 
		de.pseudo dessin
	FROM 
		bd_tome t
		INNER JOIN
		(
			SELECT DISTINCT t.id_serie,s.nom,s.id_genre
			FROM
				users_album ua 
				INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
				INNER JOIN bd_tome t ON t.id_tome = en.id_tome
				INNER JOIN bd_serie s ON s.id_serie = t.id_serie
			WHERE
				ua.user_id=".$DB->escape($_SESSION["UserId"])."
				AND NOT EXISTS (
							SELECT NULL FROM users_exclusions ues
							WHERE s.id_serie=ues.id_serie 
							AND ues.id_tome = 0 
							AND ues.user_id = ".$DB->escape($_SESSION["UserId"])."
						) 	
		) s ON t.id_serie=s.id_serie
		INNER JOIN bd_edition en ON t.ID_EDITION=en.ID_EDITION
		INNER JOIN bd_genre g ON s.id_genre = g.id_genre
		INNER JOIN bd_auteur sc ON t.id_scenar = sc.id_auteur
		INNER JOIN bd_auteur de ON t.id_dessin = de.id_auteur 
	WHERE 
		NOT EXISTS (
			SELECT NULL 
			FROM users_album ua
			INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
			WHERE 
			ua.user_id = ".$DB->escape($_SESSION["UserId"])."
			AND t.id_tome=en.id_tome 
		)
		AND NOT EXISTS (
			SELECT NULL 
			FROM users_exclusions uet
			WHERE uet.user_id = ".$DB->escape($_SESSION["UserId"])."
			AND t.id_tome=uet.id_tome
		)  
	";
	
}
elseif ($lstSource == 1) {
	
	// recherche des auteurs preferes
	$DB->query ("SELECT id_auteur FROM users_list_aut WHERE user_id = ".$DB->escape($_SESSION["UserId"]));
	$a_auteurPref = array();
	while ($DB->next_record())
	{
		$a_auteurPref[] = $DB->f("id_auteur");
	}
	if (empty($a_auteurPref)) $a_auteurPref[] = 0;
	
	// liste auteur
	$query = "
	SELECT SQL_CALC_FOUND_ROWS
		t.id_tome, 
		t.titre, 
		t.num_tome, 
		en.dte_parution, 
		s.nom s_nom,
		g.libelle,
		s.id_serie,
		sc.pseudo scenar, 
		de.pseudo dessin
	FROM 
		(
			SELECT DISTINCT s.id_serie,s.nom,s.id_genre
			FROM
				bd_serie s
			WHERE
				NOT EXISTS (
							SELECT NULL FROM users_exclusions ues
							WHERE s.id_serie=ues.id_serie 
							AND ues.id_tome = 0 
							AND ues.user_id = ".$DB->escape($_SESSION["UserId"])."
						) 	
		) s 
		INNER JOIN bd_tome t ON t.id_serie=s.id_serie
		INNER JOIN bd_edition en ON t.ID_EDITION=en.ID_EDITION
		INNER JOIN bd_genre g ON s.id_genre = g.id_genre
		INNER JOIN bd_auteur sc ON t.id_scenar = sc.id_auteur
		INNER JOIN bd_auteur de ON t.id_dessin = de.id_auteur 
	WHERE 
		(t.id_scenar IN (".implode(',',$a_auteurPref).") OR t.id_dessin IN (".implode(',',$a_auteurPref)."))
		 	
		AND	NOT EXISTS (
			SELECT NULL 
			FROM users_album ua
			INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
			WHERE 
			ua.user_id = ".$DB->escape($_SESSION["UserId"])."
			AND t.id_tome=en.id_tome 
		)
		AND NOT EXISTS (
			SELECT NULL 
			FROM users_exclusions uet
			WHERE uet.user_id = ".$DB->escape($_SESSION["UserId"])."
			AND t.id_tome=uet.id_tome
		)  
	";
}
elseif ($lstSource == 2) {
	// liste integrale et coffrets
	$query = "
	SELECT SQL_CALC_FOUND_ROWS
		t.id_tome, 
		t.titre, 
		t.num_tome, 
		en.dte_parution, 
		s.nom s_nom,
		g.libelle,
		s.id_serie,
		sc.pseudo scenar, 
		de.pseudo dessin
	FROM 
		bd_tome t
		INNER JOIN
		(
			SELECT DISTINCT s.id_serie,s.nom,s.id_genre
			FROM
				bd_serie s
			WHERE
				NOT EXISTS (
							SELECT NULL FROM users_exclusions ues
							WHERE s.id_serie=ues.id_serie 
							AND ues.id_tome = 0 
							AND ues.user_id = ".$DB->escape($_SESSION["UserId"])."
						) 	
		) s ON t.id_serie=s.id_serie
		INNER JOIN bd_edition en ON t.ID_EDITION=en.ID_EDITION
		INNER JOIN bd_genre g ON s.id_genre = g.id_genre
		INNER JOIN bd_auteur sc ON t.id_scenar = sc.id_auteur
		INNER JOIN bd_auteur de ON t.id_dessin = de.id_auteur 
	WHERE 
		(t.flg_int ='O' OR t.flg_type = 1)
		 	
		AND	NOT EXISTS (
			SELECT NULL 
			FROM users_album ua
			INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
			WHERE 
			ua.user_id = ".$DB->escape($_SESSION["UserId"])."
			AND t.id_tome=en.id_tome 
		)
		AND NOT EXISTS (
			SELECT NULL 
			FROM users_exclusions uet
			WHERE uet.user_id = ".$DB->escape($_SESSION["UserId"])."
			AND t.id_tome=uet.id_tome
		)  
	";
}

$query .= "
 	AND en.dte_parution >= DATE_SUB(NOW(), INTERVAL ".$DB->escape($duree)." MONTH) 
 	GROUP BY t.id_tome
	ORDER BY ".$clerep[$cle]. " ".$DB->escape($sort)." 
	LIMIT ".$DB->escape($first)." , ".$DB->escape($nb);

$DB->query ($query);

$resCount = mysql_query('SELECT FOUND_ROWS() as nb');
$rowCount = mysql_fetch_assoc($resCount);
$num_alb = $rowCount['nb'];


// Creation d'une nouvelle instance Fast Template
$t = new Template(BDO_DIR."public/templates");
// fichier à utiliser
$t->set_file(array(
"tpBody" => "user_actualites.tpl",
"tpMenu" => "user.menu.tpl",
"tpMenuColl" => "menu_coll.tpl",
"tpBase" => "body.tpl"));
$t->set_var(array(
"OPTSOURCE" => GetOptionValue($opt_source,$lstSource),
"OPTDUREE" => GetOptionValue($opt_duree,$duree),
"SRC" => $lstSource
));
// on déclare le block à utiliser
$t->set_block('tpBody','DetailBlock','DBlock');


//Liste les nouveautés par mois
while ($DB->next_record())
{
	if ($DB->f("scenar") == $DB->f("dessin")) {
		$auteur = $DB->f("scenar");
	}
	else {
		$auteur = $DB->f("scenar")."/".$DB->f("dessin");
	}
	$t->set_var (array
	("GENRE" => $DB->f ("libelle"),
	"SERIE" => stripslashes($DB->f ("s_nom")),
	"TOME" => stripslashes($DB->f ("num_tome")),
	"TITRE" => stripslashes($DB->f ("titre")),
	"DTEPAR" => dateParution($DB->f ("dte_parution")),
	"URLTITRE" => getAlbumUrl($DB->f("id_tome")),
	"SERID" => $DB->f ("id_serie"),
	"AUTEUR" => $auteur
	));
	//Affiche le block
	$t->parse ("DBlock", "DetailBlock",true);
}

// Converti les variable generales
$t->set_var (array
("BARRENAVIGATION" => GetNavigationBar($first,$nb,$num_alb,BDO_URL."membres/actualites.php?lstSource=".$lstSource."&duree=".$duree."&cle=".$cle."&sort=".$sort),
"DUREE" => $duree,
"ACTION" => $_SERVER['PHP_SELF']
));

// assigne la barre de login
$t->set_var (array
("LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
"PAGETITLE" => "Mon actualité"));
$t->set_var("VIEWPUB","none");
$t->parse("BODY","tpBody");
$t->parse("MENUCOLL","tpMenuColl");
$t->parse("MENUBARRE","tpMenu");
$t->pparse("MyFinalOutput","tpBase");
