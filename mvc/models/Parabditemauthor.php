<?php

require_once __DIR__ . '/ParabdDbLine.php';

class Parabditemauthor extends ParabdDbLine
{
    public $table_name = 'parabd_item_author';
    public function __construct($id = null) { parent::__construct($this->table_name, is_array($id) ? $id : array('ITEM_ID' => $id)); }
    public function forItem($itemId) { return $this->fetchAllQuery("SELECT l.*,COALESCE(NULLIF(a.PSEUDO,''),NULLIF(TRIM(CONCAT_WS(' ',a.PRENOM,a.NOM)),''),CONCAT('Auteur #',l.AUTHOR_ID)) LABEL FROM parabd_item_author l LEFT JOIN bd_auteur a ON a.ID_AUTEUR=l.AUTHOR_ID WHERE l.ITEM_ID=" . intval($itemId)); }
    public function snapshotForItem($itemId) { return $this->fetchAllQuery('SELECT AUTHOR_ID,ROLE FROM parabd_item_author WHERE ITEM_ID=' . intval($itemId) . ' ORDER BY AUTHOR_ID,ROLE'); }
    public function addForItem($itemId, array $row, $userId) { if (!$this->fetchOneQuery('SELECT ID_AUTEUR FROM bd_auteur WHERE ID_AUTEUR=' . intval($row['id']))) throw new ParabdException('VALIDATION_ERROR', 'Auteur inconnu.'); (new self())->persist(array('ITEM_ID' => intval($itemId), 'AUTHOR_ID' => intval($row['id']), 'ROLE' => $row['role'], 'CREATED_BY' => intval($userId))); }
    public function replaceForItem($itemId, array $rows, $userId) { $this->executeQuery('DELETE FROM parabd_item_author WHERE ITEM_ID=' . intval($itemId)); foreach ($rows as $row) $this->addForItem($itemId, $row, $userId); }
    public function existsForItem($itemId, $authorId) { return (bool) $this->fetchOneQuery('SELECT 1 found FROM parabd_item_author WHERE ITEM_ID=' . intval($itemId) . ' AND AUTHOR_ID=' . intval($authorId) . ' LIMIT 1'); }
    public function removeForItem($itemId, $authorId) { $this->executeQuery('DELETE FROM parabd_item_author WHERE ITEM_ID=' . intval($itemId) . ' AND AUTHOR_ID=' . intval($authorId)); }
    public function mergeInto($sourceId, $targetId) { $this->executeQuery('INSERT IGNORE INTO parabd_item_author (ITEM_ID,AUTHOR_ID,ROLE,CREATED_BY,CREATED_AT) SELECT ' . intval($targetId) . ',AUTHOR_ID,ROLE,CREATED_BY,CREATED_AT FROM parabd_item_author WHERE ITEM_ID=' . intval($sourceId)); $this->executeQuery('DELETE FROM parabd_item_author WHERE ITEM_ID=' . intval($sourceId)); }
}
