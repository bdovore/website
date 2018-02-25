<?php

class Discovery extends Bdo_Controller
{

    /**
     */
    public function Index ()
    {
         $ID_TOME = getValInteger('ID_TOME', 1);

        $this->loadModel("Tome");
        $this->Tome->set_dataPaste(array(
                "ID_TOME" => $ID_TOME
        ));
        //echo $ID_TOME;
        $this->Tome->load();
        $this->view->set_var(array(
                'tome' => $this->Tome,           
            "PAGETITLE" => "Univers proche de l'album ".$this->Tome->TITRE_TOME
        ));
         $this->view->render();
    }
}
