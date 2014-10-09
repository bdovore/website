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
            $sel_field = postVal("sel_field","");
            $memsel = postVal("memsel","");
            $contenu = postValInteger("contenu",0);
            $info = postValInteger("info",0);
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
                    $user->set_dataPaste(array("pref_export" => Db_Escape_String($codesel)));
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
                        
                        $dbs_tome = $this->Useralbum->load("c"," WHERE ua.user_id = ".$_SESSION["userConnect"]->user_id." and flg_achat = '$flg_achat' ORDER BY s.tri, s.NOM, bd_tome.NUM_TOME" );
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
                        
                        foreach ($dbs_tome->a_dataQuery as $tome ) {
                            $a_line = array();
                            $a_line[] = $tome->NOM_SERIE;
                            $a_line[] = $tome->TITRE_TOME;
                            $a_line[] = $tome->NUM_TOME;
                            $a_line[] = $tome->ISBN_EDITION;
                            $a_line[] = $tome->NOM_GENRE;
                            $a_line[] = $tome->scpeudo;
                            $a_line[] = $tome->depsceudo;
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
                        $sep = postVal("sep",",");
                        foreach ($entete as $element) {
                            //teste si le pointeur est dans l'array
                            if (in_array($pointeur, $sel_field)) {
                                $txtTitre.='"' . $element . '"'.$sep;
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
                            $txtCol .= $sep.$tome->NOM_GENRE;
                            $txtCol .= $sep.$tome->scpeudo;
                            $txtCol .= $sep.$tome->depsceudo;
                            $txtCol .= $sep.$tome->NOM_EDITEUR;
                            $txtCol .= $sep.$tome->NOM_COLLECTION;
                            $txtCol .= $sep.$tome->DTE_PARUTION;
                            
                            if ($contenu <> 2) {
                                $txtCol .= $sep.$tome->DATE_AJOUT;
                                $txtCol .= $sep.$tome->comment;
                                $txtCol .= $sep.$tome->FLG_PRET;
                                $txtCol .= $sep.$tome->NOM_PRET;
                                $txtCol .= $sep.$tome->DATE_ACHAT;
                                $txtCol .= $sep.$tome->cote;
                                $txtCol .= $sep.$tome->FLG_CADEAU;
                                $txtCol .= $sep.$tome->FLG_TETE;
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

                        require_once (BDO_DIR . "vendors/html2pdf_v4.03/html2pdf.class.php");
                        ob_start();


                        include "pdf_html2.php";


                        $content = ob_get_clean();
                        echo $content;
                        exit();
                        $html2pdf = new HTML2PDF('P', 'A4', 'fr', false, 'ISO-8859-15');
                        $html2pdf->pdf->SetDisplayMode('fullpage');
                        $html2pdf->WriteHTML($content);
                        $html2pdf->Output($nomFichier . ".pdf", 'D');

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
}

?>
