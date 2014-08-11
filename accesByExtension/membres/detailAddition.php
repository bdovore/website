<?php



include (BDO_DIR."inc/function.html.inc.php");

minAccessLevel(2);

// vérifie si on a une mise à jour des prix
if ($action == 'update')
{
	foreach ($prix_achat as $key => $value)
	{
		$value = str_replace (',','.',$value);
		$query = "UPDATE users_album SET cote =".sqlise($value,'int_null')." WHERE id_edition = ".$key. " AND user_id = ".$DB->escape($_SESSION["UserId"]);
		$DB->query ($query);
	}
}

// Vérifie si mise à jour des valeurs
if ($action == 'refresh')
{
	$defval[0] = $_POST["txtPrixAlbum"];
	$defval[1] = $_POST["txtPrixIntegrale"];
	$defval[2] = $_POST["txtPrixCoffret"];
	$defcoffret = $_POST["lstCoffret"];
	$query = "UPDATE users SET 
	val_alb = ".sqlise($defval[0],'int_null').",
	val_int = ".sqlise($defval[1],'int_null').", 
	val_cof =".sqlise($defval[2],'int_null').", 
	val_cof_type = ".sqlise($defcoffret,'text')." 
	WHERE user_id=".$DB->escape($_SESSION["UserId"]);
	$DB->query ($query);
}
else{
	$query = "SELECT val_alb, val_cof, val_int, val_cof_type FROM users WHERE user_id = ".$DB->escape($_SESSION["UserId"]);
	$DB->query ($query);
	$DB->next_record();
	$defval[0] = $DB->f("val_alb");
	$defval[1] = $DB->f("val_int");
	$defval[2] = $DB->f("val_cof");
	$defcoffret = $DB->f("val_cof_type");
}

// Variables générales
$nb = 20;
if ($first=='') $first = 0;

// initialise les compteurs
$count[0] = 0;
$count[1] = 0;
$count[2] = 0;
$count[3] = 0;
$count[4] = 0;
$count_all =0;

// Tableaux $paramètres
// Valo des coffret
$opt_coffret[0][0] = 0;
$opt_coffret[0][1] = "Par album + Surcout";
$opt_coffret[1][0] = 1;
$opt_coffret[1][1] = "Valeur du coffret";

// Récupère la collection
$query = "
SELECT 
	IFNULL(ua.date_achat, ua.date_ajout) as alb_date, 
	en.id_edition,
	en.dte_parution,
	en.flag_dte_parution,
	ed.nom enom,
	t.id_tome, 
	t.titre, 
	t.num_tome, 
	t.flg_type, 
	t.flg_int, 
	t.prix_bdnet, 
	ua.cote, 
	s.id_serie, 
	s.nom 
FROM 
	users_album ua
	INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
	INNER JOIN bd_tome t ON en.id_tome=t.id_tome
	INNER JOIN bd_serie s ON t.id_serie=s.id_serie
	INNER JOIN bd_collection c ON en.id_collection=c.id_collection
	INNER JOIN bd_editeur ed ON ed.id_editeur=c.id_editeur
WHERE 
	ua.user_id=".$DB->escape($_SESSION["UserId"])."
	AND ua.flg_achat = 'N'
";

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
		$class = 0;
		// Stocke les infos liées au prix
		$matrice_prix[0][$type] += $DB->f("cote");
		$matrice_count[0][$type]++;
		$tot_prix[$type] += $DB->f("cote");
		$tot_count[$type]++;
		$prix_retenu = $DB->f("cote");
	}
	// Verifie si l'album est noté par bdovore
	elseif (($DB->f("prix_bdnet") != '') & ($DB->f("flg_type") == 0 | ($defcoffret == 1)))
	{
		$class = 1;
		// Stocke les infos liées au prix
		$matrice_prix[1][$type] += $DB->f("prix_bdnet");
		$matrice_count[1][$type]++;
		$tot_prix[$type] += $DB->f("prix_bdnet");
		$tot_count[$type]++;
		$prix_retenu = $DB->f("prix_bdnet");
	}
	// Non valorisé
	elseif (($DB->f("flg_type") == 0) | ($defcoffret == 1))
	{
		if ($defval[$type] == '')
		{
			$class = 2;
			$matrice_count[2][$type]++;
			$tot_count[$type]++;
			$prix_retenu = 0;
		}else{
			$class = 3;
			$matrice_prix[3][$type] += $defval[$type];
			$matrice_count[3][$type]++;
			$tot_prix[$type] += $defval[$type];
			$tot_count[$type]++;
			$prix_retenu = $defval[$type];
		}
	}
	// Coffret valorisé album par album
	elseif (($DB->f("flg_type") == 1) & ($defcoffret == 0))
	{
		$class = 4;
		$matrice_prix[4][0] += $defval[2];
		$matrice_count[4][0]++;
		$tot_prix[$type] += $defval[2];
		$tot_count[$type]++;
		$prix_retenu = $defval[2];
	}

	// stocke les valeurs de détail
	$idedition[$class][$count[$class]] = $DB->f("id_edition");
	$serie[$class][$count[$class]] = stripslashes($DB->f("nom"));
	$idserie[$class][$count[$class]] = stripslashes($DB->f("id_serie"));
	$titre[$class][$count[$class]] = stripslashes($DB->f("titre"));
	$editeur[$class][$count[$class]] = $DB->f("enom") . ' / ' . dateParution($DB->f("dte_parution"),$DB->f("flag_dte_parution"));
	$tome[$class][$count[$class]] = $DB->f("num_tome");
	$prixachat[$class][$count[$class]] = $DB->f("cote");
	$prixbdovore[$class][$count[$class]] = $DB->f("prix_bdnet");
	$prixutil[$class][$count[$class]] = $prix_retenu;
	$count[$class]++;

	// stocke les valeurs totales
	$idedition_all[$count_all] = $DB->f("id_edition");
	$serie_all[$count_all] = stripslashes($DB->f("nom"));
	$titre_all[$count_all] = stripslashes($DB->f("titre"));
	$editeur_all[$count_all] = $DB->f("enom") . ' / ' . dateParution($DB->f("dte_parution"),$DB->f("flag_dte_parution"));
	$tome_all[$count_all] = $DB->f("num_tome");
	$prixachat_all[$count_all] = $DB->f("cote");
	$prixbdovore_all[$count_all] = $DB->f("prix_bdnet");
	$prixutil_all[$count_all] = $prix_retenu;
	$count_all++;
}

// Affichage du tableau de détail

// Creation d'une nouvelle instance Template
$t = new Template(BDO_DIR."public/templates");
// fichier à utiliser
$t->set_file(array(
"tpMenuColl" => "menu_coll.tpl",
"tpBody" => "user_addition_detail.tpl",
"tpMenu" => "user.menu.tpl",
"tpBase" => "body.tpl"));

// Rempli le tableau de valorisation
$t->set_var (array
("NBPA" => $matrice_count[0][0]+$matrice_count[0][1]+$matrice_count[0][2],
"VALPA" => $matrice_prix[0][0]+$matrice_prix[0][1]+$matrice_prix[0][2],
"TITRENBPA" => (($matrice_count[0][0] > 0) ? $matrice_count[0][0]." albums" : '').(($matrice_count[0][1] > 0) ? " - ".$matrice_count[0][1]." intégrales" : '').(($matrice_count[0][2] > 0) ? " - ".$matrice_count[0][2]." coffrets" : ''),
"URLNBPA" => BDO_URL."membres/detailAddition.php?detail=0",
"NBPB" => $matrice_count[1][0]+$matrice_count[1][1]+$matrice_count[1][2],
"VALPB" => $matrice_prix[1][0]+$matrice_prix[1][1]+$matrice_prix[1][2],
"TITRENBPB" => (($matrice_count[1][0] > 0) ? $matrice_count[1][0]." albums" : '').(($matrice_count[1][1] > 0) ? " - ".$matrice_count[1][1]." intégrales" : '').(($matrice_count[1][2] > 0) ? " - ".$matrice_count[1][2]." coffrets" : ''),
"URLNBPB" => BDO_URL."membres/detailAddition.php?detail=1",
"NBPD" => $matrice_count[3][0]+$matrice_count[3][1]+$matrice_count[3][2],
"VALPD" => $matrice_prix[3][0]+$matrice_prix[3][1]+$matrice_prix[3][2],
"TITRENBPD" => (($matrice_count[3][0] > 0) ? $matrice_count[3][0]." albums" : '').(($matrice_count[3][1] > 0) ? " - ".$matrice_count[3][1]." intégrales" : '').(($matrice_count[3][2] > 0) ? " - ".$matrice_count[3][2]." coffrets" : ''),
"URLNBPD" => BDO_URL."membres/detailAddition.php?detail=3",
"NBC" => $matrice_count[4][0],
"VALC" => $matrice_prix[4][0],
"URLNBC" => BDO_URL."membres/detailAddition.php?detail=4",
"NBNV" => $matrice_count[2][0]+$matrice_count[2][1],
"VALNV" => $matrice_prix[2][0]+$matrice_prix[2][1],
"TITRENBNV" => (($matrice_count[2][0] > 0) ? $matrice_count[2][0]." albums" : '').(($matrice_count[2][1] > 0) ? " - ".$matrice_count[2][1]." intégrales" : '').(($matrice_count[2][2] > 0) ? " - ".$matrice_count[2][2]." coffrets" : ''),
"URLNBNV" => BDO_URL."membres/detailAddition.php?detail=2",
"NBTOT" => $tot_count[0] + $tot_count[1] + $tot_count[2],
"VALTOT" => $tot_prix[0] + $tot_prix[1] + $tot_prix[2],
"TITRENBTOT" => (($tot_count[0] > 0) ? $tot_count[0]." albums" : '').(($tot_count[1] > 0) ?  " - ".$tot_count[1]." intégrales" : '').(($tot_count[2] > 0) ?  " - ".$tot_count[2]." coffrets" : ''),
"URLNBTOT" => BDO_URL."membres/detailAddition.php?detail=5",
));

if (isset($detail))
{
	if ($detail == 5)
	{	// Affiche tous les albums sans distinction
		$num_alb = $count_all-1;
		if (($first + $nb) > $num_alb)
		$max_display = $num_alb;
		else
		$max_display = ($first + $nb -1);

		// on déclare le block à utiliser
		$t->set_block('tpBody','RowBlock','RBlock');
		for ($i=$first ; $i <= $max_display; $i++)
		{
			$t->set_var (array
			("IDEDITION" => $idedition_all[$i],
			"ALBSERIE" => $serie_all[$i],
			"ALBTOME" => $tome_all[$i],
			"ALBTITRE" => $titre_all[$i],
			"ALBEDITEUR" => $editeur_all[$i],
			"PRIX" => ($prixbdovore_all[$i] != '') ? number_format($prixbdovore_all[$i],2,".",",") : $prixbdovore_all[$i],
			"PRIXACHAT" => $prixachat_all[$i],
			"PRIXRETENU" => number_format($prixutil_all[$i],2,".",",")
			));
			// Affiche la ligne
			$t->parse ("RBlock", "RowBlock",true);
		}
	}else{
		// Affiche uniquement la catégorie concernée
		$num_alb = $count[$detail]-1;
		if (($first + $nb) > $num_alb)
		$max_display = $num_alb;
		else
		$max_display = ($first + $nb -1);

		// on déclare le block à utiliser
		$t->set_block('tpBody','RowBlock','RBlock');
		for ($i=$first ; $i <= $max_display; $i++)
		{
			$t->set_var (array
			("IDEDITION" => $idedition[$detail][$i],
			"URLTOME" => BDO_URL."membres/album.php?id_edition=".$idedition[$detail][$i],
			"ALBSERIE" => $serie[$detail][$i],
			"URLSERIE" => BDO_URL."membres/userserie.php?id_serie=".$idserie[$detail][$i],
			"ALBTOME" => $tome[$detail][$i],
			"ALBTITRE" => $titre[$detail][$i],
			"ALBEDITEUR" => $editeur[$detail][$i],
			"PRIX" => ($prixbdovore[$detail][$i] != '') ? number_format($prixbdovore[$detail][$i],2,".",",") : $prixbdovore[$detail][$i],
			"PRIXACHAT" => $prixachat[$detail][$i],
			"PRIXRETENU" => number_format($prixutil[$detail][$i],2,".",",")
			));

			// Affiche la ligne
			$t->parse ("RBlock", "RowBlock",true);
		}
	}
}

// Rempli les paramètres
$t->set_var (array
("DEFALB" => $defval[0],
"DEFINT" => $defval[1],
"DEFCOFFRET" => $defval[2],
"OPTVALOCOFFRET" => GetOptionValue($opt_coffret,$defcoffret)
));

// Converti les variable generales
$t->set_var (array
("BARRENAVIGATION" => GetNavigationBar($first,$nb,$num_alb,BDO_URL."membres/detailAddition.php?detail=".$detail),
"URLRECALC" => isset($detail) ? BDO_URL."membres/detailAddition.php?action=refresh&detail=$detail&first=$first" : BDO_URL."membres/detailAddition.php?action=refresh",
"URLACTION" => isset($detail) ? BDO_URL."membres/detailAddition.php?action=update&detail=$detail&first=$first" : BDO_URL."membres/detailAddition.php?action=update",
"URLAIDE" => BDO_URL."bdovore-tuto.php"
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
