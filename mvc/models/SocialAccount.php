<?php

require_once __DIR__ . '/SocialOidc.php';

class SocialAccount
{
    private $db;
    private $oidc;
    private $clock;
    public function __construct($db = null, $oidc = null, $clock = null)
    {
        $this->db = $db ?: Bdo_Cfg::getVar('connexion');
        $this->oidc = $oidc ?: new SocialOidc();
        $this->clock = $clock ?: function () { return time(); };
    }
    private function now() { return call_user_func($this->clock); }
    private function execute($sql, $params = array())
    {
        $statement = $this->db->prepare($sql);
        if ($params) $statement->bind_param(str_repeat('s', count($params)), ...$params);
        $statement->execute(); return $statement;
    }
    private function row($sql, $params)
    {
        $statement = $this->execute($sql, $params); $result = $statement->get_result()->fetch_object(); $statement->close(); return $result;
    }
    private function change($sql, $params)
    {
        $statement = $this->execute($sql, $params); $count = $statement->affected_rows; $statement->close(); return $count;
    }
    public static function cookieName($state) { return '__Secure-bdo_social_' . substr(hash('sha256', $state), 0, 20); }
    public function begin($provider, $intent = 'login')
    {
        if (!isset(SocialOidc::providers()[$provider])) throw new MobileAccountException('Ce mode de connexion est indisponible.', 503, 'provider_unavailable');
        $state = bin2hex(random_bytes(32)); $cookie = bin2hex(random_bytes(32)); $nonce = bin2hex(random_bytes(32)); $verifier = bin2hex(random_bytes(32));
        if (!in_array($intent, array('login', 'manage'), true)) throw new MobileAccountException('Demande de connexion invalide.');
        $this->change('INSERT INTO account_social_flows (state_hash, cookie_hash, provider, intent, nonce, verifier, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
            array(hash('sha256', $state), hash('sha256', $cookie), $provider, $intent, $nonce, $verifier, date('Y-m-d H:i:s', $this->now() + 600)));
        return array('state' => $state, 'cookie' => $cookie, 'url' => $this->oidc->authorizeUrl($provider, $state, $nonce, $verifier));
    }
    public function callback($state, $cookie, $code, $error = null)
    {
        if (!is_string($state) || !preg_match('/^[a-f0-9]{64}$/D', $state) || !is_string($cookie) || !preg_match('/^[a-f0-9]{64}$/D', $cookie)) {
            throw new MobileAccountException('Cette connexion a expiré. Recommencez depuis BDovore.', 400, 'invalid_state');
        }
        $hash = hash('sha256', $state); $now = date('Y-m-d H:i:s', $this->now());
        $changed = $this->change('UPDATE account_social_flows SET consumed_at = ? WHERE state_hash = ? AND cookie_hash = ? AND consumed_at IS NULL AND expires_at > ?', array($now, $hash, hash('sha256', $cookie), $now));
        if (!$changed) throw new MobileAccountException('Cette connexion a expiré. Recommencez depuis BDovore.', 400, 'invalid_state');
        $flow = $this->row('SELECT * FROM account_social_flows WHERE state_hash = ?', array($hash));
        if (!isset(SocialOidc::providers()[$flow->provider])) throw new MobileAccountException('Ce mode de connexion est indisponible.', 503, 'provider_unavailable');
        if ($error !== null) throw new MobileAccountException('La connexion a été annulée. Vous pouvez utiliser votre pseudo et votre mot de passe.', 400, 'authorization_cancelled');
        $claims = $this->oidc->exchange($flow->provider, $code, $flow->nonce, $flow->verifier);
        $this->change('INSERT INTO account_social_identities (provider, subject, email, email_trusted, created_at) VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), email_trusted = IF(VALUES(email) = \'\', email_trusted, VALUES(email_trusted)), email = IF(VALUES(email) = \'\', email, VALUES(email))',
            array($claims['provider'], $claims['subject'], $claims['email'], intval($claims['email_trusted']), $now));
        $identity = $this->identity($this->db->insert_id);
        $identity->intent = $flow->intent;
        return $identity;
    }
    public function identity($id) { return $this->row('SELECT * FROM account_social_identities WHERE id = ?', array($id)); }
    public function accounts($identityId, $includePending = false)
    {
        $statement = $this->execute('SELECT u.user_id, u.username, u.level, u.email FROM users u JOIN account_social_links l ON l.user_id = u.user_id WHERE l.identity_id = ? AND ' . ($includePending ? 'u.level <= 98' : 'u.level < 98') . ' ORDER BY u.username', array($identityId));
        $users = $statement->get_result()->fetch_all(MYSQLI_ASSOC); $statement->close(); return $users;
    }
    public function selected($identityId, $userId)
    {
        $user = $this->row('SELECT u.* FROM users u JOIN account_social_links l ON l.user_id = u.user_id WHERE l.identity_id = ? AND u.user_id = ? AND u.level < 98', array($identityId, $userId));
        if (!$user) throw new MobileAccountException('Ce compte n’est pas accessible avec cette connexion.', 403, 'account_not_linked');
        return $user;
    }
    private function attach($identityId, $userId)
    {
        return $this->change('INSERT INTO account_social_links (identity_id, user_id, created_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE identity_id = VALUES(identity_id)', array($identityId, $userId, date('Y-m-d H:i:s', $this->now())));
    }
    public function link($identityId, $username, $password, $accounts)
    {
        $identity = $this->identity($identityId);
        if (!$identity) throw new MobileAccountException('Connexion expirée.', 401, 'invalid_identity');
        $user = $accounts->authenticatePassword($username, $password);
        if ($this->attach($identityId, $user->user_id)) $accounts->notifySocialLink($user, $identity->provider);
        return $user;
    }
    public function register($identityId, $username, $email, $accounts)
    {
        $identity = $this->identity($identityId);
        if (!$identity) throw new MobileAccountException('Connexion expirée.', 401, 'invalid_identity');
        $existing = $accounts->findUser($username);
        if ($existing) {
            $linked = $this->row('SELECT user_id FROM account_social_links WHERE identity_id = ? AND user_id = ?', array($identityId, $existing->user_id));
            if ($linked && intval($existing->level) === 98 && $existing->password === 'federated' && is_string($email) && strcasecmp($existing->email, $email) === 0) return $existing;
            throw new MobileAccountException('Ce pseudo est déjà utilisé. Associez votre compte existant ou choisissez un autre pseudo.', 409, 'username_unavailable');
        }
        $trusted = intval($identity->email_trusted) === 1 && is_string($email) && strcasecmp($identity->email, trim($email)) === 0;
        $user = $accounts->createFederatedUser($username, $email, $trusted);
        $this->attach($identityId, $user->user_id);
        return $user;
    }
    public function pending($identityId, $userId, $accounts)
    {
        $link = $this->row('SELECT user_id FROM account_social_links WHERE identity_id = ? AND user_id = ?', array($identityId, $userId));
        $user = $link ? $accounts->user($userId) : null;
        if (!$user || intval($user->level) !== 98 || $user->password !== 'federated') throw new MobileAccountException('Inscription invalide.', 403, 'account_not_linked');
        return $user;
    }
    public static function forgetUser($db, $userId)
    {
        // Account deletion remains usable before this optional migration is installed.
        $tables = $db->query("SELECT COUNT(*) AS total FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('account_social_links', 'account_social_identities')")->fetch_object();
        if (intval($tables->total) !== 2) return;
        $service = new self($db);
        $statement = $service->execute('SELECT identity_id FROM account_social_links WHERE user_id = ?', array($userId));
        $identities = $statement->get_result()->fetch_all(MYSQLI_ASSOC); $statement->close();
        $service->change('DELETE FROM account_social_links WHERE user_id = ?', array($userId));
        foreach ($identities as $identity) $service->change('DELETE FROM account_social_identities WHERE id = ? AND NOT EXISTS (SELECT 1 FROM account_social_links WHERE identity_id = ?)', array($identity['identity_id'], $identity['identity_id']));
    }
}
