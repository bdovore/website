<?php

require_once __DIR__ . '/ParabdDbLine.php';

class Parabditemseries extends ParabdDbLine
{
    public $table_name = 'parabd_item_series';
    public function __construct($id = null) { parent::__construct($this->table_name, is_array($id) ? $id : array('ITEM_ID' => $id)); }
    public function forItem($itemId) { return $this->fetchAllQuery("SELECT l.*,COALESCE(s.NOM,CONCAT('Série #',l.SERIES_ID)) LABEL FROM parabd_item_series l LEFT JOIN bd_serie s ON s.ID_SERIE=l.SERIES_ID WHERE l.ITEM_ID=" . intval($itemId)); }
    public function snapshotForItem($itemId) { return $this->fetchAllQuery('SELECT SERIES_ID,RELATION_TYPE FROM parabd_item_series WHERE ITEM_ID=' . intval($itemId) . ' ORDER BY SERIES_ID,RELATION_TYPE'); }
    public function addForItem($itemId, array $row, $userId) { if (!$this->fetchOneQuery('SELECT ID_SERIE FROM bd_serie WHERE ID_SERIE=' . intval($row['id']))) throw new ParabdException('VALIDATION_ERROR', 'Série inconnue.'); (new self())->persist(array('ITEM_ID' => intval($itemId), 'SERIES_ID' => intval($row['id']), 'RELATION_TYPE' => $row['relation_type'], 'CREATED_BY' => intval($userId))); }
    public function replaceForItem($itemId, array $rows, $userId) { $this->executeQuery('DELETE FROM parabd_item_series WHERE ITEM_ID=' . intval($itemId)); foreach ($rows as $row) $this->addForItem($itemId, $row, $userId); }
    public function existsForItem($itemId, $seriesId) { return (bool) $this->fetchOneQuery('SELECT 1 found FROM parabd_item_series WHERE ITEM_ID=' . intval($itemId) . ' AND SERIES_ID=' . intval($seriesId) . ' LIMIT 1'); }
    public function removeForItem($itemId, $seriesId) { $this->executeQuery('DELETE FROM parabd_item_series WHERE ITEM_ID=' . intval($itemId) . ' AND SERIES_ID=' . intval($seriesId)); }
    public function mergeInto($sourceId, $targetId) { $this->executeQuery('INSERT IGNORE INTO parabd_item_series (ITEM_ID,SERIES_ID,RELATION_TYPE,CREATED_BY,CREATED_AT) SELECT ' . intval($targetId) . ',SERIES_ID,RELATION_TYPE,CREATED_BY,CREATED_AT FROM parabd_item_series WHERE ITEM_ID=' . intval($sourceId)); $this->executeQuery('DELETE FROM parabd_item_series WHERE ITEM_ID=' . intval($sourceId)); }
}
