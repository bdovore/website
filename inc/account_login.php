<?php

class AccountLoginAmbiguous extends RuntimeException {}

/** Resolve an existing account without changing the pseudo or requiring unique emails. */
class AccountLogin
{
    const PSEUDO_REQUIRED = 'Pour vous connecter avec cette adresse, précisez votre pseudo.';

    public static function emailAttempt($identifier, $user = null)
    {
        return is_string($identifier) && filter_var(trim($identifier), FILTER_VALIDATE_EMAIL)
            && (!$user || strcasecmp(trim($identifier), (string) ($user->username ?? '')) !== 0);
    }
    public static function resolve($db, $identifier, $pseudo = '')
    {
        if (!is_string($identifier) || !is_string($pseudo) || strlen($identifier) > 254 || strlen($pseudo) > 100) return null;
        $identifier = trim($identifier); $pseudo = trim($pseudo);
        // An exact pseudo always wins, even if it resembles an email address.
        $statement = $db->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
        $statement->bind_param('s', $identifier); $statement->execute();
        $user = $statement->get_result()->fetch_object(); $statement->close();
        if ($user) return intval($user->user_id);
        if ($pseudo !== '') {
            if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) return null;
            $statement = $db->prepare('SELECT user_id FROM users WHERE username = ? AND email = ? LIMIT 1');
            $statement->bind_param('ss', $pseudo, $identifier);
        } else {
            if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) return null;
            $statement = $db->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 2');
            $statement->bind_param('s', $identifier);
        }
        $statement->execute(); $result = $statement->get_result();
        $count = $result->num_rows; $user = $result->fetch_object(); $statement->close();
        // Unknown and shared addresses receive the same prompt; no account list is exposed.
        if ($pseudo === '' && $count !== 1) throw new AccountLoginAmbiguous(self::PSEUDO_REQUIRED);
        return $user ? intval($user->user_id) : null;
    }
}
