<?php

/** 
 * @author laurent
 * 
 */
class Admin extends Bdo_Controller_Admin
{

    /**
     */
    public function __construct ()
    {
        parent::__construct();
    }

    public function search ($model)
    {
        $this->view->set_var('model', $model);
        $model = ucfirst(strtolower($model));
        
        $this->loadModel($model);
        $dbSearch = $this->$model->search();
        $this->view->set_var('dbSearch', $dbSearch);
        $this->view->addPhtmlFile("admin/search", 'BODY');
        $this->view->render();
    }

    public function search_list ($model)
    {
        $this->view->set_var('model', $model);
        $model = ucfirst(strtolower($model));
        
        $this->loadModel($model);
        $dbSearch = $this->$model->search();
        $this->view->set_var('dbSearch', $dbSearch);
        $this->view->layout = 'ajax';
        $this->view->addPhtmlFile("admin/search_list", 'BODY');
        $this->view->render();
    }
}

