<?php
set_time_limit(120);
//ini_set('memory_limit', '256M');




include (BDO_DIR."inc/function.html.inc.php");

minAccessLevel(2);

if ($act=="export") {

    // met à jour la sélection
    $codesel = "";
    for ($i=0; $i<=18; $i++) {
        if (in_array($i, $sel_field)) {
            $codesel .="1";
        }else{
            $codesel .="0";
        }
    }

    if ($memsel=="checked") {

        $query = "UPDATE users SET pref_export = '".$DB->escape($codesel)."' WHERE user_id = ".$DB->escape($_SESSION["UserId"]);
        $DB->query($query);

    }

    switch ($contenu) {

        case 0:
        case 1:

            // Determine le flag achat
            if ($contenu==0) {
                $flg_achat = 'N';
                $nomFichier = "Collection au ".strftime("%d-%m-%Y");
            }else{
                $flg_achat = 'O';
                $nomFichier = "Achats futurs au ".strftime("%d-%m-%Y");
            }

            // Construction de la query
            $query = "
    SELECT
        bd_serie.nom serie,
        bd_tome.titre,
        bd_tome.num_tome,
        bd_edition.isbn,
        bd_genre.libelle,
        bd_auteur.PSEUDO p_scenar,
        bd_auteur_1.PSEUDO p_dessin,
        bd_editeur.NOM editeur,
        bd_collection.nom,
        bd_edition.dte_parution,
        users_album.date_ajout,
        users_comment.note,
        users_album.comment,
        users_album.flg_pret,
        users_album.nom_pret,
        users_album.date_achat,
        users_album.cote,
        users_album.flg_cadeau,
        users_album.flg_tete
    FROM
        users_album
        INNER JOIN bd_edition ON bd_edition.id_edition = users_album.id_edition
        INNER JOIN bd_tome ON bd_tome.id_tome = bd_edition.ID_TOME
        INNER JOIN bd_auteur ON bd_tome.id_scenar = bd_auteur.ID_AUTEUR
        INNER JOIN bd_auteur AS bd_auteur_1 ON bd_tome.id_dessin = bd_auteur_1.ID_AUTEUR
        INNER JOIN bd_serie ON bd_tome.id_serie = bd_serie.ID_SERIE
        INNER JOIN bd_genre ON bd_serie.id_genre = bd_genre.ID_GENRE
        INNER JOIN bd_collection ON bd_edition.id_collection = bd_collection.ID_COLLECTION
        INNER JOIN bd_editeur ON bd_collection.id_editeur = bd_editeur.id_editeur
        LEFT JOIN users_comment ON bd_tome.id_tome = users_comment.ID_TOME AND users_album.user_id = users_comment.USER_ID
    WHERE
        users_album.user_id= ".$DB->escape($_SESSION["UserId"])."
        AND users_album.flg_achat='".$DB->escape($flg_achat)."'
    ORDER BY bd_serie.tri, bd_serie.NOM, bd_tome.NUM_TOME
    ";
            $entete = array('Serie', 'Titre','Tome','ISBN', 'Genre','Scénariste','Dessinateur','Editeur','Collection','Date parution','Date d\'ajout','Note','Remarque','Prêté','Emprunteur','Date d\'achat', 'Prix','Cadeau','Edition originale');
            $largeur = array(20,20,5,10, 15,15,15,15,15,15,15,5,20,5,10,15,10,5,5);
            $nbpages = 100;
            break;

        case 2:

            $query ="
SELECT
    s.nom,
    t.titre,
    t.num_tome,
    en.isbn,
    g.libelle,
    se.pseudo p_scenar,
    de.pseudo p_dessin,
    er.nom editeur,
    c.nom,
    en.dte_parution
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
ORDER BY s.tri, s.NOM, t.NUM_TOME
            ";
            $entete = array('Serie', 'Titre','Tome','ISBN', 'Genre','Scenariste','Dessinateur','Editeur','Collection','Date parution');
            $largeur = array(20,20,5,10,15,15,15,15,18,15);
            $nbpages = 100;
            $nomFichier = "Albums manquants au ".strftime("%d-%m-%Y");
    }

    switch ($info) {

        case 0:
            // Export sous format Excel
            require_once (BDO_DIR."vendors/PHPExcel_1.7.8/Classes/PHPExcel.php");

            // Create new PHPExcel object
            $objPHPExcel = new PHPExcel();

            // Set document properties

            $objPHPExcel->getProperties()->setCreator('moi')
            ->setLastModifiedBy('moi')
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
            array('fill'    => array(
            'type'      => PHPExcel_Style_Fill::FILL_SOLID,
            'color'     => array('argb' => 'FFDDDDDD')
            ),
            ));

            // ecrit titres colonnes
            $dataTitle = array();
            foreach ($entete as $key => $element)
            {
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
            $DB->query($query);
            while ($DB->next_record()) {
                $a_line = array();
                for ($colonne = 0; $colonne < $DB->num_fields(); $colonne++)
                {
                    if (in_array($colonne, $sel_field)) {
                        $a_line[] = utf8_encode($DB->f($colonne));
                    }
                }
                $dataArray[] = $a_line;
            }
            $objPHPExcel->getActiveSheet()->fromArray($dataArray, NULL, 'A2');


            $objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($dataTitle)-1).'1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->setAutoFilter('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($dataTitle)-1).'1');
            //style de remplissage
            $objPHPExcel->getActiveSheet()->setSharedStyle($sharedStyle1, ("A1:".PHPExcel_Cell::stringFromColumnIndex(count($dataTitle)-1).'1'));

            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setTitle('Albums');

            // Redirect output to a clientes web browser (Excel2007)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$nomFichier .'.xlsx"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit;

            break;

        case 1:
            // export en csv;
            $nomFichier .=".csv";

            // Construction du header
            header("Content-Type: application/csv-tab-delimited-table");
            header("Content-Disposition: attachment; filename=\"$nomFichier\"");

            // Titre des colonnes
            $titre = "";
            $pointeur = 0;
            foreach ($entete as $element)
            {
                //teste si le pointeur est dans l'array
                if (in_array($pointeur, $sel_field)) {
                    $txtTitre.='"'.$element.'",';
                }
                $pointeur++;
            }
            $txtTitre = substr($txtTitre, 0, -1);
            echo $txtTitre."\n";

            // Données de l'export
            $DB->query($query);
            while ($DB->next_record()) {
                $txtCol ="";
                for ($colonne = 0; $colonne < $DB->num_fields(); $colonne++)
                {
                    if (in_array($colonne, $sel_field)) {
                        $txtCol .= '"'.stripslashes($DB->f($colonne)).'",';
                    }
                }
                $txtCol = substr($txtCol, 0, -1);
                echo $txtCol."\n";
            }

            break;

        case 2:
            // Export en XML
            echo "export xml";
            break;
        case 3:
            // Export en PDF

            require_once (BDO_DIR."vendors/html2pdf_v4.03/html2pdf.class.php");
            ob_start();


            include "pdf_html2.php";


            $content = ob_get_clean();
            echo $content;exit();
            $html2pdf = new HTML2PDF('P', 'A4', 'fr', false, 'ISO-8859-15');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->WriteHTML($content);
            $html2pdf->Output($nomFichier.".pdf",'D');

            break;
    }

}
else
{


    // récupère les paramètres par défaut de l'utilisateur
    $query = "SELECT pref_export FROM users WHERE ".$DB->escape($_SESSION["UserId"]);
    $DB->query($query);
    $DB->next_record();

    $codesel = $DB->f('pref_export');

    // Creation d'une nouvelle instance Template
    $t = new Template(BDO_DIR."public/templates");
    // fichier à utiliser
    $t->set_file(array(
    "tpBody" => "user_export.tpl",
    "tpMenu" => "user.menu.tpl",
    "tpMenuColl" => "menu_coll.tpl",
    "tpBase" => "body.tpl"));

    // Préremplie les case à cocher
    for ($i=0; $i<=18; $i++) {
        if (substr($codesel,$i,1)=="1") {
            $t->set_var("SELFIELD".$i, 'checked');
        }
    }


    // Tableaux d'option
    $t->set_var (array
    (
    "TYPE0" => ($info==0) ? 'checked' : '',
    "TYPE1" => ($info==1) ? 'checked' : '',
    "TYPE2" => ($info==2) ? 'checked' : '',
    "TYPE3" => ($info==3) ? 'checked' : '',
    "CONTENU0" => ($contenu==0) ? 'checked' : '',
    "CONTENU1" => ($contenu==1) ? 'checked' : '',
    "CONTENU2" => ($contenu==2) ? 'checked' : '',
    ));



    // assigne la barre de login
    $t->set_var (array
    ("LOGINBARRE" => GetIdentificationBar(),
    "URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
    "PAGETITLE" => "Export de données"));
    $t->parse("BODY","tpBody");
    $t->parse("MENUCOLL","tpMenuColl");
    $t->parse("MENUBARRE","tpMenu");
    $t->pparse("MyFinalOutput","tpBase");
}
