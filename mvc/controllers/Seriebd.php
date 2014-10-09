<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Serie
 *
 * @author Tom
 */
class SerieBD extends Bdo_Controller {
    
    public function Index () {
    
        $ID_SERIE = getVal('id_serie',1);
        $page = getVal('page',1);
        
        $this->loadModel('Serie');
        
        $this->Serie->set_dataPaste(array(
            "ID_SERIE" => $ID_SERIE
        ));
        $this->Serie->load();

        $this->view->set_var(array(
            'serie' => $this->Serie,
            'PAGETITLE' => "Série BD : " . $this->Serie->NOM_SERIE,
            'NUM_PAGE' => $page
        ));
        
        
        // liste d'albums
        $this->loadModel("Tome");
       
        $dbs_tome = $this->Tome->load('c', " 
                            WHERE bd_tome.id_serie=
                            ".$ID_SERIE." 
                             ORDER BY bd_tome.FLG_INT DESC, bd_tome.FLG_TYPE, bd_tome.NUM_TOME, bd_tome.TITRE limit ".(($page-1)*20).",20");
        // selection des albums
        $this->view->set_var('dbs_tome', $dbs_tome);
        
        // liste de série mêmes auteurs
        $this->loadModel("Serie");
        $this->view->set_var(array(
                'SERIESIMI' => $this->Serie->getSerieSameAuthor($ID_SERIE),
                'LASTAJOUT' => ""
        ));

        $this->view->render();
    }
    
}

?>
