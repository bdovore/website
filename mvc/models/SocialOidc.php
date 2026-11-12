<?php

require_once dirname(__DIR__, 2) . '/config/social.php';
require_once __DIR__ . '/MobileAccount.php';

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

/** Provider endpoints are fixed; no URL or public key comes from an ID token. */
class SocialOidc
{
    private $transport;
    private $clock;
    public function __construct($transport = null, $clock = null)
    {
        $this->transport = $transport ?: array($this, 'http');
        $this->clock = $clock ?: function () { return time(); };
    }
    public static function providers()
    {
        if (!BDO_SOCIAL_ENABLED || !BDO_MOBILE_API_ENABLED || !class_exists(JWT::class) || stripos(BDO_URL, 'https://') !== 0) return array();
        $providers = array();
        if (BDO_GOOGLE_CLIENT_ID !== '' && BDO_GOOGLE_CLIENT_SECRET !== '') $providers['google'] = 'Google';
        $keyFile = BDO_APPLE_PRIVATE_KEY_FILE !== '' ? realpath(BDO_APPLE_PRIVATE_KEY_FILE) : false;
        $publicRoot = realpath(BDO_DIR . 'public');
        if (BDO_APPLE_CLIENT_ID !== '' && BDO_APPLE_TEAM_ID !== '' && BDO_APPLE_KEY_ID !== '' && $keyFile && is_readable($keyFile)
            && (!$publicRoot || strpos($keyFile, $publicRoot . DIRECTORY_SEPARATOR) !== 0)) $providers['apple'] = 'Apple';
        return $providers;
    }
    private function config($provider)
    {
        if ($provider === 'google') return array('client_id' => BDO_GOOGLE_CLIENT_ID, 'authorize' => 'https://accounts.google.com/o/oauth2/v2/auth', 'token' => 'https://oauth2.googleapis.com/token', 'keys' => 'https://www.googleapis.com/oauth2/v3/certs', 'issuers' => array('https://accounts.google.com', 'accounts.google.com'));
        if ($provider === 'apple') return array('client_id' => BDO_APPLE_CLIENT_ID, 'authorize' => 'https://appleid.apple.com/auth/authorize', 'token' => 'https://appleid.apple.com/auth/token', 'keys' => 'https://appleid.apple.com/auth/keys', 'issuers' => array('https://appleid.apple.com'));
        throw new MobileAccountException('Service de connexion inconnu.', 400, 'invalid_provider');
    }
    public function authorizeUrl($provider, $state, $nonce, $verifier)
    {
        $config = $this->config($provider);
        $params = array('client_id' => $config['client_id'], 'redirect_uri' => rtrim(BDO_URL, '/') . '/authsocial/callback', 'response_type' => 'code', 'state' => $state, 'nonce' => $nonce);
        if ($provider === 'google') {
            $params += array('scope' => 'openid email', 'prompt' => 'select_account', 'code_challenge_method' => 'S256', 'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='));
        } else { $params += array('scope' => 'email', 'response_mode' => 'form_post'); }
        return $config['authorize'] . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }
    private function appleSecret()
    {
        $now = call_user_func($this->clock);
        $key = file_get_contents(BDO_APPLE_PRIVATE_KEY_FILE);
        return JWT::encode(array('iss' => BDO_APPLE_TEAM_ID, 'iat' => $now, 'exp' => $now + 300, 'aud' => 'https://appleid.apple.com', 'sub' => BDO_APPLE_CLIENT_ID), $key, 'ES256', BDO_APPLE_KEY_ID);
    }
    public function exchange($provider, $code, $nonce, $verifier)
    {
        if (!is_string($code) || $code === '' || strlen($code) > 4096) throw new MobileAccountException('Réponse de connexion invalide.', 400, 'invalid_oauth_response');
        $config = $this->config($provider);
        $params = array('grant_type' => 'authorization_code', 'code' => $code, 'client_id' => $config['client_id'], 'client_secret' => $provider === 'google' ? BDO_GOOGLE_CLIENT_SECRET : $this->appleSecret(), 'redirect_uri' => rtrim(BDO_URL, '/') . '/authsocial/callback');
        if ($provider === 'google') $params['code_verifier'] = $verifier;
        $tokens = call_user_func($this->transport, $config['token'], $params);
        $keys = call_user_func($this->transport, $config['keys'], null);
        return $this->verify($provider, $tokens['id_token'] ?? '', $nonce, $keys);
    }
    public function verify($provider, $token, $nonce, $keys)
    {
        $config = $this->config($provider);
        try {
            if (!is_string($token) || strlen($token) > 16384) throw new UnexpectedValueException();
            $parts = explode('.', $token);
            $header = count($parts) === 3 ? json_decode(JWT::urlsafeB64Decode($parts[0]), true) : null;
            if (!$header || ($header['alg'] ?? '') !== 'RS256' || !is_string($header['kid'] ?? null)) throw new UnexpectedValueException();
            $rsaKeys = array('keys' => array_values(array_filter($keys['keys'] ?? array(), function ($key) { return ($key['kty'] ?? '') === 'RSA' && ($key['alg'] ?? 'RS256') === 'RS256' && ($key['use'] ?? 'sig') === 'sig'; })));
            $previousTimestamp = JWT::$timestamp; $previousLeeway = JWT::$leeway;
            try {
                JWT::$timestamp = call_user_func($this->clock); JWT::$leeway = 30;
                $claims = JWT::decode($token, JWK::parseKeySet($rsaKeys, 'RS256'));
            } finally { JWT::$timestamp = $previousTimestamp; JWT::$leeway = $previousLeeway; }
            $audiences = is_array($claims->aud ?? null) ? $claims->aud : array($claims->aud ?? null);
            if (!in_array($claims->iss ?? null, $config['issuers'], true) || !in_array($config['client_id'], $audiences, true)
                || (count($audiences) > 1 && ($claims->azp ?? '') !== $config['client_id'])
                || (isset($claims->azp) && $claims->azp !== $config['client_id'])
                || !is_numeric($claims->exp ?? null) || !is_numeric($claims->iat ?? null)
                || !is_string($claims->sub ?? null) || $claims->sub === '' || strlen($claims->sub) > 255 || !preg_match('/^[\x21-\x7e]+$/D', $claims->sub)
                || !is_string($claims->nonce ?? null) || !hash_equals($nonce, $claims->nonce)) throw new UnexpectedValueException();
            $email = is_string($claims->email ?? null) && filter_var($claims->email, FILTER_VALIDATE_EMAIL) ? $claims->email : '';
            $verified = ($claims->email_verified ?? false) === true || ($claims->email_verified ?? false) === 'true';
            $trusted = $verified && ($provider === 'apple' || preg_match('/@gmail\.com$/i', $email) || !empty($claims->hd));
            return array('provider' => $provider, 'subject' => $claims->sub, 'email' => $email, 'email_trusted' => (bool) $trusted);
        } catch (Throwable $e) { throw new MobileAccountException('La connexion n’a pas pu être vérifiée. Recommencez depuis BDovore.', 401, 'invalid_identity'); }
    }
    public function http($url, $params)
    {
        $curl = curl_init($url);
        $body = '';
        curl_setopt_array($curl, array(CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 15, CURLOPT_FOLLOWLOCATION => false, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => array('Accept: application/json'), CURLOPT_WRITEFUNCTION => function ($handle, $chunk) use (&$body) { if (strlen($body) + strlen($chunk) > 65536) return 0; $body .= $chunk; return strlen($chunk); }));
        if ($params !== null) { curl_setopt($curl, CURLOPT_POST, true); curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params, '', '&')); }
        $ok = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_HTTP_CODE); curl_close($curl);
        $result = json_decode($body, true);
        if ($ok === false || $status !== 200 || !is_array($result)) throw new MobileAccountException('Le service de connexion est momentanément indisponible.', 503, 'provider_unavailable');
        return $result;
    }
}
