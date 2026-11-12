<?php
// Secure website cookies; mobile Bearer identities are never persisted here.
session_set_cookie_params(array('httponly' => true, 'secure' => stripos(BDO_URL, 'https://') === 0 || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'), 'samesite' => 'Lax', 'path' => '/'));
session_start();

$accountRequest = in_array(strtolower($controller), array('apiauth', 'apicompte', 'auth', 'authsocial', 'compte'), true)
    || isset($_GET['API_TOKEN']) || isset($_GET['ticket']) || isset($_SERVER['HTTP_AUTHORIZATION']) || isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])
    || isset($_POST['user_password']) || isset($_POST['submitLoginConnect']) || isset($_COOKIE['pass']);
Bdo_Cfg::setVar('debug', DEBUG && !$accountRequest);


if (Bdo_Cfg::debug()) {
     Bdo_Debug::execTime("Entree dans le bootstrap");

     Bdo_Debug::saveInfoVar($_SESSION,'_SESSION','_SESSION en entrée');
     Bdo_Debug::saveInfoVar($_POST,'_POST');
     Bdo_Debug::saveInfoVar($_GET,'_GET');
     Bdo_Debug::saveInfoVar($_FILES,'_FILES');
     Bdo_Debug::saveInfoVar($_COOKIE,'_COOKIE');
     Bdo_Debug::saveInfoVar($_SERVER,'_SERVER');
     Bdo_Debug::saveInfoVar($_ENV,'_ENV');
}
// ----------------------------------------------------
// parametrage php
mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");

set_include_path ( '.' . PATH_SEPARATOR . BDO_DIR.'library'.DS . PATH_SEPARATOR . BDO_DIR . PATH_SEPARATOR .
get_include_path () );

// ---------------------------------------------------------------
// declaration de la langue
if (isset($_GET['lang']) and ($_GET['lang'] != $_SESSION['ID_LANG']) and in_array($_GET['lang'], array('_FR'))) {
    $_SESSION['ID_LANG'] = $_GET['lang'];
}
else if (! isset($_SESSION['ID_LANG']) or empty($_SESSION['ID_LANG'])) {
    $_SESSION['ID_LANG'] = '_FR';
}
if (!in_array($_SESSION['ID_LANG'], array('_FR','_EN'))) {
    $_SESSION['ID_LANG'] = '_FR';

}

// ---------------------------------------------------------------
// connexion base
Bdo_Cfg::Db_connect();
if (Bdo_Cfg::debug()) Bdo_Debug::execTime("apres connexion");
// ---------------------------------------------------------------
// ---------------------------------------------------------------
// chargement du schema de base de donnee
$schema = new Bdo_Db_Schema();
Bdo_Cfg::setVar('schema', $schema);
if (Bdo_Cfg::debug()) Bdo_Debug::execTime("apres charg schema");
// ---------------------------------------------------------------
// chargement des acl
/*$acl = new Bdo_Acl();
Bdo_Cfg::setVar('acl',$acl);
Bdo_Debug::execTime("apres charg acl");
 * /
 */
// ---------------------------------------------------------------
// Connexion
// ---------------------------------------------------------------
require_once BDO_DIR . 'mvc' . DS . 'models'. DS . 'User.php';
$user = new User();
$user->autoLogin();
Bdo_Error::add($user->error);

Bdo_Cfg::setVar('user',$user);
Bdo_Cfg::setVar('explicit', isset($user->EXPLICIT_CONTENT) ? $user->EXPLICIT_CONTENT : 0) ;
if (Bdo_Cfg::debug()) Bdo_Debug::execTime("apres charg user");



include_once (BDO_DIR . "language".DS.$_SESSION['ID_LANG'].".inc.php");

// ---------------------------------------------------------------

/*
$a_uri = explode('?',$_SERVER['REQUEST_URI']);
if (CFG_RELATIVE_APPLI and (strpos($a_uri[0],CFG_RELATIVE_APPLI) === 0)) {
    $a_uri[0] = substr($a_uri[0],strlen(CFG_RELATIVE_APPLI));
}

$page_include = $a_uri[0];

// a supprimer pour la securite
if (stristr($page_include,'index.php')) {
    $page_include = '';
}


if (issetNotEmpty($a_uri[1]))
    $baseAriane = $a_uri[0].'?'.$a_uri[1];
else
    $baseAriane = $a_uri[0];
Bdo_Cfg::setVar('baseAriane',$baseAriane);

if (stristr($_SERVER['REQUEST_URI'],'script/fckeditor')) {
    include_once $a_uri[0];
    Bdo_Cfg::quit();
}
*/

if (Bdo_Cfg::debug()) Bdo_Debug::execTime("chargement page");

$fileController = BDO_DIR . 'mvc' . DS . 'controllers' . DS . $controller . '.php';
if (is_file($fileController)) {
    require_once $fileController;

    $o_controller = new $controller();
    if (method_exists($o_controller, $action)) {
        unset($params[0]);
        unset($params[1]);
        call_user_func_array(array(
            $o_controller,
            $action
            ), $params);
    }
    else {
        new Bdo_Error(404);
    }
}
else {
    new Bdo_Error(404);
}

if (Bdo_Cfg::debug()) Bdo_Debug::execTime("sortie du bootstrap");
// ---------------------------------------------------------------
if (Bdo_Cfg::debug()) {
    echo Bdo_Debug::saveInfoVar($_SESSION, '_SESSION', '_SESSION en sortie');
    echo Bdo_Debug::affExecTime();
    echo Bdo_Debug::viewInclude();
    echo Bdo_Debug::viewInfoVar();
    echo Bdo_Debug::bilanQuery();
}
// echo '<pre>';
// print_r(cfg::schema());

// chargement autoload Amazon Creator API
spl_autoload_register(function ($class) {
    $prefix = 'Amazon\\CreatorsAPI\\v1\\';
    $baseDir = BDO_DIR . '/vendors/creatorsapi-php-sdk/src/'; // Chemin vers le dossier src du SDK

    // Vérifie si la classe utilise le namespace du SDK
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; // Ne pas interférer avec d'autres autoloaders
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
