<?php
/*
 * exit("Bdovore.com<br />En maintenance !<br /> Reprise de service dans 1 heure
 * (23h30 heure française)... Enfin j'espère. .<br />.<br />.<br />.<br />.<br
 * />.<br />Bon ! Finalement ce ne sera pas avant 00h30. J'ai pas les yeux en
 * face des trous.<br /> <br />Thanaos.");
 */

require_once ('..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'constante.php');
require_once BDO_DIR . "vendor".DS."autoload.php";
// ----------------------------------------------------
// time Zone
date_default_timezone_set ( 'Europe/Paris' );

// fichiers de fonctions
include_once (BDO_DIR . 'inc' . DS . 'util.inc.php');
include_once (BDO_DIR . 'inc' . DS . 'mysql.inc.php');
include_once (BDO_DIR . 'inc' . DS . 'function.inc.php');
include_once (BDO_DIR . 'inc' . DS . 'function.cle.inc.php');
include_once (BDO_DIR . 'inc' . DS . 'function_design.inc.php');

// tableau des roles
$user_status = array(
        0 => "Aministrateur",
        1 => "Modérateur",
        2 => "Membre",
        5 => "Visiteur",
        98 => "En attente",
        99 => "Désactivé"
);

// str_replace utile uniquement en local
if (defined('BDO_URL_RELATIVE') && strlen(BDO_URL_RELATIVE) > 0)
    $_SERVER['REQUEST_URI'] = str_replace(BDO_URL_RELATIVE, '', $_SERVER['REQUEST_URI']);

//
$a_request_uri = explode('?',$_SERVER['REQUEST_URI']);
$request_uri = $a_request_uri[0];

if (stristr($request_uri, '.php')) {
    $request_url = parse_url($request_uri, PHP_URL_PATH);
    $request_file = basename($request_url);

    if (empty($request_file)) {
        $request_file = 'accueil.php';
        $request_url = DS . $request_file;
    }

    $request_dir = str_replace('/', DS, $request_url);

    define('BDO_DIR_REDIRECT', BDO_DIR . 'accesByExtension' . DS . ((empty($request_file) or ($request_dir == ('index.php'))) ? 'accueil.php' : $request_url));
}
else {
    // si l'url ne contient pas .php on bascule sur le model MVC
    define('BDO_DIR_REDIRECT', '');
    // ltrim enlève le ou les '/' en début de $requete_uri
    // nb: trim() enlèverait aussi à la fin, mais un '/' final *pourrait* être une partie importante de la requête
    $request_uri = ltrim($request_uri,'/');
    /*
     * Réécriture spécifique pour les pages SerieBD : on veut du URL rewriting contenant le titre des séries pour meilleurs indexation google
     */
    // regex pour URLs du type serie-bd-id-...
    if (preg_match('/^serie-bd[-]*(\d+)/', $request_uri, $match)) {
        $request_uri = "seriebd";
        $_GET["id_serie"] = $match[1];
    }
    // regex pour URLs du type auteur-bd-id-...
    if (preg_match('/^auteur-bd[-]*(\d+)/', $request_uri, $match)) {
        $request_uri = "auteurbd";
        $_GET["id_auteur"] = $match[1];
    }
    // regex pour URLs du type collection-edition-id-...
    if (preg_match('/^collection-edition[-]*(\d+)/', $request_uri, $match)) {
        $request_uri = "collectionbd";
        $_GET["id_collection"] = $match[1];
    }
    if ($request_uri) {
        $params = explode('/', $request_uri);
    }
    else {
        $params = array();
    }

    $controller = isset($params[0]) ? ucfirst(strtolower($params[0])) : 'Accueil';

    //TODO ça devrait vraiment être dans .htaccess un truc pareil ...
    if ($controller === "Forum") {
        header("Location: " . BDO_URL_FORUM);
        die();
    } elseif ($controller === "Phone") {
        header("Location: " . BDO_PROTOCOL . "://phone.bdovore.com/");
        die();
    }
    // nb une URL du type http://bdovore.com/admin/ donne $params[1] == ''
    // --> erreur 404 si on ne vérifie pas la longueur de $params[1]
    $action = ( isset($params[1]) && strlen($params[1]) ) ? ucfirst(strtolower($params[1])) : 'Index';

    Bdo_Cfg::setVar('controller', $controller);
    Bdo_Cfg::setVar('action', $action);

    return; //exit conf.inc.php (retourne dans index.php)
}

// dans le cas non-MVC

// demarrage systematique de la session

session_start();

include_once (BDO_DIR . 'inc' . DS . 'db_mysql.php');
include_once (BDO_DIR . 'inc' . DS . 'auth.inc.php');
include_once (BDO_DIR . 'inc' . DS . 'user.inc.php');
include_once (BDO_DIR . 'inc' . DS . 'query.inc.php');

$a_postGetKey_number = array(
        'alb_id',
        'id_edition',
        'id_genre',
        'id_serie',
        'id_tome',
        'id_collection',
        'id_auteur',
        'propid',
        'lev_id',
        'id_del',
        'cote',
        'prix'
);

$a_GLOBALVAR = array(
        '_GET',
        '_POST'
);

foreach ($a_GLOBALVAR as $GLOBALVAR) {
    if (issetNotEmpty(${$GLOBALVAR})) {
        foreach (${$GLOBALVAR} as $key => $val) {
            if (is_string($val) and in_array($key, $a_postGetKey_number)) {
                $corrVal = $val + 0;
            }
            else {
                $corrVal = stripSlUtf8($val);
            }

            ${$GLOBALVAR}[$key] = $corrVal;

            $$key = $corrVal;
        }
    }
    protectAttack($GLOBALVAR);
}

if (DEBUG) {
    include_once (BDO_DIR . 'inc' . DS . 'debug.inc.php');
}

// declaration de la langue
if (isset($_GET['lang']) and ($_GET['lang'] != $_SESSION['ID_LANG']) and in_array($_GET['lang'], array(
        'fr',
        'en'
))) {
    $_SESSION['ID_LANG'] = $_GET['lang'];
}
else if (! isset($_SESSION['ID_LANG']) or empty($_SESSION['ID_LANG'])) {
    $_SESSION['ID_LANG'] = 'fr';
}

// include_once (BDO_DIR . "lang/lang_".$_SESSION['ID_LANG'].".inc.php");

$DB = new DB_Sql();
$DB->connect();
$DB->query("SET NAMES 'utf8'");

authentification();

