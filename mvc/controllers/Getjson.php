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
    
    public function Index () {
        
    }
    
    public function Auteur () {
        $id_auteur = getVal("id_auteur",0);
        $term = getVal("term","");
        $mode = getVal("mode",0);
        /*
         * Mode : 0 pour autocomple, 1 pour l'ensemble des données
         */
        
        $this->loadModel("Auteur");
        if ($id_auteur <> 0) {
            $this->Auteur->set_dataPaste(array("ID_AUTEUR" => $id_auteur));
            $this->Auteur->load();
            
        }
        else {
            $where = " WHERE PSEUDO like '%".  Db_Escape_String($term)."%'";
            $this->Auteur->load("c",$where);
            
        }
        if ($mode == 0 ) {
            foreach ($this->Auteur->dbSelect->a_dataQuery as $obj) {

            $arr[] = (object) array(

                    'label' => $obj->PSEUDO,

                    'ID_AUTEUR' => $obj->ID_AUTEUR

            );
            
            }
            $this->view->set_var('json', json_encode($arr));
        } else
        {
           $this->view->set_var('json', json_encode($this->Auteur->dbSelect->a_dataQuery));
        }
        
        $this->view->layout = "ajax";
        $this->view->render();
    }
    
    
    public function Album (){
        $ID_TOME = getVal('id_tome',0);        
        $id_edition = getVal('id_edition',0);
        $isbn = getVal('ISBN','');
        $ean = getVal('EAN','');
        
        if ($ID_TOME == 0) {
            //selection par isbn ou ean
            if ($ean <> '') {
                // selection par ean
                $filter = "en.ean  ='".$ean."'";
            }
            else if ($isbn <> '') {
                // selection par isbn
                $filter = "en.isbn = '".$isbn."'";
            }
            
            if ($filter) {
                $this->loadModel('Edition');

                $this->Edition->load(c,"WHERE  ".$filter);


                $this->view->set_var('json', json_encode($this->Edition->dbSelect->a_dataQuery));
            }
            else {
                $arr = array();
                $arr[] = array("ERROR" => "WRONG ISBN or EAN Code");
                 $this->view->set_var('json', json_encode($arr));
            }
        

        } 
        else {
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
