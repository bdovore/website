<?php

/**
 *
 * @author laurent
 *        
 */
class Collection extends Bdo_Db_Line
{

    /**
     */
    public $table_name = 'bd_collection';

    public $error = '';
    
    // initialisation
    public function __construct ($id = null)
    {
        if (is_array($id)) {
            $a_data = $id;
        }
        else {
            $a_data = array(
                    'ID_COLLECTION' => $id
            );
        }
        parent::__construct($this->table_name, $a_data);
    }

    public function select ()
    {
        return "
        SELECT 
        `bd_collection`.`ID_COLLECTION`,
        `bd_collection`.`NOM`,
        `bd_editeur`.`ID_EDITEUR`, 
        `bd_editeur`.`NOM` as NOM_EDITEUR
                
        FROM `" . $this->table_name . "`
        LEFT JOIN `bd_editeur` USING(`ID_EDITEUR`)
        ";
    }

    public function search ($a_data = array())
    {
        // --------------------------------------------------------------------
        // -------- Champs selectionnés par defaut --------
        if (empty($a_data)) $a_data = $_POST;
        if (! isset($a_data['validSubmitSearch'])) {
            $a_data['ch_NOM'] = "checked";
            $a_data['ch_NOM_EDITEUR'] = "checked";
        }
        
        $dbSearch = new Bdo_Db_Search();
        
        $dbSearch->select = "
        SELECT 
        `bd_collection`.`ID_COLLECTION`,
        `bd_collection`.`NOM`,
        `bd_editeur`.`ID_EDITEUR`, 
        `bd_editeur`.`NOM` as NOM_EDITEUR
        ";
        
        // dans les tables
        $dbSearch->from = "
FROM " . $this->table_name . "
LEFT JOIN `bd_editeur` USING(`ID_EDITEUR`)
        
        ";
        
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
    
    public function getNbAlbumForCollection($id_collection) {
        /*
         * Fonction qui renvoie le nombre d'album dans une collection donnée
         */
        $query = "SELECT count(distinct(bd_tome.ID_TOME)) as numofalb
	FROM bd_tome
	INNER JOIN bd_edition ON bd_tome.ID_EDITION=bd_edition.ID_EDITION 
	WHERE bd_edition.id_collection=" . intval($id_collection);
        $resultat = Db_query($query);
        $obj = Db_fetch_object($resultat);
        
        return $obj->numofalb;
    }
    
    public function replaceIdEditeur($source_id, $dest_id) {
        // Focntion qui remplace un editeur par un autre dans toutes les éditions
         Db_query("UPDATE bd_collection SET id_editeur = " . intval($dest_id) . " where id_editeur = " . intval($source_id));
        
        return Db_affected_rows();
    }   
}
