<?php

require_once dirname(__DIR__) . '/models/SocialAccount.php';

class Authsocial extends Bdo_Controller
{
    private function service() { return new SocialAccount(); }
    private function run($callback)
    {
        Bdo_Cfg::setVar('debug', false);
        header('Cache-Control: no-store'); header('Referrer-Policy: no-referrer');
        try {
            if (!SocialOidc::providers()) throw new MobileAccountException('Ce mode de connexion n’est pas encore disponible. Vous pouvez utiliser votre pseudo ou votre e-mail.', 503, 'provider_unavailable');
            call_user_func($callback);
        } catch (MobileAccountException $e) { http_response_code($e->status); $this->page($e->getMessage()); }
        catch (Throwable $e) { error_log('[social-account] internal_error ' . get_class($e)); http_response_code(503); $this->page('La connexion est momentanément indisponible. Réessayez plus tard.'); }
    }
    private function guard()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !adminValidateCsrf('account-auth', postVal('csrf_token', ''))) throw new MobileAccountException('Formulaire expiré. Rechargez cette page.', 403, 'invalid_form');
    }
    private function context()
    {
        $pending = $_SESSION['social_identity'] ?? null;
        if (!$pending || ($pending['expires_at'] ?? 0) <= time()) throw new MobileAccountException('Votre connexion Google ou Apple a expiré. Recommencez.', 401, 'invalid_identity');
        $identity = $this->service()->identity($pending['id']);
        if (!$identity) throw new MobileAccountException('Connexion expirée.', 401, 'invalid_identity');
        return $identity;
    }
    private function redirect($path)
    {
        header('Location: ' . rtrim(BDO_URL, '/') . '/' . $path, true, 303);
    }
    private function login($user)
    {
        session_regenerate_id(true);
        $_SESSION['userConnect'] = $user;
        $_SESSION['account_authenticated_at'] = time();
        $model = new User(); $model->set_data($user); $model->addNbConnect();
        $this->redirect('compte');
    }
    private function page($error = '')
    {
        $identity = null; $accounts = array();
        try {
            if (SocialOidc::providers() && !empty($_SESSION['social_identity']) && $_SESSION['social_identity']['expires_at'] > time()) {
                $identity = $this->service()->identity($_SESSION['social_identity']['id']);
                if ($identity) $accounts = $this->service()->accounts($identity->id, true);
            }
        } catch (Throwable $e) { $identity = null; }
        $this->view->a_phtmlFile = array('BODY' => 'authsocial/continue');
        $this->view->layout = 'account';
        $this->view->set_var(array('PAGETITLE' => 'Votre compte BDovore', 'social_error' => $error, 'social_identity' => $identity, 'social_accounts' => $accounts,
            'social_pending_registration' => $_SESSION['social_registration'] ?? null));
        $this->view->render();
    }
    public function Start()
    {
        $this->run(function () {
            $this->guard();
            (new MobileAccount())->throttle('social-start-ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown', 30, 900);
            $provider = postVal('provider', '');
            if (!is_string($provider)) throw new MobileAccountException('Service invalide.');
            $flow = $this->service()->begin($provider, postVal('intent', 'login'));
            setcookie(SocialAccount::cookieName($flow['state']), $flow['cookie'], array('expires' => time() + 600, 'path' => rtrim(parse_url(BDO_URL, PHP_URL_PATH) ?: '/', '/') . '/authsocial', 'secure' => true, 'httponly' => true, 'samesite' => 'None'));
            header('Location: ' . $flow['url'], true, 303);
        });
    }
    public function Callback()
    {
        $this->run(function () {
            if (intval($_SERVER['CONTENT_LENGTH'] ?? 0) > 16384) throw new MobileAccountException('Réponse invalide.', 413, 'invalid_oauth_response');
            // Never combine query and POST parameters: each response has one source.
            $input = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? $_POST : $_GET;
            $state = $input['state'] ?? '';
            $cookieName = is_string($state) ? SocialAccount::cookieName($state) : '';
            $identity = $this->service()->callback($state, $_COOKIE[$cookieName] ?? '', $input['code'] ?? '', $input['error'] ?? null);
            setcookie($cookieName, '', array('expires' => time() - 3600, 'path' => rtrim(parse_url(BDO_URL, PHP_URL_PATH) ?: '/', '/') . '/authsocial', 'secure' => true, 'httponly' => true, 'samesite' => 'None'));
            session_regenerate_id(true);
            unset($_SESSION['social_registration']);
            $_SESSION['social_identity'] = array('id' => intval($identity->id), 'expires_at' => time() + 600);
            $accounts = $this->service()->accounts($identity->id);
            if (count($accounts) === 1 && $identity->intent === 'login') $this->login($this->service()->selected($identity->id, $accounts[0]['user_id']));
            else $this->redirect('authsocial/continue');
        });
    }
    public function Continue() { $this->run(function () { $this->context(); $this->page(); }); }
    public function Choose()
    {
        $this->run(function () {
            $this->guard(); $identity = $this->context();
            $id = filter_var(postVal('user_id', ''), FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
            if (!$id) throw new MobileAccountException('Compte invalide.');
            $this->login($this->service()->selected($identity->id, $id));
        });
    }
    public function Link()
    {
        $this->run(function () {
            $this->guard(); $identity = $this->context(); $accounts = new MobileAccount();
            $username = postVal('username', ''); $password = postVal('password', '');
            if (!is_string($username) || !is_string($password)) throw new MobileAccountException('Identifiants invalides.');
            $accounts->throttle('login-ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown', 60, 900);
            $accounts->throttle('login-user', mb_strtolower(trim($username), 'UTF-8'), 15, 900);
            $this->login($this->service()->link($identity->id, trim($username), $password, $accounts));
        });
    }
    public function Create()
    {
        $this->run(function () {
            $this->guard(); $identity = $this->context(); $accounts = new MobileAccount();
            $accounts->throttle('register-ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown', 5, 3600);
            $username = postVal('username', ''); $email = postVal('email', '');
            if (!is_string($username) || !is_string($email)) throw new MobileAccountException('Informations invalides.');
            $user = $this->service()->register($identity->id, trim($username), trim($email), $accounts);
            if (intval($user->level) === 98) {
                $_SESSION['social_registration'] = array('user_id' => intval($user->user_id));
                $result = $accounts->federatedRegistrationCode($user);
                $_SESSION['social_registration']['challenge_id'] = $result['challenge_id'];
                $this->redirect('authsocial/continue');
            } else { $this->forum($user); $this->login($user); }
        });
    }
    public function Verify()
    {
        $this->run(function () {
            $this->guard(); $identity = $this->context(); $accounts = new MobileAccount();
            $pending = $_SESSION['social_registration'] ?? array();
            $user = $this->service()->pending($identity->id, $pending['user_id'] ?? 0, $accounts);
            $accounts->throttle('verify-ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown', 30, 900);
            $user = $accounts->verifyRegistration(array('challenge_id' => $pending['challenge_id'] ?? '', 'code' => postVal('code', '')));
            unset($_SESSION['social_registration']); $this->forum($user); $this->login($user);
        });
    }
    private function forum($user)
    {
        ob_start();
        try { (new User())->setForumAccount($user->username, bin2hex(random_bytes(24)), $user->email); }
        catch (Throwable $e) { error_log('[social-account] forum_unavailable'); }
        finally { ob_end_clean(); }
    }
}
