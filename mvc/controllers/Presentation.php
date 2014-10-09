<?php

/**
 *
 * @author laurent
 *        
 */
class Presentation extends Bdo_Controller
{

    /**
     */
    public function Index ()
    {
        //Bdo_Security::page();

        
        // integration de stats dans la page presentation
        $this->loadModel('Statistique');
        $this->Statistique->showTables();
        $this->Statistique->editionAttente();
        $this->Statistique->ajoutCorrection();
        $this->Statistique->visites();
        $this->Statistique->tomeComment();
        $this->Statistique->serieComment();
        
        // affichage du nb de bd dans les collections
        // --------------------------------------
        // Album
        $this->view->set_var(
            array(
                'NBALB' => $this->Statistique->a_tableStatus['bd_tome']->Rows,
                'NBSERIE' => $this->Statistique->a_tableStatus['bd_serie']->Rows,
                'NBCOLLEC' => $this->Statistique->a_tableStatus['users_album']->Rows,
                'NBEDITION' => $this->Statistique->nbEditionAttente,
                "NBAJOUT" => $this->Statistique->nbajout,
                "NBCORRECT" => $this->Statistique->nbcorrect,
                "NBUSER" => $this->Statistique->a_tableStatus['users']->Rows,
                "NBVISITE" => $this->Statistique->nbVisite,
                "NBNOTEALBUM" => $this->Statistique->nbNoteAlbum,
                "NBCOMMENTALBUM" => $this->Statistique->nbCommentAlbum,
                "NBNOTESERIE" => $this->Statistique->nbNoteSerie,
                "NBCOMMENTSERIE" => $this->Statistique->nbCommentSerie,
                "PAGETITLE" => "BDoVORE.com : c'est quoi ?"
            ));
        
        $this->view->render();
    }
}

