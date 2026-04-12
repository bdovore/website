<?php

/**
 * Description of Auteur
 *
 * @author Tom
 */

class AuteurBD extends Bdo_Controller {

    public function Index() {

        $ID_AUTEUR = getValInteger('id_auteur',1);
        $activite = getValInteger('activite',0);
        $page = getValInteger('page',1);
        $tri = getValInteger('tri',1); // 1=date décroissante (défaut), 2=date croissante, 3=par série
        
        $this->loadModel('Auteur');
        $this->loadModel("Tome");

        // load data from auteur
        $this->Auteur->set_dataPaste(array(
            "ID_AUTEUR" => $ID_AUTEUR
        ));
        $this->Auteur->load();
        $limit = "limit ".(($page-1)*20).", 20";
        if ($activite == 0) {
            // set default value
             if ($this->Auteur->FLG_SCENAR == "1") {
                   $activite = 1;
                } else if ($this->Auteur->FLG_DESSIN == "1") {
                    $activite = 2;
                } else if ($this->Auteur->FLG_COLOR == "1") {
                    $activite= 3;
                } else {
                     $activite= 1;
                }

        }
        // load data as scenariste, dessinateur, coloriste...
        switch ($activite) {

            case 1 :
                $dbs_tome =  $this->Tome->getAlbumAsScenariste($ID_AUTEUR, $limit, $tri);
                break;
            case 2 :
                $dbs_tome =  $this->Tome->getAlbumAsDessinateur($ID_AUTEUR, $limit, $tri);
                break;
            case 3 :
                $dbs_tome =  $this->Tome->getAlbumAsColoriste($ID_AUTEUR , $limit, $tri);
                break;

            default :
                $dbs_tome =  $this->Tome->getAlbumAsScenariste($ID_AUTEUR , $limit, $tri);
                break;
        }


        $this->view->set_var(array(
            "PAGETITLE" => "Tous les albums de ".$this->Auteur->PSEUDO,
            "DESCRIPTION" => is_null($this->Auteur->COMMENT) ? "" : substr(strip_tags($this->Auteur->COMMENT),0,150),
            "KEYWORD" => $this->Auteur->PSEUDO, 
            "auteur" =>  $this->Auteur,
            "dbs_tome" => $dbs_tome,
            "nb_total_album" => $this->Tome->getNbAlbumForAuteur($ID_AUTEUR),
            "nb_album" =>  $this->Tome->getNbAlbumForAuteur($ID_AUTEUR,$activite),
            "activite" => $activite,
            "page" => $page,
            "tri" => $tri,
            "lastAlbum" => $this->Tome->getLastAlbumForAuteur($ID_AUTEUR,10),
            "opengraph" => array(
                "type" => "webpage",
                "image" => BDO_URL_IMAGE."auteur/".($this->Auteur->IMG_AUT ?$this->Auteur->IMG_AUT : "default_auteur.png" )
            )
        ));

        $this->view->render();
    }
}