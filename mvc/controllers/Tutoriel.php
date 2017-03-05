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
        $this->loadModel('Statistique');
        $this->Statistique->showTables();
        $this->view->addPhtmlFile(__CLASS__ . DS . 'bdovore-tuto_' . $page, 'BODY', true);
        if ($page!=1) {
            $this->view->layout = "ajax";
        }
        $this->view->set_var(array(
            "PAGETITLE" => "Bdovore : le tutoriel",
            'NBALB' => $this->Statistique->a_tableStatus['bd_tome']->Rows,
            'NBSERIE' => $this->Statistique->a_tableStatus['bd_serie']->Rows
            ));
        $this->view->render();
    }

}

