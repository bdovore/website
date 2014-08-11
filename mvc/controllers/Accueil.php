<?php

/**
 *
 * @author laurent
 *        
 */
class Accueil extends Bdo_Controller
{

    /**
     */
    public function Index ()
    {
        // pas la peine de faire un $this->loadModel('Actus'). il est fait par
        // defaut dans le Bdo_Controller
        
        // modif Tom 27/092013 : je remets la gestion de l'actu uniquement en page d'acceuil pour gérer du contenu spécifique
        // à droite pour les autres pages
        $this->loadModel('Actus');

        

        $this->view->set_var(array(

                'ACTUAIR' => $this->Actus->actuAir(),

                'LASTAJOUT' => $this->Actus->lastAjout()

        ));

        $this->view->set_var(
                array(
                        'a_lastSorties' => $this->Actus->lastSorties(),
                        'a_lastNews' => $this->Actus->lastNews(5),
                        'a_lastCommentaires' => $this->Actus->lastCommentaires(),
                        'a_futurSorties' => $this->Actus->futurSorties(),
                        'PAGETITLE' => "BDOVORE.com - gestion de collection de BD, actualité BD et forums BD"
                ));
        
        $this->view->addPhtmlFile('news', 'LASTNEWS', true);

        $this->view->render();
    }

}

