<?php

require_once dirname(__DIR__, 2) . '/library/Bdo/AccountApiController.php';

class Apiauth extends Bdo_AccountApiController
{
    public function Login() { $this->run('POST', function ($service, $input) { return $service->login($input, $this->ip()); }); }
    public function Register() { $this->run('POST', function ($service, $input) { return $service->register($input, $this->ip()); }); }
    public function Verify() {
        $this->run('POST', function ($service, $input) {
            $service->throttle('verify-ip', $this->ip(), 30, 900);
            $user = $service->verifyRegistration($input);
            // Forum accounts are independent. No clear-text BDovore password is retained.
            ob_start();
            try {
                $model = new User();
                $model->setForumAccount($user->username, bin2hex(random_bytes(24)), $user->email);
            } catch (Throwable $e) { error_log('[mobile-account] forum_unavailable'); }
            finally { ob_end_clean(); }
            return array('message' => 'Compte confirmé. Vous pouvez vous connecter.');
        });
    }
    public function Forgotpassword() { $this->run('POST', function ($service, $input) { return $service->forgotPassword($input, $this->ip()); }); }
    public function Resetpassword() {
        $this->run('POST', function ($service, $input) {
            $service->throttle('verify-ip', $this->ip(), 30, 900);
            $service->resetPassword($input);
            return array('message' => 'Mot de passe modifié. Veuillez vous reconnecter.');
        });
    }
    public function Logout() { $this->run('POST', function ($service, $input, $context) { $service->logout($context['session']); return array('logged_out' => true); }, true); }
    public function Webticket() { $this->run('POST', function ($service, $input, $context) { return $service->ticket($context['session'], $input); }, true); }
    public function Openweb() {
        $this->run('GET', function ($service) {
            $context = $service->consumeTicket($_GET['ticket'] ?? null);
            session_regenerate_id(true);
            $_SESSION['userConnect'] = $context['user'];
            header('Cache-Control: no-store');
            header('Referrer-Policy: no-referrer');
            header('Location: ' . rtrim(BDO_URL, '/') . '/' . $context['destination'], true, 303);
            return array('redirected' => true);
        });
    }
}
