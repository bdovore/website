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

function AddAlbumDetaille(&$PDF, $line, $colonne, $infos)
{
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



// Tableau pour les choix d'options
$opt_status[0][0] = 0;
$opt_status[0][1] = 'Finie';
$opt_status[1][0] = 1;
$opt_status[1][1] = 'En cours';
$opt_status[2][0] = 2;
$opt_status[2][1] = 'One Shot';

$flg_achat = $_GET["flg_achat"];
if ($flg_achat == '') {
	$flg_achat = 'N';
}
//$fp = fopen( "log.txt" , "w" );



$query = "
SELECT
	t.id_tome,
	t.titre,
	t.num_tome,
	s.id_serie,
	s.nom s_nom,
	s.flg_fini s_fini,
	uc.note,
	uc.comment,
	scen.pseudo sc_pseudo,
	dess.pseudo de_pseudo,
	color.pseudo co_pseudo,
	er.nom ed_nom,
	c.nom col_nom,
	g.libelle s_genre,
	en.dte_parution dte_par,
	en.isbn,
	en.img_couv
FROM
	users_album ua
	LEFT JOIN bd_edition en ON ua.id_edition = en.ID_EDITION
	LEFT JOIN bd_tome t ON en.id_tome = t.ID_TOME
	LEFT JOIN users_comment uc ON (t.id_tome = uc.ID_TOME AND ua.user_id = uc.USER_ID)

	LEFT JOIN bd_auteur scen ON t.ID_SCENAR = scen.ID_AUTEUR
	LEFT JOIN bd_auteur dess ON t.ID_DESSIN = dess.ID_AUTEUR
	LEFT JOIN bd_auteur color ON t.ID_COLOR = color.ID_AUTEUR

	LEFT JOIN bd_collection c ON en.id_collection = c.ID_COLLECTION
	LEFT JOIN bd_editeur er ON c.id_editeur = er.ID_EDITEUR

	LEFT JOIN bd_serie s ON t.id_serie = s.ID_SERIE
	LEFT JOIN bd_genre g ON s.id_genre = g.ID_GENRE
WHERE
ua.user_id = ".$DB->escape($_SESSION["UserId"])."
AND ua.flg_achat='".$DB->escape($flg_achat)."'
ORDER BY s.nom, t.num_tome, t.flg_type
";

$DB->query($query);

$p = PDF_new();
/* open new PDF file; insert a file name to create the PDF on disk */
if (PDF_open_file($p, "") == 0) {
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
	AddAlbumDetaille($p, $line, $colonne, $info);

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
$buf = PDF_get_buffer($p);
$len = strlen($buf);
header("Content-type: application/pdf");
header("Content-Length: $len");
header("Content-Disposition: inline; filename=hello.pdf");
print $buf;
PDF_delete($p); /* delete the PDFlib object */

//fclose( $fp );
