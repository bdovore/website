<?php
require_once __DIR__ . '/account_security.php';
function authentification() {
    global $DB;
    // Legacy .php pages inherit only a validated modern session, never a password cookie.
    $_SESSION['UserLevel'] = 5;
    $_SESSION['UserId'] = null;
    $_SESSION['UserName'] = null;
    if (isset($_COOKIE['pass'])) {
        setcookie('pass', '', array('expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax'));
        unset($_COOKIE['pass']);
    }
    $sessionUser = $_SESSION['userConnect'] ?? null;
    $_SESSION['userConnect'] = (object) array('user_id' => null, 'username' => null, 'level' => 5);
    if (!$sessionUser || empty($sessionUser->user_id)) return;
    $DB->query('SELECT user_id, level, username, password, password_v2 FROM users WHERE user_id=' . intval($sessionUser->user_id) . ' AND level<98');
    if ($DB->nf() != 1) return;
    $DB->next_record();
    $current = (object) array('password' => $DB->f('password'), 'password_v2' => $DB->f('password_v2'));
    if (!hash_equals(AccountSecurity::stamp($sessionUser), AccountSecurity::stamp($current))) return;
    $_SESSION['UserId'] = intval($DB->f('user_id'));
    $_SESSION['UserLevel'] = intval($DB->f('level'));
    $_SESSION['UserName'] = $DB->f('username');
    $sessionUser->level = $_SESSION['UserLevel'];
    $sessionUser->username = $_SESSION['UserName'];
    $_SESSION['userConnect'] = $sessionUser;
}
function minAccessLevel($level = 0, $alert = true) {
    if (! isset($_SESSION['userConnect']->level) or ($_SESSION['userConnect']->level > $level)) {
        if ($alert) {
            alertLevel ( $level );
        }
        return false;
    }
    return true;
}
/*
 * function maxAccessLevel($level=0,$alert=true) { if
 * (!isset($_SESSION["UserId"]) or ($_SESSION["UserLevel"] > $level)) { if
 * ($alert) { alertLevel($level); } return false; } return true; }
 */
function alertLevel($level = false) {
    if (! isset ( $_SESSION ["UserId"] )) {
        echo GetMetaTag ( 3, "Vous devez vous enregistrer pour accéder à cette page.", (BDO_URL . "index.php") );
        exit ();
    }
    if ($level and ($_SESSION ["UserLevel"] > $level)) {
        if ($level == 1)
            echo GetMetaTag ( 3, "Vous devez être habilité pour accéder à cette page.", (BDO_URL . "index.php") );
        exit ();
    }
}

?>
