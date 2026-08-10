<?php

require_once __DIR__ . '/ParabdDbLine.php';

class Parabdrevision extends ParabdDbLine
{
    public $table_name = 'parabd_revision';
    public function __construct($id = null) { parent::__construct($this->table_name, is_array($id) ? $id : array('ID_REVISION' => $id)); }

    public function forItem($itemId)
    {
        return $this->fetchAllQuery("SELECT r.*,SUM(v.VOTE='CONFIRM') CONFIRMS,SUM(v.VOTE='CONTEST') CONTESTS FROM parabd_revision r
            LEFT JOIN parabd_revision_vote v ON v.REVISION_ID=r.ID_REVISION WHERE r.ITEM_ID=" . intval($itemId) . " AND r.STATUS IN ('PENDING','APPLIED') AND r.CHANGE_KIND<>'CREATE'
            GROUP BY r.ID_REVISION ORDER BY r.CREATED_AT DESC");
    }

    public function adminHistory($itemId)
    {
        return $this->fetchAllQuery("SELECT r.*,ua.username AUTHOR_NAME,uv.username VALIDATOR_NAME,SUM(v.VOTE='CONFIRM') CONFIRMS,SUM(v.VOTE='CONTEST') CONTESTS
            FROM parabd_revision r LEFT JOIN users ua ON ua.user_id=r.AUTHOR_ID LEFT JOIN users uv ON uv.user_id=r.VALIDATED_BY
            LEFT JOIN parabd_revision_vote v ON v.REVISION_ID=r.ID_REVISION WHERE r.ITEM_ID=" . intval($itemId) . ' GROUP BY r.ID_REVISION ORDER BY r.CREATED_AT DESC,r.ID_REVISION DESC');
    }

    public function conflictQueue()
    {
        return $this->fetchAllQuery("SELECT r.*,i.TITLE FROM parabd_revision r JOIN parabd_item i ON i.ID_ITEM=r.ITEM_ID WHERE r.STATUS='CONFLICT' ORDER BY r.CREATED_AT");
    }

    public function createRevision(array $data)
    {
        return (new self())->persist($data);
    }

    public function findForUpdate($revisionId)
    {
        return $this->fetchOneQuery('SELECT * FROM parabd_revision WHERE ID_REVISION=' . intval($revisionId) . ' FOR UPDATE');
    }

    public function setConflict($revisionId) { $this->executeQuery("UPDATE parabd_revision SET STATUS='CONFLICT' WHERE ID_REVISION=" . intval($revisionId)); }
    public function markApplied($revisionId) { $this->executeQuery('UPDATE parabd_revision SET APPLIED_AT=NOW() WHERE ID_REVISION=' . intval($revisionId)); }
    public function decide($revisionId, $status, $userId) { $this->executeQuery("UPDATE parabd_revision SET STATUS='" . $this->escape($status) . "',VALIDATED_BY=" . intval($userId) . ',VALIDATED_AT=NOW() WHERE ID_REVISION=' . intval($revisionId)); }
    public function moveToItem($sourceId, $targetId) { $this->executeQuery('UPDATE parabd_revision SET ITEM_ID=' . intval($targetId) . ' WHERE ITEM_ID=' . intval($sourceId)); }
    public function addMergeAudit($targetId, $adminId, $audit) { $this->executeQuery("INSERT INTO parabd_revision (ITEM_ID,AUTHOR_ID,BASE_REVISION_NO,PATCH_AFTER,CHANGE_KIND,STATUS,APPLIED_AT,VALIDATED_BY,VALIDATED_AT) SELECT ID_ITEM," . intval($adminId) . ",REVISION_NO-1,'" . $this->escape($audit) . "','MERGE','ACCEPTED',NOW()," . intval($adminId) . ',NOW() FROM parabd_item WHERE ID_ITEM=' . intval($targetId)); }
}
