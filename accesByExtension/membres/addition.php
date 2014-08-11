<?php



include (BDO_DIR."inc/function.html.inc.php");

minAccessLevel(2);

// Variables générales
$nb = 20;

if ($annee=='') $annee=date("Y");
if ($mois=='') $mois=date("n");
if ($info=='') $info=1;


// Valeurs d'option
$tb_mois=array("Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre");
for ($i=0; $i<12; $i++)
{
	$opt_mois[$i][0] = $i+1;
	$opt_mois[$i][1] = $tb_mois[$i];
}

// initialise les compteurs
$count = 0;

// Récupère les valeurs par défaut
$query = "SELECT val_alb, val_cof, val_int, val_cof_type FROM users WHERE user_id = ".$DB->escape($_SESSION["UserId"]);
$DB->query ($query);
$DB->next_record();
$defval[0] = $DB->f("val_alb");
$defval[1] = $DB->f("val_int");
$defval[2] = $DB->f("val_cof");
$defcoffret = $DB->f("val_cof_type");

// Récupère la collection
$query = "
SELECT 
	DATE_FORMAT(IFNULL(ua.date_achat, ua.date_ajout),'%d/%m/%y') as date_achat, 
	DATE_FORMAT(IFNULL(ua.date_achat, ua.date_ajout),'%Y') as annee_achat, 
	DATE_FORMAT(IFNULL(ua.date_achat, ua.date_ajout),'%m') as mois_achat, 
	t.id_tome, 
	t.titre, 
	t.num_tome, 
	t.flg_type, 
	t.flg_int,
	t.prix_bdnet, 
	ua.cote, 
	s.id_serie, 
	s.nom, 
	ua.id_edition 
FROM 
	users_album ua 
	INNER JOIN bd_edition en ON ua.id_edition=en.id_edition 
	INNER JOIN bd_tome t ON en.id_tome=t.id_tome
	INNER JOIN bd_serie s ON t.id_serie=s.id_serie
WHERE 
	ua.flg_achat = 'N' 
	AND ua.user_id = ".$DB->escape($_SESSION["UserId"])." 
ORDER BY IFNULL(ua.date_achat, ua.date_ajout)";

$DB->query ($query);


while ($DB->next_record())
{
	if ($DB->f("flg_int") == 'O')
	$type = 1;
	else
	$type = 0;
	if ($DB->f("flg_type") == 1) $type = 2;

	// Vérifie si l'album est coté par l'utilisateur
	if (($DB->f("cote") != '') & (($DB->f("flg_type") == 0) | ($defcoffret == 1)))
	{
		$tot_prix[$type] += $DB->f("cote");
		$tot_count[$type]++;
		$prix_retenu = $DB->f("cote");
	}
	// Verifie si l'album est noté par bdovore
	elseif (($DB->f("prix_bdnet") != '') & ($DB->f("flg_type") == 0 | ($defcoffret == 1)))
	{
		$tot_prix[$type] += $DB->f("prix_bdnet");
		$tot_count[$type]++;
		$prix_retenu = $DB->f("prix_bdnet");
	}
	// Non valorisé
	elseif (($DB->f("flg_type") == 0) | ($defcoffret == 1))
	{
		if ($defval[$type] == '')
		{
			$tot_count[$type]++;
			$prix_retenu = 0;
		}else{
			$tot_prix[$type] += $defval[$type];
			$tot_count[$type]++;
			$prix_retenu = $defval[$type];
		}
	}
	// Coffret valorisé album par album
	elseif (($DB->f("flg_type") == 1) & ($defcoffret == 0))
	{
		$tot_prix[$type] += $defval[2];
		$tot_count[$type]++;
		$prix_retenu = $defval[2];
	}

	// stocke les mini et les maxi
	$year = $DB->f("annee_achat");
	$depense[$year] += $prix_retenu;
	$nbalbums[$year]++;

	// stocke le détail par mois
	$month = $DB->f("mois_achat");

	if (($year == $annee) & ($month == $mois))
	{
		$idserie[$count] = $DB->f("id_serie");
		$dateachat[$count] = $DB->f("date_achat");
		$idtome[$count] = $DB->f("id_tome");
		$idedition[$count] = $DB->f("id_edition");
		$serie[$count] = stripslashes($DB->f("nom"));
		$titre[$count] = stripslashes($DB->f("titre"));
		$tome[$count] = $DB->f("num_tome");
		$prixretenu[$count] = $prix_retenu;
		$count++;
	}
}

// Creation d'une nouvelle instance Template
$t = new Template(BDO_DIR."public/templates");
// fichier à utiliser
$t->set_file(array(
"tpMenuColl" => "menu_coll.tpl",
"tpBody" => "user_addition_stats.tpl",
"tpMenu" => "user.menu.tpl",
"tpBase" => "body.tpl"));

// on remplie le block detail par annee
$t->set_block('tpBody','YearBlock','YBlock');
foreach ($depense as $key => $value)
{
	$t->set_var (array
	(
	"ANNEE" => ($key == $annee) ? "<b>$key</b>" : $key,
	"URLANNEE" => BDO_URL."membres/addition.php?annee=$key&info=$info&mois=1",
	"COUT" => number_format($value,2,".",","),
	"QUANTITE" => $nbalbums[$key]
	));

	// Affiche la ligne
	$t->parse ("YBlock", "YearBlock",true);
}

// on remplie le block detail par mois
if ($count > 0)
{
	$t->set_block('tpBody','MonthBlock','MBlock');
	for ($i=0; $i <$count; $i++)
	{
		$t->set_var (array
		(
		"ALBSERIE" => $serie[$i],
		"URLALBSERIE" => BDO_URL."membres/userserie.php?id_serie=".$idserie[$i],
		"ALBTOME" => $tome[$i],
		"IDTOME" => $idtome[$i],
		"IDEDITION" => $idedition[$i],
		"ALBTITRE" => $titre[$i],
		"ALBDATEACHAT" => $dateachat[$i],
		"ALBCOUT" => number_format($prixretenu[$i],2,".",",")
		));

		// Affiche la ligne
		$t->parse ("MBlock", "MonthBlock",true);
	}
}



// Tableaux d'option
$t->set_var (array
(
"OPTMOIS" => GetOptionValue($opt_mois,$mois),
"TYPE0" => ($info==0) ? 'checked' : '',
"TYPE1" => ($info==1) ? 'checked' : '',
"ANNEE" => $annee,
"IMGANNEE" => BDO_URL."graphs/rep_cout_annee.php?info=$info",
"IMGMOIS" => BDO_URL."graphs/rep_cout_mois.php?annee=$annee&info=$info"
));


// assigne la barre de login
$t->set_var (array
("LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
"PAGETITLE" => "L'addition"));

$t->parse("MENUCOLL","tpMenuColl");
$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->pparse("MyFinalOutput","tpBase");
