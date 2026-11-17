<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$database = 'bdovore_social_test_' . bin2hex(random_bytes(12)); $createdDatabase = false;
$db = new mysqli(getenv('AUTH_TEST_HOST') ?: 'localhost', getenv('AUTH_TEST_USER') ?: 'root', getenv('AUTH_TEST_PASSWORD') === false ? 'root' : getenv('AUTH_TEST_PASSWORD'), '', intval(getenv('AUTH_TEST_PORT') ?: 3306), getenv('AUTH_TEST_SOCKET') ?: '/Applications/MAMP/tmp/mysql/mysql.sock');
define('BDO_DIR', dirname(__DIR__) . '/'); define('BDO_URL', 'https://www.bdovore.test/'); define('BDO_MOBILE_API_ENABLED', true); define('BDO_SOCIAL_ENABLED', true);
define('BDO_GOOGLE_CLIENT_ID', 'test-google'); define('BDO_GOOGLE_CLIENT_SECRET', 'test-secret');
define('BDO_APPLE_CLIENT_ID', 'test-apple'); define('BDO_APPLE_TEAM_ID', 'test-team'); define('BDO_APPLE_KEY_ID', 'test-key');
$keyFile = tempnam(sys_get_temp_dir(), 'bdovore-apple-test-'); define('BDO_APPLE_PRIVATE_KEY_FILE', $keyFile);
require_once BDO_DIR . 'config/mobile.php'; require_once BDO_DIR . 'vendor/autoload.php'; require_once BDO_DIR . 'mvc/models/SocialAccount.php';
use Firebase\JWT\JWT;
$checks = 0; $failures = 0;
function socialAssert($value, $message) { global $checks, $failures; $checks++; if (!$value) { $failures++; fwrite(STDERR, "ECHEC: $message\n"); } }
function socialRejects($callback, $code, $message) { try { $callback(); socialAssert(false, $message); } catch (MobileAccountException $e) { socialAssert($e->errorCode === $code, $message . ' (' . $e->errorCode . ')'); } }
function migration($db, $file) { $db->multi_query(file_get_contents($file)); do { if ($result = $db->store_result()) $result->free(); } while ($db->more_results() && $db->next_result()); }
function base64url($data) { return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); }
try {
    $db->query("CREATE DATABASE `$database` CHARACTER SET utf8 COLLATE utf8_unicode_ci"); $createdDatabase = true; $db->select_db($database); $db->set_charset('utf8');
    $db->query("CREATE TABLE users (user_id MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, username VARCHAR(25) UNIQUE NOT NULL, password VARCHAR(60) NOT NULL DEFAULT '', password_v2 VARCHAR(255) NULL, email VARCHAR(40) NOT NULL DEFAULT '', level TINYINT NOT NULL DEFAULT 2,
        nb_connect INT NOT NULL DEFAULT 0, last_connect DATETIME NULL, LAST_UPDATE TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        location VARCHAR(20) NOT NULL DEFAULT '', birthday VARCHAR(10) NOT NULL DEFAULT '', OPEN_COLLEC CHAR(1) NOT NULL DEFAULT 'N', EXPLICIT_CONTENT TINYINT NOT NULL DEFAULT 0, ABT_NEWS TINYINT NOT NULL DEFAULT 0, CARRE_TYPE TINYINT NOT NULL DEFAULT 0,
        API_TOKEN VARCHAR(32) NULL, DATE_TOKEN DATE NULL, KEY email(email)) ENGINE=MyISAM");
    $db->query('CREATE TABLE users_album (user_id MEDIUMINT UNSIGNED NOT NULL, album_id INT NOT NULL) ENGINE=MyISAM');
    migration($db, BDO_DIR . 'sql/2026-11-12-mobile-account.sql');
    migration($db, BDO_DIR . 'sql/2026-11-12-social-login.sql'); migration($db, BDO_DIR . 'sql/2026-11-12-social-login.sql');
    migration($db, BDO_DIR . 'sql/2026-11-17-email-verification.sql');
    socialAssert(intval($db->query("SHOW COLUMNS FROM users LIKE 'email'")->fetch_object()->Type === 'varchar(254)'), 'migration e-mail 254 caractères réapplicable');
    $password = 'une phrase de passe très longue'; $hash = $db->real_escape_string(AccountSecurity::hashPassword($password));
    $db->query("INSERT INTO users (username,password,password_v2,email) VALUES ('collection-one','migrated','$hash','shared@example.test'), ('collection-two','migrated','$hash','shared@example.test'), ('unique-member','migrated','$hash','unique@example.test')");
    $db->query('INSERT INTO users_album VALUES (1,10),(1,11),(2,20)');
    socialAssert(AccountLogin::resolve($db, 'collection-one') === 1, 'pseudo historique reconnu');
    try { AccountLogin::resolve($db, 'unique@example.test'); socialAssert(false, 'e-mail historique non vérifié refusé'); }
    catch (AccountLoginAmbiguous $e) { socialAssert(true, 'e-mail historique non vérifié refusé'); }
    socialAssert(AccountLogin::resolve($db, 'shared@example.test', 'collection-two') === null, 'pseudo précisé ne contourne pas la vérification e-mail');
    $db->query('UPDATE users SET email_verified_at=NOW(), email_verified_address=email, LAST_UPDATE=LAST_UPDATE');
    socialAssert(AccountLogin::resolve($db, 'unique@example.test') === 3, 'e-mail unique reconnu');
    $ambiguousMessages = array();
    foreach (array('shared@example.test', 'absent@example.test') as $identifier) {
        try { AccountLogin::resolve($db, $identifier); socialAssert(false, 'adresse partagée ou inconnue demande un pseudo'); }
        catch (AccountLoginAmbiguous $e) { $ambiguousMessages[] = $e->getMessage(); socialAssert(true, 'adresse partagée ou inconnue demande un pseudo'); }
    }
    socialAssert($ambiguousMessages[0] === $ambiguousMessages[1], 'aucune liste ou existence de comptes révélée');
    socialAssert(AccountLogin::resolve($db, 'shared@example.test', 'collection-two') === 2, 'pseudo désambiguïse une adresse partagée');
    socialAssert(AccountLogin::resolve($db, 'unique@example.test', 'collection-two') === null, 'pseudo doit appartenir à cette adresse');
    $db->query("INSERT INTO users (username,password,password_v2,email) VALUES ('unique@example.test','migrated','$hash','elsewhere@example.test')");
    socialAssert(AccountLogin::resolve($db, 'unique@example.test') === 4, 'pseudo littéral prioritaire sur un e-mail');
    $db->query('UPDATE users SET email_verified_at=NULL, LAST_UPDATE=LAST_UPDATE WHERE user_id=2');
    $db->query("INSERT INTO users (username,email,password,level) VALUES ('unverified-copy','shared@example.test','unverified',2), ('pending-copy','shared@example.test','pending',98)");
    socialAssert(AccountLogin::resolve($db, 'shared@example.test') === 1, 'comptes non vérifiés et en attente ne perturbent pas la résolution du compte vérifié');
    socialAssert(AccountLogin::resolve($db, 'shared@example.test', 'unverified-copy') === null, 'copier une adresse vérifiée ne donne pas un accès par e-mail');
    $db->query('UPDATE users SET email_verified_at=NOW(), LAST_UPDATE=LAST_UPDATE WHERE user_id=2');
    $clock = time(); $messages = array(); $tokens = array();
    $accounts = new MobileAccount($db, function ($email, $subject, $body) use (&$messages) { $messages[] = compact('email','subject','body'); return true; }, function () use (&$clock) { return $clock; });
    socialRejects(function () use ($accounts) { $accounts->authenticatePassword('elsewhere@example.test', 'incorrect'); }, 'pseudo_required', 'échec sur e-mail unique ne révèle pas son existence');
    socialRejects(function () use ($accounts) { $accounts->authenticatePassword('absent@example.test', 'incorrect'); }, 'pseudo_required', 'échec sur e-mail inconnu a la même réponse');
    socialRejects(function () use ($accounts, $password) { $accounts->login(array('username' => 'shared@example.test', 'password' => $password), 'email-ip'); }, 'pseudo_required', 'API demande la précision du pseudo');
    $login = $accounts->login(array('username' => 'shared@example.test', 'pseudo' => 'collection-two', 'password' => $password), 'email-ip');
    socialAssert($login['user']['user_id'] === 2 && $login['user']['username'] === 'collection-two', 'API renvoie le vrai pseudo après connexion e-mail');
    $ec = openssl_pkey_new(array('private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1')); openssl_pkey_export($ec, $ecPrivate); file_put_contents($keyFile, $ecPrivate);
    $rsa = openssl_pkey_new(array('private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048)); openssl_pkey_export($rsa, $rsaPrivate); $details = openssl_pkey_get_details($rsa);
    $keys = array('keys' => array(array('kty' => 'RSA', 'alg' => 'RS256', 'kid' => 'rsa1', 'n' => base64url($details['rsa']['n']), 'e' => base64url($details['rsa']['e']))));
    $oidc = new SocialOidc(function ($url, $params) use (&$tokens, $keys) { return $params === null ? $keys : array('id_token' => $tokens[$params['code']] ?? 'invalid'); }, function () use (&$clock) { return $clock; });
    $social = new SocialAccount($db, $oidc, function () use (&$clock) { return $clock; });
    $authorize = function ($provider, $subject, $email, $intent = 'login') use ($social, &$tokens, $rsaPrivate, &$clock) {
        $flow = $social->begin($provider, $intent); parse_str(parse_url($flow['url'], PHP_URL_QUERY), $params);
        $code = bin2hex(random_bytes(12)); $claims = array('iss' => $provider === 'google' ? 'https://accounts.google.com' : 'https://appleid.apple.com', 'aud' => $provider === 'google' ? 'test-google' : 'test-apple', 'sub' => $subject,
            'nonce' => $params['nonce'], 'iat' => $clock, 'exp' => $clock + 300, 'email' => $email, 'email_verified' => true);
        $tokens[$code] = JWT::encode($claims, $rsaPrivate, 'RS256', 'rsa1');
        return array($flow, $code);
    };
    list($flow, $code) = $authorize('google', 'google-person', 'shared@example.test');
    $row = $db->query("SELECT * FROM account_social_flows WHERE state_hash='" . hash('sha256', $flow['state']) . "'")->fetch_object();
    socialAssert($row->state_hash !== $flow['state'] && $row->cookie_hash !== $flow['cookie'], 'state et secret du navigateur non stockés en clair');
    socialRejects(function () use ($social, $flow, $code) { $social->callback($flow['state'], str_repeat('0',64), $code); }, 'invalid_state', 'retour OAuth lié au navigateur initiateur');
    $identity = $social->callback($flow['state'], $flow['cookie'], $code);
    socialAssert(count($social->accounts($identity->id)) === 0, 'adresse identique ne rattache automatiquement aucun compte');
    socialRejects(function () use ($social, $flow, $code) { $social->callback($flow['state'], $flow['cookie'], $code); }, 'invalid_state', 'retour OAuth à usage unique');
    socialRejects(function () use ($social, $identity, $accounts) { $social->link($identity->id, 'collection-one', 'incorrect', $accounts); }, 'invalid_credentials', 'preuve du compte existant obligatoire');
    $social->link($identity->id, 'collection-one', $password, $accounts); $social->link($identity->id, 'collection-two', $password, $accounts);
    socialAssert(count($social->accounts($identity->id)) === 2, 'même identité Google liée à deux pseudos');
    socialAssert($social->selected($identity->id, 2)->username === 'collection-two', 'choix de collection parmi les comptes liés');
    socialRejects(function () use ($social, $identity) { $social->selected($identity->id, 3); }, 'account_not_linked', 'compte étranger non sélectionnable');
    socialAssert(intval($db->query('SELECT COUNT(*) total FROM users_album')->fetch_object()->total) === 3, 'collections historiques conservées sans fusion');
    list($changedFlow, $changedCode) = $authorize('google', 'google-person', 'changed@gmail.com', 'manage');
    $changed = $social->callback($changedFlow['state'], $changedFlow['cookie'], $changedCode);
    socialAssert($changed->id === $identity->id && $changed->intent === 'manage' && count($social->accounts($changed->id)) === 2, 'identifiant stable malgré changement e-mail et gestion explicite des associations');
    socialAssert($accounts->user(1)->email === 'shared@example.test', 'changement Google ne remplace pas e-mail BDovore');
    list($newFlow, $newCode) = $authorize('google', 'another-google-person', 'changed@gmail.com');
    $other = $social->callback($newFlow['state'], $newFlow['cookie'], $newCode);
    socialAssert($other->id !== $identity->id && count($social->accounts($other->id)) === 0, 'même e-mail et subject différent restent deux identités');
    socialRejects(function () use ($social, $other, $accounts) { $social->register($other->id, 'collection-one', 'changed@gmail.com', $accounts); }, 'username_unavailable', 'pseudo existant jamais remplacé par inscription sociale');
    $newUser = $social->register($other->id, 'new-collection', 'changed@gmail.com', $accounts);
    socialAssert(AccountSecurity::emailVerified($newUser), 'adresse de création sociale vérifiée par le fournisseur');
    socialAssert(intval($newUser->level) === 2 && empty($newUser->password_v2) && $newUser->password === 'federated', 'création sociale avec pseudo, sans mot de passe connu');
    socialRejects(function () use ($accounts) { $accounts->authenticatePassword('new-collection', 'federated'); }, 'invalid_credentials', 'sentinelle sociale ne permet pas de connexion');
    $accounts->initialPassword($newUser, array('password' => $password));
    socialAssert($accounts->authenticatePassword($newUser->username, $password)->user_id == $newUser->user_id, 'mot de passe définissable pour app actuelle');
    socialAssert($social->selected($other->id, $newUser->user_id)->user_id == $newUser->user_id, 'premier mot de passe conserve la connexion sociale');
    $reset = $accounts->forgotPassword(array('username' => 'collection-one', 'email' => 'shared@example.test'), 'social-reset-ip'); preg_match('/[0-9]{8}/', end($messages)['body'], $match);
    $accounts->resetPassword(array('challenge_id' => $reset['challenge_id'], 'code' => $match[0], 'password' => $password));
    socialRejects(function () use ($social, $identity) { $social->selected($identity->id, 1); }, 'account_not_linked', 'récupération supprime une association potentiellement malveillante');
    socialAssert($social->selected($identity->id, 2)->user_id == 2, 'récupération ne dissocie pas les autres collections');
    $social->link($identity->id, 'collection-one', $password, $accounts);
    list($pendingFlow, $pendingCode) = $authorize('google', 'third-party-person', 'contact@example.test');
    $pendingIdentity = $social->callback($pendingFlow['state'], $pendingFlow['cookie'], $pendingCode);
    $pendingUser = $social->register($pendingIdentity->id, 'pending-collection', 'contact@example.test', $accounts);
    socialAssert(intval($pendingUser->level) === 98, 'adresse Google tierce exige un code de contact');
    socialAssert(!AccountSecurity::emailVerified($pendingUser), 'adresse sociale non fiable jamais marquée vérifiée');
    socialRejects(function () use ($social, $pendingIdentity, $pendingUser) { $social->selected($pendingIdentity->id, $pendingUser->user_id); }, 'account_not_linked', 'inscription en attente non authentifiable');
    $challenge = $accounts->federatedRegistrationCode($social->pending($pendingIdentity->id, $pendingUser->user_id, $accounts)); preg_match('/[0-9]{8}/', end($messages)['body'], $match);
    $accounts->verifyRegistration(array('challenge_id' => $challenge['challenge_id'], 'code' => $match[0]));
    socialAssert($social->selected($pendingIdentity->id, $pendingUser->user_id)->username === 'pending-collection', 'code confirme le nouveau compte social');
    $db->query('UPDATE users SET level=99 WHERE user_id=2');
    socialRejects(function () use ($social, $identity) { $social->selected($identity->id, 2); }, 'account_not_linked', 'compte bloqué refusé même via Google');
    $db->query('UPDATE users SET level=2 WHERE user_id=2');
    list($expiredFlow, $expiredCode) = $authorize('google', 'google-person', 'changed@gmail.com'); $clock += 601;
    socialRejects(function () use ($social, $expiredFlow, $expiredCode) { $social->callback($expiredFlow['state'], $expiredFlow['cookie'], $expiredCode); }, 'invalid_state', 'retour expiré après dix minutes');
    list($cancelFlow, $cancelCode) = $authorize('google', 'google-person', 'changed@gmail.com');
    socialRejects(function () use ($social, $cancelFlow) { $social->callback($cancelFlow['state'], $cancelFlow['cookie'], '', 'access_denied'); }, 'authorization_cancelled', 'annulation propre du fournisseur');
    list($appleFlow, $appleCode) = $authorize('apple', 'apple-person', 'very-long-private-address@privaterelay.appleid.com');
    $apple = $social->callback($appleFlow['state'], $appleFlow['cookie'], $appleCode);
    $appleUser = $social->register($apple->id, 'apple-collection', $apple->email, $accounts);
    socialAssert(intval($appleUser->level) === 2 && strlen($appleUser->email) > 40, 'inscription Apple avec adresse masquée longue');
    SocialAccount::forgetUser($db, 1); socialAssert($social->identity($identity->id) && count($social->accounts($identity->id)) === 1, 'suppression d’une collection conserve les autres associations');
    SocialAccount::forgetUser($db, 2); socialAssert(!$social->identity($identity->id), 'dernière association supprimée : données fournisseur nettoyées');
    socialAssert($social->identity($apple->id) !== null, 'identités des autres membres conservées');
} catch (Throwable $e) { $failures++; fwrite(STDERR, get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n"); }
finally { if ($createdDatabase) $db->query("DROP DATABASE `$database`"); unlink($keyFile); }
echo "$checks contrôles sociaux/MySQL, $failures échec(s).\n"; exit($failures ? 1 : 0);
