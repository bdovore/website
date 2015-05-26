<?php

/*
 * Fonction d'export de la collection
 * Dans un premier temps, on ne reprend que l'export CSV, compatible avec tout tableur
 * On ajoute seulement le choix du caractère de séparation "," ou ";" avec ";" par défaut plus souvent utilisé en fr
 */

class Export extends Bdo_Controller {

    public function Index() {
        if (User::minAccesslevel(2)) {
            $act = getVal("act","");
            $sel_field = postVal("sel_field");
           // echo is_array($sel_field);
            $memsel = postVal("memsel","");
            $contenu = postValInteger("contenu",0);
            $info = postValInteger("info",0);
            if ($act == "export") {

                // met à jour la sélection
                $codesel = "";
                for ($i = 0; $i <= 19; $i++) {
                    if (in_array($i, $sel_field)) {
                        $codesel .="1";
                    } else {
                        $codesel .="0";
                    }
                }

                if ($memsel == "checked") {
                    $user = new User($_SESSION["userConnect"]->user_id);

                    $user->load();
                    $user->set_dataPaste(array("PREF_EXPORT" => $codesel));
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
                        $this->Useralbum->setWithUserComment(true);
                        $dbs_tome = $this->Useralbum->load("c"," WHERE ua.user_id = ".$_SESSION["userConnect"]->user_id." and flg_achat = '$flg_achat' ORDER BY s.tri, s.NOM, bd_tome.NUM_TOME" );
                        $entete = array(0 => 'Serie',
                            1 => 'Titre',
                            2 => 'Tome',
                            3 => 'ISBN', 
                            19 => 'EAN',
                            4 => 'Genre', 
                            5 => 'Scenariste',
                            6 => 'Dessinateur',
                            7 => 'Editeur',
                            8 => 'Collection',
                            9 => 'Date parution',
                            10 => 'Date d\'ajout',
                            11 => 'Note',
                            12 => 'Remarque',
                            13 => 'Prêté',
                            14 => 'Emprunteur',
                            15 => 'Date d\'achat',
                            16 => 'Prix',
                            17 => 'Cadeau',
                            18 => 'Edition originale');
                        $largeur = array(15,15,5,10,15,15,15,15,15,15,15,15,5,20,5,10,15,10,5,5);
                        //$entete = array('Serie', 'Titre', 'Tome', 'ISBN', 'Genre', 'Scenariste', 'Dessinateur', 'Editeur', 'Collection', 'Date parution', 'Date d\'ajout', 'Note', 'Remarque', 'Pret', 'Emprunteur', 'Date d\'achat', 'Prix', 'Cadeau', 'Edition originale');
                        //$largeur = array(20, 20, 5, 10, 15, 15, 15, 15, 15, 15, 15, 5, 20, 5, 10, 15, 10, 5, 5);
                        $nbpages = 100;
                        break;

                    case 2:
                        $this->loadModel("Tome");
                        
                        $dbs_tome = $this->Tome->getListAlbumToComplete($_SESSION["userConnect"]->user_id); 
                        $entete = array(0 => 'Serie',
                            1 => 'Titre', 
                            2 => 'Tome', 
                            3 => 'ISBN', 
                            19 => 'EAN', 
                            4 => 'Genre', 
                            5 => 'Scenariste', 
                            6 => 'Dessinateur', 
                            7 => 'Editeur', 
                            8 => 'Collection', 
                            9 => 'Date parution');
                        $largeur = array(15,15,5,10,15,15,15,15,15,15,15);
                        //$entete = array('Serie', 'Titre', 'Tome', 'ISBN', 'Genre', 'Scenariste', 'Dessinateur', 'Editeur', 'Collection', 'Date parution');
                        //$largeur = array(20, 20, 5, 10, 15, 15, 15, 15, 18, 15);
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

                        //TODO n'exporter que ce qui a été demandé
                        foreach ($dbs_tome->a_dataQuery as $tome ) {
                            $a_line = array();
                            $a_line[] = $tome->NOM_SERIE;
                            $a_line[] = $tome->TITRE_TOME;
                            $a_line[] = $tome->NUM_TOME;
                            $a_line[] = $tome->ISBN_EDITION;
                            $a_line[] = $tome->EAN_EDITION;
                            $a_line[] = $tome->NOM_GENRE;
                            $a_line[] = $tome->scpseudo;
                            $a_line[] = $tome->depseudo;
                            $a_line[] = $tome->NOM_EDITEUR;
                            $a_line[] = $tome->NOM_COLLECTION;
                            $a_line[] = $tome->DTE_PARUTION;
                            
                            if ($contenu <> 2) {
                                $a_line[] = $tome->DATE_AJOUT;
                                $a_line[] = $tome->USER_NOTE;
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
                       
                        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
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
                        $sep = postVal("sep","|");
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
                        
                        foreach ($dbs_tome->a_dataQuery as $tome ) {
                            $txtCol = "";
                            $txtCol .= $tome->NOM_SERIE;
                            $txtCol .= $sep.$tome->TITRE_TOME;
                            $txtCol .= $sep.$tome->NUM_TOME;
                            $txtCol .= $sep.$tome->ISBN_EDITION;
                            $txtCol .= $sep.$tome->EAN_EDITION;
                            $txtCol .= $sep.$tome->NOM_GENRE;
                            $txtCol .= $sep.$tome->scpseudo;
                            $txtCol .= $sep.$tome->depseudo;
                            $txtCol .= $sep.$tome->NOM_EDITEUR;
                            $txtCol .= $sep.$tome->NOM_COLLECTION;
                            $txtCol .= $sep.$tome->DTE_PARUTION;
                            
                            if ($contenu <> 2) {
                                $txtCol .= $sep.$tome->DATE_AJOUT;
                                $txtCol .= $sep.$tome->USER_NOTE;
                                $txtCol .= $sep.preg_replace("/\r|\n/"," ",$tome->comment);
                                $txtCol .= $sep.$tome->FLG_PRET;
                                $txtCol .= $sep.$tome->NOM_PRET;
                                $txtCol .= $sep.$tome->DATE_ACHAT;
                                $txtCol .= $sep.$tome->cote;
                                $txtCol .= $sep.$tome->FLG_CADEAU;
                                $txtCol .= $sep.$tome->FLG_TETE;
                            }
                           
                            echo $txtCol . "\n";
                        }

                        die();

                        break;

                    case 2:
                        // Export en XML
                        $nomFichier .=".xml";
                        $current_id_serie = 0;

                        // Construction du header HTTP
                        header("Content-Type: application/xml; charset=utf-8");
                        header("Content-Disposition: attaichment; filename=\"$nomFichier\"");

                        // Construction du header XML
                        echo "<?xml version='1.0' encoding='UTF-8'?>\n";
                        echo "<collection>\n";

                        foreach ($dbs_tome->a_dataQuery as $tome) {
                            if ($current_id_serie == 0) {
                                $current_id_serie = $tome->ID_SERIE;
                                $nom_serie = htmlspecialchars($tome->NOM_SERIE,ENT_QUOTES,"UTF-8");
                                echo "<serie><nom>" . $nom_serie . "</nom>\n";
                            } elseif ($tome->ID_SERIE != $current_id_serie) {
                                $current_id_serie = $tome->ID_SERIE;
                                $nom_serie = htmlspecialchars($tome->NOM_SERIE,ENT_QUOTES,"UTF-8");
                                echo "</serie>\n<serie><nom>" . $nom_serie . "</nom>\n";
                            }

                            $titre_tome = htmlspecialchars($tome->TITRE_TOME,ENT_QUOTES,"UTF-8");
                            $num_tome = htmlspecialchars($tome->NUM_TOME,ENT_QUOTES,"UTF-8");
                            $scenariste = htmlspecialchars($tome->scpseudo,ENT_QUOTES,"UTF-8");
                            $dessinateur = htmlspecialchars($tome->depseudo,ENT_QUOTES,"UTF-8");
                            $coloriste = htmlspecialchars($tome->copseudo,ENT_QUOTES,"UTF-8");
                            $editeur = htmlspecialchars($tome->NOM_EDITEUR,ENT_QUOTES,"UTF-8");
                            $collection = htmlspecialchars($tome->NOM_COLLECTION,ENT_QUOTES,"UTF-8");
                            $dte_parution = htmlspecialchars($tome->DTE_PARUTION,ENT_QUOTES,"UTF-8");
                            $isbn = $tome->ISBN_EDITION;
                            $ean = $tome->EAN_EDITION;
                            $img_couv = $tome->IMG_COUV;

                            $xml = "  <tome>";
                            $xml .= "<titre>" . $titre_tome . "</titre>";
                            $xml .= "<num>" . $num_tome . "</num>";
                            $xml .= "<scenario>" . $scenariste . "</scenario>";
                            $xml .= "<dessin>" . $dessinateur . "</dessin>";
                            $xml .= "<couleur>" . $coloriste . "</couleur>";
                            $xml .= "<editeur>" . $editeur . "</editeur>";
                            $xml .= "<collection>" . $collection . "</collection>";
                            $xml .= "<parution>" . $dte_parution . "</parution>";
                            $xml .= "<isbn>" . $isbn . "</isbn>";
                            $xml .= "<ean>" . $ean . "</ean>";
                            $xml .= "<couv>" . $img_couv . "</couv>";

                            if ($contenu == 0) {
                                $ajout = $tome->DATE_AJOUT;
                                $comment = htmlspecialchars($tome->comment,ENT_QUOTES,"UTF-8");
                                $pret = $tome->FLG_PRET;
                                $emprunteur .= htmlspecialchars($tome->NOM_PRET,ENT_QUOTES,"UTF-8");
                                $achat = $tome->DATE_ACHAT;
                                $note = $tome->USER_NOTE;
                                $prix = $tome->cote;
                                $cadeau = $tome->FLG_CADEAU;
                                $tete = $tome->FLG_TETE;
                                
                                $xml_private = "<private>";
                                $xml_private .= "<ajout>". $ajout . "</ajout>";
                                $xml_private .= "<achat>" . $achat . "</achat>";
                                $xml_private .= "<note>" . $note . "</note>";
                                $xml_private .= "<comment>" . $comment . "</comment>";
                                $xml_private .= "<pret>" . $pret . "</pret>";
                                $xml_private .= "<emprunteur>" . $emprunteur . "</emprunteur>";
                                $xml_private .= "<prix>" . $prix . "</prix>";
                                $xml_private .= "<cadeau>" . $cadeau . "</cadeau>";
                                //$xml_private .= "<eo>" . . "</eo>";
                                $xml_private .= "</private>";

                                $xml .= $xml_private;
                            }
                            $xml .= "</tome>\n";

                            echo $xml;
                        }

                        if ($current_id_serie != 0)
                            echo "</serie>";

                        echo "</collection>\n";

                        die();
                        break;
                    case 3:
                        // Export en PDF
                        
                        require_once(BDO_DIR . "vendors/mpdf60/mpdf.php");
                        ob_implicit_flush(true);
                        $opt_status[0][0] = 0;
                        $opt_status[0][1] = 'Finie';
                        $opt_status[1][0] = 1;
                        $opt_status[1][1] = 'En cours';
                        $opt_status[2][0] = 2;
                        $opt_status[2][1] = 'One Shot';

                        $info = array();
                        echo "La création du fichier est en cours ...<br/>\n";
                        ob_implicit_flush(true);

                        $pdfdir = BDO_DIR . "public/tmp/";
                        $filename = $_SESSION["userConnect"]->user_id . "-" . sha1(uniqid(mt_rand(), true)) . ".pdf";
                        $this->removeOldFiles($pdfdir, $_SESSION["userConnect"]->user_id, 7200);

                        $p = new mPDF('','A4');
                        $max_num_lines = 10;

                        /* open new PDF file; insert a file name to create the PDF on disk */

                        $p->SetAuthor("Bdovore", true);
                        $p->SetCreator("Bdovore.com");
                        $p->setTitle("Collection de Bandes-Dessinées ", true);
                        $p->AddPage();
                        //PDF_begin_page($p, 595, 842); /* start a new page */
// Initialise l'emplacement
                        $nb_col_max = 1;
                        $line = $max_num_lines;
                        $colonne_num = 0;
                        $current_id_serie = 0;
                        $balise_open = false; // pour tracer que l'on a ouvert une balise "table" pour les colonnes
                        $tr_open = false;
                       
                        foreach ($dbs_tome->a_dataQuery as $tome) {
                            if ($tome->ID_SERIE != $current_id_serie) {
                                
                                if ($tr_open) { // on ferme la table à deux colonnes
                                    $p->WriteHTML("</tr>", 2, false, false);
                                    $tr_open = false;
                                }
                                if ($balise_open) { // on ferme la table à deux colonnes
                                    $p->WriteHTML("</table>", 2, false, true);
                                    $balise_open = false;
                                }
                                if ($line <= 2) { // New page
                                   $p->AddPage(); /* start a new page */
                                   $line = $max_num_lines;
                                }
                                //Affiche un nouveau bandeau série                      
                                
                                $nom_serie = stripslashes($tome->NOM_SERIE);
                                $this->AddBandeauSerie($p, $line, $nom_serie);
                                // Affiche le détail des infos séries
                                $line -=3;
                                $info["AVANCEMENT"] = $opt_status[$tome->FLG_FINI][1];
                                $info["GENRE"] = $tome->NOM_GENRE;

                                $this->AddInfoSerie($p, $line, $info);
                                // Reinitialise les variables
                                $current_id_serie = $tome->ID_SERIE;
                                
                                $colonne_num = 0;
                                $new_line = false;
                            } else if ($new_line == true) { // nouvelle ligne d'album pour la série
                                // on ouvre la nouvelle ligne
                                $line -= 2;
                                $new_line = false;
                            }

                            //fwrite( $fp, 'Après test serie:'.$colonne_num."\n" );
                            // vérifie si il y a la place d'afficher un autre album
                            if (($colonne_num == 0) && ($line < 0)) {
                                //Affiche un nouveau bandeau série
                                $nom_serie = stripslashes($tome->NOM_SERIE);
                                if ($tr_open) { // on ferme la table à deux colonnes
                                    $p->WriteHTML("</tr>",2,false,false);
                                    $tr_open = false;
                                }
                                if ($balise_open) { // on ferme la table à deux colonnes
                                    $p->WriteHTML("</table><br>",2,false,true);
                                    $balise_open = false;
                                }
                                $p->AddPage(); /* start a new page */
                                $line = $max_num_lines;
                                $this->AddBandeauSerie($p, $line, $nom_serie);
                                $line -= 2.5;
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
                            $info["ISBN"] = (issetNotEmpty($tome->ISBN_EDITION) ? $tome->ISBN_EDITION : $tome->EAN_EDITION);
                            $info["IMG_COUV"] = $tome->IMG_COUV;
                            $cat = ($tome->FLG_ACHAT == 'O') ? 1 : 0;
                            if (!$balise_open) { // on ouvre la table à deux colonnes
                                $p->WriteHTML("<table border=0 cellspacing=20 cellpadding=10><tr>", 2, true, false);
                                $balise_open = true;
                                $tr_open = true;
                            }
                            if (!$tr_open) {
                                $p->WriteHTML("<tr>", 2, false, false);
                                $tr_open = true;
                            }
                            $p->WriteHTML("<td>", 2, false, false);
                            $this->AddAlbumDetaille($p, $line, $colonne, $info, $cat);
                            $p->WriteHTML("</td>", 2, false, false);
                            if ($colonne_num == 0) {
                                $colonne_num = 1;
                            } else {
                                $p->WriteHTML("</tr>", 2, false, false);
                                $tr_open = false;
                                $colonne_num = 0;
                                $new_line = true;
                            }
                        }
                        if ($tr_open) { // on ferme la table à deux colonnes
                            $p->WriteHTML("</tr>",2,false,false);
                            $tr_open = false;
                        }
                        if ($balise_open) { // on ferme la table à deux colonnes
                            $p->WriteHTML("</table><br>",2,false,true);
                            $balise_open = false;
                        }
                        $p->output($pdfdir . $filename); /* close PDF document */
                        /* $buf = PDF_get_buffer($p);
                          $len = strlen($buf);
                          header("Content-type: application/pdf");
                          header("Content-Length: $len");
                          header("Content-Disposition: inline; filename=hello.pdf");
                          print $buf;
                          PDF_delete($p); /* delete the PDFlib object */

                        //fclose( $fp );

                        echo "Vous pouvez télécharger le fichier <a href=\"" . BDO_URL . "tmp/" . $filename . "\">ici<a/> (clic droit / Enregistrer la cible du lien sous)<br/>\n";
                        echo "<br/><a href=\"" . BDO_URL . "export\">Retour au site<a/><br/>\n";
                        break;
                }
            } else {


                // récupère les paramètres par défaut de l'utilisateur
                $user = new User($_SESSION["userConnect"]->user_id);
                $user->load();
                $codesel = $user->PREF_EXPORT;

                // Prérempli les cases à cocher
                for ($i = 0; $i <= 19; $i++) {
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
        $html = '<div style="background-color:#990000;border:1 px solid #000;font-size: 16;color:#FFCC00;font-family:Helvetica;">' . htmlspecialchars($text) . '</div>';
        $PDF->WriteHTML($html,2,true,true);
        //PDF_show_xy($PDF, $text, 30, $line);
    }

    /* Fonction AddInfoSerie
      Affiche le cadre avancement / genre
      Les variables sont passées dans un array.
      Clé : "AVANCEMENT" et "GENRE"
     */

    private function AddInfoSerie(&$PDF, $line, $infos) {
        // Cadre autour des infos série
        //PDF_setcolor($PDF, 'both', 'rgb', 0, 0, 0, 0);
        $html = "<p style='border:1px solid #000;font-size:12; font-family:Helvetica;'>" . "Avancement : " . $infos["AVANCEMENT"] . "<br>" . "Genre : " . $infos["GENRE"]."</p>";
        $PDF->WriteHTML($html, 2, true, true);
        //PDF_show_xy($PDF, $text, 30, $line + 2);
    }

    /* Fonction AddInfoSerie
      Affiche couv + information sur un albums
      Les variables sont passées dans un array.
      Clé : "TITRE", "NUM_TOME", "SCENARISTE", "DESSINATEUR", "COLORISTE", "EDITEUR", "COLLECTION", "DATE_PARUTION", "ISBN", "IMG_COUV"
     */

    private function AddAlbumDetaille(&$PDF, $line, $colonne, $infos, $cat) {
        $imagefile = BDO_DIR_COUV . $infos["IMG_COUV"];
        if ((file_exists($imagefile))) {
            $im = file_get_contents($imagefile);
            $imdata = base64_encode($im); 
        } else {
            
            $im  = file_get_contents(BDO_DIR_COUV . "default.png");
             $imdata = base64_encode($im);
        }
                    switch (substr($infos["IMG_COUV"],-3)) { 
                        case "jpg" :
                        case "jpeg" :
                            $extension = "jpg";
                            break;
                        case "gif" :
                            $extension = "gif";
                            break;
                        case "png" :
                            $extension = "png";
                            break;
                        
                            
                    };
            ;
       
        $html = ' <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                            <td align="center"><div style="background-color:#FFCC00; width: 40px; font-size:14px;font-weight: bold;">' . ($infos["NUM_TOME"] ?  $infos["NUM_TOME"]: "HS") . '</div></td>
                            <td style="font-weight: bold;font-size:14px;">' . htmlspecialchars($infos["TITRE"]) . '</td>
                    </tr>
                    <tr>
                            <td valign="top"><img src="data:image/'.$extension. ';base64, '. $imdata. '" style="width:100px;" /> </td>
                            <td valign="top"><table border="0" style="font-size:12px;">
                                    <tr>
                                            <td>Scénariste </td><td> '. htmlspecialchars($infos["SCENARISTE"]) .' </td>
                                    </tr>
                                    <tr>
                                            <td>Dessinateur </td><td>'. htmlspecialchars($infos["DESSINATEUR"]) .' </td>
                                    </tr>
                                     <tr>
                                                    <td>Coloriste </td><td> '. htmlspecialchars($infos["COLORISTE"]) .'</td>
                                    </tr>
                                     <tr>
                                                    <td>Editeur </td><td>'. htmlspecialchars($infos["EDITEUR"]) .'</td>
                                    </tr>
                                     <tr>
                                                    <td>Collection </td><td> '. htmlspecialchars($infos["COLLECTION"]) .'</td>
                                            </tr>
                                    
                                    <tr>
                                                    <td>Dépôt Légal  </td><td> '. htmlspecialchars($infos["DATE_PARUTION"]) .' </td>
                                    </tr>
                                    <tr>
                                                    <td>ISBN  </td><td> '. htmlspecialchars($infos["ISBN"]) .'  </td>
                                    </tr>
                                    </table>
                            </td>	
                    </tr>	
            </table>';
        $PDF->WriteHTML($html, 0, false, false);
        
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
