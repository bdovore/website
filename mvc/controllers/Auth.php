<?php

class Auth extends Bdo_Controller
{

    /**
     */
    public function Provider ($openid_identifier=null)
    {
        $openid_identifier = getVal('openid_identifier',$openid_identifier);

        if ($openid_identifier == 'twitter') {
            $auth = new Bdo_Auth_Twitter();
        }
        elseif ($openid_identifier == 'facebook') {
            $auth = new Bdo_Auth_Facebook();
        }

        $auth = new Bdo_Auth_Provider($openid_identifier);
    }

    public function Google ()
    {
        $auth = new Bdo_Auth_Google();

        // confirmation de connexion
        if (isset($_GET['code'])) {
            $auth->confirmConnect();
        }

        header("location:" . BDO_URL);
    }

    public function Twitter ()
    {
        $auth = new Bdo_Auth_Twitter();
    }
    
    public function Gettoken () {
        $username = $_POST['user_login'];
        $password = $_POST['user_password'];
        $this->loadModel("User");
        $token = $this->User->getToken($username,$password );
        
        if ( $token["Error"]) header("HTTP/1.1 401 Unauthorized");
         $this->view->set_var('json', json_encode($token));
         $this->view->layout = "ajax";
        $this->view->render();
    }
}
