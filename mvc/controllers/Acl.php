<?php

/**
 *
 * @author laurent
 *
 */
class Acl extends Bdo_Controller
{

    public $activeDiv = 0;

    /**
     */
    public function __construct ()
    {
        Bdo_Security::page();

        parent::__construct();
        $this->acl = Bdo_Cfg::getVar('acl');
        $this->view->addCssFile('css/acl/acl.css');
    }

    public function Index ()
    {
        $ID_ACL_RESOURCE_PRIVILEGE = getVal('ID_ACL_RESOURCE_PRIVILEGE', null);
        $ID_ACL_RESOURCE = getVal('ID_ACL_RESOURCE', null);
        if (! is_null($ID_ACL_RESOURCE)) {
            $this->activeDiv = 2;
        }
        $ID_ACL_ROLE = getVal('ID_ACL_ROLE', null);
        if (! is_null($ID_ACL_ROLE)) {
            $this->activeDiv = 1;
        }

        $a_returnError = array();
        if (isset($_POST['formAction'])) {
            switch ($_POST['formAction']) {
                case "changeLegacy":
                    {
                        $this->activeDiv = 1;
                        $a_returnError = $this->acl->changeLegacy($_POST['ID_ACL_ROLE'], $_POST['ID_ACL_ROLE_PARENT']);
                        break;
                    }
                case "changePrivilege":
                    {
                        $this->activeDiv = 0;
                        $a_returnError = $this->acl->changePrivilege($_POST['ID_ACL_ROLE'], $_POST['ID_ACL_RESOURCE'], $_POST['ID_ACL_RESOURCE_PRIVILEGE']);
                        break;
                    }
                case "addResource":
                    {
                        $this->activeDiv = 2;
                        $a_returnError = $this->acl->addResource($_POST);
                        break;
                    }
                case "updateResource":
                    {
                        $this->activeDiv = 2;
                        $a_returnError = $this->acl->updateResource($_POST);
                        break;
                    }

                case "delResource":
                    {
                        $this->activeDiv = 2;
                        $a_returnError = $this->acl->deleteResource($_POST);
                        break;
                    }
                case "addResourcePrivilege":
                    {
                        $this->activeDiv = 0;
                        $a_returnError = $this->acl->addResourcePrivilege($_POST);
                        break;
                    }
                case "updateResourcePrivilege":
                    {
                        $this->activeDiv = 0;
                        $a_returnError = $this->acl->updateResourcePrivilege($_POST);
                        break;
                    }

                case "delResourcePrivilege":
                    {
                        $this->activeDiv = 0;
                        $a_returnError = $this->acl->delResourcePrivilege($_POST);
                        break;
                    }
                case "addRole":
                    {
                        $this->activeDiv = 1;
                        $a_returnError = $this->acl->addRole($_POST);
                        break;
                    }
                case "updateRole":
                    {
                        $this->activeDiv = 1;
                        $a_returnError = $this->acl->updateRole($_POST);
                        break;
                    }
                case "deleteRole":
                    {
                        $this->activeDiv = 1;
                        $a_returnError = $this->acl->deleteRole($_POST);
                        break;
                    }
            }
        }
        Bdo_Error::add($a_returnError);
        $this->view->set_var('ID_ACL_ROLE', $ID_ACL_ROLE);
        $this->view->set_var('ID_ACL_RESOURCE', $ID_ACL_RESOURCE);
        $this->view->set_var('ID_ACL_RESOURCE_PRIVILEGE', $ID_ACL_RESOURCE_PRIVILEGE);
        $this->view->set_var('a_resource', $a_resource);
        $this->view->set_var("activeDiv", $this->activeDiv);
        $this->view->set_var("acl", $this->acl);
        $this->view->render();
    }
}

