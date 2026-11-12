<?php

require_once dirname(__DIR__, 2) . '/mvc/models/MobileAccount.php';

class Bdo_AccountApiController extends Bdo_Controller
{
    protected function run($method, $callback, $authenticated = false)
    {
        Bdo_Cfg::setVar('debug', false);
        try {
            if (!BDO_MOBILE_API_ENABLED) throw new MobileAccountException('Le service est temporairement indisponible.', 503, 'api_unavailable');
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== $method) {
                header('Allow: ' . $method);
                throw new MobileAccountException('Méthode non autorisée.', 405, 'method_not_allowed');
            }
            $service = new MobileAccount();
            $context = null;
            if ($authenticated) {
                $token = AccountSecurity::bearer($_SERVER);
                if (!$token) throw new MobileAccountException('Connexion requise.', 401, 'invalid_session');
                $context = $service->session($token);
                $service->throttle('account-user', $context['user']->user_id, 120, 60);
            }
            $input = array();
            if ($method === 'POST') {
                if (intval($_SERVER['CONTENT_LENGTH'] ?? 0) > 16384) throw new MobileAccountException('Requête trop volumineuse.', 413, 'payload_too_large');
                if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) throw new MobileAccountException('Corps JSON attendu.', 415, 'unsupported_media_type');
                $raw = file_get_contents('php://input', false, null, 0, 16385);
                if (strlen($raw) > 16384) throw new MobileAccountException('Requête trop volumineuse.', 413, 'payload_too_large');
                $object = json_decode($raw);
                if (!is_object($object) || json_last_error() !== JSON_ERROR_NONE) throw new MobileAccountException('Corps JSON invalide.', 400, 'invalid_json');
                $input = (array) $object;
            }
            $result = call_user_func($callback, $service, $input, $context);
            $this->respond(array('data' => $result, 'error' => null), http_response_code() === 303 ? 303 : 200);
        } catch (MobileAccountException $e) {
            if ($e->status === 429) header('Retry-After: 60');
            $this->respond(array('data' => null, 'error' => array('code' => $e->errorCode, 'message' => $e->getMessage())), $e->status);
        } catch (Throwable $e) {
            // Never expose SQL, credentials, codes or request bodies to clients/logs.
            error_log('[mobile-account] internal_error ' . get_class($e));
            $this->respond(array('data' => null, 'error' => array('code' => 'server_error', 'message' => 'Le service est temporairement indisponible.')), 503);
        }
    }
    protected function respond($payload, $status = 200)
    {
        http_response_code($status);
        header('Cache-Control: no-store');
        header('Referrer-Policy: no-referrer');
        header('X-Content-Type-Options: nosniff');
        // Only render BODY: loginbarre may contain HTML and account information.
        $this->view->a_phtmlFile = array('BODY' => strtolower(Bdo_Cfg::getVar('controller') . '/' . Bdo_Cfg::getVar('action')));
        $this->view->layout = 'accountjson';
        $this->view->set_var('json', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
        $this->view->render();
    }
    protected function ip() { return $_SERVER['REMOTE_ADDR'] ?? 'unknown'; }
}
