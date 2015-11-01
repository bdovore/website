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

        $this->loadModel ("Serie");
        $this->Serie->load('c'," WHERE (NOM LIKE '".$term ."%' OR NOM LIKE '% ".$term ."%' OR NOM LIKE '%''".$term ."%') GROUP BY ID_SERIE ORDER BY NBR_USER_ID_SERIE desc, NOM LIMIT 0,10");

        foreach ($this->Serie->dbSelect->a_dataQuery as $obj) {
            $arr[] = (object) array(
                    'label' => $obj->NOM_SERIE,
                    'category' => "Séries",
                    'ID_SERIE' => $obj->ID_SERIE
            );
        }

        $this->loadModel ("Tome");
        $this->Tome->load('c'," WHERE (TITRE LIKE '". $term ."%' ) ORDER BY NBR_USER_ID_TOME desc, TITRE LIMIT 0,10");

        foreach ($this->Tome->dbSelect->a_dataQuery as $obj) {
            $arr[] = (object) array(
                    'label' => $obj->TITRE_TOME,
                    'category' => "Albums",
                    'ID_TOME' => $obj->ID_TOME
            );
        }
        
        $this->Tome->load('c'," WHERE (en.ean like '". $term ."%' ) ORDER BY TITRE LIMIT 0,10");

        foreach ($this->Tome->dbSelect->a_dataQuery as $obj) {
            $arr[] = (object) array(
                    'label' => $obj->TITRE_TOME,
                    'category' => "Albums",
                    'ID_TOME' => $obj->ID_TOME
            );
        }
        
        $this->Tome->load('c'," WHERE (en.isbn like '". $term ."%' ) ORDER BY TITRE LIMIT 0,10");

        foreach ($this->Tome->dbSelect->a_dataQuery as $obj) {
            $arr[] = (object) array(
                    'label' => $obj->TITRE_TOME,
                    'category' => "Albums",
                    'ID_TOME' => $obj->ID_TOME
            );
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
        
        $this->loadModel("User");
        $this->User->load('c'," WHERE (username LIKE '". $term ."%') AND OPEN_COLLEC = 'Y'  ORDER BY username LIMIT 0,10");
        foreach ($this->User->dbSelect->a_dataQuery as $obj) {
            $arr[] = (object) array(
                    'label' => $obj->username,
                "ID_USER" => ($this->User->user_id * 1209) + 951,
                    'category' => "Bdovore"
            );
        }
        $this->view->set_var('json', json_encode($arr));
        $this->view->layout = "ajax";
        $this->view->render();
    }
}

