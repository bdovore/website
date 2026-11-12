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
        $token = $this->User->getToken($username,$password );
        
        if ( $token["Error"]) header("HTTP/1.1 401 Unauthorized");
         $this->view->set_var('json', json_encode($token));
         $this->view->layout = "ajax";
        $this->view->render();
    }
}
