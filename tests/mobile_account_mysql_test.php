<?php
/** Creates and drops only its own per-process database. Never loads production config. */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$legacyOnly = in_array('--legacy-only', $argv, true);
$database = 'bdovore_auth_test_' . bin2hex(random_bytes(12));
$createdDatabase = false;
$db = new mysqli(getenv('AUTH_TEST_HOST') ?: 'localhost', getenv('AUTH_TEST_USER') ?: 'root', getenv('AUTH_TEST_PASSWORD') === false ? 'root' : getenv('AUTH_TEST_PASSWORD'), '',
    intval(getenv('AUTH_TEST_PORT') ?: 3306), getenv('AUTH_TEST_SOCKET') ?: '/Applications/MAMP/tmp/mysql/mysql.sock');
$checks = 0; $failures = 0;
function check($condition, $message) { global $checks, $failures; $checks++; if (!$condition) { $failures++; fwrite(STDERR, "ECHEC: $message\n"); } }
function rejects($callback, $status, $message) { try { $callback(); check(false, $message); } catch (MobileAccountException $e) { check($e->status === $status, $message . ' (HTTP ' . $e->status . ')'); } }
define('DEBUG', false); define('DS', DIRECTORY_SEPARATOR); define('BDO_DIR', dirname(__DIR__) . '/'); define('BDO_URL', 'https://www.bdovore.test/'); define('BDO_MOBILE_API_ENABLED', !$legacyOnly);
define('BDO_TD_WIDTH', 100);
require_once BDO_DIR . 'config/mobile.php';
class Bdo_Cfg {
    private static $values = array();
    public static function getVar($key) { return self::$values[$key] ?? null; }
    public static function setVar($key, $value) { self::$values[$key] = $value; }
    public static function schema() { return self::getVar('schema'); }
    public static function user() { return self::getVar('user'); }
}
require_once BDO_DIR . 'inc/mysql.inc.php';
require_once BDO_DIR . 'inc/util.inc.php';
require_once BDO_DIR . 'inc/function.inc.php';
require_once BDO_DIR . 'library/Bdo/Db/Line.php';
require_once BDO_DIR . 'library/Bdo/Db/Select.php';
require_once BDO_DIR . 'mvc/models/User.php';
require_once BDO_DIR . 'mvc/models/MobileAccount.php';
require_once BDO_DIR . 'library/Bdo/View.php';
require_once BDO_DIR . 'library/Bdo/Controller.php';
require_once BDO_DIR . 'mvc/controllers/Auth.php';
require_once BDO_DIR . 'mvc/controllers/Apicompte.php';

try {
    $db->query("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $createdDatabase = true;
    $db->select_db($database); $db->set_charset('utf8mb4');
    $db->query("CREATE TABLE users (
        user_id MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, username VARCHAR(25) NOT NULL UNIQUE, password VARCHAR(60) NOT NULL DEFAULT '', password_v2 VARCHAR(255) NULL,
        email VARCHAR(100) NOT NULL DEFAULT '', level TINYINT UNSIGNED NOT NULL DEFAULT 2,
        last_connect DATETIME NULL, nb_connect INT NOT NULL DEFAULT 0, birthday VARCHAR(10) NOT NULL DEFAULT '', location VARCHAR(20) NOT NULL DEFAULT '', image VARCHAR(15) NOT NULL DEFAULT '',
        OPEN_COLLEC CHAR(1) NOT NULL DEFAULT 'N', MSG_COLLEC TEXT NULL, CARRE_TYPE TINYINT NOT NULL DEFAULT 0, ROW_DISPLAY TINYINT NOT NULL DEFAULT 10, ABT_NEWS ENUM('0','1') NOT NULL DEFAULT '0',
        VAL_ALB DOUBLE NOT NULL DEFAULT 12, VAL_INT DOUBLE NOT NULL DEFAULT 30, VAL_COF DOUBLE NOT NULL DEFAULT 5, VAL_COF_TYPE CHAR(1) NOT NULL DEFAULT '0', ROWSERIE TINYINT NOT NULL DEFAULT 5,
        PREF_EXPORT VARCHAR(30) NOT NULL DEFAULT '', API_TOKEN VARCHAR(64) NULL, DATE_TOKEN DATE NULL, LAST_UPDATE TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, EXPLICIT_CONTENT TINYINT NOT NULL DEFAULT 0
    ) ENGINE=MyISAM");
    $migration = file_get_contents(BDO_DIR . 'sql/2026-11-12-mobile-account.sql');
    // Applying twice must not invalidate any previous data.
    foreach ($legacyOnly ? array() : array(1, 2) as $run) {
        $db->multi_query($migration);
        do { if ($result = $db->store_result()) $result->free(); } while ($db->more_results() && $db->next_result());
    }
    Bdo_Cfg::setVar('connexion', $db);
    // Real legacy model writer and reader, with a fresh schema instead of the website cache.
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
    if ($legacyOnly) {
        $db->query("INSERT INTO users (username,password,email) VALUES ('historique','" . md5('ancien') . "','same@example.test')");
        $token = (new User())->getToken('historique', 'ancien');
        check($token['Error'] === '' && preg_match('/^1-[a-f0-9]+$/D', $token['Token']), 'connexion historique sans tables mobiles');
        $_SERVER = array(); $_POST = array(); $_COOKIE = array(); $_SESSION = array(); $_GET = array('API_TOKEN' => $token['Token']);
        $legacyUser = new User(); $legacyUser->autoLogin(); Bdo_Cfg::setVar('user', $legacyUser);
        check($_SESSION['userConnect']->user_id == 1, 'authentification historique dans le bootstrap sans tables mobiles');
        require_once BDO_DIR . 'mvc/controllers/Getjson.php';
        Bdo_Cfg::setVar('controller', 'Getjson'); Bdo_Cfg::setVar('action', 'Index');
        $_GET['data'] = 'Userpref'; $_GET['compress'] = 0;
        ob_start(); (new GetJSON())->Index(); $preferences = json_decode(ob_get_clean(), true);
        check($preferences === array('OpenCollec' => false, 'ExplicitContent' => false), 'API historique Userpref sans tables mobiles');
        check((new User())->getToken('historique', 'incorrect')['Error'] !== '', 'mauvais mot de passe historique refusé sans tables mobiles');
        check((new User())->getToken('absent', 'incorrect')['Token'] === null, 'utilisateur inconnu sans avertissement ni jeton');
    } else {
    $oldToken = '1-abcdef0123456'; $oldHash = md5($oldToken);
    $db->query("INSERT INTO users (username,password,email,API_TOKEN,DATE_TOKEN,LAST_UPDATE) VALUES ('historique','" . md5('ancien') . "','same@example.test','$oldHash','2020-01-01','2026-01-01 12:00:00')");
    $clock = strtotime('2026-11-12 12:00:00'); $messages = array();
    $service = new MobileAccount($db, function ($email, $subject, $body) use (&$messages) { $messages[] = compact('email', 'subject', 'body'); return true; }, function () use (&$clock) { return $clock; });
    $login = $service->login(array('username' => 'historique', 'password' => 'ancien', 'device_name' => 'test'), 'ip1');
    check(preg_match('/^bdo2_[a-f0-9]{64}$/D', $login['token']) === 1, 'jeton moderne cryptographique');
    check($login['collection_timestamp'] === '2026-01-01 12:00:00', 'timestamp collection conservé');
    check($service->user(1)->password === 'migrated' && password_verify('ancien', $service->user(1)->password_v2), 'migration MD5 sans nouveau mot de passe imposé');
    $legacy = new User();
    check($legacy->verifyToken($oldToken) == 1, 'jeton historique antérieur au backend toujours valide');
    $legacyLogin = (new User())->getToken('historique', 'ancien');
    check(array_keys($legacyLogin) === array('Token', 'Timestamp', 'Error') && $legacyLogin['Error'] === '', 'contrat historique conservé');
    check(preg_match('/^1-[a-f0-9]+$/D', $legacyLogin['Token']) === 1, 'format compatible avec le regex mobile actuel');
    check((new User())->verifyToken($legacyLogin['Token']) == 1, 'jeton historique nouvellement émis accepté');
    check($service->session($login['token'])['user']->user_id == 1, 'connexion historique ne révoque pas la session moderne');
    $second = $service->login(array('username' => 'historique', 'password' => 'ancien'), 'ip2');
    check((new User())->verifyToken($legacyLogin['Token']) == 1, 'connexion moderne ne remplace pas API_TOKEN');
    $context = $service->session($login['token']);
    $service->logout($context['session']);
    rejects(function () use ($service, $login) { $service->session($login['token']); }, 401, 'déconnexion révoque la session concernée');
    check($service->session($second['token'])['user']->user_id == 1, 'autre appareil conservé');
    $context = $service->session($second['token']);
    $profileTimestamp = $service->user(1)->LAST_UPDATE;
    $_SERVER = array('REQUEST_METHOD' => 'GET', 'HTTP_AUTHORIZATION' => 'Bearer ' . $second['token']); $_GET = array(); $_SESSION = array();
    $requestUser = new User(); $requestUser->autoLogin(); Bdo_Cfg::setVar('user', $requestUser);
    Bdo_Cfg::setVar('controller', 'Apicompte'); Bdo_Cfg::setVar('action', 'Me');
    ob_start(); (new Apicompte())->Me(); $payload = json_decode(ob_get_clean(), true);
    check(http_response_code() === 200 && $payload['data']['user_id'] === 1 && !isset($payload['data']['password_v2']), 'contrôleur profil et template JSON réels sous Bearer');
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer invalid';
    ob_start(); (new Apicompte())->Me(); $payload = json_decode(ob_get_clean(), true);
    check(http_response_code() === 401 && $payload['error']['code'] === 'invalid_session', 'Bearer invalide refusé malgré session web valide');
    $_SERVER = array('REQUEST_METHOD' => 'POST'); $_POST = array('user_login' => 'historique', 'user_password' => 'ancien');
    Bdo_Cfg::setVar('controller', 'Auth'); Bdo_Cfg::setVar('action', 'Gettoken'); http_response_code(200);
    ob_start(); (new Auth())->Gettoken(); $payload = json_decode(ob_get_clean(), true);
    check(http_response_code() === 200 && $payload['Error'] === '' && array_keys($payload) === array('Token', 'Timestamp', 'Error'), 'ancien contrôleur Gettoken et vue existante restent compatibles');
    $legacyLogin = $payload;
    $profileTimestamp = $service->user(1)->LAST_UPDATE;
    $updated = $service->updateProfile($context['user'], array('open_collection' => true, 'newsletter' => true, 'location' => 'Paris', 'birthday' => '1980-02-29', 'square_type' => 1));
    check($updated['location'] === 'Paris' && $service->user(1)->OPEN_COLLEC === 'Y', 'profil écrit dans les données du site');
    check($service->user(1)->LAST_UPDATE === $profileTimestamp, 'profil ne désynchronise pas la collection');
    $db->query("UPDATE users SET location='Lyon', OPEN_COLLEC='N', LAST_UPDATE=LAST_UPDATE WHERE user_id=1");
    check($service->profile($service->session($second['token'])['user'])['location'] === 'Lyon', 'modification site relue côté API');
    rejects(function () use ($service) { $service->updateProfile($service->user(1), array('user_id' => 2)); }, 422, 'identité non modifiable');
    rejects(function () use ($service) { $service->updateProfile($service->user(1), array('birthday' => '2025-02-29')); }, 422, 'date impossible refusée');
    rejects(function () use ($service) { $service->updateProfile($service->user(1), array('open_collection' => 'false')); }, 422, 'booléens stricts');
    $ticket = $service->ticket($context['session'], array('type' => 'correction', 'id_edition' => 42));
    parse_str(parse_url($ticket['url'], PHP_URL_QUERY), $ticketQuery);
    check(strpos($ticket['url'], $second['token']) === false, 'aucun jeton de session dans URL web');
    check($service->consumeTicket($ticketQuery['ticket'])['destination'] === 'proposition?id_edition=42', 'ticket ouvre la destination autorisée');
    rejects(function () use ($service, $ticketQuery) { $service->consumeTicket($ticketQuery['ticket']); }, 401, 'ticket à usage unique');
    $expiredTicket = $service->ticket($context['session'], array()); parse_str(parse_url($expiredTicket['url'], PHP_URL_QUERY), $expiredQuery); $clock += 61;
    rejects(function () use ($service, $expiredQuery) { $service->consumeTicket($expiredQuery['ticket']); }, 401, 'ticket expiré après une minute');
    $password = 'une nouvelle phrase de passe';
    $registration = $service->register(array('username' => 'nouveau', 'password' => $password, 'email' => 'same@example.test'), 'ip3');
    $newUser = $service->findUser('nouveau');
    check(intval($newUser->level) === 98 && $newUser->password === 'migrated', 'compte en attente et bcrypt dès inscription');
    rejects(function () use ($service, $password) { $service->login(array('username' => 'nouveau', 'password' => $password), 'ip3'); }, 401, 'connexion impossible avant confirmation');
    preg_match('/[0-9]{8}/', end($messages)['body'], $match); $code = $match[0];
    $stored = $db->query("SELECT code_hash FROM mobile_challenges WHERE id='" . $registration['challenge_id'] . "'")->fetch_object();
    check($stored->code_hash !== $code, 'code stocké sous forme empreinte');
    $confirmed = $service->verifyRegistration(array('challenge_id' => $registration['challenge_id'], 'code' => $code));
    check(intval($confirmed->level) === 2, 'confirmation active le compte');
    rejects(function () use ($service, $registration, $code) { $service->verifyRegistration(array('challenge_id' => $registration['challenge_id'], 'code' => $code)); }, 422, 'code non réutilisable');
    check((new User())->getToken('nouveau', $password)['Error'] === '', 'nouveau compte utilisable dans ancienne app');
    $emailChange = $service->requestEmail($service->user(1), array('email' => 'new@example.test', 'current_password' => 'ancien'));
    preg_match('/[0-9]{8}/', end($messages)['body'], $match);
    check($service->user(1)->email === 'same@example.test', 'ancienne adresse conservée avant confirmation');
    rejects(function () use ($service, $confirmed, $emailChange, $match) { $service->confirmEmail($confirmed, array('challenge_id' => $emailChange['challenge_id'], 'code' => $match[0])); }, 422, 'code e-mail lié à son compte');
    $emailProfile = $service->confirmEmail($service->user(1), array('challenge_id' => $emailChange['challenge_id'], 'code' => $match[0]));
    check($emailProfile['email'] === 'new@example.test', 'nouvelle adresse confirmée');
    $db->query("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (1, 'older-reset-link', '2099-01-01')");
    $reset = $service->forgotPassword(array('username' => 'historique', 'email' => 'new@example.test'), 'ip4');
    preg_match('/[0-9]{8}/', end($messages)['body'], $match);
    $oldWebUser = $service->user(1);
    $service->resetPassword(array('challenge_id' => $reset['challenge_id'], 'code' => $match[0], 'password' => $password));
    rejects(function () use ($service, $second) { $service->session($second['token']); }, 401, 'reset révoque tous les appareils modernes');
    check(!(new User())->verifyToken($legacyLogin['Token']), 'reset révoque jeton historique');
    check(intval($db->query('SELECT COUNT(*) AS total FROM password_reset_tokens WHERE user_id=1')->fetch_object()->total) === 0, 'anciens liens de récupération également invalidés');
    $_SERVER = array(); $_GET = array(); $_POST = array(); $_COOKIE = array(); $_SESSION = array('userConnect' => $oldWebUser);
    (new User())->autoLogin();
    check(empty($_SESSION['userConnect']->user_id), 'session web antérieure au reset refusée');
    $_COOKIE = array('username' => 'historique', 'pass' => 'migrated'); $_SESSION = array();
    (new User())->autoLogin();
    check(empty($_SESSION['userConnect']->user_id), 'cookie sentinelle ne permet pas de connexion');
    $fresh = $service->login(array('username' => 'historique', 'password' => $password), 'ip5');
    $db->query('UPDATE users SET level=99, LAST_UPDATE=LAST_UPDATE WHERE user_id=1');
    rejects(function () use ($service, $fresh) { $service->session($fresh['token']); }, 401, 'blocage vérifié à chaque requête');
    $db->query('UPDATE users SET level=2, LAST_UPDATE=LAST_UPDATE WHERE user_id=1');
    $clock += BDO_MOBILE_IDLE_SECONDS + 1;
    rejects(function () use ($service, $fresh) { $service->session($fresh['token']); }, 401, 'expiration pour inactivité');
    $fresh = $service->login(array('username' => 'historique', 'password' => $password), 'ip6');
    $clock += BDO_MOBILE_ABSOLUTE_SECONDS + 1;
    $db->query("UPDATE mobile_sessions SET last_used_at='" . date('Y-m-d H:i:s', $clock) . "'");
    rejects(function () use ($service, $fresh) { $service->session($fresh['token']); }, 401, 'expiration absolue malgré activité');
    $fake = $service->forgotPassword(array('username' => 'inconnu', 'email' => 'unknown@example.test'), 'ip7');
    check(strlen($fake['challenge_id']) === 32 && $fake['message'] === $reset['message'], 'récupération ne révèle pas existence du compte');
    $service->throttle('test', 'bucket', 1, 60);
    rejects(function () use ($service) { $service->throttle('test', 'bucket', 1, 60); }, 429, 'limitation effective en base');
    $clock += 61; $service->throttle('test', 'bucket', 1, 60); check(true, 'limite réinitialisée après délai');
    $challenge = $service->forgotPassword(array('username' => 'nouveau', 'email' => 'same@example.test'), 'ip8');
    preg_match('/[0-9]{8}/', end($messages)['body'], $match);
    foreach (range(1, 5) as $attempt) rejects(function () use ($service, $challenge) { $service->resetPassword(array('challenge_id' => $challenge['challenge_id'], 'code' => '00000000', 'password' => 'une phrase de passe longue')); }, 422, 'mauvais code refusé');
    rejects(function () use ($service, $challenge, $match) { $service->resetPassword(array('challenge_id' => $challenge['challenge_id'], 'code' => $match[0], 'password' => 'une phrase de passe longue')); }, 422, 'code bloqué après cinq erreurs');
    $clock += 3601;
    $challenge = $service->forgotPassword(array('username' => 'nouveau', 'email' => 'same@example.test'), 'ip9');
    preg_match('/[0-9]{8}/', end($messages)['body'], $match); $clock += 601;
    rejects(function () use ($service, $challenge, $match) { $service->resetPassword(array('challenge_id' => $challenge['challenge_id'], 'code' => $match[0], 'password' => 'une phrase de passe longue')); }, 422, 'code expiré après dix minutes');
    $fresh = $service->login(array('username' => 'nouveau', 'password' => $password), 'ip10');
    $context = $service->session($fresh['token']); $ticket = $service->ticket($context['session'], array()); parse_str(parse_url($ticket['url'], PHP_URL_QUERY), $query);
    $service->logout($context['session']);
    rejects(function () use ($service, $query) { $service->consumeTicket($query['ticket']); }, 401, 'révocation de session invalide les tickets restants');
    $fresh = $service->login(array('username' => 'nouveau', 'password' => $password), 'ip11');
    $db->query('DELETE FROM users WHERE user_id=' . intval($confirmed->user_id));
    rejects(function () use ($service, $fresh) { $service->session($fresh['token']); }, 401, 'suppression du compte invalide ses sessions');
    }
} catch (Throwable $e) { $failures++; fwrite(STDERR, get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n"); }
finally { if ($createdDatabase) $db->query("DROP DATABASE `$database`"); }
echo "$checks contrôles, $failures échec(s).\n";
exit($failures ? 1 : 0);
