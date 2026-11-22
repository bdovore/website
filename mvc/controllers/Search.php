<?php

class Search extends Bdo_Controller
{
    private function rows(string $sql, array $params): array
    {
        $statement = Bdo_Cfg::getVar('connexion')->prepare($sql);
        $statement->execute($params);
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function catalogueSuggestions(string $term, string $prefix): array
    {
        $this->loadModel('BrowserCatalogue');
        $series = $this->BrowserCatalogue->autocompleteTitles('ser', $term, $prefix);
        $albums = $this->BrowserCatalogue->autocompleteTitles('alb', $term, $prefix);
        if (preg_match('/[0-9X]{5,10}.+/', $term)) {
            foreach (['EAN', 'ISBN'] as $code) {
                $albums = array_merge($albums, $this->rows("SELECT t.TITRE label, t.ID_TOME, e.ID_EDITION, 'Albums' category
                    FROM bd_edition e JOIN bd_tome t ON t.ID_TOME=e.ID_TOME
                    WHERE e.$code LIKE ? ESCAPE '!' ORDER BY t.TITRE, e.ID_EDITION LIMIT 10", [$prefix]));
            }
        }

        // Use PRENOM to narrow the candidates before checking the full name.
        $authors = $this->rows("SELECT a.PSEUDO label, a.ID_AUTEUR, 'Auteurs' category
            FROM (
                SELECT ID_AUTEUR FROM bd_auteur WHERE PSEUDO LIKE ? ESCAPE '!'
                UNION SELECT ID_AUTEUR FROM bd_auteur WHERE PRENOM LIKE ? ESCAPE '!'
                    AND CONCAT(PRENOM, ' ', NOM) LIKE ? ESCAPE '!'
            ) hits JOIN bd_auteur a USING (ID_AUTEUR)
            ORDER BY a.PSEUDO, a.ID_AUTEUR LIMIT 10", [$prefix, explode(' ', $prefix)[0] . '%', $prefix]);
        $collections = $this->rows("SELECT
                IF(BINARY c.NOM = '<N/A>', e.NOM, CONCAT(COALESCE(e.NOM, ''), '-', COALESCE(c.NOM, ''))) label,
                c.ID_COLLECTION, 'Collections' category
            FROM (
                SELECT ID_COLLECTION FROM bd_collection WHERE NOM LIKE ? ESCAPE '!'
                UNION SELECT c.ID_COLLECTION FROM bd_editeur e JOIN bd_collection c USING (ID_EDITEUR)
                    WHERE e.NOM LIKE ? ESCAPE '!'
            ) hits JOIN bd_collection c USING (ID_COLLECTION)
            LEFT JOIN bd_editeur e USING (ID_EDITEUR)
            ORDER BY e.NOM, c.NOM, c.ID_COLLECTION LIMIT 10", [$prefix, $prefix]);
        return array_merge($series, $albums, $authors, $collections);
    }

    public function Auto()
    {
        Bdo_Cfg::setVar('debug', false);
        $value = getVal('term', '');
        $term = is_string($value) ? trim($value) : '';
        $length = mb_strlen($term);
        $suggestions = [];
        if ($length >= 2 && $length <= 60 && mb_check_encoding($term, 'UTF-8')) {
            $prefix = strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            if ($length >= 3) {
                $suggestions = $this->catalogueSuggestions($term, $prefix);
            }
            $members = $this->rows("SELECT username label, user_id * 1209 + 951 ID_USER, 'Bdovore' category
                FROM users WHERE username LIKE ? ESCAPE '!' AND OPEN_COLLEC = 'Y'
                ORDER BY username LIMIT 10", [$prefix]);
            $suggestions = array_merge($suggestions, $members);
        }

        $this->view->set_var('json', json_encode($suggestions, JSON_INVALID_UTF8_SUBSTITUTE));
        $this->view->layout = 'ajax';
        $this->view->render();
    }
}
