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
            $sql = "SELECT t.ID_TOME id, t.NUM_TOME number, t.ID_SERIE, e.ID_EDITION, s.NOM subtitle, $cover cover
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

    private function refreshTrendingSeries(string $file): array
    {
        $rows = $this->rows("SELECT s.ID_SERIE id, s.NOM label
            FROM users_album ua JOIN bd_edition e ON e.ID_EDITION=ua.id_edition
            JOIN bd_tome t ON t.ID_TOME=e.ID_TOME JOIN bd_serie s USING (ID_SERIE)
            WHERE ua.flg_achat='N' AND ua.date_ajout BETWEEN NOW() - INTERVAL 30 DAY AND NOW()
            GROUP BY s.ID_SERIE,s.NOM
            ORDER BY COUNT(DISTINCT ua.user_id) DESC, MAX(ua.date_ajout) DESC, s.ID_SERIE
            LIMIT 10");
        $json = json_encode($rows, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
        $tmp = tempnam(dirname($file), '.series-tendance-');
        if ($tmp !== false) {
            try {
                if (file_put_contents($tmp, $json) === strlen($json)) rename($tmp, $file);
            } finally {
                if (file_exists($tmp)) unlink($tmp);
            }
        }
        return $rows;
    }

    // Cache only catalogue identities. Cover visibility is resolved for each reader.
    public function trendingSeries(): array
    {
        $file = BDO_DIR . 'cache/series-tendance.json';
        $read = static function () use ($file) {
            clearstatcache(true, $file);
            $rows = json_decode((string) @file_get_contents($file), true);
            return is_array($rows) && array_is_list($rows) ? $rows : null;
        };
        $rows = $read();
        $today = strtotime('today');
        if (($rows === null || @filemtime($file) < $today) && ($lock = @fopen($file . '.lock', 'c'))) {
            try {
                // With a previous ranking, concurrent readers can use it during refresh.
                if (flock($lock, $rows === null ? LOCK_EX : (LOCK_EX | LOCK_NB))) {
                    $rows = $read();
                    if ($rows === null || @filemtime($file) < $today) {
                        $rows = $this->refreshTrendingSeries($file);
                    }
                }
            } catch (mysqli_sql_exception $e) {
                if ($rows === null) throw $e;
                error_log('BD Browser : échec du rafraîchissement des séries tendance.');
            } finally {
                fclose($lock);
            }
        }
        return $this->hydrate('ser', $rows ?? []);
    }

    // Suggest catalogue names starting with the input, allowing one character edit.
    // Keep a literal leading character on every LIKE so the name index remains usable.
    public function suggestions(string $type, string $query, int $size): array
    {
        $letters = mb_str_split($query);
        if (count($letters) < 4 || count($letters) > 60 || !preg_match('/\p{L}/u', $query)) return [];

        $letters = array_map(static fn($letter) => strtr($letter, ['!' => '!!', '%' => '!%', '_' => '!_']), $letters);
        $patterns = [];
        foreach ($letters as $i => $letter) {
            $before = implode('', array_slice($letters, 0, $i));
            $after = implode('', array_slice($letters, $i + 1));
            $patterns[] = $before . $after . '%';
            $replacements = $i === 0 ? range('a', 'z') : ['_'];
            foreach ($replacements as $replacement) {
                $patterns[] = $before . $replacement . $after . '%';
                $patterns[] = $before . $replacement . $letter . $after . '%';
            }
            if (isset($letters[$i + 1])) {
                $patterns[] = $before . $letters[$i + 1] . $letter . implode('', array_slice($letters, $i + 2)) . '%';
            }
        }
        $patterns = array_values(array_unique($patterns));
        [$table, $key, $label] = self::TABLES[$type];
        $condition = implode(' OR ', array_fill(0, count($patterns), "$label LIKE ? ESCAPE '!'"));
        $sql = "SELECT $key id, $label label FROM $table WHERE ($condition)";
        if ($type === 'aut') $sql .= " AND ID_AUTEUR NOT IN (2,3,5572,8497,6086,6243)";
        // Prefer a complete short name (Dupuis) to a longer matching title.
        $sql .= " ORDER BY ABS(CHAR_LENGTH($label) - ?), $label, $key LIMIT $size";
        $patterns[] = mb_strlen($query);
        return $this->hydrate($type, $this->rows($sql, $patterns));
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
        int $publisher, int $offset, int $size, ?int $collection = null): array
    {
        $params = [];
        $limit = ' LIMIT ' . $offset . ',' . ($size + 1);
        $genre = $genres ? ' IN (' . implode(',', array_map('intval', $genres)) . ')' : '';
        $order = 'label, id';
        if ($type === 'col') {
            $sql = "SELECT COALESCE(e.ID_COLLECTION,0) id,
                    CASE WHEN BINARY c.NOM = '<N/A>' THEN 'Sans collection'
                         ELSE COALESCE(NULLIF(c.NOM,''),'Collection non renseignée') END label
                FROM bd_edition e LEFT JOIN bd_collection c ON c.ID_COLLECTION=e.ID_COLLECTION";
            if ($genre) $sql .= ' JOIN bd_tome t ON t.ID_TOME=e.ID_TOME JOIN bd_serie s ON s.ID_SERIE=t.ID_SERIE';
            $sql .= ' WHERE e.ID_EDITEUR=?' . ($genre ? " AND s.ID_GENRE $genre" : '') .
                ' GROUP BY COALESCE(e.ID_COLLECTION,0),c.NOM';
            $params[] = $id;
        } elseif ($parent === 'ser' && $collection !== null) {
            $cover = $this->cover();
            $condition = $collection ? 'e.ID_COLLECTION=?' : '(e.ID_COLLECTION IS NULL OR e.ID_COLLECTION=0)';
            $sql = "SELECT t.ID_TOME id,t.TITRE label,t.NUM_TOME number,t.ID_SERIE,s.NOM subtitle,e.ID_EDITION edition,$cover cover
                FROM (SELECT e.ID_TOME,COALESCE(MAX(IF(e.ID_EDITION=t.ID_EDITION,e.ID_EDITION,NULL)),MIN(e.ID_EDITION)) edition
                      FROM bd_tome t JOIN bd_edition e ON e.ID_TOME=t.ID_TOME
                      WHERE t.ID_SERIE=? AND e.ID_EDITEUR=? AND $condition GROUP BY e.ID_TOME) hits
                JOIN bd_tome t ON t.ID_TOME=hits.ID_TOME JOIN bd_serie s USING (ID_SERIE)
                JOIN bd_edition e ON e.ID_EDITION=hits.edition";
            $params = [$id, $publisher];
            if ($collection) $params[] = $collection;
            $order = 't.FLG_INT DESC, t.FLG_TYPE, t.NUM_TOME, t.TITRE, t.ID_TOME';
        } else {
            [$table,$key,$label] = self::TABLES[$type];
            $where = $type === 'aut' ? ['x.ID_AUTEUR NOT IN (2,3,5572,8497,6086,6243)'] : [];
            if ($parent === 'ser') {
                $where[] = 'x.ID_SERIE=?';
                $params[] = $id;
                $order = 'x.FLG_INT DESC, x.FLG_TYPE, x.NUM_TOME, x.TITRE, x.ID_TOME';
            } elseif ($parent === 'aut') {
                $parts = [];
                foreach (self::ROLES as $role) {
                    $parts[] = "SELECT ID_SERIE FROM bd_tome WHERE $role=?";
                    $params[] = $id;
                }
                $where[] = 'x.ID_SERIE IN (SELECT ID_SERIE FROM (' . implode(' UNION ', $parts) . ') credits)';
            } elseif ($parent === 'col') {
                $condition = $id ? 'e.ID_COLLECTION=?' : '(e.ID_COLLECTION IS NULL OR e.ID_COLLECTION=0)';
                $where[] = "x.ID_SERIE IN (SELECT t.ID_SERIE FROM bd_edition e JOIN bd_tome t USING (ID_TOME)
                    WHERE e.ID_EDITEUR=? AND $condition)";
                $params[] = $publisher;
                if ($id) $params[] = $id;
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
        return ['rows' => $parent === 'ser' && $collection !== null ? $hits : $this->hydrate($type, $hits), 'more' => $more];
    }
}
