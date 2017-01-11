<?php

/**
 *
 * @author laurent
 *
 */

class Auteur extends Bdo_Db_Line
{
    /**
     */
    public $table_name = 'bd_auteur';
    public $error = '';

    // initialisation
    public function __construct ($id = null)
    {
        if (is_array($id)) {
            $a_data = $id;
        }
        else {
            $a_data = array(
                    'ID_AUTEUR' => $id
            );
        }
        parent::__construct($this->table_name, $a_data);
    }

    public function select ()
    {
        return "
        SELECT
            `ID_AUTEUR` ,
            `PSEUDO` ,
            `PRENOM` ,
            `NOM` ,
            `FLG_SCENAR` ,
            `FLG_DESSIN` ,
            `FLG_COLOR` ,
            `COMMENT` ,
            `DTE_NAIS` ,
            `DTE_DECES` ,
            `NATIONALITE`,
            VALIDATOR,
            VALID_DTE,
            IMG_AUT
        FROM `" . $this->table_name . "`";
    }

    public function search ($a_data = array())
    {
        // --------------------------------------------------------------------
        // -------- Champs selectionnés par defaut --------
        if (empty($a_data)) $a_data = $_POST;
        if (! isset($a_data['validSubmitSearch'])) {
            $a_data['ch_PSEUDO'] = "checked";
            $a_data['ch_PRENOM'] = "checked";
            $a_data['ch_NOM'] = "checked";
            $a_data['ch_FLG_SCENAR'] = "checked";
            $a_data['ch_FLG_DESSIN'] = "checked";
            $a_data['ch_FLG_COLOR'] = "checked";
            $a_data['ch_DTE_NAIS'] = "checked";
            $a_data['ch_DTE_DECES'] = "checked";
            $a_data['ch_NATIONALITE'] = "checked";
        }

        $dbSearch = new Bdo_Db_Search();

        $dbSearch->select = "
        SELECT
            `ID_AUTEUR` ,
            `PSEUDO` ,
            `PRENOM` ,
            `NOM` ,
            `FLG_SCENAR` ,
            `FLG_DESSIN` ,
            `FLG_COLOR` ,
            `COMMENT` ,
            `DTE_NAIS` ,
            `DTE_DECES` ,
            `NATIONALITE`
        ";

        // dans les tables
        $dbSearch->from = "FROM " . $this->table_name . "";
        $dbSearch->where = "WHERE 1";

        // dans l'ordre
        if ($a_data['daff'] == "") $a_data['daff'] = "0";
        if ($a_data['sens_tri'] == "") $a_data['sens_tri'] = "ASC";
        if ($a_data['col_tri'] == "") $a_data['col_tri'] = $this->table_name . ".NOM";

        $dbSearch->groupby = "";

        // --------------=======================----------------
        $dbSearch->infoQuery();

        // --------------=======================----------------
        $dbSearch->integreData($a_data);

        // --------------=======================----------------

        if (isset($_GET['export'])) {
            $dbSearch->execNoLimit();
        }
        else {
            $dbSearch->exec();
        }

        return $dbSearch;
    }

    public function searchJSON() {

    }

    public static function getNbAlbumForAuteur($auteur_id) {
        $query = "
    select
        count(*) as nbtome
    from
        bd_tome
    where
        id_scenar = " . intval($auteur_id) . "
        or id_dessin = " . intval($auteur_id) . "
        or id_color = " . intval($auteur_id) . "";
         $resultat = Db_query($query);
        $obj = Db_fetch_object($resultat);

        return $obj->nbtome;
    }

    public function getAuteurForSerie($serie_id) {
        /*
         * Fournit la liste des auteurs contributeur sur une série
         */
        $query = "SELECT distinct ID_AUTEUR, PSEUDO
    FROM
    bd_auteur, bd_tome
    WHERE id_serie = " . $serie_id . "
    and (id_scenar = id_auteur or id_dessin = id_auteur)";
        $resultat = Db_query($query);
        $a_obj = array();
        while ($obj = Db_fetch_object($resultat)) {
            $a_obj[] = $obj;
        }
        return $a_obj;
    }

    public static function replaceAuteur($source_id, $dest_id) {
        // Remplace l'id d'auteur source par l'id auteur dest dans les différents champs de bd_tome
        // Met à jour l'information contenue dans la base de données
        $modif = 0;
        $dest_id = intval($dest_id);
        $source_id = intval($source_id);
        // scenar
        $query = "UPDATE bd_tome SET id_scenar = " . $dest_id . " where id_scenar = " . $source_id;
        Db_query($query);
        $modif += Db_affected_rows();
        // scenar_alt
        $query = "UPDATE bd_tome SET id_scenar_alt = " . $dest_id . " where id_scenar_alt = " . $source_id;
        Db_query($query);
        $modif += Db_affected_rows();
        //dessin
        $query = "UPDATE bd_tome SET id_dessin = " . $dest_id . " where id_dessin = " . $source_id;
        Db_query($query);
        $modif += Db_affected_rows();
        //dessin_alt
        $query = "UPDATE bd_tome SET id_dessin_alt = " . $dest_id . " where id_dessin_alt = " . $source_id;
        Db_query($query);
        $modif += Db_affected_rows();
        //color
        $query = "UPDATE bd_tome SET id_color = " . $dest_id . " where id_color = " . $source_id;
        Db_query($query);
        $modif += Db_affected_rows();
        //color_alt
        $query = "UPDATE bd_tome SET id_color_alt = " . $dest_id . " where id_color_alt = " . $source_id;
        Db_query($query);
        $modif += Db_affected_rows() ;

        return $modif;
    }
}
