<?php



include (BDO_DIR."inc/function.html.inc.php");
include (BDO_DIR."inc/function.cle.inc.php");

minAccessLevel(2);

function is_dateFr( $value )
{
	return preg_match('`^(((0[1-9])|(1\d)|(2\d)|(3[0-1]))\/((0[1-9])|(1[0-2]))\/(\d{4}))$`',$value);
}

/*
SELECT `ISBN`,round((`ID_EDITION`/1000),2)  FROM `bd_edition` WHERE `DTE_PARUTION` between '2005-01-01' and '2005-01-31' and isbn IS NOT NULL LIMIT 500
<br>
delete  FROM `users_album` WHERE `date_ajout`>'2011-08-26'
*/

// Creation d'une nouvelle instance Template
$t = new Template(BDO_DIR."public/templates");
// fichier à utiliser
$t->set_file(array(
"tpBody" => "user_import.tpl",
"tpMenu" => "user.menu.tpl",
"tpBase" => "body.tpl"));

if ($act=="import") {

	$a_col = array();
	$a_col[] = $_POST['ean_isbn'];

	if (issetNotEmpty($_POST['sel_field']))
	{
		foreach($_POST['sel_field'] as $sel_col)
		{
			$a_col[] = $sel_col;
		}
	}

	if (is_file($_FILES["importFile"]["tmp_name"]))
	{
		$a_edition = array();

		$a_tblError = array(
		'a_lineErrorNbrCarac' => 'Erreurs nombre de caracteres dans la ligne',
		'a_lineErrorDoublon' => 'Erreurs doublon de référence dans les lignes du fichier',
		'a_lineErrorNbrField' => 'Erreurs nombre de champ dans une ligne',
		'a_lineErrorTypeVal' => 'Erreurs de type de données envoyées',
		'a_lineErrorRef' => 'Erreurs dans la base de références BDoVore',
		'a_lineErrorInconnue' => 'Erreurs éditions inconnues',
		);
		
		$a_lineErrorNbrCarac = array(); // erreur nombre de caracteres dans la ligne
		$a_lineErrorNbrField = array(); // erreur nombre de champ sur une ligne
		$a_lineErrorTypeVal = array(); // erreur de type de donnee envoye
		$a_lineErrorRef = array(); // eanisbn en double dans la base
		$a_lineErrorDoublon = array(); // doublon dans les lignes du fichiers
		$a_lineErrorInconnue = array(); // edition inconnue

		$a_editionAjout = array(); // edition ajoutees
		$a_albumAjout = array(); // album/edition reelement ajoutes
		$a_insert = array();
		$nb_insert = 0;

		$nb_line = 0;

		$fp = fopen ($_FILES["importFile"]["tmp_name"],"r");
		while ($s_line = fgets($fp,300))
		{
			$nb_line++;

			if ($nb_line > 500) {
				$errorNbLineFile = true;
				break(1);
			}

			$errorLine = false;

			$s_line = rtrim($s_line,"\r\n");
			$s_line = str_replace('"','',$s_line);

			// erreur de nombre de caracteres
			if ((strlen($s_line) > 50) or (strlen($s_line) == 0)) {
				$a_lineErrorNbrCarac[] = $s_line ;
				$errorLine = true;
			}

			$a_line = explode(';',$s_line);

			// erreur de nombre de champ
			if (count($a_line) != count($a_col)) {
				$a_lineErrorNbrField[] = 'ligne '.$nb_line.' - '.count($a_line).' colonnes pour '.count($a_col).' annoncés - la ligne = [ <i>' .htmlspecialchars(str_replace(';',' ; ',$s_line)) .'</i> ]';
				$errorLine = true;
			}

			if (!$errorLine)
			{
				if (!isset($a_edition[$a_line[0]])) 
				{
					$field = array();

					foreach ($a_col as $id_col => $col)
					{
						$val = $a_line[$id_col];

						switch ($col)
						{
							case 'ean' :
								{
									if (!check_EAN($val))
									{
										$errorLine = true;
										$a_lineErrorTypeVal[] = 'ligne '.$nb_line.' - colonne '.($id_col+1).' ('.$col.') - doit être un EAN valide - la ligne = [ <i>' .htmlspecialchars(str_replace(';',' ; ',$s_line)) .'</i> ]';
									}
									else {
										$field[$col] = $val;
									}
									break;
								}
							case 'isbn' :
								{
									if (!check_ISBN($val))
									{
										$errorLine = true;
										$a_lineErrorTypeVal[] = 'ligne '.$nb_line.' - colonne '.($id_col+1).' ('.$col.') - doit être un ISBN valide - la ligne = [ <i>' .htmlspecialchars(str_replace(';',' ; ',$s_line)) .'</i> ]';
									}
									else {
										$field[$col] = $val;
									}
									break;
								}
							case 'date_achat' :
								{
									if (!is_dateFr($val))
									{
										$errorLine = true;
										$a_lineErrorTypeVal[] = 'ligne '.$nb_line.' - colonne '.($id_col+1).' ('.$col.') - doit être une date valide au format JJ/MM/AAAA - la ligne = [ <i>' .htmlspecialchars(str_replace(';',' ; ',$s_line)) .'</i> ]';
									}
									else {
										$field[$col] = $val;
									}
									break;
								}
							case 'cote' :
								{
									$val = str_replace(',','.',$val);

									if (is_numeric($val)) {

										if ($val <=0)
										{
											$errorLine = true;
											$a_lineErrorTypeVal[] = 'ligne '.$nb_line.' - colonne '.($id_col+1).' ('.$col.') - doit être un réel positif supérieur à 0 - la ligne = [ <i>' .htmlspecialchars(str_replace(';',' ; ',$s_line)) .'</i> ]';
										}
										else {
											$val = round(($val + 0),2);
											$field[$col] = $val;
										}
									}
									else {
										$errorLine = true;
										$a_lineErrorTypeVal[] = 'ligne '.$nb_line.' - colonne '.($id_col+1).' ('.$col.') - doit être de type numérique - la ligne = [ <i>' .htmlspecialchars(str_replace(';',' ; ',$s_line)) .'</i> ]';
									}


									break;
								}
							case 'flg_achat' :
							case 'flg_pret' :
							case 'flg_dedicace' :
							case 'flg_cadeau' :
								{
									if (!in_array(strtoupper($val),array('O','N')))
									{
										$errorLine = true;
										$a_lineErrorTypeVal[] = 'ligne '.$nb_line.' - colonne '.($id_col+1).' ('.$col.') - valeurs attendue O ou N - la ligne = [ <i>' .htmlspecialchars(str_replace(';',' ; ',$s_line)) .'</i> ]';

									}
									else {
										$val = strtoupper($val);
										$field[$col] = $val;
									}
									break;
								}
						}
						if ($errorLine) {
							break(0);
						}
					}

					// erreur valeur de champ
					if (!$errorLine)
					{
						$a_edition[$a_line[0]] = $field;
						$a_editionAjout[] = $field;
					}
				}
				else {
					// erreur ligne en doublon
					$a_lineErrorDoublon[] = 'ligne '.$nb_line.' - colonne 1 ('.$a_line[0].') - référence déjà présente dans votre fichier - la ligne = [ <i>' .htmlspecialchars(str_replace(';',' ; ',$s_line)) .'</i> ]';
				}
			}
		}
		fclose ($fp);
		

		if (!empty($a_editionAjout))
		{
			$a_eanIsbn = array();
			$a_editionByEanIsbn = array();

			foreach($a_editionAjout as $editionAjout)
			{
				$eanIsbn= $editionAjout[$a_col[0]];
				$a_eanIsbn[] = $DB->escape($eanIsbn);
				$a_editionByEanIsbn[$eanIsbn] = $editionAjout;
			}

			// controle d'existence ean ou isbn
			$query_select = "
			SELECT 
				count(1) as nbval,
				bd_edition.".$a_col[0].",
				bd_edition.ID_EDITION,
				bd_tome.TITRE TOME,
				bd_serie.NOM SERIE,
				bd_collection.NOM COLLECTION,
				bd_editeur.NOM EDITEUR,
				DATE_FORMAT(bd_edition.DTE_PARUTION,'%d/%m/%Y') DTE_PARUTION
			FROM 
				bd_edition 
				INNER JOIN bd_tome USING(ID_TOME)
				INNER JOIN bd_serie USING(ID_SERIE)
				INNER JOIN bd_collection USING(ID_COLLECTION)
				INNER JOIN bd_editeur ON bd_collection.ID_EDITEUR=bd_editeur.ID_EDITEUR
			WHERE bd_edition.".$DB->escape($a_col[0])." IN ('".implode("','", $a_eanIsbn)."')
			GROUP BY bd_edition.".$DB->escape($a_col[0]);

			$DB->query($query_select);
			if ($DB->nf() != 0) {

				$a_insert = array();
				while ($DB->next_record()) {

					if ($DB->f('nbval') != 1)
					{
						$a_lineErrorRef[] = 'Erreur BDoVore : l\''.$a_col[0].' ' . $DB->f($a_col[0]) . ' est référencée '.$DB->f('nbval').' fois et demande une correction.';
					}
					else {

						$id_edition = $DB->f("ID_EDITION");

						$a_albumAjout[$DB->f($a_col[0])] = array(
						"ID_EDITION" => $DB->f("ID_EDITION"),
						"TOME" => $DB->f("TOME"),
						"SERIE" => $DB->f("SERIE"),
						"COLLECTION" => $DB->f("COLLECTION"),
						"EDITEUR" => $DB->f("EDITEUR"),
						"DTE_PARUTION" => $DB->f("DTE_PARUTION"),
						);

						$a_insert[] = "(
						".$_SESSION['UserId'].",
						".$id_edition.",
						NOW(),
						".(issetNotEmpty($field['date_achat']) ? "STR_TO_DATE('".$a_editionByEanIsbn[$DB->f($a_col[0])]['date_achat']."','%d/%m/%Y')" : 'NULL').",
						".(issetNotEmpty($field['cote']) ? "'".$DB->escape($a_editionByEanIsbn[$DB->f($a_col[0])]['cote'])."'" : 'NULL').",
						'".(issetNotEmpty($field['flg_achat']) ? $DB->escape($a_editionByEanIsbn[$DB->f($a_col[0])]['flg_achat']) : 'N')."',
						'".(issetNotEmpty($field['flg_pret']) ? $DB->escape($a_editionByEanIsbn[$DB->f($a_col[0])]['flg_pret']) : 'N')."',
						'".(issetNotEmpty($field['flg_tete']) ? $DB->escape($a_editionByEanIsbn[$DB->f($a_col[0])]['flg_tete']) : 'N')."',
						'".(issetNotEmpty($field['flg_dedicace']) ? $DB->escape($a_editionByEanIsbn[$DB->f($a_col[0])]['flg_dedicace']) : 'N')."',
						'".(issetNotEmpty($field['flg_cadeau']) ? $DB->escape($a_editionByEanIsbn[$DB->f($a_col[0])]['flg_cadeau']) : 'N')."'
						)";
					}
				}

				foreach($a_editionByEanIsbn as $eanIsbn => $editionAjout)
				{
					if (!isset($a_albumAjout[$eanIsbn]))
					{
						$a_lineErrorInconnue[] = 'Erreur BDoVore : l\''.$a_col[0].' ' . $eanIsbn . ' est inconnue dans la base. Merci de demander son intégration.';
					}
				}

				if (!empty($a_insert))
				{
					$query_insert = "
					INSERT IGNORE INTO users_album (
						user_id, 
						id_edition,
						date_ajout,
						date_achat,
						cote,
						flg_achat,
						flg_pret,
						flg_tete,
						flg_dedicace,
						flg_cadeau
					)
					VALUES
					" . implode(",\n",$a_insert);

					$DB->query($query_insert);
					$nb_insert= mysql_affected_rows();
				}
			}


		}

		// affichage du bilan
		ob_start();
		echo '
<div class="cadre1">
	<div class="expTitle">Bilan de l\'importation du fichier '.$_FILES["importFile"]["name"].'</div>
		';
		foreach($a_tblError as $aError=>$aLibelle)
		{
			if (!empty($$aError))
			{
				echo '<h3>' . $aLibelle . '</h3>' . implode('<br />',$$aError);
			}
		}

		echo "<h2>".$nb_insert." édition(s) ajoutée(s) sur ".count($a_insert)." lignes correctes dans le fichier envoyé.</h2>";
		echo '
	</div>';

		$bilan = ob_get_clean();

		$t->set_var (array
		(
		"BILAN" => $bilan,
		));

	}
}

/*
Date d'achat<input type="checkbox" name="sel_field[]" value="date_achat" {SELdate_achat} /><br />
Prix d'achat<input type="checkbox" name="sel_field[]" value="cote" {SELcote} /><br />
Futur achat<input type="checkbox" name="sel_field[]" value="flg_achat" {SELflg_achat} /><br />
Album prêté<input type="checkbox" name="sel_field[]" value="flg_pret" {SELflg_pret} /><br />
Cadeau<input type="checkbox" name="sel_field[]" value="flg_cadeau" {SELflg_cadeau} /><br />
Edition originale<input type="checkbox" name="sel_field[]" value="flg_tete" {SELflg_tete} /><br />
Dédicacée<input type="checkbox" name="sel_field[]" value="flg_dedicace" {SELflg_dedicace} /><br />
*/


if (issetNotEmpty($_POST['sel_field']))
{
	foreach($_POST['sel_field'] as $selField)
	{
		$t->set_var (array
		(
		"SEL".$selField => 'checked',
		));
	}
}

if (notIssetOrEmpty($ean_isbn)) $ean_isbn = 'ean';

// Tableaux d'option
$t->set_var (array
(
"SELEAN" => ($ean_isbn=='ean') ? 'checked' : '',
"SELISBN" => ($ean_isbn=='isbn') ? 'checked' : '',
));



// assigne la barre de login
$t->set_var (array
("LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
"PAGETITLE" => "Import de données"));
$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->pparse("MyFinalOutput","tpBase");
