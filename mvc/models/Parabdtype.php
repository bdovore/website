<?php

require_once __DIR__ . '/ParabdDbLine.php';

class Parabdtype extends ParabdDbLine
{
    public $table_name = 'parabd_type';

    public function __construct($id = null)
    {
        parent::__construct($this->table_name, is_array($id) ? $id : array('ID_TYPE' => $id));
    }

    public function active()
    {
        return $this->fetchAllQuery("SELECT * FROM parabd_type WHERE IS_ACTIVE=1 ORDER BY PARENT_ID IS NOT NULL,SORT_ORDER,LABEL");
    }

    public function resolveCodes($typeCode, $subtypeCode)
    {
        $type = $this->fetchOneQuery("SELECT * FROM parabd_type WHERE CODE='" . $this->escape(strtoupper($typeCode)) . "' AND PARENT_ID IS NULL AND IS_ACTIVE=1");
        if (!$type) throw new ParabdException('VALIDATION_ERROR', 'Type Para-BD invalide.', array('type_code' => 'Type invalide.'));
        $subtype = null;
        if ($subtypeCode !== '') $subtype = $this->fetchOneQuery("SELECT * FROM parabd_type WHERE CODE='" . $this->escape(strtoupper($subtypeCode)) . "' AND PARENT_ID=" . intval($type['ID_TYPE']) . " AND IS_ACTIVE=1");
        if (intval($type['IS_REQUIRED_SUBTYPE']) && !$subtype) throw new ParabdException('VALIDATION_ERROR', 'Le sous-type est obligatoire.', array('subtype_code' => 'Sous-type obligatoire.'));
        if ($subtypeCode !== '' && !$subtype) throw new ParabdException('VALIDATION_ERROR', 'Sous-type Para-BD invalide.', array('subtype_code' => 'Sous-type invalide.'));
        return array(intval($type['ID_TYPE']), $subtype ? intval($subtype['ID_TYPE']) : null);
    }
}

