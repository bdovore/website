<?php

require_once dirname(__DIR__, 2) . '/inc/account_security.php';

class MobileAccountException extends RuntimeException
{
    public $status;
    public $errorCode;
    public function __construct($message, $status = 422, $errorCode = 'validation_error')
    {
        parent::__construct($message);
        $this->status = $status;
        $this->errorCode = $errorCode;
    }
}

/** Dedicated account service, using the application's existing mysqli connection. */
class MobileAccount
{
    private $db;
    private $sendMail;
    private $clock;
    private $columnLengths;

    public function __construct($db = null, $sendMail = null, $clock = null)
    {
        $this->db = $db ?: Bdo_Cfg::getVar('connexion');
        $this->sendMail = $sendMail ?: function ($email, $subject, $body) {
            require_once dirname(__DIR__, 2) . '/inc/mail.php';
            return bdovoreMail($email, $subject, $body);
        };
        $this->clock = $clock ?: function () { return time(); };
    }

    private function now() { return call_user_func($this->clock); }
    private function date($time = null) { return date('Y-m-d H:i:s', $time === null ? $this->now() : $time); }
    private function execute($sql, $params = array())
    {
        $statement = $this->db->prepare($sql);
        if ($params) $statement->bind_param(str_repeat('s', count($params)), ...$params);
        $statement->execute();
        return $statement;
    }
    private function row($sql, $params = array())
    {
        $statement = $this->execute($sql, $params);
        $row = $statement->get_result()->fetch_object();
        $statement->close();
        return $row;
    }
    private function change($sql, $params = array())
    {
        $statement = $this->execute($sql, $params);
        $affected = $statement->affected_rows;
        $statement->close();
        return $affected;
    }
    public function user($id) { return $this->row('SELECT * FROM users WHERE user_id = ?', array($id)); }
    public function findUser($username) { return $this->row('SELECT * FROM users WHERE username = ?', array($username)); }
    private function denied()
    {
        throw new MobileAccountException('Identifiant ou mot de passe invalide.', 401, 'invalid_credentials');
    }
    private function text($input, $field, $max, $required = false)
    {
        $value = $input[$field] ?? '';
        if (!is_string($value) || ($required && trim($value) === '') || mb_strlen($value, 'UTF-8') > $max || strpos($value, "\0") !== false) {
            throw new MobileAccountException('Champ invalide : ' . $field . '.');
        }
        return $value;
    }
    private function maxLength($field, $fallback)
    {
        if ($this->columnLengths === null) {
            $this->columnLengths = array();
            $result = $this->db->query('SHOW COLUMNS FROM users');
            while ($column = $result->fetch_object()) {
                if (preg_match('/^(?:var)?char\((\d+)\)/i', $column->Type, $match)) $this->columnLengths[$column->Field] = intval($match[1]);
            }
        }
        return $this->columnLengths[$field] ?? $fallback;
    }
    private function email($input)
    {
        $email = trim($this->text($input, 'email', $this->maxLength('email', 40), true));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new MobileAccountException('Adresse e-mail invalide.');
        return $email;
    }
    private function newPassword($input)
    {
        $password = $this->text($input, 'password', 72, true);
        $error = AccountSecurity::passwordError($password);
        if ($error) throw new MobileAccountException($error);
        return $password;
    }

    public function throttle($action, $identity, $limit, $seconds)
    {
        $bucket = hash('sha256', $action . ':' . $identity);
        $now = $this->date();
        $expiry = $this->date($this->now() + $seconds);
        $this->change('INSERT INTO mobile_rate_limits (bucket, attempts, expires_at) VALUES (?, 1, ?)
            ON DUPLICATE KEY UPDATE attempts = IF(expires_at <= ?, 1, attempts + 1), expires_at = IF(expires_at <= ?, VALUES(expires_at), expires_at)', array($bucket, $expiry, $now, $now));
        $row = $this->row('SELECT attempts FROM mobile_rate_limits WHERE bucket = ?', array($bucket));
        if (intval($row->attempts) > $limit) throw new MobileAccountException('Trop de tentatives. Réessayez plus tard.', 429, 'rate_limited');
    }

    /** Also used by the website and the legacy token endpoint. */
    public function authenticatePassword($username, $password)
    {
        $user = $this->findUser($username);
        // Perform an expensive check even for unknown users.
        if (!$user) {
            AccountSecurity::dummyPasswordCheck($password);
            $this->denied();
        }
        if (!AccountSecurity::verifyPassword($password, $user) || intval($user->level) >= 98) $this->denied();
        if (empty($user->password_v2)) {
            // Preserve the existing MD5 migration, without imposing the new-password policy.
            $hash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));
            $this->change("UPDATE users SET password_v2 = ?, password = 'migrated', LAST_UPDATE = LAST_UPDATE WHERE user_id = ? AND COALESCE(password_v2, '') = '' AND password = ?", array($hash, $user->user_id, $user->password));
            $user = $this->user($user->user_id);
            if (!AccountSecurity::verifyPassword($password, $user)) $this->denied();
        }
        return $user;
    }

    public function login($input, $ip)
    {
        $username = trim($this->text($input, 'username', 100, true));
        $password = $this->text($input, 'password', 4096, true);
        $this->throttle('login-ip', $ip, 60, 900);
        $this->throttle('login-user', mb_strtolower($username, 'UTF-8'), 15, 900);
        $user = $this->authenticatePassword($username, $password);
        $token = 'bdo2_' . bin2hex(random_bytes(32));
        $now = $this->date();
        $expiry = $this->date($this->now() + BDO_MOBILE_ABSOLUTE_SECONDS);
        $this->change('INSERT INTO mobile_sessions (user_id, token_hash, credential_stamp, device_name, created_at, last_used_at, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
            array($user->user_id, hash('sha256', $token), AccountSecurity::stamp($user), $this->text($input, 'device_name', 80), $now, $now, $expiry));
        $this->change('UPDATE users SET nb_connect = nb_connect + 1, last_connect = ?, LAST_UPDATE = LAST_UPDATE WHERE user_id = ?', array($now, $user->user_id));
        return array('token' => $token, 'expires_at' => date(DATE_ATOM, strtotime($expiry)), 'user' => $this->profile($user), 'collection_timestamp' => $user->LAST_UPDATE);
    }

    public function session($token)
    {
        if (!is_string($token) || !preg_match('/^bdo2_[a-f0-9]{64}$/D', $token)) throw new MobileAccountException('Session invalide.', 401, 'invalid_session');
        return $this->sessionByHash(hash('sha256', $token));
    }
    private function sessionByHash($hash)
    {
        $session = $this->row('SELECT * FROM mobile_sessions WHERE token_hash = ? AND revoked_at IS NULL AND expires_at > ? AND last_used_at > ?',
            array($hash, $this->date(), $this->date($this->now() - BDO_MOBILE_IDLE_SECONDS)));
        $user = $session ? $this->user($session->user_id) : null;
        if (!$user || intval($user->level) >= 98 || !hash_equals($session->credential_stamp, AccountSecurity::stamp($user))) {
            throw new MobileAccountException('Votre session a expiré. Veuillez vous reconnecter.', 401, 'invalid_session');
        }
        $this->change('UPDATE mobile_sessions SET last_used_at = ? WHERE id = ? AND revoked_at IS NULL', array($this->date(), $session->id));
        return array('session' => $session, 'user' => $user);
    }
    public function logout($session)
    {
        $this->change('UPDATE mobile_sessions SET revoked_at = ? WHERE id = ?', array($this->date(), $session->id));
    }
    public function revokeAll($id)
    {
        $this->change('UPDATE mobile_sessions SET revoked_at = ? WHERE user_id = ? AND revoked_at IS NULL', array($this->date(), $id));
        $this->change('UPDATE mobile_challenges SET consumed_at = ? WHERE user_id = ? AND consumed_at IS NULL', array($this->date(), $id));
        $this->change('UPDATE users SET API_TOKEN = NULL, DATE_TOKEN = NULL, LAST_UPDATE = LAST_UPDATE WHERE user_id = ?', array($id));
        $this->change('DELETE FROM password_reset_tokens WHERE user_id = ?', array($id));
    }
    public function profile($user)
    {
        return array('user_id' => intval($user->user_id), 'public_id' => intval($user->user_id) * 1209 + 951,
            'username' => $user->username, 'email' => $user->email, 'location' => $user->location ?? '', 'birthday' => $user->birthday ?? '',
            'open_collection' => $user->OPEN_COLLEC === 'Y', 'explicit_content' => intval($user->EXPLICIT_CONTENT) === 1,
            'newsletter' => intval($user->ABT_NEWS) === 1, 'square_type' => intval($user->CARRE_TYPE));
    }
    public function updateProfile($user, $input)
    {
        $allowed = array('location', 'birthday', 'open_collection', 'explicit_content', 'newsletter', 'square_type');
        if (array_diff(array_keys($input), $allowed)) throw new MobileAccountException('Champ de profil non modifiable.');
        $columns = array(); $values = array();
        foreach (array('location' => $this->maxLength('location', 20), 'birthday' => 10) as $field => $max) {
            if (!array_key_exists($field, $input)) continue;
            $value = $this->text($input, $field, $max);
            if ($field === 'birthday' && $value !== '') {
                $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
                if (!$date || $date->format('Y-m-d') !== $value || $value > date('Y-m-d', $this->now())) throw new MobileAccountException('Date de naissance invalide (AAAA-MM-JJ).');
            }
            $columns[] = "`$field` = ?"; $values[] = $value;
        }
        foreach (array('open_collection' => 'OPEN_COLLEC', 'explicit_content' => 'EXPLICIT_CONTENT', 'newsletter' => 'ABT_NEWS') as $field => $column) {
            if (!array_key_exists($field, $input)) continue;
            if (!is_bool($input[$field])) throw new MobileAccountException('Préférence invalide : ' . $field . '.');
            $columns[] = "`$column` = ?";
            $values[] = $field === 'open_collection' ? ($input[$field] ? 'Y' : 'N') : intval($input[$field]);
        }
        if (array_key_exists('square_type', $input)) {
            if (!in_array($input['square_type'], array(0, 1), true)) throw new MobileAccountException('Présentation du carré invalide.');
            $columns[] = 'CARRE_TYPE = ?'; $values[] = $input['square_type'];
        }
        if ($columns) {
            $values[] = $user->user_id;
            $this->change('UPDATE users SET ' . implode(', ', $columns) . ', LAST_UPDATE = LAST_UPDATE WHERE user_id = ?', $values);
        }
        return $this->profile($this->user($user->user_id));
    }

    private function challenge($user, $purpose, $email)
    {
        $id = bin2hex(random_bytes(16));
        $code = (string) random_int(10000000, 99999999);
        $now = $this->date();
        $this->change('UPDATE mobile_challenges SET consumed_at = ? WHERE user_id = ? AND purpose = ? AND consumed_at IS NULL', array($now, $user->user_id, $purpose));
        $this->change('INSERT INTO mobile_challenges (id, user_id, purpose, code_hash, credential_stamp, target_email, created_at, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            array($id, $user->user_id, $purpose, hash('sha256', $id . ':' . $code), AccountSecurity::stamp($user), $email, $now, $this->date($this->now() + 600)));
        $subjects = array('register' => 'Confirmez votre compte BDovore', 'reset' => 'Réinitialisation du mot de passe BDovore', 'email' => 'Confirmez votre nouvelle adresse BDovore');
        if (!call_user_func($this->sendMail, $email, $subjects[$purpose], "Votre code BDovore : $code\n\nIl est valable 10 minutes et ne peut être utilisé qu'une fois.\nSi vous n'êtes pas à l'origine de cette demande, ignorez ce message.")) {
            $this->change('UPDATE mobile_challenges SET consumed_at = ? WHERE id = ?', array($now, $id));
            throw new MobileAccountException("L'e-mail n'a pas pu être envoyé. Réessayez plus tard.", 503, 'mail_unavailable');
        }
        return array('challenge_id' => $id, 'message' => 'Si les informations correspondent à un compte, un code a été envoyé par e-mail.');
    }
    private function consume($input, $purpose, $userId = null)
    {
        $id = $this->text($input, 'challenge_id', 32, true);
        $code = $this->text($input, 'code', 8, true);
        $error = new MobileAccountException('Code invalide ou expiré.', 422, 'invalid_code');
        if (!preg_match('/^[a-f0-9]{32}$/D', $id) || !preg_match('/^[0-9]{8}$/D', $code)) throw $error;
        $affected = $this->change('UPDATE mobile_challenges SET attempts = attempts + 1 WHERE id = ? AND purpose = ? AND consumed_at IS NULL AND expires_at > ? AND attempts < 5', array($id, $purpose, $this->date()));
        if (!$affected) throw $error;
        $challenge = $this->row('SELECT * FROM mobile_challenges WHERE id = ?', array($id));
        $user = $this->user($challenge->user_id);
        if (!$user || ($userId !== null && intval($userId) !== intval($user->user_id)) || !hash_equals($challenge->credential_stamp, AccountSecurity::stamp($user))
            || !hash_equals($challenge->code_hash, hash('sha256', $id . ':' . $code))) throw $error;
        $affected = $this->change('UPDATE mobile_challenges SET consumed_at = ? WHERE id = ? AND consumed_at IS NULL AND expires_at > ?', array($this->date(), $id, $this->date()));
        if (!$affected) throw $error;
        return array($challenge, $user);
    }

    public function register($input, $ip)
    {
        $this->throttle('register-ip', $ip, 5, 3600);
        $username = trim($this->text($input, 'username', $this->maxLength('username', 25), true));
        if (!preg_match('/^[A-Za-z0-9_@-]+$/D', $username) || (function_exists('AuthorisedLogin') && !AuthorisedLogin($username))) throw new MobileAccountException('Pseudo non autorisé.');
        $email = $this->email($input);
        $password = $this->newPassword($input);
        $existing = $this->findUser($username);
        if ($existing) {
            // A pending registration can be resumed without storing its password in the app.
            if (intval($existing->level) === 98 && hash_equals($existing->email, $email) && AccountSecurity::verifyPassword($password, $existing)) {
                $this->throttle('register-resend', $existing->user_id, 1, 60);
                return $this->challenge($existing, 'register', $email);
            }
            throw new MobileAccountException('Ce pseudo est indisponible.', 409, 'username_unavailable');
        }
        try {
            $this->change("INSERT INTO users (username, password, password_v2, email, level) VALUES (?, 'migrated', ?, ?, 98)", array($username, AccountSecurity::hashPassword($password), $email));
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) throw new MobileAccountException('Ce pseudo est indisponible.', 409, 'username_unavailable');
            throw $e;
        }
        $user = $this->user($this->db->insert_id);
        return $this->challenge($user, 'register', $email);
    }
    public function verifyRegistration($input)
    {
        list($challenge, $user) = $this->consume($input, 'register');
        if (intval($user->level) !== 98) throw new MobileAccountException('Confirmation invalide.');
        $this->change('UPDATE users SET level = 2, LAST_UPDATE = LAST_UPDATE WHERE user_id = ? AND level = 98', array($user->user_id));
        return $this->user($user->user_id);
    }
    public function forgotPassword($input, $ip)
    {
        $this->throttle('reset-ip', $ip, 10, 3600);
        $username = trim($this->text($input, 'username', 100, true));
        $email = $this->email($input);
        $this->throttle('reset-user', mb_strtolower($username, 'UTF-8'), 3, 3600);
        $user = $this->findUser($username);
        // Match the existing pseudo + email recovery semantics; emails need not be unique.
        if ($user && intval($user->level) < 98 && strcasecmp($user->email, $email) === 0) return $this->challenge($user, 'reset', $user->email);
        password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT, array('cost' => 12));
        return array('challenge_id' => bin2hex(random_bytes(16)), 'message' => 'Si les informations correspondent à un compte, un code a été envoyé par e-mail.');
    }
    public function resetPassword($input)
    {
        $password = $this->newPassword($input);
        list($challenge, $user) = $this->consume($input, 'reset');
        if (intval($user->level) >= 98) throw new MobileAccountException('Code invalide ou expiré.');
        $this->setPassword($user, $password);
    }
    private function requirePassword($user, $input)
    {
        $this->throttle('sensitive-user', $user->user_id, 10, 900);
        $password = $this->text($input, 'current_password', 4096, true);
        if (!AccountSecurity::verifyPassword($password, $user)) $this->denied();
    }
    private function setPassword($user, $password)
    {
        $changed = $this->change("UPDATE users SET password_v2 = ?, password = 'migrated', API_TOKEN = NULL, DATE_TOKEN = NULL, LAST_UPDATE = LAST_UPDATE WHERE user_id = ? AND COALESCE(password_v2, '') = ? AND password = ?",
            array(AccountSecurity::hashPassword($password), $user->user_id, $user->password_v2 ?? '', $user->password));
        if (!$changed) throw new MobileAccountException('Le compte a changé. Veuillez vous reconnecter.', 409, 'account_changed');
        $this->revokeAll($user->user_id);
        call_user_func($this->sendMail, $user->email, 'Votre mot de passe BDovore a été modifié', "Votre mot de passe BDovore a été modifié. Si vous n'êtes pas à l'origine de cette modification, contactez l'équipe BDovore.");
    }
    public function changePassword($user, $input)
    {
        $this->requirePassword($user, $input);
        $this->setPassword($user, $this->newPassword($input));
    }
    public function requestEmail($user, $input)
    {
        $this->requirePassword($user, $input);
        $this->throttle('email-user', $user->user_id, 3, 3600);
        $email = $this->email($input);
        return $this->challenge($user, 'email', $email);
    }
    public function confirmEmail($user, $input)
    {
        list($challenge) = $this->consume($input, 'email', $user->user_id);
        $this->change('UPDATE users SET email = ?, LAST_UPDATE = LAST_UPDATE WHERE user_id = ?', array($challenge->target_email, $user->user_id));
        call_user_func($this->sendMail, $user->email, 'Votre adresse BDovore a été modifiée', "Votre adresse e-mail BDovore a été modifiée. Si vous n'êtes pas à l'origine de cette modification, contactez l'équipe BDovore.");
        return $this->profile($this->user($user->user_id));
    }
    public function prepareDeletion($user, $input)
    {
        $this->requirePassword($user, $input);
        if (intval($user->level) < 2) throw new MobileAccountException('La suppression des comptes administrateurs nécessite une intervention sur le site.', 403, 'protected_account');
        $this->revokeAll($user->user_id);
    }
    public function ticket($session, $input)
    {
        $destination = AccountSecurity::webDestination($input);
        $token = bin2hex(random_bytes(32));
        $this->change('INSERT INTO mobile_web_tickets (token_hash, session_id, destination, expires_at) VALUES (?, ?, ?, ?)',
            array(hash('sha256', $token), $session->id, $destination, $this->date($this->now() + 60)));
        return array('url' => rtrim(BDO_URL, '/') . '/apiauth/openweb?ticket=' . $token);
    }
    public function consumeTicket($token)
    {
        if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token)) throw new MobileAccountException('Lien invalide ou expiré.', 401, 'invalid_ticket');
        $hash = hash('sha256', $token);
        if (!$this->change('UPDATE mobile_web_tickets SET consumed_at = ? WHERE token_hash = ? AND consumed_at IS NULL AND expires_at > ?', array($this->date(), $hash, $this->date()))) {
            throw new MobileAccountException('Lien invalide ou expiré.', 401, 'invalid_ticket');
        }
        $ticket = $this->row('SELECT t.destination, s.token_hash FROM mobile_web_tickets t JOIN mobile_sessions s ON s.id = t.session_id WHERE t.token_hash = ?', array($hash));
        if (!$ticket) throw new MobileAccountException('Lien invalide ou expiré.', 401, 'invalid_ticket');
        $context = $this->sessionByHash($ticket->token_hash);
        return array('user' => $context['user'], 'destination' => $ticket->destination);
    }
}
