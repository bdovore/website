<?php

require_once __DIR__ . '/ParabdDbLine.php';

class Parabdreport extends ParabdDbLine
{
    public $table_name = 'parabd_report';
    public function __construct($id = null) { parent::__construct($this->table_name, is_array($id) ? $id : array('ID_REPORT' => $id)); }
    public function openQueue() { return $this->fetchAllQuery("SELECT * FROM parabd_report WHERE STATUS='OPEN' ORDER BY CREATED_AT"); }
    public function targetExists($targetType, $targetId) { $targets = array('ITEM' => array('parabd_item','ID_ITEM'), 'MEDIA' => array('parabd_media','ID_MEDIA'), 'REVISION' => array('parabd_revision','ID_REVISION')); if (!isset($targets[$targetType])) return false; $target = $targets[$targetType]; return (bool) $this->fetchOneQuery("SELECT {$target[1]} FROM {$target[0]} WHERE {$target[1]}=" . intval($targetId)); }
    public function createReport($userId, $targetType, $targetId, $reason, $details) { return (new self())->persist(array('REPORTER_ID' => intval($userId), 'TARGET_TYPE' => $targetType, 'TARGET_ID' => intval($targetId), 'REASON' => $reason, 'DETAILS' => $details)); }
    public function resolve($reportId, $status, $adminId) { $this->executeQuery("UPDATE parabd_report SET STATUS='" . $this->escape($status) . "',RESOLVED_BY=" . intval($adminId) . ',RESOLVED_AT=NOW() WHERE ID_REPORT=' . intval($reportId) . " AND STATUS='OPEN'"); return Db_affected_rows($this->connection()) === 1; }
}
