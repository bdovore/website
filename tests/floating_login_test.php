<?php
/** Contrat de présentation du login flottant, sans base ni service externe. */
define('BDO_DIR', dirname(__DIR__) . '/');
define('BDO_URL', 'https://www.bdovore.test/');
define('DS', DIRECTORY_SEPARATOR);
define('BDO_MOBILE_API_ENABLED', false);
define('BDO_SOCIAL_ENABLED', false);
require BDO_DIR . 'inc/util.inc.php';
require BDO_DIR . 'vendor/autoload.php';
class Bdo_Cfg { public static $user; public static function user() { return self::$user; } }
class User { public $error = ''; public $loginNeedsPseudo = false; public $password_v2 = 'fixture'; public static $connected = false; public static function minAccesslevel($level) { return self::$connected; } }
class Bdo_Controller { public $view; }
class FloatingView {
    public $layout; public $a_phtmlFile; public $values = array(); public $rendered = false;
    public function set_var($name, $value = null) { if (is_array($name)) $this->values = $name; else $this->values[$name] = $value; }
    public function render() { $this->rendered = true; }
}
require BDO_DIR . 'mvc/controllers/Compte.php';
$_SESSION = array(); Bdo_Cfg::$user = new User();
$checks = 0; $failures = 0;
function floatingAssert($condition, $message) { global $checks, $failures; $checks++; if (!$condition) { $failures++; fwrite(STDERR, "ECHEC : $message\n"); } }
function floatingResponse($connected, $error, $pseudo) {
    User::$connected = $connected; Bdo_Cfg::$user->error = $error; Bdo_Cfg::$user->loginNeedsPseudo = $pseudo;
    $_SERVER['REQUEST_METHOD'] = 'POST'; $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    $_POST = array('account_login_form' => 'floating', 'submitLoginConnect' => '1');
    $controller = new Compte(); $controller->view = new FloatingView(); $controller->Connexion();
    return array($controller->view, json_decode($controller->view->values['json'], true), http_response_code());
}
list($view, $response, $status) = floatingResponse(true, '', false);
floatingAssert($status === 200 && $response['authenticated'] === true, 'session authentifiée signalée sans redirection');
floatingAssert($view->rendered && $view->layout === 'accountjson' && $view->a_phtmlFile === array('BODY' => 'compte/connexionjson'), 'réponse JSON isolée du layout et du loginbarre');
list($view, $response, $status) = floatingResponse(false, 'Identifiant ou mot de passe invalide', false);
floatingAssert($status === 401 && !$response['authenticated'] && !$response['needs_pseudo'], 'échec de connexion dans le panneau');
list($view, $response, $status) = floatingResponse(false, 'Précisez le pseudo de votre collection.', true);
floatingAssert($status === 409 && $response['needs_pseudo'] && !$response['authenticated'], 'e-mail partagé demande une précision sans authentification');
list($view, $response, $status) = floatingResponse(false, 'Formulaire expiré. Rechargez la page de connexion.', false);
floatingAssert($status === 401 && strpos($response['message'], 'Formulaire expiré') === 0, 'erreur CSRF du bootstrap conservée');
floatingAssert(array_keys($response) === array('authenticated', 'needs_pseudo', 'message'), 'aucun jeton ou détail de compte dans la réponse');
User::$connected = false; $_POST = array(); unset($_SERVER['HTTP_X_REQUESTED_WITH']); $_SERVER['REQUEST_METHOD'] = 'GET';
$controller = new Compte(); $controller->view = new FloatingView(); $controller->Connexion();
floatingAssert($controller->view->layout === 'account' && $controller->view->a_phtmlFile === array('BODY' => 'compte/connexion'), 'page classique accessible sans JavaScript');
floatingAssert($controller->view->values['login_form_action'] === BDO_URL . 'compte/connexion', 'le lien du menu ouvre une connexion sans retour au profil');
foreach (array(array(), array('mobile' => 'T')) as $query) {
    $_GET = $query;
    $controller->view = new FloatingView(); $controller->Index();
    $action = BDO_URL . 'compte/connexion?retour=compte' . ($query ? '&mobile=T' : '');
    floatingAssert($controller->view->rendered && $controller->view->layout === 'account'
        && $controller->view->a_phtmlFile === array('BODY' => 'compte/connexion')
        && $controller->view->values['login_form_action'] === $action, 'le profil sans session ouvre la connexion avec retour au profil');
    $_GET['retour'] = 'compte';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = array('submitLoginConnect' => '1', 'user_login' => 'lecteur@example.test');
    $controller->view = new FloatingView(); $controller->Connexion();
    floatingAssert($controller->view->rendered && $controller->view->values['login_form_action'] === $action
        && $controller->view->values['login_identifier'] === 'lecteur@example.test', 'une nouvelle tentative conserve le retour au profil');
    User::$connected = true;
    $controller->view = new FloatingView(); $controller->Connexion();
    floatingAssert(http_response_code() === 303 && !$controller->view->rendered, 'une connexion réussie rouvre le profil en GET');
    User::$connected = false;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_POST = array();
}
$_GET = array();
ob_start(); require BDO_DIR . 'mvc/views/views_controllers/loginbarre.phtml'; $guest = ob_get_clean();
floatingAssert(strpos($guest, 'Google, Apple ou plusieurs collections') === false && strpos($guest, 'authsocial/start') === false, 'aucune entrée technique ni fournisseur non configuré');
floatingAssert(strpos($guest, 'name="csrf_token"') !== false && strpos($guest, 'autocomplete="current-password"') !== false && strpos($guest, 'href="' . BDO_URL . 'compte/connexion"') !== false, 'CSRF, gestionnaires de mots de passe et repli par lien conservés');
floatingAssert(strpos($guest, 'compte/forgotlogin') !== false, 'pseudo oublié accessible depuis le panneau de connexion');
User::$connected = true; $_SESSION['userConnect'] = (object) array('username' => '<script>"&');
ob_start(); require BDO_DIR . 'mvc/views/views_controllers/loginbarre.phtml'; $member = ob_get_clean();
floatingAssert(strpos($member, '<script>') === false && strpos($member, '&lt;script&gt;') !== false, 'pseudo échappé dans le bouton et le panneau connecté');
floatingAssert(strpos($member, 'submitLoginDisconnect') !== false && strpos($member, 'name="csrf_token"') !== false && strpos($member, 'user_password') === false, 'état connecté avec profil et déconnexion protégée');
floatingAssert(preg_match('/<a class="[^"]*header-profile-link[^\"]*fancybox\.iframe[^\"]*" href="' . preg_quote(BDO_URL, '/') . 'compte">/', $member) === 1, 'profil du site ouvert en iframe Fancybox sans changer la route autonome de l’app');
foreach (array('', 'T') as $mobile) {
    $_GET = $mobile === 'T' ? array('mobile' => 'T') : array();
    $view = (object) array('USERID' => 7, 'UTILISATEUR' => 'Lecteur', 'EMAIL' => 'lecteur@example.test',
        'YESISSELECTED' => '', 'NOISSELECTED' => 'selected', 'URLCOLLEC' => BDO_URL . 'guest?user=7', 'OPTIONCARRE' => '',
        'ADULTYESISSELECTED' => '', 'ADULTNOISSELECTED' => 'selected', 'HASFORUMACCOUNT' => true, 'IS_NEWSLETTER' => '');
    ob_start(); require BDO_DIR . 'mvc/views/views_controllers/compte/index.phtml'; $profile = ob_get_clean();
    preg_match_all('/<form[^>]+action="([^"]+)"/', $profile, $forms);
    floatingAssert(count($forms[1]) === 3 && count(array_filter($forms[1], function ($url) { return strpos($url, 'mobile=T') !== false; })) === ($mobile === 'T' ? 3 : 0),
        $mobile === 'T' ? 'formulaires du navigateur mobile conservent le contexte du site' : 'formulaires autonomes de l’app conservent leurs URL historiques');
}
echo "$checks contrôles du login flottant, $failures échec(s).\n"; exit($failures ? 1 : 0);
