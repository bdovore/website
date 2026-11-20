<?php

/**
 *
 * @author laurent
 *
 */
class Actus
{

    public function lastNews ($limit=10)
    {
        $requete = "SELECT * FROM news WHERE news_level>=5  ORDER BY News_id DESC LIMIT 0, " . intval($limit);
        $resultat = Db_query($requete);

        $a_obj = array();
        while ($obj = Db_fetch_object($resultat)) {
            $a_obj[] = $obj;
        }
        return $a_obj;
    }

    public function lastCommentaires ($limit=5)
    {
        $requete = "
        select
        t.TITRE as TITRE_TOME,
        t.ID_TOME,
        max(c.DTE_POST) as dte,
        en.IMG_COUV
        from
        bd_tome t INNER JOIN bd_edition en ON en.id_edition = t.id_edition,
        users_comment c
        where
        t.id_tome = c.id_tome
        and c.comment <> ''
        group by titre, t.id_tome
        order by dte desc limit 0," . intval($limit);
        $resultat = Db_query($requete);

        $a_obj = array();
        while ($obj = Db_fetch_object($resultat)) {
            $a_obj[] = $obj;
        }
        return $a_obj;
    }

    public function lastSorties ($limit=5)
    {
        $requete = "
        select
        t.TITRE as TITRE_TOME,
        t.ID_TOME,
        en.IMG_COUV ,
        en.DTE_PARUTION as DTE_PARUTION_EDITION
        from
        bd_tome t
        INNER JOIN bd_edition en ON en.id_edition = t.id_edition
        where
        en.dte_parution <= CURDATE()
        order by en.dte_parution desc
        limit 0," . intval($limit);
        $resultat = Db_query($requete);

        $a_obj = array();
        while ($obj = Db_fetch_object($resultat)) {
            $a_obj[] = $obj;
        }
        return $a_obj;
    }

    public function futurSorties ($limit=5)
    {
        $requete = "
        select
        t.TITRE as TITRE_TOME,
        t.ID_TOME,
        en.IMG_COUV ,
        en.DTE_PARUTION as DTE_PARUTION_EDITION
        from
        bd_tome t
        INNER JOIN bd_edition en ON en.id_edition = t.id_edition
        where
        en.dte_parution > CURDATE()
        order by en.dte_parution
        limit 0," . intval($limit);
        $resultat = Db_query($requete);

        $a_obj = array();
        while ($obj = Db_fetch_object($resultat)) {
            $a_obj[] = $obj;
        }
        return $a_obj;
    }

    public function actuAir ()
    {
        $file = BDO_DIR . 'cache/actualite.json';
        $albums = json_decode((string) @file_get_contents($file));
        // Recharge aussi les fichiers créés avant l’ajout des métadonnées.
        $cacheAncien = is_array($albums) && $albums && !isset($albums[0]->ID_EDITION);
        if (!is_array($albums) || @filemtime($file) < strtotime('today') || $cacheAncien) {
            if ($this->setTopActu($file) !== false) {
                $albums = json_decode((string) @file_get_contents($file));
            }
        }
        return is_array($albums) ? $albums : [];
    }

    public function lastAjout ()
    {
        // dernier ajout recharger toutes les 10min
        $file = BDO_DIR . "cache/lastajout.json";
        if (! file_exists($file) or (@filemtime($file) < (time() - 600)) or (@filesize($file) == 0)) {
            $size = $this->setLastAjout($file);
        }
        // $this->set_var("LASTAVIS",file_get_contents($file));
        return json_decode(file_get_contents($file));
    }

    function setTopActu ($file)
    {
        $select_topactu = "
    SELECT
        t.TITRE as TITRE_TOME,
        t.ID_TOME,
        note_tome.MOYENNE_NOTE_TOME as MOYENNE_TOME,
        en.ID_EDITION,
        en.IMG_COUV,
        note_tome.NB_NOTE_TOME as NB_VOTE_TOME,
        s.NOM as NOM_SERIE,
        s.ID_SERIE,
        g.ORIGINE as ORIGINE_GENRE,
                note_tome.MOYENNE_NOTE_TOME*log(note_tome.NB_NOTE_TOME + 1) score
    FROM
        bd_tome t
        INNER JOIN bd_edition en ON en.id_edition = t.id_edition
        INNER JOIN bd_serie s ON s.id_serie = t.id_serie
        INNER JOIN bd_genre g ON s.id_genre = g.id_genre
                INNER JOIN note_tome on note_tome.ID_TOME= t.id_tome
    WHERE
        en.dte_parution >= DATE_SUB(NOW(),INTERVAL 3 MONTH)AND
                en.dte_parution <= NOW() AND
                en.FLG_EXPLICIT = 0
    ";

        $order_actu = " ORDER BY score desc, en.dte_parution DESC LIMIT 0,6";

        // requete select de base pour dans l'air
        $select_topair = "
    SELECT
        t.TITRE as TITRE_TOME,
        t.ID_TOME,
        s.NOM as NOM_SERIE,
        s.ID_SERIE,
        g.ORIGINE as ORIGINE_GENRE,
        t.MOYENNE as MOYENNE_TOME,
        en.ID_EDITION,
        en.IMG_COUV,
        count(*) nb
    FROM
        users_album ua
        INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
        INNER JOIN bd_tome t ON en.id_tome=t.id_tome
        INNER JOIN bd_serie s ON t.id_serie=s.id_serie
        INNER JOIN bd_genre g ON s.id_genre=g.id_genre
    WHERE
        ua.date_ajout >= DATE_SUB(NOW(),INTERVAL 3 MONTH)
        and en.dte_parution >= DATE_SUB(NOW(),INTERVAL 3 MONTH) AND
                en.dte_parution <= NOW()
        ";

        $order_air = "
    GROUP BY t.id_tome
    ORDER BY nb DESC, IFNULL(ua.date_achat,ua.date_ajout) DESC
    ";

        $albums = [];
        $resultat = Db_query($select_topactu . $order_actu);
        if (!$resultat) {
            return false;
        }
        while ($album = Db_fetch_object($resultat)) {
            $albums[] = $album;
        }

        $idsTomes = array_map('intval', array_column($albums, 'ID_TOME'));
        $filtre = $idsTomes ? ' AND t.ID_TOME NOT IN (' . implode(',', $idsTomes) . ')' : '';
        $limite = 12 - count($albums);
        $resultat = Db_query($select_topair . $filtre . $order_air . " LIMIT $limite");
        if (!$resultat) {
            return false;
        }
        while ($album = Db_fetch_object($resultat)) {
            $albums[] = $album;
        }

        if ($albums && !$this->completerTendances($albums)) {
            return false;
        }

        $json = json_encode($albums, JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            return false;
        }
        $temporaire = tempnam(dirname($file), '.actualite-');
        if ($temporaire === false) {
            return false;
        }
        $taille = file_put_contents($temporaire, $json);
        if ($taille !== strlen($json) || !rename($temporaire, $file)) {
            unlink($temporaire);
            return false;
        }
        return $taille;
    }

    private function completerTendances(array $albums): bool
    {
        // Les infos d’édition doivent correspondre à la couverture retenue par le classement.
        $ids = implode(',', array_map('intval', array_column($albums, 'ID_EDITION')));
        $resultat = Db_query("
            SELECT en.ID_EDITION, t.NUM_TOME, t.FLG_INT AS FLG_INT_TOME,
                g.LIBELLE AS NOM_GENRE, en.DTE_PARUTION, en.FLG_EXPLICIT,
                c.NOM AS NOM_COLLECTION, er.NOM AS NOM_EDITEUR,
                t.ID_SCENAR, sc.PSEUDO AS scpseudo, t.ID_DESSIN, de.PSEUDO AS depseudo,
                n.NB_NOTE_TOME, n.MOYENNE_NOTE_TOME
            FROM bd_edition en
            INNER JOIN bd_tome t ON t.ID_TOME = en.ID_TOME
            INNER JOIN bd_serie s ON s.ID_SERIE = t.ID_SERIE
            LEFT JOIN bd_genre g ON g.ID_GENRE = s.ID_GENRE
            LEFT JOIN bd_collection c ON c.ID_COLLECTION = en.ID_COLLECTION
            LEFT JOIN bd_editeur er ON er.ID_EDITEUR = c.ID_EDITEUR
            LEFT JOIN bd_auteur sc ON sc.ID_AUTEUR = t.ID_SCENAR
            LEFT JOIN bd_auteur de ON de.ID_AUTEUR = t.ID_DESSIN
            LEFT JOIN note_tome n ON n.ID_TOME = t.ID_TOME
            WHERE en.ID_EDITION IN ($ids)");
        if (!$resultat) {
            return false;
        }
        $details = [];
        while ($edition = Db_fetch_object($resultat)) {
            $details[$edition->ID_EDITION] = (array) $edition;
        }
        foreach ($albums as $album) {
            foreach ($details[$album->ID_EDITION] ?? [] as $nom => $valeur) {
                $album->$nom = $valeur;
            }
        }
        return true;
    }

    function setLastAjout ($file)
    {

        // insertion des 10 derniers albums ajoutés
       $a_ajout = array();
        $requete = "SELECT
                bd_tome.TITRE as TITRE_TOME,
                bd_tome.ID_TOME,
                bd_tome.MOYENNE
                from bd_tome
                INNER JOIN (SELECT
                MAX(bd_tome.ID_TOME) ID_TOME
                from bd_tome inner join bd_edition using (id_edition)
                WHERE PROP_STATUS = 1
                GROUP BY ID_SERIE
                ORDER BY id_tome DESC LIMIT 0,10) lasttome USING (ID_TOME)";
        $resultat = Db_query($requete);
        while ($obj = Db_fetch_object($resultat)) {
            $obj->TITRE_TOME = ' - '.$obj->TITRE_TOME;
            $a_ajout[] = $obj;
        }
        

        return file_put_contents($file, json_encode($a_ajout));
    }
}
