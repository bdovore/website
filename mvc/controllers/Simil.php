<?php

/**
 *
 * @author laurent
 *        
 */
class Simil extends Bdo_Controller
{

    /**
     */
    public function Index ()
    {
        $ID_TOME = getVal('ID_TOME', 1);
        
        $this->loadModel("Tome");
        $this->Tome->set_dataPaste(array(
                "ID_TOME" => $ID_TOME
        ));
		//echo $ID_TOME;
        $this->Tome->load();
        
        $this->view->set_var(array(
                'tome' => $this->Tome
        ));
        
        $a_simil = $this->Tome->simil();
        
        //if (empty($a_simil) and (604800 < (time() - TimestampDate($a_simil[0]->TSMP_TOME_SIMIL))))
        if (empty($a_simil))
        {
            $this->loadModel("Tome_simil");
            $this->Tome_simil->load($this->Tome);
            $a_simil = $this->Tome->simil();
        }
        
        $this->view->set_var(array(					"PAGETITLE" => "Albums proches de ".$this->Tome->TITRE_TOME,
                'a_simil' => $a_simil
        ));
        
         $this->loadModel('Actus');

        

        $this->view->set_var(array(

                'ACTUAIR' => $this->Actus->actuAir(),

                'LASTAJOUT' => $this->Actus->lastAjout()

        ));

        $this->view->render();
    }
}

