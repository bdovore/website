<?php
require_once dirname(__DIR__) . '/inc/auth.inc.php';
$checks = 0; $failures = 0;
function bridgeAssert($value, $message) { global $checks, $failures; $checks++; if (!$value) { $failures++; fwrite(STDERR, "ECHEC: $message\n"); } }
class BridgeFakeDb {
    public $rows = array(); public $queries = array();
    public function query($sql) { $this->queries[] = $sql; }
    public function nf() { return count($this->rows); }
    public function next_record() {}
    public function f($key) { return $this->rows[0][$key]; }
}
$DB = new BridgeFakeDb(); $_SERVER = array();
$_SESSION = array('UserId' => 7, 'UserLevel' => 0); $_COOKIE = array('username' => 'admin', 'pass' => 'migrated');
authentification(); bridgeAssert($_SESSION['UserId'] === null && $_SESSION['UserLevel'] === 5 && !$DB->queries, 'cookie de mot de passe ne suffit pas sur les anciennes pages PHP');
$DB->rows = array(array('user_id' => 7, 'level' => 2, 'username' => 'member', 'password' => 'migrated', 'password_v2' => 'current-hash'));
$_SESSION['userConnect'] = (object) array('user_id' => 7, 'password' => 'migrated', 'password_v2' => 'current-hash');
authentification(); bridgeAssert($_SESSION['UserId'] === 7 && $_SESSION['UserLevel'] === 2, 'session moderne valide utilisable sur les anciennes pages');
$_SESSION['userConnect']->level = 0; authentification(); bridgeAssert(!minAccessLevel(0, false) && $_SESSION['userConnect']->level === 2, 'niveau d’accès actualisé depuis la base');
$_SESSION['userConnect']->password_v2 = 'old-hash'; authentification(); bridgeAssert($_SESSION['UserId'] === null, 'changement de mot de passe révoque aussi le pont historique');
$DB->rows = array(); authentification(); bridgeAssert($_SESSION['UserId'] === null && !minAccessLevel(2, false), 'compte bloqué ou supprimé non authentifié');
echo "$checks contrôles du pont historique, $failures échec(s).\n"; exit($failures ? 1 : 0);
