<?php

/** Shared password rules; existing passwords remain accepted at login. */
class AccountSecurity
{
    public static function emailVerified($user)
    {
        return !empty($user->email_verified_at) && !empty($user->email_verified_address)
            && strcasecmp($user->email_verified_address, (string) $user->email) === 0;
    }

    public static function dummyPasswordCheck($password)
    {
        password_verify(is_string($password) ? $password : '', '$2y$12$6TKgWmxoG2Art8zk4wsqa.QBXl/hijQrDQJV03ylfTGB.aUQcc2rK');
    }
    public static function passwordError($password)
    {
        if (!is_string($password) || mb_strlen($password, 'UTF-8') < 6 || strlen($password) > 72 || strpos($password, "\0") !== false) {
            return 'Le mot de passe doit contenir au moins 6 caractères et au maximum 72 octets. Les espaces sont autorisés.';
        }
        return null;
    }

    public static function hashPassword($password)
    {
        $error = self::passwordError($password);
        if ($error) throw new InvalidArgumentException($error);
        return password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));
    }

    public static function verifyPassword($password, $user)
    {
        if (!is_string($password) || strlen($password) > 4096 || strpos($password, "\0") !== false) return false;
        if (!empty($user->password_v2)) return password_verify($password, $user->password_v2);
        self::dummyPasswordCheck($password);
        return preg_match('/^[a-f0-9]{32}$/i', (string) $user->password)
            && hash_equals(strtolower($user->password), md5($password));
    }

    public static function stamp($user)
    {
        return hash('sha256', (string) ($user->password_v2 ?? '') . ':' . (string) ($user->password ?? ''));
    }

    public static function bearer($server)
    {
        $header = $server['HTTP_AUTHORIZATION'] ?? $server['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
        if ($header === null) return null;
        if (!is_string($header) || !preg_match('/^Bearer (bdo2_[a-f0-9]{64})$/D', $header, $match)) return false;
        return $match[1];
    }

    public static function webDestination($input)
    {
        // Construct the destination rather than accepting arbitrary redirect URLs.
        $type = $input['type'] ?? 'album';
        if ($type === 'correction' || $type === 'edition') {
            $key = $type === 'correction' ? 'id_edition' : 'id_tome';
            $id = filter_var($input[$key] ?? null, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
            if (!$id) throw new InvalidArgumentException('Album invalide.');
            return 'proposition?' . ($type === 'edition' ? 'type=EDITION&' : '') . $key . '=' . $id;
        }
        if ($type !== 'album' || !is_string($input['keyword'] ?? '')) throw new InvalidArgumentException('Destination invalide.');
        return 'proposition?keyword=' . rawurlencode(mb_substr($input['keyword'] ?? '', 0, 100));
    }
}
