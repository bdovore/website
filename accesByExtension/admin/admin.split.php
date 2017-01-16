<?php




minAccessLevel(1);

// FUSIONNE LES EDITIONS
if($act=="update")
{
	// v�rifie si une s�rie a �t� pass�
	$new_serie_id = $_POST["txtNewSerieId"];
	$old_tome_id = $_POST["txtTomeId"];

	// teste si des editions ont �t� coch�e
	if (count($chkEdition) == 0) {
		echo GetMetaTag(2,"Aucune �dition � transf�rer.",(BDO_URL."admin/admin.split.php?alb_id=".$old_tome_id));
		exit();
	}

	// R�cup�re le genre de la nouvelle s�rie
	$query = "SELECT id_genre FROM bd_serie WHERE id_serie =".$DB->escape($new_serie_id);
	$DB->query($query);
	$DB->next_record();

	$id_genre = $DB->f("id_genre");

	// cr�ation du nouvel album dans la base bd_tome

	$query = "
	INSERT INTO `bd_tome` (
	`TITRE`, `NUM_TOME`, `ID_SERIE`, `ID_GENRE`, `ID_SCENAR`, `ID_SCENAR_ALT`,
	`ID_DESSIN`, `ID_DESSIN_ALT`, `ID_COLOR`, `ID_COLOR_ALT`, `FLG_INT`,
	`FLG_TYPE`, `PRIX_BDNET`, `HISTOIRE`
	)
	SELECT
		`TITRE`,
		`NUM_TOME`,
		".$DB->escape($new_serie_id).",
		".$DB->escape($id_genre).",
		`ID_SCENAR`,
		`ID_SCENAR_ALT`,
		`ID_DESSIN`,
		`ID_DESSIN_ALT`,
		`ID_COLOR`,
		`ID_COLOR_ALT`,
		`FLG_INT`,
		`FLG_TYPE`,
		`PRIX_BDNET`,
		`HISTOIRE`
	FROM
		bd_tome
	WHERE
		id_tome=".$DB->escape($old_tome_id);

	$DB->query($query);

	// r�cup�re la valeur du dernier album ins�r�
	$new_tome_id = mysql_insert_id();

	echo "new tome:".$new_tome_id."<br>";

	// transf�re les �ditions � transf�rer sur le nouvel album
	// et prend la premi�re �dition comme �dition par d�faut
	$flg_edition = "O";

	foreach ($chkEdition as $idedition) {

		// si une couverture existe, son nom est modifi�
		$old_filename = $txtCouv[$idedition];
		if ($old_filename == "")
		{
			$new_filename = "";
		}else{
			$new_filename = "CV-".sprintf("%06d",$new_tome_id)."-".sprintf("%06d",$idedition).substr($old_filename,-4);
			echo "renomme $old_filename en $new_filename<br>";
			rename(BDO_DIR."images/couv/".$old_filename, BDO_DIR."images/couv/".$new_filename);
		}

		if ($flg_edition == "O") //premi�re �dition comme �dition par d�faut
		{
			// renseigne cette edition comme defaut pour bd_tome
			$DB->query("UPDATE bd_tome SET ID_EDITION='" . $DB->escape($idedition) . "' WHERE id_tome=" . $DB->escape($new_tome_id));
		}

		// Transf�re les �ditions s�lectionn�es sous le nouvel albums
		$query = "UPDATE bd_edition SET
		id_tome = ".$DB->escape($new_tome_id).",
		img_couv = ".sqlise($new_filename,'text_simple')."
		WHERE id_edition = ".$DB->escape($idedition);

		$DB->query($query);

		echo "Nombre de records modifi�es dans la table bd_edition : ".$DB->affected_rows()."<br>";
	}

	echo GetMetaTag(2,"Split effectu�.",(BDO_URL."admin/adminalbums.php?alb_id=".$new_tome_id));
	exit();
}

// AFFICHER UN ALBUM
elseif($act=="")
{
	$query = q_tome("t.id_tome = ".$DB->escape($alb_id));
	$DB->query($query);
	$DB->next_record();

	$id_edition = $DB->f("id_edition");

	// D�termine l'affichage des infos
	$scenaristes1 = ($DB->f("id_scenar_alt") == 0) ? stripslashes($DB->f("scpseudo")) : stripslashes($DB->f("scpseudo"))." / ".stripslashes($DB->f("scapseudo"));
	$dessinateurs1 = ($DB->f("id_dessin_alt") == 0) ? stripslashes($DB->f("depseudo")) : stripslashes($DB->f("scdeeudo"))." / ".stripslashes($DB->f("deapseudo"));
	$coloristes1 = ($DB->f("id_color_alt") == 0) ? stripslashes($DB->f("copseudo")) : stripslashes($DB->f("codeeudo"))." / ".stripslashes($DB->f("coapseudo"));
	$edcollec1 = ($DB->f("cnom") == "<N/A>") ? stripslashes($DB->f("enom")) : stripslashes($DB->f("enom"))." / ".stripslashes($DB->f("cnom"));
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier � utiliser
	$t->set_file(array(
	"tpBody" => "admin.split.album.tpl",
	"tpBase" => "body.tpl"));

	$t->set_var (array
	("IDTOME" => $DB->f("id_tome"),
	"TITRE" => stripslashes($DB->f("titre")),
	"SERIEID" => $DB->f("id_serie"),
	"SERIE" => stripslashes($DB->f("s_nom")),
	"TOME" => $DB->f("num_tome"),
	"SCENARISTES" => $scenaristes1,
	"DESSINATEURS" => $dessinateurs1,
	"COLORISTES" => $coloristes1,
	"EDCOLLEC" => $edcollec1,
	));

	// Affiche les informations relatives aux diff�rentes �ditions sauf celle par defaut
	$query = q_AllEditionByIdTome($DB->escape($alb_id), "AND en.id_edition<>".$DB->escape($id_edition));
	$DB->query ($query);

	// on d�clare le block � utiliser
	$t->set_block('tpBody','EditionBlock','EBlock');

	//Affiche les diff�rentes �ditions
	while ($DB->next_record())
	{
		// Determine l'URL image
		if (is_null($DB->f("img_couv")) | ($DB->f("img_couv")==''))
		{
			$url_image = BDO_URL."images/couv/default.png";
			$url_couv = "";
		}else{
			$url_image = BDO_URL."images/couv/".$DB->f("img_couv");
			$url_couv = $DB->f("img_couv");
		}
		// Affiche le r�sultat
		$t->set_var (array
		("EDITEUR" => stripslashes($DB->f("enom")),
		"COLLECTION" => htmlentities(stripslashes($DB->f("cnom"))),
		"IMGTT" => (($DB->f("flg_tt") == 'O') ? BDO_URL_IMAGE.'site/ic_TT.gif' : BDO_URL_IMAGE.'site/ic_TT_nb.gif'),
		"DTPAR" => $DB->f("dte_parution"),
		"IDEDITION" => $DB->f("id_edition"),
		"URLEDITEDITION" => BDO_URL."admin/admineditions.php?edition_id=".$DB->f("id_edition"),
		"URLIMAGE"=> $url_image,
		"URLCOUV" => $url_couv
		));
		$t->parse ("EBlock", "EditionBlock",true);
	}

	$t->set_var (array
	("NBEDITIONS" => $DB->num_rows(),
	"REFRESHPAGE" => "admin.delete.php?alb_id=".$alb_id,
	"URLRETOURFICHE" => BDO_URL."admin/adminalbums.php?alb_id=".$alb_id,
	"ACTIONNAME" => "Effectuer les modifications",
	"URLACTION" => BDO_URL."admin/admin.split.php?act=update"
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
