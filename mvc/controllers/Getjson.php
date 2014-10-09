<?php

/*
 * @author : Tom
 * 
 * Contrôleur pour l'échange de fichier JSON sur les différentes tables de base
 * auteur, Collection, Série, Album etc... pour remplir les listes en mode AJAX
 * 
 * Peut servir de base d'une API pour mobile ?
 * 
 */

class GetJSON extends Bdo_Controller {

    public function Index() {
        $data = getVal("data", "");
        switch ($data) {
            case "Auteur" :
                $this->Auteur();
                break;
            case "Genre" :
                $this->Genre();
                break;
            case "Edition" :
                $this->Edition();
                break;

            case "Editeur" :
                $this->Editeur();
                break;
            case "Collection":
                $this->Collection();
                break;
            case "Album" :
                $this->Album();
                break;
            case "Serie" :
                $this->Serie();
                break;
            default :
                break;
        }
    }

    private function Auteur() {
        $id_auteur = getValInteger("id_auteur", 0);
        $term = getVal("term", "");
        $mode = getValInteger("mode", 0);
        /*
         * Mode : 0 pour autocomplete, 1 pour l'ensemble des données
         */

        $this->loadModel("Auteur");
        if ($id_auteur <> 0) {
            $this->Auteur->set_dataPaste(array("ID_AUTEUR" => $id_auteur));
            $this->Auteur->load();
        } else {
            $where = " WHERE PSEUDO like '%" . Db_Escape_String($term) . "%'";
            $this->Auteur->load("c", $where);
        }
        if ($mode == 0) {
            foreach ($this->Auteur->dbSelect->a_dataQuery as $obj) {

                $arr[] = (object) array(
                            'label' => $obj->PSEUDO,
                            'id' => $obj->ID_AUTEUR
                );
            }
            $this->view->set_var('json', json_encode($arr));
        } else {
            $this->view->set_var('json', json_encode($this->Auteur->dbSelect->a_dataQuery));
        }

        $this->view->layout = "ajax";
        $this->view->render();
    }

    private function Album() {
        $ID_TOME = getValInteger('id_tome', 0);
        $id_edition = getValInteger('id_edition', 0);
        $isbn = getVal('ISBN', '');
        $ean = getVal('EAN', '');
        $mode = getValInteger("mode", 0);
        if ($id_edition or $isbn <> '' or $ean <> '' ) {
            //selection par isbn ou ean
            
            if ($ean <> '') {
                // selection par ean
                $filter = "bd_edition.ean  ='" . Db_Escape_String($ean) . "'";
            } else if ($isbn <> '') {
                // selection par isbn
                $filter = "bd_edition.isbn = '" . Db_Escape_String($isbn) . "'";
            } else if ($id_edition){
                 $filter = "bd_edition.id_edition = ". $id_edition;
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
        } elseif ($ID_TOME <> 0) {
            // selection par id_tome
            $this->loadModel('Tome');
            $this->Tome->set_dataPaste(array(
                "ID_TOME" => $ID_TOME
            ));
            $this->Tome->load();

            $this->view->set_var('json', json_encode($this->Tome->dbSelect->a_dataQuery));
        } else {
            $term = getVal("term", "");
            $this->loadModel('Tome');
            $this->Tome->load("c", " WHERE bd_tome.TITRE like '" . $term . "%' limit 0,10");
           
            if ($mode == 0) {
                foreach ($this->Tome->dbSelect->a_dataQuery as $obj) {

                    $arr[] = (object) array(
                                'label' => $obj->TITRE_TOME,
                                'id' => $obj->ID_TOME
                    );
                }
                $this->view->set_var('json', json_encode($arr));
            } else {
                $this->view->set_var('json', json_encode($this->Tome->dbSelect->a_dataQuery));
            }
        }

        $this->view->layout = "ajax";

        $this->view->render();
    }

    private function Genre() {
        $id_genre = getValInteger("id_genre", 0);
        $term = getVal("term", "");
        $mode = getValInteger("mode", 0);
        /*
         * Mode : 0 pour autocomplete, 1 pour l'ensemble des données
         */
        $this->loadModel("Genre");
        if ($id_genre <> 0) {
            $this->Genre->set_dataPaste(array("ID_GENRE" => $id_genre));
            $this->Genre->load();
        } else {
            $where = " WHERE LIBELLE like '%" . Db_Escape_String($term) . "%'";
            $this->Genre->load("c", $where);
        }
        if ($mode == 0) {
            foreach ($this->Genre->dbSelect->a_dataQuery as $obj) {

                $arr[] = (object) array(
                            'label' => $obj->LIBELLE,
                            'id' => $obj->ID_GENRE
                );
            }
            $this->view->set_var('json', json_encode($arr));
        } else {
            $this->view->set_var('json', json_encode($this->Genre->dbSelect->a_dataQuery));
        }

        $this->view->layout = "ajax";
        $this->view->render();
    }

    private function Editeur() {
        $id_editeur = getValInteger("id_editeur", 0);
        $term = getVal("term", "");
        $mode = getValInteger("mode", 0);
        /*
         * Mode : 0 pour autocomplete, 1 pour l'ensemble des données
         */
        $this->loadModel("Editeur");
        if ($id_editeur <> 0) {
            $this->Editeur->set_dataPaste(array("ID_EDITEUR" => $id_editeur));
            $this->Editeur->load();
        } else {
            $where = " WHERE NOM like '%" . Db_Escape_String($term) . "%'";

            $this->Editeur->load("c", $where);
        }
        if ($mode == 0) {
            foreach ($this->Editeur->dbSelect->a_dataQuery as $obj) {

                $arr[] = (object) array(
                            'label' => $obj->NOM,
                            'id' => $obj->ID_EDITEUR
                );
            }
            $this->view->set_var('json', json_encode($arr));
        } else {
            $this->view->set_var('json', json_encode($this->Editeur->dbSelect->a_dataQuery));
        }

        $this->view->layout = "ajax";
        $this->view->render();
    }

    private function Collection() {
        $id_editeur = getValInteger("id_editeur", 0);
        $id_collection = getValInteger("id_collection", 0);
        $term = getVal("term", "");
        $mode = getValInteger("mode", 0);
        /*
         * Mode : 0 pour autocomplete, 1 pour l'ensemble des données
         */
        $this->loadModel("Collection");
        if ($id_collection <> 0) {
            $this->Collection->set_dataPaste(array("ID_COLLECTION" => $id_collection));
            $this->Collection->load();
        } else if ($id_editeur <> 0) {
            /*
             * Sélection de la liste des collection disponible pour cet éditeur
             */
            $where = " WHERE bd_collection.ID_EDITEUR =" . $id_editeur . " ";
            $this->Collection->load("c", $where);
        } else {
            $where = " WHERE bd_collection.NOM like '%" . Db_Escape_String($term) . "%'";
            $this->Collection->load("c", $where);
        }
        if ($mode == 0) {
            foreach ($this->Collection->dbSelect->a_dataQuery as $obj) {

                $arr[] = (object) array(
                            'label' => $obj->NOM,
                            'id' => $obj->ID_COLLECTION
                );
            }
            $this->view->set_var('json', json_encode($arr));
        } else {
            $this->view->set_var('json', json_encode($this->Collection->dbSelect->a_dataQuery));
        }

        $this->view->layout = "ajax";
        $this->view->render();
    }

    private function Serie() {
        $id_serie = getValInteger("id_serie", 0);
        $term = getVal("term", "");
        $mode = getValInteger("mode", 0);

        $this->loadModel("Serie");

        if ($id_serie <> 0) {
            $this->Serie->set_dataPaste(array("ID_SERIE" => $id_serie));
            $this->Serie->load();
        } else {
            $this->Serie->load("c", " WHERE bd_serie.nom like '" . Db_Escape_String($term) . "%' group by id_serie");
        }

        if ($mode == 0) {
            foreach ($this->Serie->dbSelect->a_dataQuery as $obj) {

                $arr[] = (object) array(
                            'label' => $obj->NOM_SERIE,
                            'id' => $obj->ID_SERIE
                );
            }
            $this->view->set_var('json', json_encode($arr));
        } else {
            $this->view->set_var('json', json_encode($this->Serie->dbSelect->a_dataQuery));
        }

        $this->view->layout = "ajax";
        $this->view->render();
    }

}

?>
