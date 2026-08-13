<?php

require_once __DIR__ . '/ParabdDbLine.php';

class Parabdreport extends ParabdDbLine
{
    public $table_name = 'parabd_report';
    public function __construct($id = null) { parent::__construct($this->table_name, is_array($id) ? $id : array('ID_REPORT' => $id)); }
    public function openQueue() { return $this->fetchAllQuery("SELECT pr.*,u.username REPORTER_NAME,i.ID_ITEM,i.TITLE FROM parabd_report pr
        LEFT JOIN users u ON u.user_id=pr.REPORTER_ID
        LEFT JOIN parabd_media pm ON pr.TARGET_TYPE='MEDIA' AND pm.ID_MEDIA=pr.TARGET_ID
        LEFT JOIN parabd_revision rv ON pr.TARGET_TYPE='REVISION' AND rv.ID_REVISION=pr.TARGET_ID
        LEFT JOIN parabd_item i ON i.ID_ITEM=CASE WHEN pr.TARGET_TYPE='ITEM' THEN pr.TARGET_ID WHEN pr.TARGET_TYPE='MEDIA' THEN pm.ITEM_ID ELSE rv.ITEM_ID END
        WHERE pr.STATUS='OPEN' ORDER BY pr.CREATED_AT"); }
    public function targetExists($targetType, $targetId) { $targets = array('ITEM' => array('parabd_item','ID_ITEM'), 'MEDIA' => array('parabd_media','ID_MEDIA'), 'REVISION' => array('parabd_revision','ID_REVISION')); if (!isset($targets[$targetType])) return false; $target = $targets[$targetType]; return (bool) $this->fetchOneQuery("SELECT {$target[1]} FROM {$target[0]} WHERE {$target[1]}=" . intval($targetId)); }
    public function createReport($userId, $targetType, $targetId, $reason, $details) { return (new self())->persist(array('REPORTER_ID' => intval($userId), 'TARGET_TYPE' => $targetType, 'TARGET_ID' => intval($targetId), 'REASON' => $reason, 'DETAILS' => $details)); }
    public function resolve($reportId, $status, $adminId) { $this->executeQuery("UPDATE parabd_report SET STATUS='" . $this->escape($status) . "',RESOLVED_BY=" . intval($adminId) . ',RESOLVED_AT=NOW() WHERE ID_REPORT=' . intval($reportId) . " AND STATUS='OPEN'"); return Db_affected_rows($this->connection()) === 1; }
    public function openForItem($reportId, $itemId) { foreach ($this->openQueue() as $row) if (intval($row['ID_REPORT']) === intval($reportId) && intval($row['ID_ITEM']) === intval($itemId)) return $row; return null; }
}
