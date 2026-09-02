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

    private function catalogueWhere($search, $filters)
    {
        $where = "i.STATUS='ACTIVE'";
        $filters = is_array($filters) ? $filters : array();
        $typeId = intval(isset($filters['type_id']) ? $filters['type_id'] : 0);
        $authorId = intval(isset($filters['author_id']) ? $filters['author_id'] : 0);
        $seriesId = intval(isset($filters['series_id']) ? $filters['series_id'] : 0);
        $tomeId = intval(isset($filters['tome_id']) ? $filters['tome_id'] : 0);
        $manufacturer = trim((string) (isset($filters['manufacturer']) ? $filters['manufacturer'] : ''));
        $publisher = trim((string) (isset($filters['publisher']) ? $filters['publisher'] : ''));
        if ($typeId) $where .= " AND i.TYPE_ID=$typeId";
        if ($authorId) $where .= " AND EXISTS (SELECT 1 FROM parabd_item_author ia WHERE ia.ITEM_ID=i.ID_ITEM AND ia.AUTHOR_ID=$authorId)";
        if ($seriesId) $where .= " AND EXISTS (SELECT 1 FROM parabd_item_series isa WHERE isa.ITEM_ID=i.ID_ITEM AND isa.SERIES_ID=$seriesId)";
        if ($tomeId) $where .= " AND EXISTS (SELECT 1 FROM parabd_item_tome it WHERE it.ITEM_ID=i.ID_ITEM AND it.TOME_ID=$tomeId)";
        if ($manufacturer !== '') $where .= " AND i.MANUFACTURER_NORMALIZED='" . $this->escape(ParabdRules::normalizeText($manufacturer)) . "'";
        if ($publisher !== '') $where .= " AND i.PUBLISHER='" . $this->escape($publisher) . "'";
        if (trim($search) !== '') {
            $raw = $this->escape(trim($search)); $normalized = $this->escape(ParabdRules::normalizeText($search));
            $where .= " AND (i.TITLE_NORMALIZED LIKE '%$normalized%' OR i.MANUFACTURER_NORMALIZED LIKE '%$normalized%' OR i.PUBLISHER LIKE '%$raw%'
                OR EXISTS (SELECT 1 FROM parabd_item_author ia JOIN bd_auteur a ON a.ID_AUTEUR=ia.AUTHOR_ID WHERE ia.ITEM_ID=i.ID_ITEM AND (a.PSEUDO LIKE '%$raw%' OR a.NOM LIKE '%$raw%'))
                OR EXISTS (SELECT 1 FROM parabd_item_series isa JOIN bd_serie s ON s.ID_SERIE=isa.SERIES_ID WHERE isa.ITEM_ID=i.ID_ITEM AND s.NOM LIKE '%$raw%')
                OR EXISTS (SELECT 1 FROM parabd_item_tome it JOIN bd_tome bt ON bt.ID_TOME=it.TOME_ID WHERE it.ITEM_ID=i.ID_ITEM AND bt.TITRE LIKE '%$raw%'))";
        }
        return $where;
    }

    public function catalogue($search = '', $filters = array(), $page = 1, $perPage = 20)
    {
        $where = $this->catalogueWhere($search, $filters);
        $mediaPath = $this->mediaPathSql();
        $page = max(1, intval($page)); $perPage = max(1, min(100, intval($perPage)));
        $offset = ($page - 1) * $perPage;
        return $this->fetchAllQuery("SELECT i.*,t.LABEL TYPE_LABEL,st.LABEL SUBTYPE_LABEL,$mediaPath PRIMARY_IMAGE,m.IS_EXPLICIT PRIMARY_IMAGE_IS_EXPLICIT
            FROM parabd_item i JOIN parabd_type t ON t.ID_TYPE=i.TYPE_ID LEFT JOIN parabd_type st ON st.ID_TYPE=i.SUBTYPE_ID
            LEFT JOIN parabd_media m ON m.ITEM_ID=i.ID_ITEM AND m.IS_PRIMARY=1 AND m.IS_HIDDEN=0
            WHERE $where ORDER BY i.UPDATED_AT DESC LIMIT $perPage OFFSET $offset");
    }

    public function countCatalogue($search = '', $filters = array())
    {
        $where = $this->catalogueWhere($search, $filters);
        return intval($this->fetchOneQuery("SELECT COUNT(*) n FROM parabd_item i JOIN parabd_type t ON t.ID_TYPE=i.TYPE_ID LEFT JOIN parabd_type st ON st.ID_TYPE=i.SUBTYPE_ID
            WHERE $where")['n']);
    }

    public function recentByType($typeId, $limit = 5)
    {
        $typeId = intval($typeId);
        if (!$typeId) return array();
        $mediaPath = $this->mediaPathSql();
        $limit = max(1, min(60, intval($limit)));
        return $this->fetchAllQuery("SELECT i.*,t.LABEL TYPE_LABEL,st.LABEL SUBTYPE_LABEL,$mediaPath PRIMARY_IMAGE,m.IS_EXPLICIT PRIMARY_IMAGE_IS_EXPLICIT
            FROM parabd_item i JOIN parabd_type t ON t.ID_TYPE=i.TYPE_ID LEFT JOIN parabd_type st ON st.ID_TYPE=i.SUBTYPE_ID
            LEFT JOIN parabd_media m ON m.ITEM_ID=i.ID_ITEM AND m.IS_PRIMARY=1 AND m.IS_HIDDEN=0
            WHERE i.STATUS='ACTIVE' AND i.TYPE_ID=$typeId ORDER BY i.CREATED_AT DESC LIMIT $limit");
    }

    public function adminCatalogue($search = '', $status = '', $sort = 'updated', $dir = 'DESC', $limit = 100)
    {
        $where = '1=1'; $search = ltrim(trim((string) $search), '#'); $status = strtoupper(trim((string) $status));
        if ($search !== '') {
            if (ctype_digit($search)) $where .= ' AND (i.ID_ITEM=' . intval($search) . " OR i.TITLE LIKE '%" . $this->escape($search) . "%')";
            else $where .= " AND (i.TITLE_NORMALIZED LIKE '%" . $this->escape(ParabdRules::normalizeText($search)) . "%' OR i.MANUFACTURER LIKE '%" . $this->escape($search) . "%' OR i.PUBLISHER LIKE '%" . $this->escape($search) . "%')";
        }
        if (in_array($status, array('ACTIVE','HIDDEN','MERGED'), true)) $where .= " AND i.STATUS='$status'";
        $sortMap = array('id'=>'i.ID_ITEM','title'=>'i.TITLE','status'=>'i.STATUS','copies'=>'COPY_COUNT','history'=>'HISTORY_COUNT','updated'=>'i.UPDATED_AT');
        $orderCol = isset($sortMap[$sort]) ? $sortMap[$sort] : 'i.UPDATED_AT';
        $orderDir = (strtoupper($dir) === 'ASC') ? 'ASC' : 'DESC';
        $cap = max(1, min(100, intval($limit)));
        return $this->fetchAllQuery("SELECT i.*,t.LABEL TYPE_LABEL,st.LABEL SUBTYPE_LABEL,
            (SELECT COUNT(*) FROM users_parabd up WHERE up.ITEM_ID=i.ID_ITEM) COPY_COUNT,
            (SELECT COUNT(*) FROM parabd_revision r WHERE r.ITEM_ID=i.ID_ITEM) HISTORY_COUNT
            FROM parabd_item i JOIN parabd_type t ON t.ID_TYPE=i.TYPE_ID LEFT JOIN parabd_type st ON st.ID_TYPE=i.SUBTYPE_ID
            WHERE $where ORDER BY $orderCol $orderDir LIMIT $cap");
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
            $result[] = array('category' => 'Artistes', 'type' => 'author', 'id' => intval($row['id']), 'label' => $row['label']);
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

    public function autocompleteField($field, $term, $limit = 15)
    {
        $columns = array('manufacturer' => 'MANUFACTURER', 'publisher' => 'PUBLISHER', 'material' => 'MATERIAL', 'color' => 'COLOR', 'universe' => 'UNIVERSE_NAME');
        if (!isset($columns[$field])) return array();
        $term = trim((string) $term);
        $normalizedTerm = ParabdRules::normalizeText($term);
        $limit = max(1, min(30, intval($limit)));
        $suggestions = array();

        if ($field === 'material') {
            foreach (array('Bois', 'Carton', 'Céramique', 'Cuir', 'Métal', 'Papier', 'Plastique', 'Plâtre', 'Porcelaine', 'Résine', 'Textile', 'Verre', 'Vinyle') as $material) {
                if ($normalizedTerm === '' || strpos(ParabdRules::normalizeText($material), $normalizedTerm) !== false) $suggestions[] = $material;
            }
        }
        if ($field === 'color') {
            foreach (array('Polychrome', 'Monochrome', 'Noir & blanc') as $color) {
                if ($normalizedTerm === '' || strpos(ParabdRules::normalizeText($color), $normalizedTerm) !== false) $suggestions[] = $color;
            }
        }

        $column = $columns[$field];
        $like = $this->escape($term);
        $where = "STATUS='ACTIVE' AND $column IS NOT NULL AND $column<>''";
        if ($term !== '') $where .= " AND $column LIKE '%$like%'";
        foreach ($this->fetchAllQuery("SELECT DISTINCT $column label FROM parabd_item WHERE $where ORDER BY ($column='$like') DESC,($column LIKE '$like%') DESC,$column LIMIT $limit") as $row) {
            $suggestions[] = $row['label'];
        }

        if ($field === 'manufacturer' || $field === 'publisher') {
            $publisherWhere = "NOM IS NOT NULL AND NOM<>''";
            if ($term !== '') $publisherWhere .= " AND NOM LIKE '%$like%'";
            foreach ($this->fetchAllQuery("SELECT DISTINCT NOM label FROM bd_editeur WHERE $publisherWhere ORDER BY (NOM='$like') DESC,(NOM LIKE '$like%') DESC,NOM LIMIT $limit") as $row) {
                $suggestions[] = $row['label'];
            }
        }

        $result = array(); $seen = array();
        foreach ($suggestions as $label) {
            $key = ParabdRules::normalizeText($label);
            if ($key === '' || isset($seen[$key])) continue;
            $seen[$key] = true; $result[] = array('label' => $label);
            if (count($result) >= $limit) break;
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

    public function rowForUpdate($itemId)
    {
        return $this->fetchOneQuery('SELECT * FROM parabd_item WHERE ID_ITEM=' . intval($itemId) . ' FOR UPDATE');
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
        foreach (array(
            array('AUTHOR_ID', 'AUTHOR_IDS', 'parabd_item_author'),
            array('SERIES_ID', 'SERIES_IDS', 'parabd_item_series'),
            array('TOME_ID', 'TOME_IDS', 'parabd_item_tome')
        ) as $relation) {
            $values = !empty($relations[$relation[1]]) && is_array($relations[$relation[1]]) ? $relations[$relation[1]] : array();
            if (!empty($relations[$relation[0]])) $values[] = $relations[$relation[0]];
            foreach (array_unique(array_map('intval', $values)) as $value) {
                if ($value) $clauses[] = 'ID_ITEM IN (SELECT ITEM_ID FROM ' . $relation[2] . ' WHERE ' . $relation[0] . '=' . $value . ')';
            }
        }
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
