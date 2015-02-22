<?php

/*
 * Fonction d'export de la collection
 * Dans un premier temps, on ne reprend que l'export CSV, compatible avec tout tableur
 * On ajoute seulement le choix du caractère de séparation "," ou ";" avec ";" par défaut plus souvent utilisé en fr
 */


class Export extends Bdo_Controller {
     
    public function Index() {
        if (User::minAccesslevel(2)) {
            $act = getVal("act", "");
            $sel_field = postVal("sel_field", "");
            $memsel = postVal("memsel", "");
            $contenu = postValInteger("contenu", 0);
            $info = postValInteger("info", 0);
            if ($act == "export") {

                // met à jour la sélection
                $codesel = "";
                for ($i = 0; $i <= 18; $i++) {
                    if (in_array($i, $sel_field)) {
                        $codesel .="1";
                    } else {
                        $codesel .="0";
                    }
                }

                if ($memsel == "checked") {
                    $user = new User($_SESSION["userConnect"]->user_id);

                    $user->load();
                    $user->set_dataPaste(array("pref_export" => $codesel));
                    $user->update();
                }

                switch ($contenu) {

                    case 0:
                    case 1:

                        // Determine le flag achat
                        if ($contenu == 0) {
                            $flg_achat = 'N';
                            $nomFichier = "Collection au " . strftime("%d-%m-%Y");
                        } else {
                            $flg_achat = 'O';
                            $nomFichier = "Achats futurs au " . strftime("%d-%m-%Y");
                        }

                        // Construction de la query
                        $this->loadModel("Useralbum");

                        $dbs_tome = $this->Useralbum->load("c", " WHERE ua.user_id = " . $_SESSION["userConnect"]->user_id . " and flg_achat = '$flg_achat' ORDER BY s.tri, s.NOM, bd_tome.NUM_TOME");
                        $entete = array('Serie', 'Titre', 'Tome', 'ISBN', 'Genre', 'Scenariste', 'Dessinateur', 'Editeur', 'Collection', 'Date parution', 'Date d\'ajout', 'Remarque', 'Pret', 'Emprunteur', 'Date d\'achat', 'Prix', 'Cadeau', 'Edition originale');
                        $largeur = array(20, 20, 5, 10, 15, 15, 15, 15, 15, 15, 15, 5, 20, 5, 10, 15, 10, 5, 5);
                        $nbpages = 100;
                        break;

                    case 2:
                        $this->loadModel("Tome");

                        $dbs_tome = $this->Tome->getListAlbumToComplete($_SESSION["userConnect"]->user_id);
                        $entete = array('Serie', 'Titre', 'Tome', 'ISBN', 'Genre', 'Scenariste', 'Dessinateur', 'Editeur', 'Collection', 'Date parution');
                        $largeur = array(20, 20, 5, 10, 15, 15, 15, 15, 18, 15);
                        $nbpages = 100;
                        $nomFichier = "Albums manquants au " . strftime("%d-%m-%Y");
                }

                switch ($info) {

                    case 0:
                        // Export sous format Excel
                        require_once (BDO_DIR . "vendors/PHPExcel_1.7.8/Classes/PHPExcel.php");

                        // Create new PHPExcel object
                        $objPHPExcel = new PHPExcel();

                        // Set document properties

                        $objPHPExcel->getProperties()->setCreator('Bdovore')
                                ->setLastModifiedBy('Bdovore')
                                ->setTitle($nomFichier)
                                ->setSubject('Ma collection ')
                                ->setDescription($nomFichier)
                                ->setKeywords('bdovore collection export')
                                ->setCategory('bdovore result file');

                        //style par defaut
                        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')
                                ->setSize(9);


                        $sharedStyle1 = new PHPExcel_Style();
                        $sharedStyle1->applyFromArray(
                                array('fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('argb' => 'FFDDDDDD')
                                    ),
                        ));

                        // ecrit titres colonnes
                        $dataTitle = array();
                        foreach ($entete as $key => $element) {
                            $a_line = array();
                            if (in_array($key, $sel_field)) {
                                $dataTitle[] = utf8_encode($element);
                                //$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($key)->setWidth($largeur[$key]);
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($key)->setAutoSize(true);
                            }
                        }
                        $objPHPExcel->getActiveSheet()->fromArray(array($dataTitle), NULL, 'A1');

                        //Ecrit le contenu
                        $dataArray = array();

                        foreach ($dbs_tome->a_dataQuery as $tome) {
                            $a_line = array();
                            $a_line[] = $tome->NOM_SERIE;
                            $a_line[] = $tome->TITRE_TOME;
                            $a_line[] = $tome->NUM_TOME;
                            $a_line[] = $tome->ISBN_EDITION;
                            $a_line[] = $tome->NOM_GENRE;
                            $a_line[] = $tome->scpseudo;
                            $a_line[] = $tome->depseudo;
                            $a_line[] = $tome->NOM_EDITEUR;
                            $a_line[] = $tome->NOM_COLLECTION;
                            $a_line[] = $tome->DTE_PARUTION;

                            if ($contenu <> 2) {
                                $a_line[] = $tome->DATE_AJOUT;
                                $a_line[] = $tome->comment;
                                $a_line[] = $tome->FLG_PRET;
                                $a_line[] = $tome->NOM_PRET;
                                $a_line[] = $tome->DATE_ACHAT;
                                $a_line[] = $tome->cote;
                                $a_line[] = $tome->FLG_CADEAU;
                                $a_line[] = $tome->FLG_TETE;
                            }

                            $dataArray[] = $a_line;
                        }
                        $objPHPExcel->getActiveSheet()->fromArray($dataArray, NULL, 'A2');


                        $objPHPExcel->getActiveSheet()->getStyle('A1:' . PHPExcel_Cell::stringFromColumnIndex(count($dataTitle) - 1) . '1')->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setAutoFilter('A1:' . PHPExcel_Cell::stringFromColumnIndex(count($dataTitle) - 1) . '1');
                        //style de remplissage
                        $objPHPExcel->getActiveSheet()->setSharedStyle($sharedStyle1, ("A1:" . PHPExcel_Cell::stringFromColumnIndex(count($dataTitle) - 1) . '1'));

                        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                        $objPHPExcel->setActiveSheetIndex(0);
                        $objPHPExcel->getActiveSheet()->setTitle('Albums');

                        // Redirect output to a clientes web browser (Excel2007)
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $nomFichier . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                        $objWriter->save('php://output');

                        exit;

                        break;

                    case 1:
                        // export en csv;
                        $nomFichier .=".csv";

                        // Construction du header
                        header("Content-Type: application/csv-tab-delimited-table; charset=utf-8");
                        header("Content-Disposition: attachment; filename=\"$nomFichier\"");

                        // Titre des colonnes
                        $txtTitre = "";
                        $pointeur = 0;
                        $sep = postVal("sep", ",");
                        foreach ($entete as $element) {
                            //teste si le pointeur est dans l'array
                            if (in_array($pointeur, $sel_field)) {
                                $txtTitre.='"' . $element . '"' . $sep;
                            }
                            $pointeur++;
                        }
                        $txtTitre = substr($txtTitre, 0, -1);
                        echo $txtTitre . "\n";

                        // Données de l'export

                        foreach ($dbs_tome->a_dataQuery as $tome) {
                            $txtCol = "";
                            $txtCol .= $tome->NOM_SERIE;
                            $txtCol .= $sep . $tome->TITRE_TOME;
                            $txtCol .= $sep . $tome->NUM_TOME;
                            $txtCol .= $sep . $tome->ISBN_EDITION;
                            $txtCol .= $sep . $tome->NOM_GENRE;
                            $txtCol .= $sep . $tome->scpseudo;
                            $txtCol .= $sep . $tome->depseudo;
                            $txtCol .= $sep . $tome->NOM_EDITEUR;
                            $txtCol .= $sep . $tome->NOM_COLLECTION;
                            $txtCol .= $sep . $tome->DTE_PARUTION;

                            if ($contenu <> 2) {
                                $txtCol .= $sep . $tome->DATE_AJOUT;
                                $txtCol .= $sep . $tome->comment;
                                $txtCol .= $sep . $tome->FLG_PRET;
                                $txtCol .= $sep . $tome->NOM_PRET;
                                $txtCol .= $sep . $tome->DATE_ACHAT;
                                $txtCol .= $sep . $tome->cote;
                                $txtCol .= $sep . $tome->FLG_CADEAU;
                                $txtCol .= $sep . $tome->FLG_TETE;
                            }

                            echo $txtCol . "\n";
                        }

                        break;

                    case 2:
                        // Export en XML
                        echo "export xml";
                        break;
                    case 3:
                        // Export en PDF
                      
                        
                        $opt_status[0][0] = 0;
                        $opt_status[0][1] = 'Finie';
                        $opt_status[1][0] = 1;
                        $opt_status[1][1] = 'En cours';
                        $opt_status[2][0] = 2;
                        $opt_status[2][1] = 'One Shot';
                        ob_implicit_flush(true);
                        $info = array();
                        echo "La création du fichier est en cours ...<br/>\n";

                        $pdfdir = BDO_DIR."public/tmp/";
                        $filename =  $_SESSION["userConnect"]->user_id . "-" . sha1(uniqid(mt_rand(), true)) . ".pdf";
                        $this->removeOldFiles($pdfdir, $_SESSION["userConnect"]->user_id, 7200);

                        $p = PDF_new();
                        PDF_set_parameter($p,"textformat", "utf8");
                        /* open new PDF file; insert a file name to create the PDF on disk */
                        if (PDF_open_file($p, $pdfdir .$filename) == 0) {
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

                        foreach ($dbs_tome->a_dataQuery as $tome) {
                            if ($tome->ID_SERIE != $current_id_serie) {
                                if ($line <= 250) { // New page
                                    PDF_end_page($p);
                                    PDF_begin_page($p, 595, 842); /* start a new page */
                                    $line = 800;
                                    $new_page = true;
                                }
                                //Affiche un nouveau bandeau série
                                if ($line <> 800)
                                    $line -= 40;
                                $nom_série = stripslashes($tome->NOM_SERIE);
                                $this->AddBandeauSerie($p, $line, $nom_série);
                                // Affiche le détail des infos séries
                                $line -=50;
                                $info["AVANCEMENT"] = $opt_status[$tome->FLG_FINI][1];
                                $info["GENRE"] = $tome->NOM_GENRE;
                                $this->AddInfoSerie($p, $line, $info);
                                // Reinitialise les variables
                                $current_id_serie = $tome->ID_SERIE;
                                $line -= 120;
                                $colonne_num = 0;
                                $new_line = false;
                            }

                            if ($new_line == true) {
                                $line -= 120;
                                $new_line = false;
                            }

                            //fwrite( $fp, 'Après test serie:'.$colonne_num."\n" );
                            // vérifie si il y a la place d'afficher un autre album
                            if (($colonne_num == 0) & ($line <= 30)) {
                                PDF_end_page($p);
                                PDF_begin_page($p, 595, 842); /* start a new page */
                                $line = 800;
                                //Affiche un nouveau bandeau série
                                $nom_série = stripslashes($tome->NOM_SERIE);
                                $this->AddBandeauSerie($p, $line, $nom_série);
                                $line -= 120;
                            }

                            //fwrite( $fp, 'Après test place:'.$colonne_num."\n" );

                            $colonne = $colonne_num * 290;
                            // Affiche l'album
                            //Construit l'array d'info
                            $info["TITRE"] = $tome->TITRE_TOME;
                            $info["NUM_TOME"] = $tome->NUM_TOME;
                            $info["SCENARISTE"] = $tome->scpseudo;
                            $info["DESSINATEUR"] = $tome->depseudo;
                            $info["COLORISTE"] = $tome->copseudo;
                            $info["EDITEUR"] = $tome->NOM_EDITEUR;
                            $info["COLLECTION"] = $tome->NOM_COLLECTION;
                            $info["DATE_PARUTION"] = $tome->DTE_PARUTION;
                            $info["ISBN"] = (issetNotEmpty($tome->ISBN) ? $tome->ISBN : $tome->EAN);
                            $info["IMG_COUV"] = $tome->IMG_COUV;
                            $cat = ($tome->FLG_ACHAT == 'O') ? 1 : 0;
                            $this->AddAlbumDetaille($p, $line, $colonne, $info, $cat);

                            if ($colonne_num == 0) {
                                $colonne_num = 1;
                            } else {
                                $colonne_num = 0;
                                $new_line = true;
                            }
                        }
                        PDF_end_page($p); /* close page */
                        PDF_close($p); /* close PDF document */
                        /* $buf = PDF_get_buffer($p);
                          $len = strlen($buf);
                          header("Content-type: application/pdf");
                          header("Content-Length: $len");
                          header("Content-Disposition: inline; filename=hello.pdf");
                          print $buf;
                          PDF_delete($p); /* delete the PDFlib object */

                        //fclose( $fp );

                        echo "Vous pouvez télécharger le fichier <a href=\"" . BDO_URL . "tmp/" . $filename . "\">ici<a/> (clic droit / Enregistrer la cible du lien sous)<br/>\n";
                        echo "<br/><a href=\"" . BDO_URL . "macollection/export\">Retour au site<a/><br/>\n";
                        break;
                }
            } else {


                // récupère les paramètres par défaut de l'utilisateur
                $user = new User($_SESSION["userConnect"]->user_id);
                $user->load();
                $codesel = $user->PREF_EXPORT;

                // Prérempli les cases à cocher
                for ($i = 0; $i <= 18; $i++) {
                    if (substr($codesel, $i, 1) == "1") {
                        $this->view->set_var("SELFIELD" . $i, 'checked');
                    }
                }

                // Tableaux d'option
                $this->view->set_var(array
                    (
                    "TYPE0" => ($info == 0) ? 'checked' : '',
                    "TYPE1" => ($info == 1) ? 'checked' : '',
                    "TYPE2" => ($info == 2) ? 'checked' : '',
                    "TYPE3" => ($info == 3) ? 'checked' : '',
                    "CONTENU0" => ($contenu == 0) ? 'checked' : '',
                    "CONTENU1" => ($contenu == 1) ? 'checked' : '',
                    "CONTENU2" => ($contenu == 2) ? 'checked' : '',
                ));

                // assigne la barre de login
                $this->view->set_var(array
                    (
                    "PAGETITLE" => "Export de données"));
                $this->view->layout = "iframe";

                $this->view->render();
            }
        } else {
            die("Vous devez vous authentifier pour accéder à cette page.");
        }
    }

    private function AddBandeauSerie(&$PDF, $line, $text) {
        $f = PDF_load_font($PDF,"Helvetica","unicode","");
        PDF_setfont($PDF, $f, 12);
        // Block de couleur
        PDF_setcolor($PDF, 'fill', 'rgb', 0.5, 0.0, 0.0, 0);
        PDF_setcolor($PDF, 'stroke', 'rgb', 0, 0, 0, 0);
        PDF_setlinewidth($PDF, 0.5);
        PDF_rect($PDF, 25, $line - 4, 520, 15);
        PDF_fill_stroke($PDF);

        // Nom de la série
        PDF_setcolor($PDF, 'both', 'rgb', 1, 0.8, 0, 0);
        PDF_show_xy($PDF, $text, 30, $line);
    }

    /* Fonction AddInfoSerie
      Affiche le cadre avancement / genre
      Les variables sont passées dans un array.
      Clé : "AVANCEMENT" et "GENRE"
     */

    private function AddInfoSerie(&$PDF, $line, $infos) {
        // Cadre autour des infos série
        PDF_setcolor($PDF, 'both', 'rgb', 0, 0, 0, 0);
        PDF_rect($PDF, 25, $line - 3, 520, 25);
        PDF_stroke($PDF);

        // afiche le descriptif de la série
        $f = PDF_load_font($PDF,"Helvetica","unicode","");
        PDF_setfont($PDF, $f, 10);
        PDF_setcolor($PDF, 'both', 'rgb', 0, 0, 0, 0);
        $colonne = 30;
        $text = "Avancement : " . $infos["AVANCEMENT"];
        PDF_show_xy($PDF, $text, 30, $line + 12);
        $text = "Genre : " . $infos["GENRE"];
        PDF_show_xy($PDF, $text, 30, $line + 2);
    }

    /* Fonction AddInfoSerie
      Affiche couv + information sur un albums
      Les variables sont passées dans un array.
      Clé : "TITRE", "NUM_TOME", "SCENARISTE", "DESSINATEUR", "COLORISTE", "EDITEUR", "COLLECTION", "DATE_PARUTION", "ISBN", "IMG_COUV"
     */

    private function AddAlbumDetaille(&$PDF, $line, $colonne, $infos, $cat) {
        // Verifie s'il y a lieu de faire un cadre gris
        switch ($cat) {
            case 1:
                // Achat futur
                // Block de couleur
                PDF_setcolor($PDF, 'fill', 'rgb', 0.8, 0.8, 0.8, 0);
                PDF_setcolor($PDF, 'stroke', 'rgb', 0, 0, 0, 0);
                PDF_setlinewidth($PDF, 0.5);
                PDF_rect($PDF, $colonne + 25, $line - 4, 275, 103);
                PDF_fill_stroke($PDF);
                break;

            case 2:
                // Achat Alb manquant
                // Block de couleur
                PDF_setcolor($PDF, 'fill', 'rgb', 0.5, 0.5, 0.5, 0);
                PDF_setcolor($PDF, 'stroke', 'rgb', 0, 0, 0, 0);
                PDF_setlinewidth($PDF, 0.5);
                PDF_rect($PDF, $colonne + 25, $line - 4, 275, 103);
                PDF_fill_stroke($PDF);
                break;
        }

        // Place un bloc avec le numéro du tome
        PDF_setcolor($PDF, 'both', 'rgb', 1, 0.8, 0, 0);
        PDF_rect($PDF, 53 + $colonne, $line + 80, 24, 15);
        PDF_fill($PDF);
         $f = PDF_load_font($PDF,"Helvetica","unicode","");
        PDF_setfont($PDF, $f, 10);
        PDF_setcolor($PDF, 'both', 'rgb', 0, 0, 0, 0);
        $text = $infos["NUM_TOME"];
        if (($text == "0") | ($text == ""))
            $text = "HS";
        $lenght = strlen($text);
        PDF_show_xy($PDF, $text, 65 - ($lenght * 3) + $colonne, $line + 86);

        // Détermine l'echelle à utiliser
        $imagefile = BDO_DIR_COUV . $infos["IMG_COUV"];

        if ((file_exists($imagefile)) & ($infos["IMG_COUV"] != "")) {
            $file_ext = strtolower(getFileExtension($infos["IMG_COUV"]));
            switch ($file_ext) {
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

            $image = PDF_open_image_file($PDF, $file_type, $imagefile, "", "");

            $width = PDF_get_value($PDF, "imagewidth", $image);
            $height = PDF_get_value($PDF, "imageheight", $image);

            $scale_x = 84 / $height; // Hauteur maximum de l'image = 84
            $scale_y = 70 / $width; // Largeur maximum de l'image = 70
            if ($scale_x < $scale_y)
                $scale_a = $scale_x;
            else
                $scale_a = $scale_y;

            $width = (int) $width * $scale_a * 0.5;
            $height = (int) $height * $scale_a;

            PDF_place_image($PDF, $image, 65 - $width + $colonne, $line + (84 - $height), $scale_a);
        }

        // Affiche la colonne de titre
         $f = PDF_load_font($PDF,"Helvetica-Bold","unicode","");
        PDF_setfont($PDF, $f, 10);
        $line += 87;
        $text = $infos["TITRE"];
        if (strlen($text) > 35)
            $text = substr($text, 0, 35) . " (...)";
        PDF_show_xy($PDF, $text, 105 + $colonne, $line);

         $f = PDF_load_font($PDF,"Helvetica","unicode","");
        PDF_setfont($PDF, $f, 9);

        $line -= 14;
        $text = "Scénariste";
        PDF_show_xy($PDF, $text, 105 + $colonne, $line);
        $text = $infos["SCENARISTE"];
        PDF_show_xy($PDF, $text, 163 + $colonne, $line);

        $line -= 12;
        $text = "Dessinateur";
        PDF_show_xy($PDF, $text, 105 + $colonne, $line);
        $text = $infos["DESSINATEUR"];
        PDF_show_xy($PDF, $text, 163 + $colonne, $line);

        $line -= 12;
        $text = "Coloriste";
        PDF_show_xy($PDF, $text, 105 + $colonne, $line);
        $text = $infos["COLORISTE"];
        PDF_show_xy($PDF, $text, 163 + $colonne, $line);

        $line -= 12;
        $text = "Editeur";
        PDF_show_xy($PDF, $text, 105 + $colonne, $line);
        $text = $infos["EDITEUR"];
        PDF_show_xy($PDF, $text, 163 + $colonne, $line);

        $line -= 12;
        $text = "Collection";
        PDF_show_xy($PDF, $text, 105 + $colonne, $line);
        $text = $infos["COLLECTION"];
        PDF_show_xy($PDF, $text, 163 + $colonne, $line);

        $line -= 12;
        $text = "Dépôt Légal";
        PDF_show_xy($PDF, $text, 105 + $colonne, $line);
        $text = $infos["DATE_PARUTION"];
        PDF_show_xy($PDF, $text, 163 + $colonne, $line);

        $line -= 12;
        $text = "ISBN";
        PDF_show_xy($PDF, $text, 105 + $colonne, $line);
        $text = $infos["ISBN"];
        PDF_show_xy($PDF, $text, 163 + $colonne, $line);
    }

    private function removeOldFiles($dir, $user_id, $timelimit) {
        if (($dh = opendir($dir))) {
            while (($file = readdir($dh)) !== false) {
                if (($file != '.') && ($file != '..')) {
                    //this user files
                    $parts = explode("-", $file);
                    if ($user_id == intval($parts[0])) {
                        unlink($dir . $file);
                        continue;
                    }

                    //too old files
                    $filetime = filemtime($dir . $file);
                    //echo "time : " . $filetime . "<br/>";
                    $time = date("U");
                    if ($time - $filetime > $timelimit) {
                        unlink($dir . $file);
                    }
                }
            }
            closedir($dh);
        }
    }

}

?>
