<?php
define('BDO_DIR', dirname(__DIR__) . '/'); define('BDO_URL', 'https://www.bdovore.test/');
define('BDO_MOBILE_API_ENABLED', true); define('BDO_SOCIAL_ENABLED', true);
define('BDO_GOOGLE_CLIENT_ID', 'google-client'); define('BDO_GOOGLE_CLIENT_SECRET', 'test-secret');
define('BDO_APPLE_CLIENT_ID', 'apple-client'); define('BDO_APPLE_TEAM_ID', 'test-team'); define('BDO_APPLE_KEY_ID', 'test-key');
$keyFile = tempnam(sys_get_temp_dir(), 'bdovore-apple-test-'); define('BDO_APPLE_PRIVATE_KEY_FILE', $keyFile);
require_once BDO_DIR . 'vendor/autoload.php'; require_once BDO_DIR . 'mvc/models/SocialOidc.php';
use Firebase\JWT\JWT;
$checks = 0; $failures = 0;
function oidcAssert($value, $message) { global $checks, $failures; $checks++; if (!$value) { $failures++; fwrite(STDERR, "ECHEC: $message\n"); } }
function oidcRejects($callback, $message) { try { $callback(); oidcAssert(false, $message); } catch (MobileAccountException $e) { oidcAssert($e->errorCode === 'invalid_identity', $message); } }
function b64url($data) { return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); }
try {
    $rsa = openssl_pkey_new(array('private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048)); openssl_pkey_export($rsa, $private);
    $details = openssl_pkey_get_details($rsa); $keys = array('keys' => array(array('kty' => 'RSA', 'alg' => 'RS256', 'use' => 'sig', 'kid' => 'rsa1', 'n' => b64url($details['rsa']['n']), 'e' => b64url($details['rsa']['e']))));
    $ec = openssl_pkey_new(array('private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1')); openssl_pkey_export($ec, $applePrivate); file_put_contents($keyFile, $applePrivate);
    $now = time(); $nonce = str_repeat('a', 64);
    $claims = array('iss' => 'https://accounts.google.com', 'aud' => 'google-client', 'sub' => 'stable-google-id', 'nonce' => $nonce, 'iat' => $now, 'exp' => $now + 300, 'email' => 'user@gmail.com', 'email_verified' => true);
    $oidc = new SocialOidc(null, function () use ($now) { return $now; });
    $token = JWT::encode($claims, $private, 'RS256', 'rsa1');
    $identity = $oidc->verify('google', $token, $nonce, $keys);
    oidcAssert($identity['subject'] === 'stable-google-id' && $identity['email_trusted'], 'identité Google vérifiée par signature');
    foreach (array('aud' => 'another-client', 'iss' => 'https://evil.test', 'nonce' => 'other-nonce', 'exp' => $now - 60, 'iat' => $now + 120, 'sub' => '', 'azp' => 'another-client') as $field => $value) {
        $wrong = $claims; $wrong[$field] = $value; $wrongToken = JWT::encode($wrong, $private, 'RS256', 'rsa1');
        oidcRejects(function () use ($oidc, $wrongToken, $nonce, $keys) { $oidc->verify('google', $wrongToken, $nonce, $keys); }, 'claim ' . $field . ' incorrect refusé');
    }
    $wrongKey = openssl_pkey_new(array('private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048)); openssl_pkey_export($wrongKey, $wrongPrivate);
    $wrongToken = JWT::encode($claims, $wrongPrivate, 'RS256', 'rsa1');
    oidcRejects(function () use ($oidc, $wrongToken, $nonce, $keys) { $oidc->verify('google', $wrongToken, $nonce, $keys); }, 'signature non approuvée refusée');
    $wrongToken = JWT::encode($claims, str_repeat('secret', 8), 'HS256', 'rsa1');
    oidcRejects(function () use ($oidc, $wrongToken, $nonce, $keys) { $oidc->verify('google', $wrongToken, $nonce, $keys); }, 'confusion HS256/RS256 refusée');
    $wrong = $claims; unset($wrong['exp']); $wrongToken = JWT::encode($wrong, $private, 'RS256', 'rsa1');
    oidcRejects(function () use ($oidc, $wrongToken, $nonce, $keys) { $oidc->verify('google', $wrongToken, $nonce, $keys); }, 'expiration obligatoire');
    $thirdParty = $claims; $thirdParty['email'] = 'user@example.test';
    $identity = $oidc->verify('google', JWT::encode($thirdParty, $private, 'RS256', 'rsa1'), $nonce, $keys);
    oidcAssert(!$identity['email_trusted'], 'adresse tierce Google demande une confirmation de contact');
    $workspace = $thirdParty; $workspace['hd'] = 'example.test';
    oidcAssert($oidc->verify('google', JWT::encode($workspace, $private, 'RS256', 'rsa1'), $nonce, $keys)['email_trusted'], 'adresse Workspace vérifiée acceptée');
    $apple = $claims; $apple['iss'] = 'https://appleid.apple.com'; $apple['aud'] = 'apple-client'; $apple['sub'] = 'stable-apple-id'; $apple['email'] = 'private-address@privaterelay.appleid.com'; $apple['email_verified'] = 'true';
    oidcAssert($oidc->verify('apple', JWT::encode($apple, $private, 'RS256', 'rsa1'), $nonce, $keys)['email_trusted'], 'adresse masquée Apple acceptée');
    $apple['email_verified'] = 'false'; oidcAssert(!$oidc->verify('apple', JWT::encode($apple, $private, 'RS256', 'rsa1'), $nonce, $keys)['email_trusted'], 'adresse Apple non vérifiée non approuvée');
    parse_str(parse_url($oidc->authorizeUrl('google', 'state', $nonce, str_repeat('v', 64)), PHP_URL_QUERY), $googleQuery);
    oidcAssert($googleQuery['code_challenge_method'] === 'S256' && $googleQuery['response_type'] === 'code', 'code flow et PKCE Google');
    parse_str(parse_url($oidc->authorizeUrl('apple', 'state', $nonce, str_repeat('v', 64)), PHP_URL_QUERY), $appleQuery);
    oidcAssert($appleQuery['response_mode'] === 'form_post' && $appleQuery['nonce'] === $nonce, 'retour Apple form_post et nonce');
    $calls = array();
    $exchange = new SocialOidc(function ($url, $params) use (&$calls, $token, $keys) { $calls[] = array($url, $params); return strpos($url, '/token') !== false ? array('id_token' => $token) : $keys; });
    $exchange->exchange('google', 'one-use-code', $nonce, 'verifier');
    oidcAssert($calls[0][1]['code_verifier'] === 'verifier' && $calls[0][0] === 'https://oauth2.googleapis.com/token', 'échange serveur à destination fixe avec PKCE');
    oidcAssert(count(SocialOidc::providers()) === 2, 'fournisseurs configurés disponibles');
} catch (Throwable $e) { $failures++; fwrite(STDERR, get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n"); }
finally { unlink($keyFile); }
echo "$checks contrôles OIDC, $failures échec(s).\n"; exit($failures ? 1 : 0);
