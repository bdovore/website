<?php

require_once __DIR__ . '/ParabdDbLine.php';

class Parabditemtome extends ParabdDbLine
{
    public $table_name = 'parabd_item_tome';
    public function __construct($id = null) { parent::__construct($this->table_name, is_array($id) ? $id : array('ITEM_ID' => $id)); }
    public function forItem($itemId) { return $this->fetchAllQuery("SELECT l.ITEM_ID,l.TOME_ID,l.RELATION_TYPE,l.CREATED_BY,l.CREATED_AT,COALESCE(t.TITRE,CONCAT('Album #',l.TOME_ID)) LABEL FROM parabd_item_tome l LEFT JOIN bd_tome t ON t.ID_TOME=l.TOME_ID WHERE l.ITEM_ID=" . intval($itemId)); }
    public function snapshotForItem($itemId) { return $this->fetchAllQuery('SELECT TOME_ID,RELATION_TYPE FROM parabd_item_tome WHERE ITEM_ID=' . intval($itemId) . ' ORDER BY TOME_ID,RELATION_TYPE'); }
    public function addForItem($itemId, array $row, $userId) { if (!$this->fetchOneQuery('SELECT ID_TOME FROM bd_tome WHERE ID_TOME=' . intval($row['id']))) throw new ParabdException('VALIDATION_ERROR', 'Album inconnu.'); (new self())->persist(array('ITEM_ID' => intval($itemId), 'TOME_ID' => intval($row['id']), 'RELATION_TYPE' => $row['relation_type'], 'CREATED_BY' => intval($userId))); }
    public function replaceForItem($itemId, array $rows, $userId) { $this->executeQuery('DELETE FROM parabd_item_tome WHERE ITEM_ID=' . intval($itemId)); foreach ($rows as $row) $this->addForItem($itemId, $row, $userId); }
    public function existsForItem($itemId, $tomeId) { return (bool) $this->fetchOneQuery('SELECT 1 found FROM parabd_item_tome WHERE ITEM_ID=' . intval($itemId) . ' AND TOME_ID=' . intval($tomeId) . ' LIMIT 1'); }
    public function removeForItem($itemId, $tomeId) { $this->executeQuery('DELETE FROM parabd_item_tome WHERE ITEM_ID=' . intval($itemId) . ' AND TOME_ID=' . intval($tomeId)); }
    public function mergeInto($sourceId, $targetId) { $this->executeQuery('INSERT IGNORE INTO parabd_item_tome (ITEM_ID,TOME_ID,RELATION_TYPE,CREATED_BY,CREATED_AT) SELECT ' . intval($targetId) . ',TOME_ID,RELATION_TYPE,CREATED_BY,CREATED_AT FROM parabd_item_tome WHERE ITEM_ID=' . intval($sourceId)); $this->executeQuery('DELETE FROM parabd_item_tome WHERE ITEM_ID=' . intval($sourceId)); }
}
