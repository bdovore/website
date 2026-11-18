<?php
/** Remember-me cookie tests. Creates and drops only its own per-process database. */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$database = 'bdovore_remember_test_' . bin2hex(random_bytes(12));
$createdDatabase = false;
$db = new mysqli(getenv('AUTH_TEST_HOST') ?: 'localhost', getenv('AUTH_TEST_USER') ?: 'root', getenv('AUTH_TEST_PASSWORD') === false ? 'root' : getenv('AUTH_TEST_PASSWORD'), '',
    intval(getenv('AUTH_TEST_PORT') ?: 3306), getenv('AUTH_TEST_SOCKET') ?: '/Applications/MAMP/tmp/mysql/mysql.sock');
$checks = 0; $failures = 0;
function check($condition, $message) { global $checks, $failures; $checks++; if (!$condition) { $failures++; fwrite(STDERR, "ECHEC: $message\n"); } }
function rejects($callback, $status, $message) { try { $callback(); check(false, $message); } catch (MobileAccountException $e) { check($e->status === $status, $message . ' (HTTP ' . $e->status . ')'); } }
define('DEBUG', false); define('DS', DIRECTORY_SEPARATOR); define('BDO_DIR', dirname(__DIR__) . '/'); define('BDO_URL', 'https://www.bdovore.test/'); define('BDO_MOBILE_API_ENABLED', true);
define('BDO_TD_WIDTH', 100);
require_once BDO_DIR . 'config/mobile.php';
class BdoQuit extends Exception {}
class Bdo_Cfg {
    private static $values = array();
    public static function getVar($key) { return self::$values[$key] ?? null; }
    public static function setVar($key, $value) { self::$values[$key] = $value; }
    public static function schema() { return self::getVar('schema'); }
    public static function user() { return self::getVar('user'); }
    public static function quit($message = '') { throw new BdoQuit($message); }
}
require_once BDO_DIR . 'inc/mysql.inc.php';
require_once BDO_DIR . 'inc/util.inc.php';
require_once BDO_DIR . 'inc/function.inc.php';
require_once BDO_DIR . 'library/Bdo/Db/Line.php';
require_once BDO_DIR . 'library/Bdo/Db/Select.php';
require_once BDO_DIR . 'mvc/models/User.php';
require_once BDO_DIR . 'mvc/models/MobileAccount.php';

function rowHash($db, $token) { return $db->query("SELECT * FROM mobile_sessions WHERE token_hash='" . hash('sha256', $token) . "'")->fetch_object(); }

try {
    $db->query("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $createdDatabase = true;
    $db->select_db($database); $db->set_charset('utf8mb4');
    $db->query("CREATE TABLE users (
        user_id MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, username VARCHAR(25) NOT NULL UNIQUE, password VARCHAR(60) NOT NULL DEFAULT '', password_v2 VARCHAR(255) NULL,
        email VARCHAR(254) NOT NULL DEFAULT '', level TINYINT UNSIGNED NOT NULL DEFAULT 2,
        last_connect DATETIME NULL, nb_connect INT NOT NULL DEFAULT 0, birthday VARCHAR(10) NOT NULL DEFAULT '', location VARCHAR(20) NOT NULL DEFAULT '', image VARCHAR(15) NOT NULL DEFAULT '',
        OPEN_COLLEC CHAR(1) NOT NULL DEFAULT 'N', MSG_COLLEC TEXT NULL, CARRE_TYPE TINYINT NOT NULL DEFAULT 0, ROW_DISPLAY TINYINT NOT NULL DEFAULT 10, ABT_NEWS ENUM('0','1') NOT NULL DEFAULT '0',
        VAL_ALB DOUBLE NOT NULL DEFAULT 12, VAL_INT DOUBLE NOT NULL DEFAULT 30, VAL_COF DOUBLE NOT NULL DEFAULT 5, VAL_COF_TYPE CHAR(1) NOT NULL DEFAULT '0', ROWSERIE TINYINT NOT NULL DEFAULT 5,
        PREF_EXPORT VARCHAR(30) NOT NULL DEFAULT '', API_TOKEN VARCHAR(64) NULL, DATE_TOKEN DATE NULL, LAST_UPDATE TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, EXPLICIT_CONTENT TINYINT NOT NULL DEFAULT 0
    ) ENGINE=MyISAM");
    foreach (array(BDO_DIR . 'sql/2026-11-12-mobile-account.sql', BDO_DIR . 'sql/2026-11-17-email-verification.sql') as $migrationFile) {
        $db->multi_query(file_get_contents($migrationFile));
        do { if ($result = $db->store_result()) $result->free(); } while ($db->more_results() && $db->next_result());
    }
    Bdo_Cfg::setVar('connexion', $db);
    $schema = new class($db, $database) {
        public $schema; public $dbColumn = array(); public $dbConstraint = array();
        public function __construct($db, $database) {
            $this->schema = $database;
            $columns = $db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database'");
            while ($column = $columns->fetch_object()) {
                $column->TITRE_CHAMP = $column->COLUMN_NAME; $column->EXTRA_CHAMP = '';
                if ($column->DATA_TYPE === 'enum') $column->TAB_CHECK_VALUE = array('0', '1');
                $this->dbColumn[$column->TABLE_NAME][$column->COLUMN_NAME] = $column;
            }
            $keys = $db->query("SELECT k.TABLE_NAME,k.COLUMN_NAME,k.CONSTRAINT_NAME,c.CONSTRAINT_TYPE FROM information_schema.TABLE_CONSTRAINTS c JOIN information_schema.KEY_COLUMN_USAGE k USING (TABLE_NAME,CONSTRAINT_NAME,CONSTRAINT_SCHEMA) WHERE c.CONSTRAINT_SCHEMA='$database'");
            while ($key = $keys->fetch_object()) $this->dbConstraint[$key->TABLE_NAME][$key->CONSTRAINT_NAME][$key->COLUMN_NAME] = $key;
        }
        public function is_table($name) { return isset($this->dbColumn[$name]); }
    };
    Bdo_Cfg::setVar('schema', $schema);
    $db->query("INSERT INTO users (username,password,password_v2,email,level,email_verified_at,email_verified_address) VALUES ('remember-user','migrated','" . AccountSecurity::hashPassword('secret-pass') . "','remember@example.test',2,NOW(),'remember@example.test')");
    $db->query("INSERT INTO users (username,password,password_v2,email,level) VALUES ('pending-user','migrated','" . AccountSecurity::hashPassword('pending-pass') . "','pending@example.test',98)");
    $service = new MobileAccount($db, function () { return true; });
    $user = $service->findUser('remember-user');

    // Emission : le cookie ne contient que la valeur aléatoire, la base son empreinte.
    $token = $service->rememberIssue($user);
    check(is_string($token) && preg_match('/^bdo2_[a-f0-9]{64}$/D', $token), 'format du jeton remember');
    $row = rowHash($db, $token);
    check($row && $row->revoked_at === null && $row->device_name === 'web' && $row->credential_stamp === AccountSecurity::stamp($user), 'empreinte sha256 + stamp stockés, jeton jamais en clair');
    check(strpos((string) $db->query('SELECT token_hash FROM mobile_sessions')->fetch_object()->token_hash, $token) === false, 'la base ne contient pas le jeton en clair');

    // rememberLogin valide et pivote le jeton en conservant l'expiration absolue.
    $result = $service->rememberLogin($token);
    check(intval($result['user']->user_id) === intval($user->user_id), 'rememberLogin renvoie le bon utilisateur');
    check($result['token'] !== $token && preg_match('/^bdo2_[a-f0-9]{64}$/D', $result['token']), 'rotation : nouveau jeton émis');
    check(rowHash($db, $token)->revoked_at !== null, 'ancien jeton révoqué après rotation');
    check(rowHash($db, $result['token'])->expires_at === $row->expires_at, 'la rotation conserve l’expiration absolue');

    // Rejeu de l'ancien jeton : refusé et tous les accès du compte révoqués.
    rejects(function () use ($service, $token) { $service->rememberLogin($token); }, 401, 'rejeu de l’ancien jeton refusé');
    check(rowHash($db, $result['token'])->revoked_at !== null, 'rejeu révoque tous les accès du compte');

    // Changement de mot de passe : le stamp ne correspond plus, tout est révoqué.
    $token2 = $service->rememberIssue($user);
    $db->query("UPDATE users SET password_v2='" . AccountSecurity::hashPassword('nouveau-mot-de-passe') . "', password='migrated', LAST_UPDATE=LAST_UPDATE WHERE user_id=" . intval($user->user_id));
    rejects(function () use ($service, $token2) { $service->rememberLogin($token2); }, 401, 'changement de mot de passe invalide le cookie remember');
    check(rowHash($db, $token2)->revoked_at !== null, 'stamp modifié : révocation de tous les accès');
    $user = $service->findUser('remember-user');

    // Jeton expiré refusé.
    $token3 = $service->rememberIssue($user);
    $db->query("UPDATE mobile_sessions SET expires_at='2000-01-01 00:00:00' WHERE token_hash='" . hash('sha256', $token3) . "'");
    rejects(function () use ($service, $token3) { $service->rememberLogin($token3); }, 401, 'jeton expiré refusé');

    // Déconnexion (revokeToken) puis compte en attente refusé.
    $token4 = $service->rememberIssue($user);
    $service->revokeToken($token4);
    rejects(function () use ($service, $token4) { $service->rememberLogin($token4); }, 401, 'déconnexion révoque le jeton remember');
    $pending = $service->findUser('pending-user');
    rejects(function () use ($service, $pending) { $service->rememberLogin($service->rememberIssue($pending)); }, 401, 'compte en attente (niveau 98) refusé');
    rejects(function () use ($service) { $service->rememberLogin('bdo2_' . str_repeat('z', 64)); }, 401, 'jeton malformé refusé');
    rejects(function () use ($service) { $service->rememberLogin('bdo2_' . bin2hex(random_bytes(32))); }, 401, 'jeton inconnu refusé');

    // Intégration web : autoLogin restaure la session depuis le cookie seul.
    session_save_path(sys_get_temp_dir()); session_id('bdovoreremember' . bin2hex(random_bytes(12))); session_start();
    $_SERVER = array('REQUEST_METHOD' => 'GET', 'REMOTE_ADDR' => '127.0.0.1');
    $_GET = array(); $_POST = array();
    $token5 = $service->rememberIssue($user);
    $_SESSION = array(); $_COOKIE = array(AccountSecurity::REMEMBER_COOKIE => $token5);
    $web = new User(); $web->autoLogin();
    check(!empty($_SESSION['userConnect']->user_id) && intval($_SESSION['userConnect']->user_id) === intval($user->user_id), 'autoLogin restaure la session depuis le cookie remember seul');
    check(intval($web->user_id) === intval($user->user_id), 'objet User peuplé depuis le cookie');
    check(rowHash($db, $token5)->revoked_at !== null, 'autoLogin pivote le jeton (ancien révoqué)');

    // Login web avec la case cochée : un jeton remember est créé.
    $before = intval($db->query('SELECT COUNT(*) c FROM mobile_sessions WHERE user_id=' . intval($user->user_id) . ' AND revoked_at IS NULL')->fetch_object()->c);
    $_SESSION = array(); $_COOKIE = array('PHPSESSID' => session_id());
    $_POST = array('submitLoginConnect' => '1', 'user_login' => 'remember-user', 'user_password' => 'nouveau-mot-de-passe', 'remember_me' => '1', 'csrf_token' => adminCsrfToken('account-auth'));
    (new User())->autoLogin();
    check(intval($_SESSION['userConnect']->user_id) === intval($user->user_id), 'login web avec mot de passe aboutit');
    $after = intval($db->query('SELECT COUNT(*) c FROM mobile_sessions WHERE user_id=' . intval($user->user_id) . ' AND revoked_at IS NULL')->fetch_object()->c);
    check($after === $before + 1, 'case « Rester connecté » crée un jeton remember');

    // Sans la case, aucun jeton supplémentaire.
    $_SESSION = array(); $_POST = array('submitLoginConnect' => '1', 'user_login' => 'remember-user', 'user_password' => 'nouveau-mot-de-passe', 'csrf_token' => adminCsrfToken('account-auth'));
    (new User())->autoLogin();
    $afterNoBox = intval($db->query('SELECT COUNT(*) c FROM mobile_sessions WHERE user_id=' . intval($user->user_id) . ' AND revoked_at IS NULL')->fetch_object()->c);
    check($afterNoBox === $after, 'sans la case, aucun jeton remember créé');

    // Logout : le jeton présenté est révoqué.
    $token6 = $service->rememberIssue($user);
    $_SESSION = array('userConnect' => $service->user($user->user_id)); $_COOKIE = array(AccountSecurity::REMEMBER_COOKIE => $token6);
    try { (new User())->logout(); } catch (BdoQuit $e) {}
    check(rowHash($db, $token6)->revoked_at !== null, 'logout révoque le jeton remember de l’appareil');
    check(!isset($_COOKIE[AccountSecurity::REMEMBER_COOKIE]), 'logout efface le cookie');
} finally {
    if ($createdDatabase) $db->query("DROP DATABASE `$database`");
}
echo "$checks contrôles, $failures échec(s).\n";
exit($failures ? 1 : 0);
