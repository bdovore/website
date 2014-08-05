<?php

/**
 * 
 * @author laurent
 *
 */
class Bdo_Controller_Admin
{

    public function __construct ()
    {
        $this->view = new Bdo_View();
        $this->view->layout = 'admin';
        $this->view->addPhtmlFile(Bdo_Cfg::getVar('controller').DS.Bdo_Cfg::getVar('action'),'BODY');
        $this->view->addPhtmlFile('loginbarre','LOGINBARRE');
        $this->view->addPhtmlFile("menuadmin", 'MENUBARRE');
        
    }

    public function loadModel ($model,$options=array())
    {
        require_once BDO_DIR . 'mvc' . DS . 'models'. DS . $model . '.php';
        $this->$model = new $model($options);
    }

}

