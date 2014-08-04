<?php
minAccessLevel(2);


function traiteChaine($info) {
	$a_info = array();
	foreach ($info as $key=>$val) {
		if (!in_array($key, array('NUM_TOME','IMG_COUV'))){
			$key = htmlspecialchars($key,ENT_QUOTES);
			$a_info[$key] = htmlspecialchars($val,ENT_QUOTES);
		}
		else {
			$a_info[$key] = $val;
		}
	}
	return $a_info;
}
/* Fonction AddInfoSerie
Affiche le cadre avancement / genre
Les variables sont passées dans un array.
Clé : "AVANCEMENT" et "GENRE"
*/

function AddInfoSerie($info)
{
	$info = traiteChaine($info);
	return '<table style="width: 100%; border:solid 1px #999999;" cellspacing="0">
        <tr>
            <td class="tdSerie" bgcolor="#660000">'.$info["SERIE"].'</td>
        </tr>
        </table>
        <table style="width: 100%; border:solid 1px #000000;">
        <tr>
            <td class="tdSubSerie">Genre : '.$info["GENRE"].'</td>
            <td class="tdSubSerie">Avancement : '.$info["AVANCEMENT"].'</td>
        </tr>
    </table>';
}

/* Fonction AddInfoSerie
Affiche couv + information sur un albums
Les variables sont passées dans un array.
Clé : "TITRE", "NUM_TOME", "SCENARISTE", "DESSINATEUR", "COLORISTE", "EDITEUR", "COLLECTION", "DATE_PARUTION", "EAN", "IMG_COUV"
*/

function AddAlbumDetaille($info)
{
	$infos = traiteChaine($infos);

	$html .= "\n".'<table style="width: 100%;" cellpading="0" cellspacing="0">';

	// Place un bloc avec le numéro du tome
	// Affiche la colonne de titre
	$html .= "\n".'<tr><td align="center" class="tdNumTome">'.$info["NUM_TOME"].'</td>';
	$html .= "\n".'<td class="tdTitre" colspan="2">'.$info["TITRE"].'</td></tr>';

	// Détermine l'echelle à utiliser
	$html .= "\n".'<tr><td class="tdImg" rowspan="8">';


	$html .= "\n".'<img src="'.BDO_URL_COUV.$infos["IMG_COUV"].'" border="0" width="90"></td></tr>';

	/*$html .= "\n".'Image</td></tr>';*/

	unset($info["TITRE"]);
	unset($info["NUM_TOME"]);
	unset($info["IMG_COUV"]);

	foreach ($info as $lib => $data) {
		$html .= "\n".'<tr><td class="tdLib">'.$lib.' : </td><td class="tdData">'.$data.'</td></tr>';
	}

	$html .= "\n".'</table>'."\n";

	return $html;
}



// Tableau pour les choix d'options
$opt_status[0][0] = 0;
$opt_status[0][1] = 'Finie';
$opt_status[1][0] = 1;
$opt_status[1][1] = 'En cours';
$opt_status[2][0] = 2;
$opt_status[2][1] = 'One Shot';

$reporttype = $_GET["rpt"];

switch ($contenu) {
	case '0':
		// Affichage normal de la collection
	case '1':
		// Determine le flag achat
		if ($contenu==0) {
			$flg_achat = 'N';
			$nomFichier = "Collection au ".strftime("%d-%m-%Y");
		}else{
			$flg_achat = 'O';
			$nomFichier = "Achats futurs au ".strftime("%d-%m-%Y");
		}

		// Affichage des achats futurs
		$query = "SELECT t.id_tome, t.titre, t.num_tome, s.id_serie,
		s.nom s_nom,s.flg_fini s_fini, c.note, c.comment, scen.pseudo sc_pseudo, 
		dess.pseudo de_pseudo, color.pseudo co_pseudo, e.nom ed_nom, 
		col.nom col_nom, g.libelle s_genre, ed.dte_parution dte_par, 
		ed.ean, ed.img_couv, '0' cat
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
	AND u.flg_achat='".$DB->escape($flg_achat)."'
	ORDER BY s.nom, t.num_tome, t.flg_type";
		break;

	case '2':
		$nomFichier = "Albums manquants au ".strftime("%d-%m-%Y");
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
	en.ean,
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


	default : exit('Vous devez choisir ce que vous voulez exporter.');
}


$DB->query($query);


?>
<style type="text/css">
body {
	color: #000000;
	background-color:#FFFFFF;
	font-size: 11px;
	font-family: Trebuchet MS, Arial, Helvetica, sans-serif;
}
.tdImg     { vertical-align:top; width:100px; text-align:center;}
.tdNumTome     { font-size:12px; width:100px; vertical-align:top; text-align:center; font-weight:bold;}
.tdTitre     { font-size:12px; vertical-align:top; text-align:left; font-weight:bold;}
.tdLib     { font-size:11px; width:80px; vertical-align:top; text-align:right;}
.tdData    { font-size:11px; vertical-align:top; text-align:left;}
.tdSerie   { padding:3px 0 3px 10px;  background-color:#660000; color:#FFCC00; font-size:14px; font-weight:bold; width:100%; border:solid 1px #000000; }
.tdSubSerie   {width:50%; color:#660000; font-size:10px;}
table.page_header {width: 100%; border: none; border-bottom:solid 1mm #999999;}
table.page_footer {width: 100%; border: none; border-top:solid 1mm #999999;}

</style>

        <table class="page_header">
            <tr>
                <td style="width: 50%; text-align: left">
                    <?php echo $nomFichier;?>
                </td>
                <td style="width: 50%; text-align: right; color:#880000; font-weight:bold;font-size: 18pt">
                    BDo'vore 
                </td>
            </tr>
        </table>

<?php

// Initialise l'emplacement
$current_id_serie = 0;
$col=1;
$num = 0;
while ($DB->next_record() and ($num<300))
{
	$num++;
	// Vérifie si la série a changé
	if ($DB->f("id_serie") !== $current_id_serie)
	{
		if ($current_id_serie != 0) {
			if ($col == 2) {
				echo '<td>&nbsp;</td></tr></table>';
			}
		}
		// Affiche le détail des infos séries
		$info= array();
		$info["SERIE"] = $DB->f("s_nom");
		$info["AVANCEMENT"] = $opt_status[$DB->f("s_fini")][1];
		$info["GENRE"] = $DB->f("s_genre");
		$info = traiteChaine($info);
		

		echo '<br>
		<table style="width: 100%; border:solid 1px #999999;" cellspacing="0">
        <tr>
            <td class="tdSerie" bgcolor="#660000">'.$info["SERIE"].'</td>
        </tr>
        </table>
        <table style="width: 100%; border:solid 1px #000000;">
        <tr>
            <td class="tdSubSerie">Genre : '.$info["GENRE"].'</td>
            <td class="tdSubSerie">Avancement : '.$info["AVANCEMENT"].'</td>
        </tr>
    </table>';


		$col=1;
		$countForDiv = 1;
	}
	else {
		$countForDiv++;
	}
	$current_id_serie = $DB->f("id_serie");

	if ($col == 1) {
		echo '<table  style="width: 100%;" cellpading="0" cellspacing="2">';
		echo '<tr>';
	}
	echo '<td style="width: 50%;">';
	// Affiche l'album
	//Construit l'array d'info
	$info = array();
	$info["TITRE"] = $DB->f("titre");
	$info["NUM_TOME"] = $DB->f("num_tome");
	$info["Scénariste"] = $DB->f("sc_pseudo");
	$info["Dessinateur"] = $DB->f("de_pseudo");
	$info["Coloriste"] = $DB->f("co_pseudo");
	$info["Editeur"] = $DB->f("ed_nom");
	$info["Collection"] = $DB->f("col_nom");
	$info["Dépôt légal"] = $DB->f("dte_par");
	$info["EAN"] = $DB->f("ean");
	$info["IMG_COUV"] = $DB->f("img_couv");
	
	$info = traiteChaine($info);

	echo '
	<table style="width: 100%;" cellpading="0" cellspacing="0">
	<tr><td align="center" class="tdNumTome">'.$info["NUM_TOME"].'</td>
	<td class="tdTitre" colspan="2">'.$info["TITRE"].'</td></tr>
	<tr><td class="tdImg" rowspan="8">
	<img src="'.BDO_URL_COUV.$info["IMG_COUV"].'" border="0" width="90"></td></tr>
	';

	unset($info["TITRE"]);
	unset($info["NUM_TOME"]);
	unset($info["IMG_COUV"]);

	foreach ($info as $lib => $data) {
		echo '<tr><td class="tdLib">'.$lib.' : </td><td class="tdData">'.$data.'</td></tr>';
	}


	echo '</table>
	</td>';
	
	if ($col == 2) {
		echo '</tr></table>';
		$col = 1;

	}
	else {
		$col = 2;
	}
}
if ($col == 2) {
	echo '<td style="width: 50%;">&nbsp;</td></tr></table>';
}

