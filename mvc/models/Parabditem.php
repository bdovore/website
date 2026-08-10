<?php

require_once __DIR__ . '/ParabdDbLine.php';
require_once __DIR__ . '/ParabdRules.php';

class Parabditem extends ParabdDbLine
{
    public $table_name = 'parabd_item';

    public function __construct($id = null)
    {
        parent::__construct($this->table_name, is_array($id) ? $id : array('ID_ITEM' => $id));
    }

    private function mediaPathSql($mediaAlias = 'm')
    {
        if (Bdo_Cfg::getVar('explicit')) return $mediaAlias . '.FILE_PATH';
        return "IF($mediaAlias.IS_EXPLICIT=1,CONCAT('?source=',$mediaAlias.FILE_PATH),$mediaAlias.FILE_PATH)";
    }

    public function catalogue($search = '', $filterType = '', $filterId = 0, $filterValue = '', $limit = 60)
    {
        $where = "i.STATUS='ACTIVE'";
        $filterType = strtolower(trim((string) $filterType)); $filterId = intval($filterId); $filterValue = trim((string) $filterValue);
        if ($filterType === 'author' && $filterId) $where .= " AND EXISTS (SELECT 1 FROM parabd_item_author ia WHERE ia.ITEM_ID=i.ID_ITEM AND ia.AUTHOR_ID=$filterId)";
        elseif ($filterType === 'series' && $filterId) $where .= " AND EXISTS (SELECT 1 FROM parabd_item_series isa WHERE isa.ITEM_ID=i.ID_ITEM AND isa.SERIES_ID=$filterId)";
        elseif ($filterType === 'tome' && $filterId) $where .= " AND EXISTS (SELECT 1 FROM parabd_item_tome it WHERE it.ITEM_ID=i.ID_ITEM AND it.TOME_ID=$filterId)";
        elseif ($filterType === 'manufacturer' && $filterValue !== '') $where .= " AND i.MANUFACTURER_NORMALIZED='" . $this->escape(ParabdRules::normalizeText($filterValue)) . "'";
        elseif ($filterType === 'publisher' && $filterValue !== '') $where .= " AND i.PUBLISHER='" . $this->escape($filterValue) . "'";
        elseif (trim($search) !== '') {
            $raw = $this->escape(trim($search)); $normalized = $this->escape(ParabdRules::normalizeText($search));
            $where .= " AND (i.TITLE_NORMALIZED LIKE '%$normalized%' OR i.MANUFACTURER_NORMALIZED LIKE '%$normalized%' OR i.PUBLISHER LIKE '%$raw%'
                OR EXISTS (SELECT 1 FROM parabd_item_author ia JOIN bd_auteur a ON a.ID_AUTEUR=ia.AUTHOR_ID WHERE ia.ITEM_ID=i.ID_ITEM AND (a.PSEUDO LIKE '%$raw%' OR a.NOM LIKE '%$raw%'))
                OR EXISTS (SELECT 1 FROM parabd_item_series isa JOIN bd_serie s ON s.ID_SERIE=isa.SERIES_ID WHERE isa.ITEM_ID=i.ID_ITEM AND s.NOM LIKE '%$raw%')
                OR EXISTS (SELECT 1 FROM parabd_item_tome it JOIN bd_tome bt ON bt.ID_TOME=it.TOME_ID WHERE it.ITEM_ID=i.ID_ITEM AND bt.TITRE LIKE '%$raw%'))";
        }
        $mediaPath = $this->mediaPathSql();
        return $this->fetchAllQuery("SELECT i.*,t.LABEL TYPE_LABEL,st.LABEL SUBTYPE_LABEL,$mediaPath PRIMARY_IMAGE,m.IS_EXPLICIT PRIMARY_IMAGE_IS_EXPLICIT
            FROM parabd_item i JOIN parabd_type t ON t.ID_TYPE=i.TYPE_ID LEFT JOIN parabd_type st ON st.ID_TYPE=i.SUBTYPE_ID
            LEFT JOIN parabd_media m ON m.ITEM_ID=i.ID_ITEM AND m.IS_PRIMARY=1 AND m.IS_HIDDEN=0
            WHERE $where ORDER BY i.UPDATED_AT DESC LIMIT " . max(1, min(200, intval($limit))));
    }

    public function adminCatalogue($search = '', $status = '', $limit = 200)
    {
        $where = '1=1'; $search = trim((string) $search); $status = strtoupper(trim((string) $status));
        if ($search !== '') {
            if (ctype_digit($search)) $where .= ' AND (i.ID_ITEM=' . intval($search) . " OR i.TITLE LIKE '%" . $this->escape($search) . "%')";
            else $where .= " AND (i.TITLE_NORMALIZED LIKE '%" . $this->escape(ParabdRules::normalizeText($search)) . "%' OR i.MANUFACTURER LIKE '%" . $this->escape($search) . "%' OR i.PUBLISHER LIKE '%" . $this->escape($search) . "%')";
        }
        if (in_array($status, array('ACTIVE','HIDDEN','MERGED'), true)) $where .= " AND i.STATUS='$status'";
        return $this->fetchAllQuery("SELECT i.*,t.LABEL TYPE_LABEL,st.LABEL SUBTYPE_LABEL,
            (SELECT COUNT(*) FROM users_parabd up WHERE up.ITEM_ID=i.ID_ITEM) COPY_COUNT,
            (SELECT COUNT(*) FROM parabd_revision r WHERE r.ITEM_ID=i.ID_ITEM) HISTORY_COUNT
            FROM parabd_item i JOIN parabd_type t ON t.ID_TYPE=i.TYPE_ID LEFT JOIN parabd_type st ON st.ID_TYPE=i.SUBTYPE_ID
            WHERE $where ORDER BY i.UPDATED_AT DESC LIMIT " . max(1, min(500, intval($limit))));
    }

    public function autocomplete($term, $limitPerCategory = 6)
    {
        $term = trim((string) $term);
        if (mb_strlen($term, 'UTF-8') < 2) return array();
        $like = $this->escape($term);
        $normalized = $this->escape(ParabdRules::normalizeText($term));
        $limit = max(1, min(10, intval($limitPerCategory)));
        $result = array();

        foreach ($this->fetchAllQuery("SELECT ID_AUTEUR id,COALESCE(NULLIF(PSEUDO,''),TRIM(CONCAT_WS(' ',PRENOM,NOM))) label FROM bd_auteur WHERE PSEUDO LIKE '%$like%' OR NOM LIKE '%$like%' OR PRENOM LIKE '%$like%' ORDER BY (PSEUDO='$like') DESC,PSEUDO LIMIT $limit") as $row) {
            $result[] = array('category' => 'Auteurs', 'type' => 'author', 'id' => intval($row['id']), 'label' => $row['label']);
        }
        foreach ($this->fetchAllQuery("SELECT ID_SERIE id,NOM label FROM bd_serie WHERE NOM LIKE '%$like%' ORDER BY (NOM='$like') DESC,NOM LIMIT $limit") as $row) {
            $result[] = array('category' => 'Séries', 'type' => 'series', 'id' => intval($row['id']), 'label' => $row['label']);
        }
        foreach ($this->fetchAllQuery("SELECT t.ID_TOME id,t.TITRE label,s.NOM series_label FROM bd_tome t LEFT JOIN bd_serie s ON s.ID_SERIE=t.ID_SERIE WHERE t.TITRE LIKE '%$like%' ORDER BY (t.TITRE='$like') DESC,t.TITRE LIMIT $limit") as $row) {
            $result[] = array('category' => 'Albums', 'type' => 'tome', 'id' => intval($row['id']), 'label' => $row['label'] . (!empty($row['series_label']) ? ' — ' . $row['series_label'] : ''));
        }
        foreach ($this->fetchAllQuery("SELECT DISTINCT MANUFACTURER label FROM parabd_item WHERE STATUS='ACTIVE' AND MANUFACTURER_NORMALIZED LIKE '%$normalized%' AND MANUFACTURER IS NOT NULL AND MANUFACTURER<>'' ORDER BY MANUFACTURER LIMIT $limit") as $row) {
            $result[] = array('category' => 'Fabricants', 'type' => 'manufacturer', 'id' => 0, 'label' => $row['label']);
        }
        foreach ($this->fetchAllQuery("SELECT DISTINCT PUBLISHER label FROM parabd_item WHERE STATUS='ACTIVE' AND PUBLISHER LIKE '%$like%' AND PUBLISHER IS NOT NULL AND PUBLISHER<>'' ORDER BY PUBLISHER LIMIT $limit") as $row) {
            $result[] = array('category' => 'Éditeurs', 'type' => 'publisher', 'id' => 0, 'label' => $row['label']);
        }
        return $result;
    }

    public function findBase($itemId, $includeHidden = false, $lock = false)
    {
        return $this->fetchOneQuery("SELECT i.*,t.CODE TYPE_CODE,t.LABEL TYPE_LABEL,st.CODE SUBTYPE_CODE,st.LABEL SUBTYPE_LABEL
            FROM parabd_item i JOIN parabd_type t ON t.ID_TYPE=i.TYPE_ID LEFT JOIN parabd_type st ON st.ID_TYPE=i.SUBTYPE_ID
            WHERE i.ID_ITEM=" . intval($itemId) . ($includeHidden ? '' : " AND i.STATUS IN ('ACTIVE','MERGED')") . ($lock ? ' FOR UPDATE' : ''));
    }

    public function findActive($itemId, $lock = false)
    {
        return $this->fetchOneQuery('SELECT * FROM parabd_item WHERE ID_ITEM=' . intval($itemId) . " AND STATUS='ACTIVE'" . ($lock ? ' FOR UPDATE' : ''));
    }

    public function lockPair($firstId, $secondId)
    {
        $low = min(intval($firstId), intval($secondId));
        $high = max(intval($firstId), intval($secondId));
        return $this->fetchAllQuery("SELECT * FROM parabd_item WHERE ID_ITEM IN ($low,$high) FOR UPDATE");
    }

    public function revisionNoForUpdate($itemId)
    {
        return $this->fetchOneQuery('SELECT REVISION_NO FROM parabd_item WHERE ID_ITEM=' . intval($itemId) . ' FOR UPDATE');
    }

    public function snapshotFields($itemId)
    {
        return $this->fetchOneQuery('SELECT TYPE_ID,SUBTYPE_ID,TITLE,DESCRIPTION,MATERIAL,COLOR,WIDTH_MM,HEIGHT_MM,DEPTH_MM,WEIGHT_G,SCALE,RELEASE_DATE,DATE_PRECISION,PRINT_RUN,IS_NUMBERED,IS_SIGNED,HAS_CERTIFICATE,IS_LIMITED,MANUFACTURER,PUBLISHER,LICENSE_NAME,RANGE_NAME,UNIVERSE_NAME,STATUS,MERGED_INTO_ID FROM parabd_item WHERE ID_ITEM=' . intval($itemId));
    }

    public function updateFields($itemId, array $data, $userId, $incrementRevision = false)
    {
        $allowed = array('TYPE_ID','SUBTYPE_ID','TITLE','TITLE_NORMALIZED','DESCRIPTION','MATERIAL','COLOR','WIDTH_MM','HEIGHT_MM','DEPTH_MM','WEIGHT_G','SCALE','RELEASE_DATE','DATE_PRECISION','PRINT_RUN','IS_NUMBERED','IS_SIGNED','HAS_CERTIFICATE','IS_LIMITED','MANUFACTURER','MANUFACTURER_NORMALIZED','PUBLISHER','LICENSE_NAME','RANGE_NAME','UNIVERSE_NAME','STATUS','MERGED_INTO_ID');
        $sets = array();
        foreach ($data as $field => $value) {
            if (!in_array($field, $allowed, true)) throw new RuntimeException('Champ Para-BD non modifiable.');
            $sets[] = "`$field`=" . (($value === '' && in_array($field, array('TITLE_NORMALIZED','MANUFACTURER_NORMALIZED'), true)) ? "''" : $this->sqlValue($value));
        }
        if ($incrementRevision) $sets[] = 'REVISION_NO=REVISION_NO+1';
        $sets[] = 'UPDATED_BY=' . intval($userId);
        if (!$sets) return;
        $this->executeQuery('UPDATE parabd_item SET ' . implode(',', $sets) . ' WHERE ID_ITEM=' . intval($itemId));
    }

    public function incrementRevision($itemId, $userId)
    {
        $this->executeQuery('UPDATE parabd_item SET REVISION_NO=REVISION_NO+1,UPDATED_BY=' . intval($userId) . ' WHERE ID_ITEM=' . intval($itemId));
    }

    public function moderate($itemId, $status, $userId)
    {
        $this->executeQuery("UPDATE parabd_item SET STATUS='" . $this->escape($status) . "',UPDATED_BY=" . intval($userId) . ',REVISION_NO=REVISION_NO+1 WHERE ID_ITEM=' . intval($itemId));
        return Db_affected_rows($this->connection()) === 1;
    }

    public function markMerged($sourceId, $targetId, $userId)
    {
        $this->executeQuery("UPDATE parabd_item SET STATUS='MERGED',MERGED_INTO_ID=" . intval($targetId) . ',UPDATED_BY=' . intval($userId) . ',REVISION_NO=REVISION_NO+1 WHERE ID_ITEM=' . intval($sourceId));
        $this->incrementRevision($targetId, $userId);
    }

    public function candidateRows($normalizedTitle, array $relations, $typeId)
    {
        $words = array_values(array_filter(explode(' ', $normalizedTitle), function ($word) { return strlen($word) >= 3; }));
        usort($words, function ($left, $right) { return strlen($right) <=> strlen($left); });
        $where = "STATUS='ACTIVE'"; $clauses = array();
        foreach (array_slice($words, 0, 3) as $word) $clauses[] = "TITLE_NORMALIZED LIKE '%" . $this->escape($word) . "%'";
        if (!empty($relations['AUTHOR_ID'])) $clauses[] = 'ID_ITEM IN (SELECT ITEM_ID FROM parabd_item_author WHERE AUTHOR_ID=' . intval($relations['AUTHOR_ID']) . ')';
        if (!empty($relations['SERIES_ID'])) $clauses[] = 'ID_ITEM IN (SELECT ITEM_ID FROM parabd_item_series WHERE SERIES_ID=' . intval($relations['SERIES_ID']) . ')';
        if (!empty($relations['TOME_ID'])) $clauses[] = 'ID_ITEM IN (SELECT ITEM_ID FROM parabd_item_tome WHERE TOME_ID=' . intval($relations['TOME_ID']) . ')';
        if ($clauses) $where .= ' AND (' . implode(' OR ', $clauses) . ')';
        elseif ($typeId) $where .= ' AND TYPE_ID=' . intval($typeId);
        return $this->fetchAllQuery("SELECT * FROM parabd_item WHERE $where ORDER BY UPDATED_AT DESC LIMIT 200");
    }

    public function hasRelation($itemId, $table, $column, $value)
    {
        $allowed = array('parabd_item_author' => 'AUTHOR_ID', 'parabd_item_series' => 'SERIES_ID', 'parabd_item_tome' => 'TOME_ID');
        if (!isset($allowed[$table]) || $allowed[$table] !== $column) return false;
        return (bool) $this->fetchOneQuery("SELECT 1 found FROM $table WHERE ITEM_ID=" . intval($itemId) . " AND $column=" . intval($value) . ' LIMIT 1');
    }

    public function createLine(array $data, $userId)
    {
        if (isset($data['MANUFACTURER_NORMALIZED']) && $data['MANUFACTURER_NORMALIZED'] === '') unset($data['MANUFACTURER_NORMALIZED']);
        $data['CREATED_BY'] = intval($userId); $data['UPDATED_BY'] = intval($userId);
        return $this->persist($data);
    }

    public function incompleteQueue()
    {
        return $this->fetchAllQuery("SELECT i.* FROM parabd_item i LEFT JOIN parabd_media m ON m.ITEM_ID=i.ID_ITEM AND m.IS_PRIMARY=1
            WHERE i.STATUS='ACTIVE' AND (i.DESCRIPTION IS NULL OR i.DESCRIPTION='' OR m.ID_MEDIA IS NULL) GROUP BY i.ID_ITEM ORDER BY i.UPDATED_AT");
    }

    public function hiddenQueue()
    {
        return $this->fetchAllQuery("SELECT * FROM parabd_item WHERE STATUS='HIDDEN' ORDER BY UPDATED_AT DESC");
    }
}
