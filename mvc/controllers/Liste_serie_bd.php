<?php


/**
 * Liste les séries de BD pour indexation google
 *
 * @author Tom
 */
class Liste_serie_bd extends Bdo_Controller {
    
    public function Index () {
        $let = getVal("lettre","A");
        $this->loadModel("Serie");
        
        $dbs_serie = $this->Serie->getListSerie($let);
        
        $this->view->set_var(array(
           "PAGETITLE" => "Toutes les séries : ".$let,
            "dbs_serie" => $dbs_serie
        ));
        $this->view->render();
    }
  }