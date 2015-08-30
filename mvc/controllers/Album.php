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
        $frame = getVal("frame","iframe");
        if ($id_edition > 0) {
            // ajout du filtre edition 
            $this->loadModel('Edition');
            $this->Edition->set_dataPaste(array(
                "ID_EDITION" => $id_edition
            ));

            $this->Edition->load();
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

        $this->view->set_var(array(
            'tome' => notIssetOrEmpty($this->Tome) ? $this->Edition : $this->Tome,
            //TODO rendre ça plus 'clean'. Pourquoi avoir deux noms différents ? 
            //(pour les cas où on présente album *et* éditions sur une même vue ?
            'dateparution' => notIssetOrEmpty($this->Tome) ? $this->Edition->DATE_PARUTION_EDITION : $this->Tome->DTE_PARUTION,
            'PAGETITLE' => "Album BD : " . ( notIssetOrEmpty($this->Tome) ? $this->Edition->TITRE_TOME : $this->Tome->TITRE_TOME),
            'connected' => (!empty($_SESSION['userConnect']->user_id))
        ));


        $this->loadModel('Edition');

        $dbs_edition = $this->Edition->load(c, "where PROP_STATUS not in ('0','99','98') and bd_tome.id_tome =" . $ID_TOME);

        $this->view->set_var(array("dbs_edition" => $dbs_edition));
        // set frame
        $url_referer = parse_url($_SERVER["HTTP_REFERER"]);
        $domaine = $url_referer['host'];      
        if ($domaine != parse_url(BDO_URL)['host']) {
            $frame = "default";
        }
        if ($frame == "iframe") {
            $this->view->layout = "iframe";
        }
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

                $this->Edition->load(c, "WHERE  " . $filter);


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
