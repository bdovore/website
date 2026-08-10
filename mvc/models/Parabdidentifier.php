<?php

require_once __DIR__ . '/ParabdDbLine.php';

class Parabdidentifier extends ParabdDbLine
{
    public $table_name = 'parabd_identifier';

    public function __construct($id = null)
    {
        parent::__construct($this->table_name, is_array($id) ? $id : array('ID_IDENTIFIER' => $id));
    }

    public function forItem($itemId)
    {
        return $this->fetchAllQuery('SELECT * FROM parabd_identifier WHERE ITEM_ID=' . intval($itemId) . ' ORDER BY ID_IDENTIFIER');
    }
    public function snapshotForItem($itemId) { return $this->fetchAllQuery('SELECT SCHEME,ISSUER,VALUE FROM parabd_identifier WHERE ITEM_ID=' . intval($itemId) . ' ORDER BY ID_IDENTIFIER'); }

    public function findExact($scheme, $issuerNormalized, $valueNormalized)
    {
        return $this->fetchOneQuery("SELECT i.ID_ITEM,i.TITLE,i.STATUS FROM parabd_identifier x JOIN parabd_item i ON i.ID_ITEM=x.ITEM_ID
            WHERE x.SCHEME='" . $this->escape($scheme) . "' AND x.ISSUER_NORMALIZED='" . $this->escape($issuerNormalized) . "'
            AND x.VALUE_NORMALIZED='" . $this->escape($valueNormalized) . "' LIMIT 1");
    }

    public function addForItem($itemId, array $identifier, $userId)
    {
        $data = array('ITEM_ID' => intval($itemId), 'SCHEME' => $identifier['scheme'], 'ISSUER' => $identifier['issuer'], 'ISSUER_NORMALIZED' => $identifier['issuer_normalized'], 'VALUE' => $identifier['value'], 'VALUE_NORMALIZED' => $identifier['value_normalized'], 'CREATED_BY' => intval($userId));
        // Bdo_Db_Line maps an explicitly empty string to NULL. Let the table
        // default provide the required empty issuer for global identifiers.
        if ($data['ISSUER_NORMALIZED'] === '') unset($data['ISSUER_NORMALIZED']);
        return (new self())->persist($data);
    }

    public function replaceForItem($itemId, array $identifiers, $userId)
    {
        $this->executeQuery('DELETE FROM parabd_identifier WHERE ITEM_ID=' . intval($itemId));
        foreach ($identifiers as $identifier) $this->addForItem($itemId, $identifier, $userId);
    }

    public function moveToItem($sourceId, $targetId)
    {
        $this->executeQuery('UPDATE parabd_identifier SET ITEM_ID=' . intval($targetId) . ' WHERE ITEM_ID=' . intval($sourceId));
    }
}
