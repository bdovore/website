<?php

require_once __DIR__ . '/ParabdDbLine.php';

class Parabdmedia extends ParabdDbLine
{
    public $table_name = 'parabd_media';
    public function __construct($id = null) { parent::__construct($this->table_name, is_array($id) ? $id : array('ID_MEDIA' => $id)); }
    public function forItem($itemId, $includeHidden = false) { return $this->fetchAllQuery('SELECT * FROM parabd_media WHERE ITEM_ID=' . intval($itemId) . ($includeHidden ? '' : ' AND IS_HIDDEN=0') . ' ORDER BY IS_PRIMARY DESC,SORT_ORDER,ID_MEDIA'); }
    public function primaryPath($itemId, $explicitAllowed) { $path = $explicitAllowed ? 'FILE_PATH' : "IF(IS_EXPLICIT=1,CONCAT('?source=',FILE_PATH),FILE_PATH)"; $row = $this->fetchOneQuery("SELECT $path FILE_PATH FROM parabd_media WHERE ITEM_ID=" . intval($itemId) . ' AND IS_PRIMARY=1 AND IS_HIDDEN=0 LIMIT 1'); return $row ? $row['FILE_PATH'] : null; }
    public function snapshotForItem($itemId) { return $this->fetchAllQuery('SELECT ID_MEDIA,MEDIA_TYPE,FILE_PATH,IS_PRIMARY,IS_HIDDEN,IS_EXPLICIT,SORT_ORDER FROM parabd_media WHERE ITEM_ID=' . intval($itemId) . ' ORDER BY ID_MEDIA'); }
    public function selectionForItem($itemId) { return $this->fetchAllQuery('SELECT ID_MEDIA,IS_PRIMARY,IS_EXPLICIT FROM parabd_media WHERE ITEM_ID=' . intval($itemId) . ' ORDER BY ID_MEDIA'); }
    public function countForItem($itemId) { $row = $this->fetchOneQuery('SELECT COUNT(*) total FROM parabd_media WHERE ITEM_ID=' . intval($itemId)); return intval($row['total']); }
    public function addImage($itemId, $mediaType, array $image, $userId, $isPrimary = false, $isExplicit = false) { return (new self())->persist(array('ITEM_ID' => intval($itemId), 'MEDIA_TYPE' => $mediaType, 'FILE_PATH' => $image['relative_path'], 'MIME_TYPE' => $image['mime'], 'WIDTH_PX' => intval($image['width']), 'HEIGHT_PX' => intval($image['height']), 'IS_PRIMARY' => $isPrimary ? 1 : 0, 'IS_EXPLICIT' => $isExplicit ? 1 : 0, 'CREATED_BY' => intval($userId))); }
    public function setHidden($itemId, $mediaId, $hidden) { $this->executeQuery('UPDATE parabd_media SET IS_HIDDEN=' . ($hidden ? '1' : '0') . ' WHERE ID_MEDIA=' . intval($mediaId) . ' AND ITEM_ID=' . intval($itemId)); }
    public function setExplicit($itemId, $mediaId, $explicit) { $this->executeQuery('UPDATE parabd_media SET IS_EXPLICIT=' . ($explicit ? '1' : '0') . ' WHERE ID_MEDIA=' . intval($mediaId) . ' AND ITEM_ID=' . intval($itemId)); }
    public function isVisibleForItem($itemId, $mediaId) { return (bool) $this->fetchOneQuery('SELECT ID_MEDIA FROM parabd_media WHERE ID_MEDIA=' . intval($mediaId) . ' AND ITEM_ID=' . intval($itemId) . ' AND IS_HIDDEN=0'); }
    public function selectPrimary($itemId, $mediaId) { $this->executeQuery("UPDATE parabd_media SET IS_PRIMARY=0,MEDIA_TYPE=IF(MEDIA_TYPE='PRIMARY','GALLERY',MEDIA_TYPE) WHERE ITEM_ID=" . intval($itemId)); $this->executeQuery("UPDATE parabd_media SET IS_PRIMARY=1,MEDIA_TYPE='PRIMARY' WHERE ID_MEDIA=" . intval($mediaId) . ' AND ITEM_ID=' . intval($itemId)); }
    public function moveToItem($sourceId, $targetId) { $this->executeQuery('UPDATE parabd_media SET ITEM_ID=' . intval($targetId) . ' WHERE ITEM_ID=' . intval($sourceId)); }
}
