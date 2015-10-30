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
    
    public function getNbAlbumForAuteur($auteur_id) {
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
}
