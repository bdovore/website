<?php



include (BDO_DIR."inc/function.cle.inc.php");

minAccessLevel(1);


// Tableau pour les choix d'options
$opt_type[0][0] = 0;
$opt_type[0][1] = 'Album';
$opt_type[1][0] = 1;
$opt_type[1][1] = 'Coffret';


// Mettre à jour les informations
if ($act=="update")
{
	$nb = 0;
	
	foreach ($id_tome as $idtome)
	{
		if (($txtISBN[$idtome] != "") & ($txtEAN[$idtome] == ""))
		{
			$isbn = $txtISBN[$idtome];
			$ean = ISBN_to_EAN($isbn);
		}
		elseif (($txtISBN[$idtome] == "") & ($txtEAN[$idtome] != ""))
		{
			$ean = $txtEAN[$idtome];
			$isbn = EAN_to_ISBN($ean);
		}
		else
		{
			$isbn = $txtISBN[$idtome];
			$ean = $txtEAN[$idtome];
		}

		if (!$txtDateParution[$idtome] || $txtDateParution[$idtome] == '') {
			$dte_achat = "";
		}
		else {
			$dte_achat = substr($txtDateParution[$idtome],6,4)."-".substr($txtDateParution[$idtome],3,2)."-".substr($txtDateParution[$idtome],0,2);
		}
		
		$query = "
		UPDATE bd_tome SET 
			num_tome = ".sqlise($txtNumTome[$idtome],'int_null').",
			titre = ".sqlise($txtTitre[$idtome],'text_simple').",
			flg_type = ".$DB->escape($lstType[$idtome]).",
			flg_int = ".(($chkIntegrale[$idtome]=='checkbox') ? "'O'" : "'N'").",
			prix_bdnet = ".sqlise($txtPrix[$idtome],'text')."
		WHERE
			id_tome = ".$DB->escape($idtome);

		
		$DB->query ($query);
		
		$query = "
		UPDATE bd_edition en INNER JOIN bd_tome t ON t.ID_EDITION=en.ID_EDITION
		SET 
			en.isbn = ".sqlise($isbn,'text').",
			en.ean = ".sqlise($ean,'text').",
			en.dte_parution = ".sqlise($dte_achat,'text_simple')."
		WHERE 
			t.id_tome = ".$DB->escape($idtome) ;
		
		$DB->query ($query);
		
		$nb++;
	}
	echo GetMetaTag(2,"$nb albums ont été traités.",(BDO_URL."admin/mu_detail.php?serie=".$serie));
}

// AFFICHER UNE FICHE SERIE
elseif($act=="")
{
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "admin.mu.detail.tpl",
	"tpBase" => "body.tpl"));

	if ($serie != "")
	{
		
		// récupère le infos liées à la série
		$query = "SELECT nom FROM bd_serie WHERE id_serie = ".$DB->escape($serie);
		$DB->query ($query);
		$DB->next_record();
		$t->set_var (array
		("SERIE" => stripslashes($DB->f("nom")),
		"IDSERIE" => $serie
		));


		$query = "
		SELECT 
			t.id_tome, 
			t.num_tome, 
			t.titre, 
			t.flg_int, 
			t.flg_type, 
			e.isbn, 
			e.ean, 
			IFNULL(date_format(e.dte_parution,'%d/%m/%Y'),NULL) dtpar, 
			t.prix_bdnet, 
			count(e.id_edition) nbed
		FROM 
			bd_tome t, 
			bd_edition e
		WHERE 
			t.id_tome = e.id_tome 
			AND t.id_serie = ".$DB->escape($serie)."
		GROUP BY 
			t.id_tome, 
			t.num_tome, 
			t.titre, 
			t.flg_int, 
			t.flg_type, 
			e.isbn, 
			e.ean, 
			dtpar, 
			t.prix_bdnet
		ORDER BY 
			t.flg_int DESC, 
			t.flg_type, 
			t.num_tome;";
		$DB->query ($query);
		// on déclare le block à utiliser
		$t->set_block('tpBody','AlbumsBlock','ABlock');
		//Affiche les différentes éléments
		while ($DB->next_record())
		{
			$t->set_var (array
			("NUMTOME" => $DB->f("num_tome"),
			"TITRE" => stripslashes($DB->f("titre")),
			"IDTOME" =>  $DB->f("id_tome"),
			"OPTTYPE" => GetOptionValue($opt_type,$DB->f("flg_type")),
			"ISINT" => (($DB->f("flg_int")=='O') ? 'checked' : ''),
			"EAN" => $DB->f("ean"),
			"ISBN" => $DB->f("isbn"),
			"DTPAR" => $DB->f("dtpar"),
			"PRIX" => $DB->f("prix_bdnet"),
			"URLEDITEDIT" => BDO_URL."admin/adminalbums.php?alb_id=".$DB->f("id_tome"),
			"BGCOLOR" => (($DB->f("nbed") > 1) ? "#CC0033" : "#FFFFCC")
			));
			$t->parse ("ABlock", "AlbumsBlock",true);
		}
	}

	$t->set_var (array
	("ACTIONNAME" => "Mettre à Jour",
	"URLACTION" => BDO_URL."admin/mu_detail.php?act=update&serie=".$serie,
	"URLREFRESH" => BDO_URL."admin/mu_detail.php",
	"URLEDITSERIE" => BDO_URL."admin/adminseries.php?serie_id=".$serie
	));

	// assigne la barre de login
	$t->set_var (array
	("LOGINBARRE" => GetIdentificationBar(),
	"MENUBARRE" => admin_menu(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,));
	$t->parse("BODY","tpBody");
	$t->pparse("MyFinalOutput","tpBase");
}
