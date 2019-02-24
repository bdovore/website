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
        // dernier topactu recharger si cree avant 0h00
        $file = BDO_DIR . "cache/actualite.json";
        if (! file_exists($file) or @filemtime($file) < mktime(0, 0, 0, date("m"), date("d"), date("Y")) or (@filesize($file) == 0)) {
            $size = $this->setTopActu($file);
        }
        // $this->set_var("ACTUAIR",file_get_contents($file));
        return json_decode(file_get_contents($file));
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
        $a_genre = array(
                'BD',
                'Mangas',
                'Comics'
        );
        // requete select de base pour les top 1 et 2

        $select_topactu = "
    SELECT
        t.TITRE as TITRE_TOME,
        t.ID_TOME,
        note_tome.MOYENNE_NOTE_TOME as MOYENNE_TOME,
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
        en.dte_parution >= DATE_SUB(NOW(),INTERVAL 4 MONTH)AND
                en.dte_parution <= NOW()
    ";

        $order_actu = " ORDER BY score desc, en.dte_parution DESC LIMIT 0,2";

        // requete select de base pour dans l'air
        $select_topair = "
    SELECT
        t.TITRE as TITRE_TOME,
        t.ID_TOME,
        s.NOM as NOM_SERIE,
        s.ID_SERIE,
        g.ORIGINE as ORIGINE_GENRE,
        t.MOYENNE as MOYENNE_TOME,
        en.IMG_COUV,
        count(*) nb
    FROM
        users_album ua
        INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
        INNER JOIN bd_tome t ON en.id_tome=t.id_tome
        INNER JOIN bd_serie s ON t.id_serie=s.id_serie
        INNER JOIN bd_genre g ON s.id_genre=g.id_genre
    WHERE
        ua.date_ajout >= DATE_SUB(NOW(),INTERVAL 1 MONTH)
        and en.dte_parution >= DATE_SUB(NOW(),INTERVAL 4 MONTH) AND
                en.dte_parution <= NOW()
        ";

        $order_air = "
    GROUP BY t.id_tome
    ORDER BY nb DESC, IFNULL(ua.date_achat,ua.date_ajout) DESC
    ";

      
        $a_actu= array();
        foreach ($a_genre as $genre) {
           
            $filter = " and g.origine = '" . $genre . "' ";

            // actu
            $requete = $select_topactu . $filter . " " . $order_actu;
            $resultat = Db_query($requete);
            $nb = 0;
            if ($obj = Db_fetch_object($resultat)) {
                 $a_actu[] = $obj;
                 $filter .= " and t.ID_TOME <> '" . $obj->ID_TOME . "' ";
                 $nb++;
            }
             if ($obj = Db_fetch_object($resultat)) {
                  $a_actu[] = $obj;
                 $filter .= " and t.ID_TOME <> '" . $obj->ID_TOME . "' ";
                 $nb++;
            }

            // air du temps
            $limit = "LIMIT 0,".(4-$nb);
            $requete = $select_topair . $filter.$limit . $order_air;
            $resultat = Db_query($requete);
            if ($obj = Db_fetch_object($resultat)) {
                 $a_actu[] = $obj;
            }
            if ($obj = Db_fetch_object($resultat)) {
                 $a_actu[] = $obj;
            }
           
        }

       

        return file_put_contents($file, json_encode($a_actu));
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
