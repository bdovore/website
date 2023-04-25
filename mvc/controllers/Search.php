<?php

/**
 *
 * @author laurent
 *
 */

class Search extends Bdo_Controller
{
    /**
     */
    public function Auto ()
    {
        Bdo_Cfg::setVar('debug',false);

        $arr = array();

        $term = Db_Escape_String(getVal("term",""));

        if (strlen($term) > 2) {
            
            // recherche d'album, série ou auteur à partir de 4 caractères
            $this->loadModel ("Serie");
            $this->Serie->load('c'," WHERE NOM like '". $term ."%' OR MATCH (NOM) AGAINST ( '.$term.' IN NATURAL LANGUAGE MODE)  GROUP BY ID_SERIE ORDER BY (LOG(NBR_USER_ID_SERIE +2) + IF('".$term."' = NOM, 1000, MATCH (NOM) AGAINST ( '".$term."' IN NATURAL LANGUAGE MODE))) desc, NOM LIMIT 0,10");

            foreach ($this->Serie->dbSelect->a_dataQuery as $obj) {
                $arr[] = (object) array(
                        'label' => $obj->NOM_SERIE,
                        'category' => "Séries",
                        'ID_SERIE' => $obj->ID_SERIE
                );
            }

            $this->loadModel ("Tome");
            $this->Tome->load('c'," WHERE MATCH (TITRE) AGAINST   ( '.$term.' IN NATURAL LANGUAGE MODE)  ORDER BY (LOG(NBR_USER_ID_TOME + 1)+ MATCH (TITRE) AGAINST   ( '".$term."' IN NATURAL LANGUAGE MODE)) desc, TITRE LIMIT 0,10");

            foreach ($this->Tome->dbSelect->a_dataQuery as $obj) {
                $arr[] = (object) array(
                        'label' => $obj->TITRE_TOME,
                        'category' => "Albums",
                    "ID_TOME" => $obj->ID_TOME,
                        'ID_EDITION' => $obj->ID_EDITION
                );
            }
            // si isbn ou ean possible
            if (preg_match("/[0-9X]{5,10}.+/",$term )) {
                $this->loadModel("Edition");
                 $this->Edition->load('c'," WHERE (bd_edition.ean like '". $term ."%' ) ORDER BY TITRE LIMIT 0,10");

                foreach ($this->Edition->dbSelect->a_dataQuery as $obj) {
                    $arr[] = (object) array(
                            'label' => $obj->TITRE_TOME,
                            'category' => "Albums",
                        "ID_TOME" => $obj->ID_TOME,
                            'ID_EDITION' => $obj->ID_EDITION
                    );
                }

                $this->Edition->load('c'," WHERE (bd_edition.isbn like '". $term ."%' ) ORDER BY TITRE LIMIT 0,10");

                foreach ($this->Edition->dbSelect->a_dataQuery as $obj) {
                    $arr[] = (object) array(
                            'label' => $obj->TITRE_TOME,
                            'category' => "Albums",
                            "ID_TOME" => $obj->ID_TOME,
                            'ID_EDITION' => $obj->ID_EDITION
                    );
                }
            }
           

            $this->loadModel ("Auteur");
            $this->Auteur->load('c'," WHERE (PSEUDO LIKE '". $term ."%') OR (CONCAT(PRENOM, ' ', NOM) LIKE '". $term ."%')  ORDER BY PSEUDO LIMIT 0,10");

            foreach ($this->Auteur->dbSelect->a_dataQuery as $obj) {
                $arr[] = (object) array(
                        'label' => $obj->PSEUDO,
                    "ID_AUTEUR" => $obj->ID_AUTEUR,
                        'category' => "Auteurs"
                );
            }
            
             $this->loadModel ("Collection");
            $this->Collection->load('c'," WHERE (bd_collection.NOM LIKE '". $term ."%') OR (bd_editeur.NOM LIKE '". $term ."%')  ORDER BY bd_editeur.NOM, bd_collection.NOM LIMIT 0,10");

            foreach ($this->Collection->dbSelect->a_dataQuery as $obj) {
                if ($obj->NOM_COLLECTION == "<N/A>") {
                    $label = $obj->NOM_EDITEUR;
                } else {
                    $label = $obj->NOM_EDITEUR."-".$obj->NOM_COLLECTION;
                }
                 
                $arr[] = (object) array(
                        'label' => $label,
                    "ID_COLLECTION" => $obj->ID_COLLECTION,
                        'category' => "Collections"
                );
            }
        }

        $this->loadModel("User");
        $this->User->load('c'," WHERE (username LIKE '". $term ."%') AND OPEN_COLLEC = 'Y'  ORDER BY username LIMIT 0,10");
        foreach ($this->User->dbSelect->a_dataQuery as $obj) {
            $arr[] = (object) array(
                    'label' => $obj->username,
                "ID_USER" => ($obj->user_id * 1209) + 951,
                    'category' => "Bdovore"
            );
        }
        $this->view->set_var('json', json_encode($arr));
        $this->view->layout = "ajax";
        $this->view->render();
    }
}

