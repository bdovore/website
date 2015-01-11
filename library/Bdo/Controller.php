<?php

/**
 * 
 * @author laurent
 *
 */
class Bdo_Controller
{
    public function __construct ()
    {
        $this->view = new Bdo_View();
        
        $this->view->addPhtmlFile(Bdo_Cfg::getVar('controller') . DS . Bdo_Cfg::getVar('action'), 'BODY');
        $this->view->addPhtmlFile('loginbarre', 'LOGINBARRE');
        
       /* if (Bdo_Cfg::user()->minAccessLevel(1)) {
            $this->view->addPhtmlFile("menuadmin", 'MENUBARRE');
        }*/
    }

    public function loadModel ($model)
    {
        require_once BDO_DIR . 'mvc' . DS . 'models' . DS . $model . '.php';
        $this->$model = new $model();
    }
}

