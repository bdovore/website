<?php

/**
 *
 * @author laurent
 *        
 */
class Index extends Bdo_Controller
{

    public function __construct ()
    {
        parent::__construct();
    }
    /**
     */
    public function Index ()
    {
        //Bdo_Security::page();
        // pas la peine de faire un $this->loadModel('Actus'). il est fait par
        // defaut dans le Bdo_Controller

        $this->view->set_var(
                array(
                        'a_lastSorties' => $this->Actus->lastSorties(),
                        'a_lastNews' => $this->Actus->lastNews(5),
                        'a_lastCommentaires' => $this->Actus->lastCommentaires(),
                        'a_futurSorties' => $this->Actus->futurSorties()
                ));
        
        $this->view->addPhtmlFile('news', 'LASTNEWS', true);

        $this->view->render();
    }

}

