<?php

require_once __DIR__ . '/ParabdDbLine.php';

class Parabdduplicate extends ParabdDbLine
{
    public $table_name = 'parabd_duplicate';
    public function __construct($id = null) { parent::__construct($this->table_name, is_array($id) ? $id : array('ID_DUPLICATE' => $id)); }
    public function openQueue() { return $this->fetchAllQuery("SELECT d.*,a.TITLE TITLE_LOW,b.TITLE TITLE_HIGH FROM parabd_duplicate d JOIN parabd_item a ON a.ID_ITEM=d.ITEM_ID_LOW JOIN parabd_item b ON b.ID_ITEM=d.ITEM_ID_HIGH WHERE d.STATUS='OPEN' ORDER BY FIELD(d.LEVEL,'CERTAIN','STRONG','POSSIBLE'),d.SCORE DESC"); }
    public function record($itemId, $otherId, array $duplicate) { $low = min(intval($itemId), intval($otherId)); $high = max(intval($itemId), intval($otherId)); $this->executeQuery("INSERT INTO parabd_duplicate (ITEM_ID_LOW,ITEM_ID_HIGH,LEVEL,SCORE,REASONS) VALUES ($low,$high,'" . $this->escape($duplicate['level']) . "'," . floatval($duplicate['score']) . ",'" . $this->escape(json_encode($duplicate['reasons'], JSON_UNESCAPED_UNICODE)) . "') ON DUPLICATE KEY UPDATE LEVEL=VALUES(LEVEL),SCORE=VALUES(SCORE),REASONS=VALUES(REASONS),STATUS='OPEN',RESOLVED_BY=NULL,RESOLVED_AT=NULL"); }
    public function resolve($duplicateId, $status, $adminId) { $this->executeQuery("UPDATE parabd_duplicate SET STATUS='" . $this->escape($status) . "',RESOLVED_BY=" . intval($adminId) . ',RESOLVED_AT=NOW() WHERE ID_DUPLICATE=' . intval($duplicateId) . " AND STATUS='OPEN'"); return Db_affected_rows($this->connection()) === 1; }
    public function markMergedForItem($itemId, $adminId) { $id = intval($itemId); $this->executeQuery("UPDATE parabd_duplicate SET STATUS='MERGED',RESOLVED_BY=" . intval($adminId) . ",RESOLVED_AT=NOW() WHERE ITEM_ID_LOW=$id OR ITEM_ID_HIGH=$id"); }
}
