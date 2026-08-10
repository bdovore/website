<?php

require_once __DIR__ . '/ParabdDbLine.php';

class Parabdsource extends ParabdDbLine
{
    public $table_name = 'parabd_source';
    public function __construct($id = null) { parent::__construct($this->table_name, is_array($id) ? $id : array('ID_SOURCE' => $id)); }
    public function forItem($itemId) { return $this->fetchAllQuery('SELECT * FROM parabd_source WHERE ITEM_ID=' . intval($itemId) . ' ORDER BY ID_SOURCE'); }
    public function snapshotForItem($itemId) { return $this->fetchAllQuery('SELECT SOURCE_TYPE,URL,LABEL,NOTES FROM parabd_source WHERE ITEM_ID=' . intval($itemId) . ' ORDER BY ID_SOURCE'); }
    public function addUrl($itemId, $userId, $url, $label = '', $notes = '') { return (new self())->persist(array('ITEM_ID' => intval($itemId), 'SOURCE_TYPE' => 'URL', 'URL' => $url, 'LABEL' => $label, 'NOTES' => $notes, 'CREATED_BY' => intval($userId))); }
    public function replaceForItem($itemId, array $sources, $userId) { $this->executeQuery('DELETE FROM parabd_source WHERE ITEM_ID=' . intval($itemId)); foreach ($sources as $source) $this->addUrl($itemId, $userId, $source['url'], $source['label'], $source['notes']); }
    public function moveToItem($sourceId, $targetId) { $this->executeQuery('UPDATE parabd_source SET ITEM_ID=' . intval($targetId) . ' WHERE ITEM_ID=' . intval($sourceId)); }
}
