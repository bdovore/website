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
        $page = getValInteger('page',1);

        $this->view->addPhtmlFile(__CLASS__ . DS . 'bdovore-tuto_' . $page, 'BODY', true);
        if ($page!=1) {
            $this->view->layout = "ajax";
        }
        $this->view->set_var("PAGETITLE","Bdovore : le tutoriel");
        $this->view->render();
    }

}

