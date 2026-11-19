<?php
// Exercise the real authentication bootstrap, controllers and response templates without a DB.
if (isset($argv[1])) {
    $scenario = $argv[1];
    define('BDO_DIR', dirname(__DIR__) . '/'); define('DS', DIRECTORY_SEPARATOR);
    define('BDO_MOBILE_API_ENABLED', !in_array($scenario, array('disabled', 'bootstrap-legacy-disabled', 'bootstrap-bearer-disabled'), true));
    require_once BDO_DIR . 'config/mobile.php';
    if (str_starts_with($scenario, 'bootstrap-')) {
        class Bdo_Db_Line {
            public static $account;
            public $dbSelect;
            public $level;
            public $API_TOKEN;
            public $DATE_TOKEN;
            public function __construct($table, $data) {}
            public function load($mode, $where) {
                $this->dbSelect = (object) array('nbLineResult' => 0, 'a_dataQuery' => array());
                if ($where === 'WHERE user_id =' . self::$account->user_id) {
                    $this->dbSelect->nbLineResult = 1;
                    $this->dbSelect->a_dataQuery = array(self::$account);
                    $this->set_data(self::$account);
                }
            }
            public function set_data($account) {
                $this->level = $account->level;
                $this->API_TOKEN = $account->API_TOKEN;
                $this->DATE_TOKEN = $account->DATE_TOKEN;
            }
        }
        function Db_Escape_String($value) { return $value; }
        require_once BDO_DIR . 'mvc/models/User.php';
        $token = '7-abcdef0123456';
        $account = (object) array('user_id' => 7, 'level' => 2, 'API_TOKEN' => md5($token), 'DATE_TOKEN' => '2026-01-01');
        $_GET['API_TOKEN'] = $token;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION = array('userConnect' => (object) array('user_id' => 8, 'level' => 2));
        switch ($scenario) {
            case 'bootstrap-legacy-unknown':
                $_GET['API_TOKEN'] = '2147483647-0000000000000';
                break;
            case 'bootstrap-legacy-mismatch':
                $_GET['API_TOKEN'] = '7-0000000000000';
                break;
            case 'bootstrap-legacy-revoked':
                $account->API_TOKEN = null;
                $account->DATE_TOKEN = null;
                break;
            case 'bootstrap-legacy-undated':
                $account->DATE_TOKEN = null;
                break;
            case 'bootstrap-legacy-blocked':
                $account->level = 98;
                break;
            case 'bootstrap-legacy-array':
                $_GET['API_TOKEN'] = array($token);
                break;
            case 'bootstrap-legacy-empty':
                $_GET['API_TOKEN'] = '';
                break;
            case 'bootstrap-bearer-invalid':
                $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer invalid';
                break;
            case 'bootstrap-bearer-disabled':
                $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer bdo2_' . str_repeat('0', 64);
                break;
        }
        Bdo_Db_Line::$account = $account;
        http_response_code(200);
        ob_start();
        register_shutdown_function(function () {
            echo json_encode(array('status' => http_response_code(), 'body' => ob_get_clean()));
        });
        $user = new User();
        $user->autoLogin();
        // A successful bootstrap must leave the request identified as the token's owner.
        echo json_encode(array('user_id' => $_SESSION['userConnect']->user_id));
        exit;
    }
    class Bdo_Cfg {
        private static $values = array('controller' => 'Apiauth', 'action' => 'Login');
        public static function getVar($key) { return self::$values[$key] ?? null; }
        public static function setVar($key, $value) { self::$values[$key] = $value; }
        public static function user() { return new stdClass(); }
    }
    require_once BDO_DIR . 'library/Bdo/View.php';
    require_once BDO_DIR . 'library/Bdo/Controller.php';
    require_once BDO_DIR . 'mvc/controllers/Apiauth.php';
    require_once BDO_DIR . 'mvc/controllers/Apicompte.php';
    $_SERVER['REQUEST_METHOD'] = in_array($argv[1], array('method', 'forgotlogin-method'), true) ? 'GET' : 'POST';
    $_SERVER['CONTENT_TYPE'] = $argv[1] === 'media' ? 'text/plain' : 'application/json';
    if ($argv[1] === 'oversized') $_SERVER['CONTENT_LENGTH'] = 20000;
    if ($argv[1] === 'bad-bearer') $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer invalid';
    ob_start();
    if (in_array($argv[1], array('unauthenticated', 'bad-bearer'), true)) {
        Bdo_Cfg::setVar('controller', 'Apicompte'); Bdo_Cfg::setVar('action', 'Update');
        (new Apicompte())->Update();
    } elseif ($argv[1] === 'forgotlogin-method') {
        Bdo_Cfg::setVar('action', 'Forgotlogin');
        (new Apiauth())->Forgotlogin();
    } else { (new Apiauth())->Login(); }
    $body = ob_get_clean();
    echo json_encode(array('status' => http_response_code(), 'body' => $body));
    exit;
}
$checks = 0; $failures = 0;
foreach (array('disabled' => array(503, 'api_unavailable'), 'method' => array(405, 'method_not_allowed'), 'forgotlogin-method' => array(405, 'method_not_allowed'), 'media' => array(415, 'unsupported_media_type'),
    'json' => array(400, 'invalid_json'), 'oversized' => array(413, 'payload_too_large'), 'unauthenticated' => array(401, 'invalid_session'), 'bad-bearer' => array(401, 'invalid_session'),
    'bootstrap-legacy-valid' => array(200, array('user_id' => 7)), 'bootstrap-legacy-disabled' => array(200, array('user_id' => 7)),
    'bootstrap-legacy-unknown' => array(401, null), 'bootstrap-legacy-mismatch' => array(401, null), 'bootstrap-legacy-revoked' => array(401, null),
    'bootstrap-legacy-undated' => array(401, null), 'bootstrap-legacy-blocked' => array(401, null), 'bootstrap-legacy-array' => array(401, null),
    'bootstrap-legacy-empty' => array(401, null),
    'bootstrap-bearer-invalid' => array(401, 'invalid_session'), 'bootstrap-bearer-disabled' => array(503, 'api_unavailable')) as $scenario => $expected) {
    $pipes = array();
    $process = proc_open(array(PHP_BINARY, '-d', 'xdebug.mode=off', __FILE__, $scenario), array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
    fclose($pipes[0]); $output = stream_get_contents($pipes[1]); $error = stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
    $exit = proc_close($process); $result = json_decode($output, true); $body = json_decode($result['body'] ?? '', true);
    $checks++;
    if ($expected[1] === null) {
        $bodyMatches = ($result['body'] ?? null) === '';
    } elseif (is_array($expected[1])) {
        $bodyMatches = $body === $expected[1];
    } else {
        $bodyMatches = ($body['error']['code'] ?? '') === $expected[1] && array_key_exists('data', $body ?? array());
    }
    if ($exit !== 0 || ($result['status'] ?? null) !== $expected[0] || !$bodyMatches) {
        $failures++; fwrite(STDERR, "ECHEC: $scenario : $output $error\n");
    }
}
echo "$checks contrôles de contrat HTTP/JSON, $failures échec(s).\n";
exit($failures ? 1 : 0);
