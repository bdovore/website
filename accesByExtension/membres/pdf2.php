<?php



include (BDO_DIR."inc/queryfunction.php");

minAccessLevel(2);

function AddBandeauSerie(&$PDF, $line, $text)
{
	PDF_set_font($PDF, "Helvetica", 12, "winansi");
	// Block de couleur
	PDF_setcolor($PDF,'fill','rgb',0.5,0.0,0.0,0);
	PDF_setcolor($PDF,'stroke','rgb',0,0,0,0);
	PDF_setlinewidth($PDF,0.5);
	PDF_rect($PDF,25,$line -4 ,520,15);
	PDF_fill_stroke($PDF);

	// Nom de la série
	PDF_setcolor($PDF,'both','rgb',1,0.8,0,0);
	PDF_show_xy($PDF, $text, 30, $line);
}

/* Fonction AddInfoSerie
Affiche le cadre avancement / genre
Les variables sont passées dans un array.
Clé : "AVANCEMENT" et "GENRE"
*/

function AddInfoSerie(&$PDF, $line, $infos)
{
	// Cadre autour des infos série
	PDF_setcolor($PDF,'both','rgb',0,0,0,0);
	PDF_rect($PDF,25,$line -3 ,520,25);
	PDF_stroke($PDF);

	// afiche le descriptif de la série
	PDF_set_font($PDF, "Helvetica", 10, "winansi");
	PDF_setcolor($PDF,'both','rgb',0,0,0,0);
	$colonne = 30;
	$text = "Avancement : ".$infos["AVANCEMENT"];
	PDF_show_xy($PDF, $text, 30, $line+12);
	$text = "Genre :".$infos["GENRE"];
	PDF_show_xy($PDF, $text, 30, $line+2);
}

/* Fonction AddInfoSerie
Affiche couv + information sur un albums
Les variables sont passées dans un array.
Clé : "TITRE", "NUM_TOME", "SCENARISTE", "DESSINATEUR", "COLORISTE", "EDITEUR", "COLLECTION", "DATE_PARUTION", "ISBN", "IMG_COUV"
*/

function AddAlbumDetaille(&$PDF, $line, $colonne, $infos, $cat)
{
	// Verifie s'il y a lieu de faire un cadre gris
	switch ($cat) {
		case 1:
			// Achat futur
			// Block de couleur
			PDF_setcolor($PDF,'fill','rgb',0.8,0.8,0.8,0);
			PDF_setcolor($PDF,'stroke','rgb',0,0,0,0);
			PDF_setlinewidth($PDF,0.5);
			PDF_rect($PDF,$colonne+25 ,$line -4 ,275,103);
			PDF_fill_stroke($PDF);
			break;

		case 2:
			// Achat Alb manquant
			// Block de couleur
			PDF_setcolor($PDF,'fill','rgb',0.5,0.5,0.5,0);
			PDF_setcolor($PDF,'stroke','rgb',0,0,0,0);
			PDF_setlinewidth($PDF,0.5);
			PDF_rect($PDF,$colonne+25 ,$line -4 ,275,103);
			PDF_fill_stroke($PDF);
			break;
	}

	// Place un bloc avec le numéro du tome
	PDF_setcolor($PDF,'both','rgb',1,0.8,0,0);
	PDF_rect($PDF,53 + $colonne,$line + 80,24,15);
	PDF_fill($PDF);
	PDF_set_font($PDF, "Helvetica-Bold", 10, "winansi");
	PDF_setcolor($PDF,'both','rgb',0,0,0,0);
	$text = $infos["NUM_TOME"];
	if (($text == "0") | ($text == "")) $text ="HS";
	$lenght = strlen($text);
	PDF_show_xy($PDF, $text, 65 - ($lenght * 3) + $colonne, $line+86);

	// Détermine l'echelle à utiliser
	$imagefile = "../images/couv/".$infos["IMG_COUV"];

	if ((file_exists($imagefile)) & ($infos["IMG_COUV"] != ""))
	{
		$file_ext = strtolower(getFileExtension($infos["IMG_COUV"]));
		switch ($file_ext)
		{
			case 'gif':
				$file_type = 'gif';
				break;
			case 'jpeg':
			case 'jpg':
				$file_type = 'jpeg';
				break;
			case 'png':
				$file_type = 'png';
				break;
		}

		$image = PDF_open_image_file($PDF, $file_type, $imagefile, "","");

		$width = PDF_get_value($PDF, "imagewidth", $image);
		$height = PDF_get_value($PDF, "imageheight", $image);

		$scale_x = 84/$height; // Hauteur maximum de l'image = 84
		$scale_y = 70/$width; // Largeur maximum de l'image = 70
		if ($scale_x < $scale_y)
		$scale_a = $scale_x;
		else
		$scale_a = $scale_y;

		$width = (int) $width * $scale_a * 0.5;
		$height = (int) $height * $scale_a;

		PDF_place_image($PDF, $image,65 - $width + $colonne,$line+(84-$height),$scale_a);
	}

	// Affiche la colonne de titre
	PDF_set_font($PDF, "Helvetica-Bold", 10, "winansi");
	$line += 87;
	$text = $infos["TITRE"];
	if (strlen($text) > 35) $text = substr($text,0,35)." (...)";
	PDF_show_xy($PDF, $text, 105+$colonne, $line);

	PDF_set_font($PDF, "Helvetica", 9, "winansi");

	$line -= 14;
	$text = "Scénariste";
	PDF_show_xy($PDF, $text, 105+$colonne, $line);
	$text = $infos["SCENARISTE"];
	PDF_show_xy($PDF, $text, 163+$colonne, $line);

	$line -= 12;
	$text = "Dessinateur";
	PDF_show_xy($PDF, $text, 105+$colonne, $line);
	$text = $infos["DESSINATEUR"];
	PDF_show_xy($PDF, $text, 163+$colonne, $line);

	$line -= 12;
	$text = "Coloriste";
	PDF_show_xy($PDF, $text, 105+$colonne, $line);
	$text = $infos["COLORISTE"];
	PDF_show_xy($PDF, $text, 163+$colonne, $line);

	$line -= 12;
	$text = "Editeur";
	PDF_show_xy($PDF, $text, 105+$colonne, $line);
	$text = $infos["EDITEUR"];
	PDF_show_xy($PDF, $text, 163+$colonne, $line);

	$line -= 12;
	$text = "Collection";
	PDF_show_xy($PDF, $text, 105+$colonne, $line);
	$text = $infos["COLLECTION"];
	PDF_show_xy($PDF, $text, 163+$colonne, $line);

	$line -= 12;
	$text = "Dépôt Légal";
	PDF_show_xy($PDF, $text, 105+$colonne, $line);
	$text = $infos["DATE_PARUTION"];
	PDF_show_xy($PDF, $text, 163+$colonne, $line);

	$line -= 12;
	$text = "ISBN";
	PDF_show_xy($PDF, $text, 105+$colonne, $line);
	$text = $infos["ISBN"];
	PDF_show_xy($PDF, $text, 163+$colonne, $line);
}

function removeOldFiles($dir,$user_id,$timelimit) {
	if ( ($dh = opendir($dir)) ) {
		while (($file = readdir($dh)) !== false) {
			if (($file != '.') && ($file != '..')) {
				//this user files
				$parts = explode("-", $file);
				if ($user_id == intval($parts[0])) {
					unlink($dir.$file);
					continue;
				}

				//too old files
				$filetime = filemtime($dir.$file);
				//echo "time : " . $filetime . "<br/>";
				$time = date("U");
				if ($time - $filetime > $timelimit) {
					unlink($dir.$file);
				}
			}
		}
		closedir($dh);
	}
}


// Tableau pour les choix d'options
$opt_status[0][0] = 0;
$opt_status[0][1] = 'Finie';
$opt_status[1][0] = 1;
$opt_status[1][1] = 'En cours';
$opt_status[2][0] = 2;
$opt_status[2][1] = 'One Shot';

$reporttype = $_GET["rpt"];

switch ($reporttype) {
	case '1':
		// Affichage normal de la collection
		$query = "SELECT t.id_tome, t.titre, t.num_tome, s.id_serie, 
		s.nom s_nom,s.flg_fini s_fini, c.note, c.comment, scen.pseudo sc_pseudo, 
		dess.pseudo de_pseudo, color.pseudo co_pseudo, e.nom ed_nom, 
		col.nom col_nom, g.libelle s_genre, ed.dte_parution dte_par, 
		ed.isbn, ed.img_couv, '0' cat
	FROM users_album u
	LEFT JOIN bd_edition ed ON u.id_edition = ed.ID_EDITION
	LEFT JOIN bd_tome t ON ed.id_tome = t.ID_TOME
	LEFT JOIN users_comment c ON (t.id_tome = c.ID_TOME AND u.user_id = c.USER_ID)
	LEFT JOIN bd_auteur scen ON t.ID_SCENAR = scen.ID_AUTEUR
	LEFT JOIN bd_auteur dess ON t.ID_DESSIN = dess.ID_AUTEUR
	LEFT JOIN bd_auteur color ON t.ID_COLOR = color.ID_AUTEUR
	LEFT JOIN bd_collection col ON ed.id_collection = col.ID_COLLECTION
	LEFT JOIN bd_editeur e ON col.id_editeur = e.ID_EDITEUR
	LEFT JOIN bd_serie s ON t.id_serie = s.ID_SERIE
	LEFT JOIN bd_genre g ON s.id_genre = g.ID_GENRE
	WHERE u.user_id = ".$DB->escape($_SESSION["UserId"])." 
	AND u.flg_achat='N'
	ORDER BY s.nom, t.num_tome, t.flg_type";
		break;

	case '2':
		// Affichage des achats futurs
		$query = "SELECT t.id_tome, t.titre, t.num_tome, s.id_serie, 
		s.nom s_nom,s.flg_fini s_fini, c.note, c.comment, scen.pseudo sc_pseudo, 
		dess.pseudo de_pseudo, color.pseudo co_pseudo, e.nom ed_nom, 
		col.nom col_nom, g.libelle s_genre, ed.dte_parution dte_par, 
		ed.isbn, ed.img_couv, '0' cat
	FROM users_album u
	LEFT JOIN bd_edition ed ON u.id_edition = ed.ID_EDITION
	LEFT JOIN bd_tome t ON ed.id_tome = t.ID_TOME
	LEFT JOIN users_comment c ON (t.id_tome = c.ID_TOME AND u.user_id = c.USER_ID)
	LEFT JOIN bd_auteur scen ON t.ID_SCENAR = scen.ID_AUTEUR
	LEFT JOIN bd_auteur dess ON t.ID_DESSIN = dess.ID_AUTEUR
	LEFT JOIN bd_auteur color ON t.ID_COLOR = color.ID_AUTEUR
	LEFT JOIN bd_collection col ON ed.id_collection = col.ID_COLLECTION
	LEFT JOIN bd_editeur e ON col.id_editeur = e.ID_EDITEUR
	LEFT JOIN bd_serie s ON t.id_serie = s.ID_SERIE
	LEFT JOIN bd_genre g ON s.id_genre = g.ID_GENRE
	WHERE u.user_id = ".$DB->escape($_SESSION["UserId"])." 
	AND u.flg_achat='O'
	ORDER BY s.nom, t.num_tome, t.flg_type";
		break;

	case '3':

		$query = "
SELECT 
	t.id_tome,
	t.titre,
	t.num_tome,
	s.id_serie,
	s.nom s_nom,
	s.flg_fini s_fini,
	g.libelle s_genre,
	se.pseudo sc_pseudo,
	de.pseudo de_pseudo,
	co.pseudo co_pseudo,
	er.nom ed_nom,
	c.nom col_nom,
	en.dte_parution,
	en.isbn,
	en.img_couv,
	'0' cat
FROM 
	(
		SELECT DISTINCT
			s.*
		FROM 
			users_album ua 
			INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
			INNER JOIN bd_tome t ON t.id_tome = en.id_tome
			INNER JOIN bd_serie s ON t.ID_SERIE=s.ID_SERIE 
		WHERE 
			ua.user_id = ".$DB->escape($_SESSION["UserId"])."
			AND NOT EXISTS (
						SELECT NULL FROM users_exclusions ues
						WHERE s.id_serie=ues.id_serie 
						AND ues.id_tome = 0 
						AND ues.user_id = ".$DB->escape($_SESSION["UserId"])."
					)
		) s
	INNER JOIN bd_tome t ON t.ID_SERIE=s.ID_SERIE
	INNER JOIN bd_edition en ON t.ID_EDITION=en.ID_EDITION
	INNER JOIN bd_genre g ON s.id_genre = g.id_genre
	INNER JOIN bd_collection c ON en.id_collection = c.id_collection
	INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur
	INNER JOIN bd_auteur se ON t.id_scenar = se.id_auteur
	INNER JOIN bd_auteur de ON t.id_dessin = de.id_auteur
	INNER JOIN bd_auteur co ON t.id_color = co.id_auteur
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
ORDER BY s.nom, t.num_tome
		";
		break;


	case '4':
		// Affichage des album présents et des achats futurs
		$query = "SELECT t.id_tome, t.titre, t.num_tome, s.id_serie, 
		s.nom s_nom,s.flg_fini s_fini, c.note, c.comment, scen.pseudo sc_pseudo, 
		dess.pseudo de_pseudo, color.pseudo co_pseudo, e.nom ed_nom, 
		col.nom col_nom, g.libelle s_genre, ed.dte_parution dte_par, 
		ed.isbn, ed.img_couv, IF(u.flg_achat='O','1','0') cat
	FROM users_album u
	LEFT JOIN bd_edition ed ON u.id_edition = ed.ID_EDITION
	LEFT JOIN bd_tome t ON ed.id_tome = t.ID_TOME
	LEFT JOIN users_comment c ON (t.id_tome = c.ID_TOME AND u.user_id = c.USER_ID)
	LEFT JOIN bd_auteur scen ON t.ID_SCENAR = scen.ID_AUTEUR
	LEFT JOIN bd_auteur dess ON t.ID_DESSIN = dess.ID_AUTEUR
	LEFT JOIN bd_auteur color ON t.ID_COLOR = color.ID_AUTEUR
	LEFT JOIN bd_collection col ON ed.id_collection = col.ID_COLLECTION
	LEFT JOIN bd_editeur e ON col.id_editeur = e.ID_EDITEUR
	LEFT JOIN bd_serie s ON t.id_serie = s.ID_SERIE
	LEFT JOIN bd_genre g ON s.id_genre = g.ID_GENRE
	WHERE u.user_id=".$DB->escape($_SESSION["UserId"])."
	ORDER BY s.nom, t.num_tome";
		break;

	case '5':
		$col = $_GET["col"];
		$ach_fut = $_GET["af"];

		if (($col != 'O') || ($ach_fut !='O')) {
			$comp_query = " AND users_album.flg_achat='".$ach_fut."' ";
			if ($ach_fut !='O') {
				$comp_query2 = " AND flg_achat = 'N' ";
			}else{
				$comp_query2 = "";
			}
		}else{
			$comp_query = '';
		}

		$query = "
SELECT * FROM
((
  SELECT
    bd_tome.id_tome,
    bd_tome.titre,
    bd_tome.num_tome,
    bd_serie.id_serie,
    bd_serie.nom s_nom,
    bd_serie.flg_fini s_fini,
    bd_genre.libelle s_genre,
    bd_auteur_sc.pseudo sc_pseudo,
    bd_auteur_des.pseudo de_pseudo,
    bd_auteur_co.pseudo co_pseudo,
    bd_editeur.nom ed_nom,
    bd_collection.nom col_nom,
    bd_edition.dte_parution,
    bd_edition.isbn,
    bd_edition.img_couv,
    IF(users_album.flg_achat='O','1','0') cat
  FROM
    users_album
    INNER JOIN bd_edition ON bd_edition.id_edition = users_album.id_edition
    INNER JOIN bd_tome ON bd_tome.id_tome = bd_edition.id_tome
    INNER JOIN bd_serie ON bd_tome.id_serie = bd_serie.id_serie
    INNER JOIN bd_genre ON bd_serie.id_genre = bd_genre.id_genre
    INNER JOIN bd_auteur bd_auteur_des ON bd_auteur_des.id_auteur = bd_tome.id_dessin
    INNER JOIN bd_auteur bd_auteur_sc ON bd_auteur_sc.id_auteur = bd_tome.id_scenar
    INNER JOIN bd_auteur bd_auteur_co ON bd_auteur_co.id_auteur = bd_tome.id_color
    INNER JOIN bd_collection ON bd_edition.id_collection = bd_collection.id_collection
    INNER JOIN bd_editeur ON bd_collection.id_editeur = bd_editeur.id_editeur
  WHERE users_album.user_id=".$DB->escape($_SESSION["UserId"])."
    ".$comp_query."
  )
  UNION
  (
	SELECT 
		t.id_tome,
		t.titre,
		t.num_tome,
		s.id_serie,
		s.nom s_nom,
		s.flg_fini s_fini,
		g.libelle s_genre,
		se.pseudo sc_pseudo,
		de.pseudo de_pseudo,
		co.pseudo co_pseudo,
		er.nom ed_nom,
		c.nom col_nom,
		en.dte_parution,
		en.isbn,
		en.img_couv,
		'2' cat
	FROM 
		(
			SELECT DISTINCT
				s.*
			FROM 
				users_album ua 
				INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
				INNER JOIN bd_tome t ON t.id_tome = en.id_tome
				INNER JOIN bd_serie s ON t.ID_SERIE=s.ID_SERIE 
			WHERE 
				ua.user_id = ".$DB->escape($_SESSION["UserId"])."
				AND NOT EXISTS (
							SELECT NULL FROM users_exclusions ues
							WHERE s.id_serie=ues.id_serie 
							AND ues.id_tome = 0 
							AND ues.user_id = ".$DB->escape($_SESSION["UserId"])."
						)
			) s
		INNER JOIN bd_tome t ON t.ID_SERIE=s.ID_SERIE
		INNER JOIN bd_edition en ON t.ID_EDITION=en.ID_EDITION
		INNER JOIN bd_genre g ON s.id_genre = g.id_genre
		INNER JOIN bd_collection c ON en.id_collection = c.id_collection
		INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur
		INNER JOIN bd_auteur se ON t.id_scenar = se.id_auteur
		INNER JOIN bd_auteur de ON t.id_dessin = de.id_auteur
		INNER JOIN bd_auteur co ON t.id_color = co.id_auteur
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
	)
) as result
ORDER BY s_nom, num_tome, cat
		";		
		break;
	default : exit('Vous devez choisir ce que vous voulez exporter.');
}

ob_implicit_flush(true);
echo "La création du fichier est en cours ...<br/>\n";

$DB->query($query);

$pdfdir = "pdffiles/";
$filename = $pdfdir . $_SESSION["UserId"] . "-" . sha1(uniqid(mt_rand(), true)) . ".pdf";
removeOldFiles($pdfdir, $_SESSION["UserId"], 7200);

$p = PDF_new();
/* open new PDF file; insert a file name to create the PDF on disk */
if (PDF_open_file($p, $filename) == 0) {
	die("Error: " . PDF_get_errmsg($p));
}
PDF_set_info($p, "Creator", "Bdovore");
PDF_set_info($p, "Author", "BDovore");
PDF_set_info($p, "Title", "Collection au 31.12.2004");
PDF_begin_page($p, 595, 842); /* start a new page */


// Initialise l'emplacement
$nb_col_max = 1;
$line = 800;
$colonne_num = 0;
$current_id_serie = 0;

while ($DB->next_record())
{
	//fwrite( $fp, $DB->f("id_tome")."\n" );

	// Vérifie si la série a changé
	if ($DB->f("id_serie") != $current_id_serie)
	{
		if ($line <= 250)
		{ // New page
			PDF_end_page($p);
			PDF_begin_page($p, 595, 842); /* start a new page */
			$line = 800;
			$new_page = true;
		}
		//Affiche un nouveau bandeau série
		if ($line <> 800) $line -= 40;
		$nom_série = stripslashes($DB->f("s_nom"));
		AddBandeauSerie($p, $line, $nom_série);
		// Affiche le détail des infos séries
		$line -=50;
		$info["AVANCEMENT"] = $opt_status[$DB->f("s_fini")][1];
		$info["GENRE"] = $DB->f("s_genre");
		AddInfoSerie($p, $line, $info);
		// Reinitialise les variables
		$current_id_serie = $DB->f("id_serie");
		$line -= 120;
		$colonne_num = 0;
		$new_line = false;
	}

	if ($new_line == true)
	{
		$line -= 120;
		$new_line = false;
	}

	//fwrite( $fp, 'Après test serie:'.$colonne_num."\n" );

	// vérifie si il y a la place d'afficher un autre album
	if (($colonne_num == 0) & ($line <= 30))
	{
		PDF_end_page($p);
		PDF_begin_page($p, 595, 842); /* start a new page */
		$line = 800;
		//Affiche un nouveau bandeau série
		$nom_série = stripslashes($DB->f("s_nom"));
		AddBandeauSerie($p, $line, $nom_série);
		$line -= 120;
	}

	//fwrite( $fp, 'Après test place:'.$colonne_num."\n" );

	$colonne = $colonne_num * 290;
	// Affiche l'album
	//Construit l'array d'info
	$info["TITRE"] = stripslashes($DB->f("titre"));
	$info["NUM_TOME"] = $DB->f("num_tome");
	$info["SCENARISTE"] = $DB->f("sc_pseudo");
	$info["DESSINATEUR"] = $DB->f("de_pseudo");
	$info["COLORISTE"] = $DB->f("co_pseudo");
	$info["EDITEUR"] = $DB->f("ed_nom");
	$info["COLLECTION"] = $DB->f("col_nom");
	$info["DATE_PARUTION"] = $DB->f("dte_par");
	$info["ISBN"] = $DB->f("isbn");
	$info["IMG_COUV"] = $DB->f("img_couv");
	$cat = $DB->f("cat");
	AddAlbumDetaille($p, $line, $colonne, $info, $cat);

	if ($colonne_num == 0)
	{
		$colonne_num = 1;
	}else{
		$colonne_num = 0;
		$new_line = true;
	}

	//fwrite( $fp, $colonne_num."\n" );


}

PDF_end_page($p); /* close page*/
PDF_close($p); /* close PDF document*/
/*$buf = PDF_get_buffer($p);
$len = strlen($buf);
header("Content-type: application/pdf");
header("Content-Length: $len");
header("Content-Disposition: inline; filename=hello.pdf");
print $buf;
PDF_delete($p); /* delete the PDFlib object */

//fclose( $fp );

echo "Vous pouvez télécharger le fichier <a href=\"" . BDO_URL . "membres/" . $filename . "\">ici<a/>(clic droit / Enregistrer la cible du lien sous)<br/>\n";
echo "<br/><a href=\"" . BDO_URL . "membres/export_pdf.php\">Retour au site<a/><br/>\n";
