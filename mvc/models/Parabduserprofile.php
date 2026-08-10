<?php

require_once __DIR__ . '/ParabdDbLine.php';
require_once __DIR__ . '/ParabdRules.php';

class Parabduserprofile extends ParabdDbLine
{
    public $table_name = 'parabd_user_profile';
    public function __construct($id = null) { parent::__construct($this->table_name, is_array($id) ? $id : array('USER_ID' => $id)); }

    public function isTrustedUser($userId)
    {
        $profile = $this->fetchOneQuery('SELECT TRUST_OVERRIDE FROM parabd_user_profile WHERE USER_ID=' . intval($userId));
        $user = $this->fetchOneQuery('SELECT CREATED_AT FROM users WHERE user_id=' . intval($userId));
        if (!$user) return false;
        $legacy = $this->fetchOneQuery("SELECT
            (SELECT COUNT(*) FROM users_alb_prop WHERE USER_ID=" . intval($userId) . " AND STATUS=1) +
            (SELECT COUNT(*) FROM bd_edition WHERE USER_ID=" . intval($userId) . " AND PROP_STATUS=1) +
            (SELECT COUNT(*) FROM parabd_revision WHERE AUTHOR_ID=" . intval($userId) . " AND VALIDATED_AT IS NOT NULL AND STATUS IN ('ACCEPTED','APPLIED')) total");
        return ParabdRules::calculateTrust($user['CREATED_AT'], $legacy ? intval($legacy['total']) : 0, $profile ? $profile['TRUST_OVERRIDE'] : 'NONE');
    }

    public function acceptCharter($userId, $version)
    {
        $this->executeQuery("INSERT INTO parabd_user_profile (USER_ID,CHARTER_VERSION,CHARTER_ACCEPTED_AT) VALUES (" . intval($userId) . ",'" . $this->escape($version) . "',NOW())
            ON DUPLICATE KEY UPDATE CHARTER_VERSION=VALUES(CHARTER_VERSION),CHARTER_ACCEPTED_AT=NOW()");
    }

    public function charterVersion($userId)
    {
        $row = $this->fetchOneQuery('SELECT CHARTER_VERSION FROM parabd_user_profile WHERE USER_ID=' . intval($userId));
        return $row ? $row['CHARTER_VERSION'] : null;
    }

    public function consume($userId, $kind, $limit)
    {
        $columnAt = $kind === 'upload' ? 'UPLOADS_WINDOW_AT' : 'CREATIONS_WINDOW_AT';
        $columnCount = $kind === 'upload' ? 'UPLOADS_IN_WINDOW' : 'CREATIONS_IN_WINDOW';
        $this->executeQuery('INSERT IGNORE INTO parabd_user_profile (USER_ID) VALUES (' . intval($userId) . ')');
        $row = $this->fetchOneQuery("SELECT $columnAt window_at,$columnCount count_value FROM parabd_user_profile WHERE USER_ID=" . intval($userId) . ' FOR UPDATE');
        $fresh = empty($row['window_at']) || strtotime($row['window_at']) <= strtotime('-1 hour');
        if (!$fresh && intval($row['count_value']) >= intval($limit)) throw new ParabdException('RATE_LIMITED', 'Limite horaire atteinte. Réessayez plus tard.');
        $this->executeQuery("UPDATE parabd_user_profile SET $columnAt=" . ($fresh ? 'NOW()' : $columnAt) . ",$columnCount=" . ($fresh ? '1' : "$columnCount+1") . ' WHERE USER_ID=' . intval($userId));
    }
}

