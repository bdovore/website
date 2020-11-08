<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *//**
 * Description of Album
 *
 * @author Tom
 *
 * Controller de la fiche album
 */

class Album extends Bdo_Controller {

    public function Index() {

        $ID_TOME = getValInteger('id_tome', 1);
        $id_edition = getValInteger('id_edition', 0);
        $ean = getVal("ean","");
        $isbn = getVal("isbn","");
        $frame = getVal("frame","iframe");
        $mobile = getVal("mobile",""); // check if mobile device detected
        if ($id_edition > 0 || $ean || $isbn) {
            // ajout du filtre edition
            $this->loadModel('Edition');
            if ($ean) { // filtre par ean
                $this->Edition->load("c"," WHERE bd_edition.ean = '".Db_Escape_String($ean)."'");
            } else if ($isbn) {
                 $this->Edition->load("c"," WHERE bd_edition.isbn = '".Db_Escape_String($isbn)."'");
            } else {
                $this->Edition->set_dataPaste(array(
                    "ID_EDITION" => $id_edition
                ));

                $this->Edition->load();
            }
        } else {
            /*
             * Ouverture d'une fiche sans préciser l'édition
             * On vérififie si l'album est dans la collection de l'utilisateur, dans ce cas on affichera son édition plutot que l'édition par défaut
             */
            if (Bdo_Cfg::user()->minAccesslevel(2)) {
                $this->loadModel('Useralbum');
                $this->Useralbum->load("c", " WHERE ua.user_id = " . intval($_SESSION['userConnect']->user_id) . " AND bd_tome.id_tome = " . $ID_TOME . " GROUP BY bd_tome.id_tome");
                if (issetNotEmpty($this->Useralbum->ID_EDITION)) {
                    // ajout du filtre edition

                    $this->loadModel('Edition');
                    $this->Edition->set_dataPaste(array(
                        "ID_EDITION" => $this->Useralbum->ID_EDITION
                    ));

                    $this->Edition->load();
                } else {
                    $this->loadModel('Tome');
                    $this->Tome->set_dataPaste(array(
                        "ID_TOME" => $ID_TOME
                    ));
                    $this->Tome->load();
                }
            } else {
                $this->loadModel('Tome');
                $this->Tome->set_dataPaste(array(
                    "ID_TOME" => $ID_TOME
                ));
                $this->Tome->load();
            }
        }

        // Récupération de la liste des éditions d'un album
        $o_tome = notIssetOrEmpty($this->Tome) ? $this->Edition : $this->Tome;
        $this->view->set_var(array(
            'tome' => $o_tome,
            //TODO rendre ça plus 'clean'. Pourquoi avoir deux noms différents ?
            //(pour les cas où on présente album *et* éditions sur une même vue ?
            'dateparution' => notIssetOrEmpty($this->Tome) ? $this->Edition->DATE_PARUTION_EDITION : $this->Tome->DTE_PARUTION,
            'PAGETITLE' => "Album BD : " . $o_tome->TITRE_TOME,
            "KEYWORD" => $o_tome->TITRE_TOME,
            'connected' => (!empty($_SESSION['userConnect']->user_id)),
            "DESCRIPTION" => "Tout sur l'album BD " .$o_tome->TITRE_TOME . " "  . strip_tags( notIssetOrEmpty($this->Tome) ? $this->Edition->HISTOIRE_TOME :  $this->Tome->HISTOIRE_TOME),
            "mobile" => $mobile
        ));


        $this->loadModel('Edition');

        $dbs_edition = $this->Edition->load("c", "where PROP_STATUS not in ('0','99','98') and bd_tome.id_tome =" . $ID_TOME ." ORDER BY DATE_PARUTION_EDITION");

        $this->view->set_var(array("dbs_edition" => $dbs_edition));
        // opengraph info
        $opengraph = array (
          "type" => "book",
          "image" => BDO_URL_COUV.$this->view->a_var["tome"]->IMG_COUV,
        "author" => $this->view->a_var['tome']->scpseudo,
         "isbn" => $this->view->a_var['tome']->EAN_EDITION,
         "release_date" => $this->view->a_var['dateparution'],
         "tag"=> $this->view->a_var['tome']->NOM_GENRE
        );
        $this->view->set_var("opengraph",$opengraph);
        // set frame
        $url_referer = parse_url($_SERVER["HTTP_REFERER"]);
        $domaine = $url_referer['host'];
        $url_host =  parse_url(BDO_URL);
        if ($domaine != $url_host['host']) {
            $frame = "default";
        }
        if ($mobile == "T") $frame = "default";
        if ($frame == "iframe") {
            $this->view->layout = "iframe";
        }
        // ajout des commentaires : remplacement du mode précédent en ajax
        $this->loadModel('Comment');         
        $where = "WHERE c.id_tome = " .$o_tome->ID_TOME ." and c.comment <> '' order by dte_post desc ";
        $dbs_comment = $this->Comment->load("c",$where);
        $this->view->set_var("dbs_comment",$dbs_comment);
        // est ce un tome exclu ?
        $exclu = "false";
        if (Bdo_Cfg::user()->minAccesslevel(2)) {
            $this->loadModel("Users_exclusions");
            $this->Users_exclusions->load("c", "WHERE id_tome = ".$o_tome->ID_TOME . " AND user_id = ".intval($_SESSION['userConnect']->user_id));
            if ($this->Users_exclusions->dbSelect->nbLineResult > 0) {
                $exclu = "true";
            } 
         }
         $this->view->set_var("tomeexclu",$exclu);
        // rendering
        $this->view->set_var("frame",$frame);
        $this->view->render();
    }

    public function Fiche() {
        $ID_TOME = getValInteger('id_tome', 1);
        $this->loadModel('Tome');
        $this->Tome->set_dataPaste(array(
            "ID_TOME" => $ID_TOME
        ));
        $this->Tome->load();
        $this->view->set_var(array(
            'tome' => $this->Tome,
            'PAGETITLE' => "Fiche Album : " . $this->Tome->TITRE_TOME));
        $this->view->layout = "fiche";
        $this->view->render();
    }

    public function getJSON() {
        $ID_TOME = getValInteger('id_tome', 0);
        $id_edition = getValInteger('id_edition', 0);
        $isbn = Db_Escape_String(getVal('ISBN', ''));
        $ean = Db_Escape_String(getVal('EAN', ''));

        if ($ID_TOME == 0) {
            //selection par isbn ou ean
            if ($ean <> '') {
                // selection par ean
                $filter = "en.ean  ='" . $ean . "'";
            } else if ($isbn <> '') {
                // selection par isbn
                $filter = "en.isbn = '" . $isbn . "'";
            }

            if ($filter) {
                $this->loadModel('Edition');

                $this->Edition->load("c", "WHERE  " . $filter);


                $this->view->set_var('json', json_encode($this->Edition->dbSelect->a_dataQuery));
            } else {
                $arr = array();
                $arr[] = array("ERROR" => "WRONG ISBN or EAN Code");
                $this->view->set_var('json', json_encode($arr));
            }
        } else {
            // selection par id_tome
            $this->loadModel('Tome');
            $this->Tome->set_dataPaste(array(
                "ID_TOME" => $ID_TOME
            ));
            $this->Tome->load();

            $this->view->set_var('json', json_encode($this->Tome->dbSelect->a_dataQuery));
        }

        $this->view->layout = "ajax";

        $this->view->render();
    }

}

?>
