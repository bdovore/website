<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class CollectionBd extends Bdo_Controller {

    public function Index() {
        $ID_COLLECTION = getValInteger('id_collection',1);
        $page = getValInteger('page',1);
        $this->loadModel('Collection');
        $this->loadModel("Edition");
        
         // load data from auteur
        $this->Collection->set_dataPaste(array(
            "ID_COLLECTION" => $ID_COLLECTION
        ));
        
         $this->Collection->load();
         
        $limit = "limit ".(($page-1)*20).", 20";
        $dbs_tome =  $this->Edition->getAlbumForCollection($ID_COLLECTION, $limit);
        
         $this->view->set_var(array(
            "PAGETITLE" => "Les albums ".$this->Collection->NOM_EDITEUR. " Collection ".$this->Collection->NOM_COLLECTION,
             
            "collection" =>  $this->Collection,
            "dbs_tome" => $dbs_tome,
            "nb_album" =>  $this->Collection->getNbAlbumForCollection($ID_COLLECTION),
             "lastAlbum" => $this->Edition->getLastAlbumForCollection($ID_COLLECTION,10),
            "page" => $page
        ));

        $this->view->render();
    }
}