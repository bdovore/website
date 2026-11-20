<?php

class BrowserCatalogue
{
    const TYPES = ['ser' => 'Séries', 'alb' => 'Albums', 'aut' => 'Auteurs', 'edit' => 'Éditeurs'];
    const ROLES = ['ID_SCENAR', 'ID_SCENAR_ALT', 'ID_DESSIN', 'ID_DESSIN_ALT', 'ID_COLOR', 'ID_COLOR_ALT'];

    private const TABLES = [
        'ser' => ['bd_serie', 'ID_SERIE', 'NOM'],
        'alb' => ['bd_tome', 'ID_TOME', 'TITRE'],
        'aut' => ['bd_auteur', 'ID_AUTEUR', 'PSEUDO'],
        'edit' => ['bd_editeur', 'ID_EDITEUR', 'NOM'],
    ];

    private function rows(string $sql, array $params = []): array
    {
        $statement = Bdo_Cfg::getVar('connexion')->prepare($sql);
        $statement->execute($params);
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function cover(): string
    {
        return Bdo_Cfg::getVar('explicit') ? 'e.IMG_COUV' :
            "IF(e.FLG_EXPLICIT, CONCAT('?source=', e.IMG_COUV), e.IMG_COUV)";
    }

    // Hydrate only the page of IDs selected by the search or branch query.
    private function hydrate(string $type, array $hits): array
    {
        if (!$hits || !in_array($type, ['alb', 'ser'], true)) return $hits;
        $ids = implode(',', array_map('intval', array_column($hits, 'id')));
        $cover = $this->cover();
        if ($type === 'alb') {
            $sql = "SELECT t.ID_TOME id, t.NUM_TOME number, s.NOM subtitle, $cover cover
                FROM bd_tome t JOIN bd_serie s USING (ID_SERIE)
                LEFT JOIN bd_edition e ON e.ID_EDITION=t.ID_EDITION WHERE t.ID_TOME IN ($ids)";
        } else {
            $sql = "SELECT s.ID_SERIE id, g.LIBELLE subtitle,
                (SELECT $cover FROM bd_tome t JOIN bd_edition e ON e.ID_EDITION=t.ID_EDITION
                 WHERE t.ID_SERIE=s.ID_SERIE AND e.IMG_COUV <> '' ORDER BY t.NUM_TOME,t.ID_TOME LIMIT 1) cover
                FROM bd_serie s LEFT JOIN bd_genre g USING (ID_GENRE) WHERE s.ID_SERIE IN ($ids)";
        }
        $details = array_column($this->rows($sql), null, 'id');
        foreach ($hits as &$hit) $hit += $details[$hit['id']] ?? [];
        return $hits;
    }

    private function genrePredicate(string $type, string $genre): string
    {
        if ($type === 'aut') {
            $roles = [];
            foreach (self::ROLES as $role) {
                $roles[] = "EXISTS (SELECT 1 FROM bd_tome t JOIN bd_serie s USING (ID_SERIE)
                    WHERE t.$role=x.ID_AUTEUR AND s.ID_GENRE $genre)";
            }
            return '(' . implode(' OR ', $roles) . ')';
        }
        return match ($type) {
            'edit' => "EXISTS (SELECT 1 FROM bd_edition_stat st WHERE st.ID_EDITEUR=x.ID_EDITEUR AND st.ID_GENRE $genre)",
            'alb' => "EXISTS (SELECT 1 FROM bd_serie s WHERE s.ID_SERIE=x.ID_SERIE AND s.ID_GENRE $genre)",
            default => "x.ID_GENRE $genre",
        };
    }

    public function page(string $type, string $query, array $genres, string $parent, int $id,
        int $publisher, int $offset, int $size): array
    {
        $params = [];
        $limit = ' LIMIT ' . $offset . ',' . ($size + 1);
        $genre = $genres ? ' IN (' . implode(',', array_map('intval', $genres)) . ')' : '';
        $order = 'label, id';
        if ($type === 'col') {
            $sql = "SELECT st.ID_COLLECTION id, COALESCE(NULLIF(NULLIF(c.NOM,'<N/A>'),''),'Sans collection') label
                FROM bd_edition_stat st LEFT JOIN bd_collection c USING (ID_COLLECTION)
                WHERE st.ID_EDITEUR=?" . ($genre ? " AND st.ID_GENRE $genre" : '') . ' GROUP BY st.ID_COLLECTION,c.NOM';
            $params[] = $id;
        } else {
            [$table,$key,$label] = self::TABLES[$type];
            $where = $type === 'aut' ? ['x.ID_AUTEUR NOT IN (2,3,5572,8497,6086,6243)'] : [];
            if ($parent === 'ser') {
                $where[] = 'x.ID_SERIE=?';
                $params[] = $id;
                $order = 'x.NUM_TOME, id';
            } elseif ($parent === 'aut') {
                $parts = [];
                foreach (self::ROLES as $role) {
                    $parts[] = "SELECT ID_SERIE FROM bd_tome WHERE $role=?";
                    $params[] = $id;
                }
                $where[] = 'x.ID_SERIE IN (SELECT ID_SERIE FROM (' . implode(' UNION ', $parts) . ') credits)';
            } elseif ($parent === 'col') {
                $where[] = 'x.ID_SERIE IN (SELECT ID_SERIE FROM bd_edition_stat WHERE ID_EDITEUR=? AND ID_COLLECTION=?)';
                array_push($params, $publisher, $id);
            }
            if ($genre) $where[] = $this->genrePredicate($type, $genre);
            if ($query !== '') {
                $prefix = strtr($query, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
                if (in_array($type, ['alb', 'ser'], true) && mb_strlen($query) >= 4) {
                    // Separate index probes: combining MATCH and LIKE with OR scans the catalogue.
                    $matches = "SELECT $key FROM $table WHERE $label LIKE ? ESCAPE '!'";
                    $params[] = $prefix;
                    preg_match_all('/[\p{L}\p{N}]+/u', $query, $tokens);
                    if ($tokens[0]) {
                        $matches .= " UNION SELECT $key FROM $table WHERE MATCH($label) AGAINST (? IN BOOLEAN MODE)";
                        $params[] = '+' . implode('* +', $tokens[0]) . '*';
                        // A required prefix such as +le* can miss the unindexed article "le".
                        // Phrase matching lets the full-text parser handle short words and stopwords.
                        if (count($tokens[0]) > 1) {
                            $matches .= " UNION SELECT $key FROM $table WHERE MATCH($label) AGAINST (? IN BOOLEAN MODE)";
                            $params[] = '"' . implode(' ', $tokens[0]) . '"';
                        }
                    }
                    $where[] = "x.$key IN (SELECT $key FROM ($matches) hits)";
                } elseif ($type === 'aut') {
                    $where[] = "(x.PSEUDO LIKE ? ESCAPE '!' OR x.NOM LIKE ? ESCAPE '!' OR
                        (x.PRENOM LIKE ? ESCAPE '!' AND CONCAT_WS(' ',x.PRENOM,x.NOM) LIKE ? ESCAPE '!'))";
                    array_push($params, $prefix, $prefix, explode(' ', $prefix)[0] . '%', $prefix);
                } else {
                    $where[] = "x.$label LIKE ? ESCAPE '!'";
                    $params[] = $prefix;
                }
                if ($type !== 'aut' && mb_strlen($query) < 4) {
                    // A short prefix is already ordered with exact matches first.
                    $order = "x.$label";
                } else {
                    $order = "(x.$label = ?) DESC, (x.$label LIKE ? ESCAPE '!') DESC, label, id";
                    array_push($params, $query, $prefix);
                }
            }
            $sql = "SELECT x.$key id, x.$label label FROM $table x";
            // Walk the name index until the page is full. A filesort would
            // evaluate every correlated genre check before applying LIMIT.
            if ($parent === '' && $query === '') {
                $sql .= " FORCE INDEX ($label)";
                $order = "x.$label";
            }
            if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $hits = $this->rows("$sql ORDER BY $order $limit", $params);
        $more = count($hits) > $size;
        if ($more) array_pop($hits);
        return ['rows' => $this->hydrate($type, $hits), 'more' => $more];
    }
}
