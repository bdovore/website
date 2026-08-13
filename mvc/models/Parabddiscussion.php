<?php

require_once __DIR__ . '/ParabdDbLine.php';

class Parabddiscussion extends ParabdDbLine
{
    public $table_name = 'parabd_discussion';
    public function __construct($id = null) { parent::__construct($this->table_name, is_array($id) ? $id : array('ID_DISCUSSION' => $id)); }

    public function addProposal($itemId, $revisionId, $authorId)
    {
        return (new self())->persist(array('ITEM_ID' => intval($itemId), 'REVISION_ID' => intval($revisionId), 'AUTHOR_ID' => intval($authorId), 'MESSAGE_TYPE' => 'PROPOSAL'));
    }

    public function addComment($itemId, $revisionId, $authorId, $body)
    {
        $data = array('ITEM_ID' => intval($itemId), 'AUTHOR_ID' => intval($authorId), 'MESSAGE_TYPE' => 'COMMENT', 'BODY' => $body);
        if ($revisionId) $data['REVISION_ID'] = intval($revisionId);
        return (new self())->persist($data);
    }

    public function forItem($itemId, $includeHidden = false, $limit = 100)
    {
        $where = 'd.ITEM_ID=' . intval($itemId);
        if (!$includeHidden) $where .= " AND d.STATUS='VISIBLE'";
        $rows = $this->fetchAllQuery("SELECT d.ID_DISCUSSION,d.ITEM_ID,d.REVISION_ID,d.AUTHOR_ID,d.MESSAGE_TYPE,d.BODY,d.STATUS DISCUSSION_STATUS,d.CREATED_AT,d.HIDDEN_BY,d.HIDDEN_AT,
                u.username AUTHOR_NAME,hu.username HIDDEN_BY_NAME,r.STATUS REVISION_STATUS,r.CHANGE_KIND,r.PATCH_BEFORE,r.PATCH_AFTER,r.BASE_REVISION_NO,
                COALESCE(vc.CONFIRMS,0) CONFIRMS,COALESCE(vc.CONTESTS,0) CONTESTS
            FROM parabd_discussion d
            LEFT JOIN users u ON u.user_id=d.AUTHOR_ID LEFT JOIN users hu ON hu.user_id=d.HIDDEN_BY
            LEFT JOIN parabd_revision r ON r.ID_REVISION=d.REVISION_ID
            LEFT JOIN (SELECT REVISION_ID,SUM(VOTE='CONFIRM') CONFIRMS,SUM(VOTE='CONTEST') CONTESTS FROM parabd_revision_vote GROUP BY REVISION_ID) vc ON vc.REVISION_ID=d.REVISION_ID
            WHERE $where ORDER BY d.CREATED_AT DESC,d.ID_DISCUSSION DESC LIMIT " . max(1, min(100, intval($limit))));
        return array_reverse($rows);
    }

    public function visibleCommentCount($itemId)
    {
        $row = $this->fetchOneQuery("SELECT COUNT(*) total FROM parabd_discussion WHERE ITEM_ID=" . intval($itemId) . " AND MESSAGE_TYPE='COMMENT' AND STATUS='VISIBLE'");
        return intval($row['total']);
    }

    public function revisionBelongsToItem($revisionId, $itemId)
    {
        return (bool) $this->fetchOneQuery('SELECT ID_REVISION FROM parabd_revision WHERE ID_REVISION=' . intval($revisionId) . ' AND ITEM_ID=' . intval($itemId));
    }

    public function hideComment($discussionId, $adminId)
    {
        $this->executeQuery("UPDATE parabd_discussion SET STATUS='HIDDEN',HIDDEN_BY=" . intval($adminId) . ',HIDDEN_AT=NOW() WHERE ID_DISCUSSION=' . intval($discussionId) . " AND MESSAGE_TYPE='COMMENT' AND STATUS='VISIBLE'");
        return Db_affected_rows($this->connection()) === 1;
    }

    public function moveToItem($sourceId, $targetId)
    {
        $this->executeQuery('UPDATE parabd_discussion SET ITEM_ID=' . intval($targetId) . ' WHERE ITEM_ID=' . intval($sourceId));
    }
}
