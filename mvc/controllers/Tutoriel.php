<?php

/**
 *
 * @author laurent
 *        
 */
class Tutoriel extends Bdo_Controller
{

    /**
     */
    public function Index ()
    {
        $page = getVal('page',1);
                
        $this->view->addPhtmlFile(__CLASS__ . DS . 'bdovore-tuto_' . $page, 'BODY', true);
        if ($page!=1) {
            $this->view->layout = "ajax";
        }
            
        $this->view->render();
    }

}

