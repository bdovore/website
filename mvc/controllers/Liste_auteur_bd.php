<?php


/**
 * Liste les séries de BD pour indexation google
 *
 * @author Tom
 */
class Liste_auteur_bd extends Bdo_Controller {
    
    public function Index () {
        $let = getVal("lettre","A");
        $this->loadModel("Auteur");
        
        $dbs_auteur = $this->Auteur->load("c", " WHERE PSEUDO like '".Db_Escape_String($let) ."%'");
        
        $this->view->set_var(array(
           "PAGETITLE" => "Tous les auteurs : ".$let,
            "dbs_auteur" => $dbs_auteur
        ));
        $this->view->render();
    }
  }