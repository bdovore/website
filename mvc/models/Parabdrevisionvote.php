<?php

require_once __DIR__ . '/ParabdDbLine.php';

class Parabdrevisionvote extends ParabdDbLine
{
    public $table_name = 'parabd_revision_vote';
    public function __construct($id = null) { parent::__construct($this->table_name, is_array($id) ? $id : array('REVISION_ID' => $id)); }
    public function castVote($revisionId, $userId, $vote) { $this->executeQuery("INSERT INTO parabd_revision_vote (REVISION_ID,USER_ID,VOTE) VALUES (" . intval($revisionId) . ',' . intval($userId) . ",'" . $this->escape($vote) . "') ON DUPLICATE KEY UPDATE VOTE=VALUES(VOTE),CREATED_AT=NOW()"); }
    public function counts($revisionId) { return $this->fetchOneQuery("SELECT SUM(VOTE='CONFIRM') confirms,SUM(VOTE='CONTEST') contests FROM parabd_revision_vote WHERE REVISION_ID=" . intval($revisionId)); }
}
