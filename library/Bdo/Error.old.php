<?php
/**
 *
 * @author laurent
 *
 */

class Bdo_Error extends Bdo_Controller
{
    public function __construct($codeError=404)
    {
        parent::__construct();
        $this->view->a_phtmlFile = array();
        $this->view->addPhtmlFile('error','BODY');

        $this->view->set_var(array(
                'errorCode' => $codeError,
        ));

        $this->view->layout = '';
        $this->view->render();
    }
}
