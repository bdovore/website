<?php

class Auth extends Bdo_Controller
{

    /**
     */
    public function Provider ($openid_identifier=null)
    {
        // Retire the old OpenID 2.0 callback, which trusted profile attributes.
        header('Location: ' . BDO_URL . 'compte/connexion', true, 303);
    }

    public function Google ()
    {
        header('Location: ' . BDO_URL . 'compte/connexion', true, 303);
    }

    public function Twitter ()
    {
        header('Location: ' . BDO_URL . 'compte/connexion', true, 303);
    }
    
    public function Gettoken () {
        $username = isset($_POST['user_login']) && is_string($_POST['user_login']) ? $_POST['user_login'] : '';
        $password = isset($_POST['user_password']) && is_string($_POST['user_password']) ? $_POST['user_password'] : '';
        if (BDO_MOBILE_API_ENABLED) {
            require_once dirname(__DIR__) . '/models/MobileAccount.php';
            try {
                $security = new MobileAccount();
                $security->throttle('login-ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown', 60, 900);
                $security->throttle('login-user', mb_strtolower($username, 'UTF-8'), 15, 900);
            } catch (MobileAccountException $e) {
                http_response_code($e->status);
                header('Content-Type: application/json; charset=UTF-8');
                header('Cache-Control: no-store');
                echo json_encode(array('Token' => null, 'Timestamp' => null, 'Error' => $e->getMessage()));
                return;
            }
        }
        $this->loadModel("User");
        $pseudo = isset($_POST['user_pseudo']) && is_string($_POST['user_pseudo']) ? $_POST['user_pseudo'] : '';
        $token = $this->User->getToken($username,$password, $pseudo);
        
        if ( $token["Error"]) header("HTTP/1.1 401 Unauthorized");
         $this->view->set_var('json', json_encode($token));
         $this->view->layout = "ajax";
        $this->view->render();
    }
}
