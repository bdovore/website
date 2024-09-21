<?php

/*
 * Fonction d'import de la collection
 * Dans un premier temps, on ne reprend que l'export CSV, compatible avec tout tableur
 * On ajoute seulement le choix du caractère de séparation "," ou ";" avec ";" par défaut plus souvent utilisé en fr
 */

class Import extends Bdo_Controller {

    public function Index() {

        if (User::minAccesslevel(2)) {
            $act = postVal("act", "");
            $this->loadModel("Edition");
            $this->loadModel("Useralbum");
            if ($act == "import") {

                $a_col = array();
                $a_col[] = $_POST['ean_isbn'];

                if (issetNotEmpty($_POST['sel_field'])) {
                    foreach ($_POST['sel_field'] as $sel_col) {
                        $a_col[] = $sel_col;
                    }
                }

                if (is_file($_FILES["importFile"]["tmp_name"])) {
                    $a_edition = array();

                    $a_tblError = array(
                        'a_lineErrorNbrCarac' => 'Erreurs nombre de caracteres dans la ligne',
                        'a_lineErrorDoublon' => 'Erreurs doublon de r&eacute;f&eacute;rence dans les lignes du fichier',
                        'a_lineErrorNbrField' => 'Erreurs nombre de champ dans une ligne',
                        'a_lineErrorTypeVal' => 'Erreurs de type de donn&eacute;es envoy&eacute;es',
                        'a_lineErrorRef' => 'Erreurs dans la base de r&eaute;f&eacute;rences BDoVore',
                        'a_lineErrorInconnue' => 'Erreurs &eacute;ditions inconnues',
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

                    $fp = fopen($_FILES["importFile"]["tmp_name"], "r");
                    while ($s_line = fgets($fp, 300)) {
                        $nb_line++;
//
                        if ($nb_line > 500) {
                            $errorNbLineFile = true;
                            break(1);
                        }

                        $errorLine = false;

                        $s_line = rtrim($s_line, "\r\n");
                        $s_line = str_replace('"', '', $s_line);

                        // erreur de nombre de caracteres
                        if ((strlen($s_line) > 50) or ( strlen($s_line) == 0)) {
                            $a_lineErrorNbrCarac[] = $s_line;
                            $errorLine = true;
                        }

                        $a_line = explode(';', $s_line);

                        // erreur de nombre de champ
                        if (count($a_line) != count($a_col)) {
                            $a_lineErrorNbrField[] = 'ligne ' . $nb_line . ' - ' . count($a_line) . ' colonnes pour ' . count($a_col) . ' annonc�s - la ligne = [ <i>' . htmlspecialchars(str_replace(';', ' ; ', $s_line)) . '</i> ]';
                            $errorLine = true;
                        }

                        if (!$errorLine) {
                            if (!isset($a_edition[$a_line[0]])) {
                                $field = array();

                                foreach ($a_col as $id_col => $col) {
                                    $val = $a_line[$id_col];

                                    switch ($col) {
                                        case 'ean' : {
                                                if (!check_EAN($val)) {
                                                    $errorLine = true;
                                                    $a_lineErrorTypeVal[] = 'ligne ' . $nb_line . ' - colonne ' . ($id_col + 1) . ' (' . $col . ') - doit etre un EAN valide - la ligne = [ <i>' . htmlspecialchars(str_replace(';', ' ; ', $s_line)) . '</i> ]';
                                                } else {
                                                    $field[$col] = $val;
                                                }
                                                break;
                                            }
                                        case 'isbn' : {
                                                if (!check_ISBN($val)) {
                                                    $errorLine = true;
                                                    $a_lineErrorTypeVal[] = 'ligne ' . $nb_line . ' - colonne ' . ($id_col + 1) . ' (' . $col . ') - doit etre un ISBN valide - la ligne = [ <i>' . htmlspecialchars(str_replace(';', ' ; ', $s_line)) . '</i> ]';
                                                } else {
                                                    $field[$col] = $val;
                                                }
                                                break;
                                            }
                                        case 'date_achat' : {
                                                if (!is_dateFr($val)) {
                                                    $errorLine = true;
                                                    $a_lineErrorTypeVal[] = 'ligne ' . $nb_line . ' - colonne ' . ($id_col + 1) . ' (' . $col . ') - doit etre une date valide au format JJ/MM/AAAA - la ligne = [ <i>' . htmlspecialchars(str_replace(';', ' ; ', $s_line)) . '</i> ]';
                                                } else {
                                                    $field[$col] = $val;
                                                }
                                                break;
                                            }
                                        case 'cote' : {
                                                $val = str_replace(',', '.', $val);

                                                if (is_numeric($val)) {

                                                    if ($val <= 0) {
                                                        $errorLine = true;
                                                        $a_lineErrorTypeVal[] = 'ligne ' . $nb_line . ' - colonne ' . ($id_col + 1) . ' (' . $col . ') - doit etre un r&eactue;el positif sup&eacute;rieur a 0 - la ligne = [ <i>' . htmlspecialchars(str_replace(';', ' ; ', $s_line)) . '</i> ]';
                                                    } else {
                                                        $val = round(($val + 0), 2);
                                                        $field[$col] = $val;
                                                    }
                                                } else {
                                                    $errorLine = true;
                                                    $a_lineErrorTypeVal[] = 'ligne ' . $nb_line . ' - colonne ' . ($id_col + 1) . ' (' . $col . ') - doit etre de type num&eacute;rique - la ligne = [ <i>' . htmlspecialchars(str_replace(';', ' ; ', $s_line)) . '</i> ]';
                                                }


                                                break;
                                            }
                                        case 'flg_achat' :
                                        case 'flg_tete' : 
                                        case 'flg_pret' :
                                        case 'flg_dedicace' :
                                        case 'flg_cadeau' : {
                                                if (!in_array(strtoupper($val), array('O', 'N'))) {
                                                    $errorLine = true;
                                                    $a_lineErrorTypeVal[] = 'ligne ' . $nb_line . ' - colonne ' . ($id_col + 1) . ' (' . $col . ') - valeurs attendue O ou N - la ligne = [ <i>' . htmlspecialchars(str_replace(';', ' ; ', $s_line)) . '</i> ]';
                                                } else {
                                                    $val = strtoupper($val);
                                                    $field[$col] = $val;
                                                }
                                                break;
                                            }
                                    }
                                    if ($errorLine) {
                                        break;
                                    }
                                }

                                // erreur valeur de champ
                                if (!$errorLine) {
                                    $a_edition[$a_line[0]] = $field;
                                    $a_editionAjout[] = $field;
                                }
                            } else {
                                // erreur ligne en doublon
                                $a_lineErrorDoublon[] = 'ligne ' . $nb_line . ' - colonne 1 (' . $a_line[0] . ') - r&eacute;f&eacute;rence d&eacute;ja pr&eacute;sente dans votre fichier - la ligne = [ <i>' . htmlspecialchars(str_replace(';', ' ; ', $s_line)) . '</i> ]';
                            }
                        }
                    }
                    fclose($fp);


                    if (!empty($a_editionAjout)) {
                        $a_eanIsbn = array();
                        $a_editionByEanIsbn = array();

                        foreach ($a_editionAjout as $editionAjout) {
                            $eanIsbn = $editionAjout[$a_col[0]];
                            $a_eanIsbn[] = Db_Escape_String($eanIsbn);
                            $a_editionByEanIsbn[$eanIsbn] = $editionAjout;
                        }


                        // controle d'existence ean ou isbn
                        $where = "
            WHERE bd_edition." . Db_Escape_String($a_col[0]) . " IN ('" . implode("','", $a_eanIsbn) . "')
            GROUP BY bd_edition." . Db_Escape_String($a_col[0]);

                        $dbs_edition = $this->Edition->load("c", $where);

                        if ($dbs_edition->nbLineResult != 0) {

                            $a_insert = array();
                            foreach($dbs_edition->a_dataQuery as $edition) {
                                    $id_edition = $edition->ID_EDITION;
                                    $val = ($a_col[0]== "ean" ? $edition->EAN_EDITION : $edition->ISBN_EDITION);
                                    $a_albumAjout[$val] = array(
                                        "ID_EDITION" => $edition->ID_EDITION,
                                        "TOME" => $edition->TITRE_TOME,
                                        "SERIE" => $edition->NOM_SERIE,
                                        "COLLECTION" => $edition->NOM_COLLECTION,
                                        "EDITEUR" => $edition->NOM_EDITIEUR,
                                        "DTE_PARUTION" => $edition->DATE_PARUTION_EDITION,
                                    );

                                    $a_insert[] = "(
                        " . $_SESSION['userConnect']->user_id . ",
                        " . $id_edition . ",
                        NOW(),
                        " . (issetNotEmpty($field['date_achat']) ? "STR_TO_DATE('" . $a_editionByEanIsbn[$val]['date_achat'] . "','%d/%m/%Y')" : 'NULL') . ",
                        " . (issetNotEmpty($field['cote']) ? "'" . Db_Escape_String($a_editionByEanIsbn[$val]['cote']) . "'" : 'NULL') . ",
                        '" . (issetNotEmpty($field['flg_achat']) ? Db_Escape_String($a_editionByEanIsbn[$val]['flg_achat']) : 'N') . "',
                        '" . (issetNotEmpty($field['flg_pret']) ? Db_Escape_String($a_editionByEanIsbn[$val]['flg_pret']) : 'N') . "',
                        '" . (issetNotEmpty($field['flg_tete']) ? Db_Escape_String($a_editionByEanIsbn[$val]['flg_tete']) : 'N') . "',
                        '" . (issetNotEmpty($field['flg_dedicace']) ? Db_Escape_String($a_editionByEanIsbn[$val]['flg_dedicace']) : 'N') . "',
                        '" . (issetNotEmpty($field['flg_cadeau']) ? Db_Escape_String($a_editionByEanIsbn[$val]['flg_cadeau']) : 'N') . "'
                        )";

                            }

                            foreach ($a_editionByEanIsbn as $eanIsbn => $editionAjout) {
                                if (!isset($a_albumAjout[$eanIsbn])) {
                                    $a_lineErrorInconnue[] = 'Erreur BDoVore : l\'' . $a_col[0] . ' ' . $eanIsbn . ' est inconnue dans la base. Merci de demander son int&eacute;gration.';
                                }
                            }

                            if (!empty($a_insert)) {
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
                    " . implode(",\n", $a_insert);

                                Db_query($query_insert);
                                $nb_insert = Db_affected_rows();
                            }
                        }
                    }

                    // affichage du bilan
                    ob_start();
                    echo '
<div class="cadre1">
    <div class="expTitle">Bilan de l\'importation du fichier ' . $_FILES["importFile"]["name"] . '</div>
        ';
                    foreach ($a_tblError as $aError => $aLibelle) {
                        if (!empty($$aError)) {
                            echo '<h3>' . $aLibelle . '</h3>' . implode('<br />', $$aError);
                        }
                    }

                    echo "<h2>" . $nb_insert . " &eacute;dition(s) ajout&eacute;e(s) sur " . count($a_insert) . " lignes correctes dans le fichier envoy&eacute;.</h2>";
                    echo '
    </div>';

                    $bilan = ob_get_clean();

                    $this->view->set_var(array
                        (
                        "BILAN" => $bilan,
                    ));
                }
            }




            if (issetNotEmpty($_POST['sel_field'])) {
                foreach ($_POST['sel_field'] as $selField) {
                    $this->view->set_var(array
                        (
                        "SEL" . $selField => 'checked',
                    ));
                }
            }

            if (notIssetOrEmpty($ean_isbn))
                $ean_isbn = 'ean';

// Tableaux d'option
            $this->view->set_var(array
                (
                "SELEAN" => ($ean_isbn == 'ean') ? 'checked' : '',
                "SELISBN" => ($ean_isbn == 'isbn') ? 'checked' : '',
            ));
        } else {
            $this->view->addAlertPage("Vous devez vous authentifier pour accéder à cette page !");
            $this->view->addPhtmlFile('alert', 'BODY');
        }
       $this->view->layout = "iframe";
        $this->view->render();
    }

}
