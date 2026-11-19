<?php
// Exercise the real controllers, JSON layout and action templates without a DB.
if (isset($argv[1])) {
    define('BDO_DIR', dirname(__DIR__) . '/'); define('DS', DIRECTORY_SEPARATOR);
    define('BDO_MOBILE_API_ENABLED', $argv[1] !== 'disabled');
    require_once BDO_DIR . 'config/mobile.php';
    if ($argv[1] === 'invalid-legacy-token') {
        class Bdo_Db_Line {
            public $dbSelect;
            public function __construct($table, $data) {}
            public function load($mode, $where) {
                $this->dbSelect = (object) array('nbLineResult' => 0);
            }
        }
        function Db_Escape_String($value) { return $value; }
        require_once BDO_DIR . 'mvc/models/User.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['API_TOKEN'] = '2147483647-0000000000000';
        ob_start();
        register_shutdown_function(function () {
            echo json_encode(array('status' => http_response_code(), 'body' => ob_get_clean()));
        });
        (new User())->autoLogin();
        exit(1);
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
    'invalid-legacy-token' => array(401, null)) as $scenario => $expected) {
    $pipes = array();
    $process = proc_open(array(PHP_BINARY, '-d', 'xdebug.mode=off', __FILE__, $scenario), array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
    fclose($pipes[0]); $output = stream_get_contents($pipes[1]); $error = stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
    $exit = proc_close($process); $result = json_decode($output, true); $body = json_decode($result['body'] ?? '', true);
    $checks++;
    $bodyMatches = $expected[1] === null
        ? ($result['body'] ?? null) === ''
        : ($body['error']['code'] ?? '') === $expected[1] && array_key_exists('data', $body ?? array());
    if ($exit !== 0 || ($result['status'] ?? null) !== $expected[0] || !$bodyMatches) {
        $failures++; fwrite(STDERR, "ECHEC: $scenario : $output $error\n");
    }
}
echo "$checks contrôles de contrat HTTP/JSON, $failures échec(s).\n";
exit($failures ? 1 : 0);
