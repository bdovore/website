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

        $ID_SERIE = getValInteger('id_serie',1);
        $page = getValInteger('page',1);

        $this->loadModel('Serie');

        $this->Serie->set_dataPaste(array(
            "ID_SERIE" => $ID_SERIE
        ));
        $this->Serie->load();

        $this->view->set_var(array(
            'serie' => $this->Serie,
            'PAGETITLE' => "Série BD : " . $this->Serie->NOM_SERIE,
            "DESCRIPTION" => "Tout sur la série ".$this->Serie->NOM_SERIE . " ".$this->Serie->HISTOIRE, 
            "KEYWORD" => $this->Serie->NOM_SERIE,
            'NUM_PAGE' => $page,
            "opengraph" => array ("type" => "website",
                     "image" => BDO_URL_COUV.$this->Serie->IMG_COUV_SERIE)
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
        
        // liste des séries liées
        $this->loadModel("Groupeserie");
        $listSerieLiee = $this->Groupeserie->getSerieLiee( $ID_SERIE);
         $this->view->set_var(array(
                'dbs_SerieLie' =>  $listSerieLiee
              
        ));
        
         // l'utiliateur possède t-il des albums de la série ?
         if (Bdo_Cfg::user()->minAccesslevel(2)) {
             $this->loadModel("Useralbum");
             $this->loadModel("Users_exclusions");
             $nbalbum = $this->Useralbum->isSerieInCollection($ID_SERIE,$_SESSION['userConnect']->user_id);
             $serieExclu = $this->Users_exclusions->getListSerieExcluSource($_SESSION['userConnect']->user_id,$ID_SERIE);
         } else {
             $nbalbum = 0;
             $serieExclu = false;
         }
         $this->view->set_var(array(
           "nbAlbumCollec" =>  $nbalbum,
             "serieExclue"
         ));
        $this->view->render();
    }

}

?>
