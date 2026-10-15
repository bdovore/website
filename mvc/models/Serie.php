<?php




/**
 *
 * @author laurent
 *
 */

class Serie extends Bdo_Db_Line

{



    /**

     */

    public $table_name = 'bd_serie';

    public $selectType = "select"; // selectType : 2 valeurs possible "select" pour sélection d'une série, "browse" pour les requetes de recherche

    public $error = '';



    // initialisation

    public function __construct ($id = null)

    {

        if (is_array($id)) {

            $a_data = $id;

        }

        else {

            $a_data = array(

                    'ID_SERIE' => $id

            );

        }

        parent::__construct($this->table_name, $a_data);

    }



    public function select ()

    {
        if ($this->selectType=="select") {
            return "
            SELECT 

                    `bd_serie`.`ID_SERIE`  ,

                    `bd_serie`.`NOM` as `NOM_SERIE` ,

                    `bd_serie`.`FLG_FINI` as `FLG_FINI_SERIE`,
                    `bd_serie`.`FLAG_SANS_ISBN_EAN`,

                    CASE bd_serie.FLG_FINI WHEN 0 then 'Fini' when 1 then 'En cours' when 2 then 'One Shot' when 3 then 'Interrompue' ELSE '?' end LIB_FLG_FINI_SERIE,

                    CASE WHEN bd_serie.NB_TOME > 0 THEN bd_serie.NB_TOME ELSE count(distinct bd_tome.NUM_TOME) END  as `NB_TOME` ,
                    bd_serie.NB_TOME as NB_TOME_FINAL,

                    `bd_serie`.`TRI` as `TRI_SERIE`,

                    `bd_serie`.`HISTOIRE` as `HISTOIRE_SERIE`,

                    `bd_genre`.`ID_GENRE`,

                    `bd_genre`.`LIBELLE` as `NOM_GENRE`,
                    `bd_genre`.`ORIGINE`, 


                    `bd_edition_stat`.`NBR_USER_ID_SERIE`,


                    count(distinct bd_tome.ID_TOME) as NB_ALBUM, ".
                   (Bdo_Cfg::getVar('explicit') ? "max(img_couv)" : " max(IF (bd_edition.FLG_EXPLICIT, CONCAT('?source=',bd_edition.IMG_COUV), bd_edition.IMG_COUV)) " )  ." as IMG_COUV_SERIE,
                    avg(MOYENNE_NOTE_TOME) NOTE_SERIE,
                    sum(NB_NOTE_TOME) NB_NOTE_SERIE

                    FROM `" . $this->table_name . "`

              LEFT JOIN `bd_genre` USING(`ID_GENRE`)
              LEFT JOIN (SELECT `ID_SERIE`,NBR_USER_ID_SERIE FROM `bd_edition_stat` group by id_serie) `bd_edition_stat` USING(`ID_SERIE`)
            LEFT JOIN bd_tome using (ID_SERIE)
            LEFT JOIN bd_edition using (id_edition)
            LEFT JOIN note_tome on (bd_tome.ID_TOME =note_tome.ID_TOME)
            ";
        } else {
            return "  SELECT 

                `bd_serie`.`ID_SERIE`  ,

                `bd_serie`.`NOM` as `NOM_SERIE` 
                 FROM `" . $this->table_name . "`";
        }

    }



    /** Réglage de série utilisé comme valeur par défaut lors des ajouts. */
    public function isSansIsbnEan($serie_id) {
        $serie_id = intval($serie_id);
        if ($serie_id <= 0) return false;
        $result = Db_query('SELECT FLAG_SANS_ISBN_EAN FROM bd_serie WHERE ID_SERIE = ' . $serie_id);
        $serie = Db_fetch_object($result);
        return $serie && intval($serie->FLAG_SANS_ISBN_EAN) === 1;
    }

    public function isAlbumSansIsbnEan($tome_id) {
        $tome_id = intval($tome_id);
        if ($tome_id <= 0) return false;
        $result = Db_query('SELECT s.FLAG_SANS_ISBN_EAN FROM bd_tome t INNER JOIN bd_serie s ON s.ID_SERIE = t.ID_SERIE WHERE t.ID_TOME = ' . $tome_id);
        $serie = Db_fetch_object($result);
        return $serie && intval($serie->FLAG_SANS_ISBN_EAN) === 1;
    }

    public function getSansIsbnEanImpact($serie_id) {
        $result = Db_query('SELECT s.FLAG_SANS_ISBN_EAN, COUNT(DISTINCT t.ID_TOME) AS NB_ALBUMS, COUNT(e.ID_EDITION) AS NB_EDITIONS
            FROM bd_serie s LEFT JOIN bd_tome t ON t.ID_SERIE = s.ID_SERIE
            LEFT JOIN bd_edition e ON e.ID_TOME = t.ID_TOME
            WHERE s.ID_SERIE = ' . intval($serie_id) . ' GROUP BY s.ID_SERIE, s.FLAG_SANS_ISBN_EAN');
        return Db_fetch_object($result);
    }

    /** Une seule requête applique le réglage et, si confirmé, marque toutes les éditions.
     * Aucun ISBN/EAN n'est effacé. Désactiver le défaut ne modifie pas les éditions.
     */
    public function setSansIsbnEan($serie_id, $enabled, $applyToEditions = false) {
        $serie_id = intval($serie_id);
        if ($serie_id <= 0) throw new InvalidArgumentException('Identifiant de série invalide.');
        if ($enabled && $applyToEditions) {
            $query = 'UPDATE bd_serie s LEFT JOIN bd_tome t ON t.ID_SERIE = s.ID_SERIE
                LEFT JOIN bd_edition e ON e.ID_TOME = t.ID_TOME
                SET s.FLAG_SANS_ISBN_EAN = 1, e.FLAG_SANS_ISBN_EAN = 1 WHERE s.ID_SERIE = ' . $serie_id;
        } else {
            $query = 'UPDATE bd_serie SET FLAG_SANS_ISBN_EAN = ' . ($enabled ? '1' : '0') . ' WHERE ID_SERIE = ' . $serie_id;
        }
        if (!Db_query($query)) throw new RuntimeException('Impossible de mettre à jour le réglage ISBN/EAN de la série.');
    }

    public function browseSerie ($filter) {
        $this->select = "  SELECT 

                `bd_serie`.`ID_SERIE`  ,

                `bd_serie`.`NOM` as `NOM_SERIE` 
                FROM BD_SERIE ";
        return $this->load("c", $filter);
        
    }

    public function getSerieSameAuthor($p_serie) {
        // Renvoie des séries des mêmes auteurs que la série de départ
        $filtre_auteur = "";

        $requete = "select distinct id_scenar id from bd_tome
           where id_scenar <> 885 AND id_scenar <> 5572 AND id_serie = ".$p_serie ;
        $resultat = Db_query($requete);
        $a_scenar = array();

         while ($obj = Db_fetch_object($resultat)) {

            $a_scenar[] = $obj->id;

        }


        $requete = "select distinct id_dessin id from bd_tome
           where id_dessin <> 885 AND id_dessin <> 5572 AND id_serie = ".$p_serie ;
        $resultat = Db_query($requete);
        $a_dessin = array();

         while ($obj = Db_fetch_object($resultat)) {

            $a_dessin[] = $obj->id;

        }
        $where = " WHERE id_serie <> " .getValInteger($p_serie); 
        $and_or = " AND (";
        $close = "";
        $same = false;
        if (count($a_dessin) > 0) {
            $where .= " AND ( bd_serie.id_serie in (select distinct id_serie from bd_tome where id_scenar in (".implode(",",$a_dessin)."))";
            $and_or = " OR ";
            $close = ")";
            $same = true;
        }
        if (count($a_scenar) > 0) {
            $where .= $and_or. "bd_serie.id_serie in (select distinct id_serie from bd_tome where id_dessin in (".implode(",",$a_scenar)."))";
            $close = ")";
            $same = true;
        }
        $where .= $close;
        /*$where = " WHERE (bd_serie.id_serie in (select distinct id_serie from bd_tome where id_scenar in (".implode(",",$a_scenar)."))
                            OR bd_serie.id_serie in (select distinct id_serie from bd_tome where id_dessin in (".implode(",",$a_dessin).")))
                                and id_serie <> ".$p_serie; */
        $order = " ORDER BY NBR_USER_ID_SERIE desc";
        $requete = $this->select().$where." group by id_serie ".$order." LIMIT 0,20";
        
        $a_obj = array();
        if ($same) {
            $resultat = Db_query($requete);
            while ($obj = Db_fetch_object($resultat)) {

                $a_obj[] = $obj;

            }

        }
        


        

        
        return $a_obj;

    }

    public function getListSerie($lettre="A") {
        $select = "SELECT

                `bd_serie`.`ID_SERIE`  ,

                `bd_serie`.`NOM` as NOM_SERIE
                FROM bd_serie
                WHERE NOM like '".$lettre."%' order by NOM";
        $resultat = Db_query($select);
        $a_obj = array();

        while ($obj = Db_fetch_object($resultat)) {

            $a_obj[] = $obj;

        }

        return $a_obj;
    }

}
