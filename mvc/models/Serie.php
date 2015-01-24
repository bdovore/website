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

        return "
        SELECT 

                `bd_serie`.`ID_SERIE`  , 

                `bd_serie`.`NOM` as `NOM_SERIE` , 

                `bd_serie`.`FLG_FINI` as `FLG_FINI_SERIE`, 
                
                CASE bd_serie.FLG_FINI WHEN 0 then 'Fini' when 1 then 'En cours' when 2 then 'One Shot' when 3 then 'Interrompue/Abandonn&eacute;e' ELSE '?' end LIB_FLG_FINI_SERIE,

               IFNULL(bd_serie.NB_TOME, max(bd_tome.NUM_TOME))  as `NB_TOME` , 

                `bd_serie`.`TRI` as `TRI_SERIE`,

                `bd_serie`.`HISTOIRE` as `HISTOIRE_SERIE`, 

                `bd_genre`.`ID_GENRE`, 

                `bd_genre`.`LIBELLE` as `NOM_GENRE`, 

                `note_serie`.`MOYENNE_NOTE_SERIE` , 

                `note_serie`.`NB_NOTE_SERIE`, 

               

                `bd_edition_stat`.`NBR_USER_ID_SERIE`,
                
                count(bd_tome.ID_TOME) as NB_ALBUM,
                max(img_couv) as IMG_COUV_SERIE

                FROM `" . $this->table_name . "`

          LEFT JOIN `bd_genre` USING(`ID_GENRE`)

        LEFT JOIN `note_serie` USING(`ID_SERIE`)

        LEFT JOIN (SELECT `ID_SERIE`,NBR_USER_ID_SERIE FROM `bd_edition_stat` group by id_serie) `bd_edition_stat` USING(`ID_SERIE`)
        LEFT JOIN bd_tome using (ID_SERIE)
        LEFT JOIN bd_edition using(id_tome) 
        ";

    }



    public function search ($a_data = array())

    {

        // --------------------------------------------------------------------

        // -------- Champs selectionnés par defaut --------

        if (empty($a_data)) $a_data = $_POST;

        if (! isset($a_data['validSubmitSearch'])) {

            $a_data['ch_NOM'] = "checked";

            $a_data['ch_NOTE'] = "checked";

            $a_data['ch_FLG_FINI'] = "checked";

            $a_data['ch_NB_TOME'] = "checked";

            $a_data['ch_NB_NOTE'] = "checked";

            $a_data['ch_TRI'] = "checked";

            $a_data['ch_LIBELLE'] = "checked";



        }

        

        $dbSearch = new Bdo_Db_Search();

        

        $dbSearch->select = "
        SELECT 

                `bd_serie`.`ID_SERIE`  , 

                `bd_serie`.`NOM`  , 

                `bd_serie`.`NOTE` , 

                `bd_serie`.`FLG_FINI` , 

                `bd_serie`.`NB_TOME` , 

                `bd_serie`.`NB_NOTE` , 

                `bd_serie`.`TRI` , 

                `bd_serie`.`HISTOIRE`, 

                `bd_genre`.`ID_GENRE`, 

                `bd_genre`.`LIBELLE`
        ";

        

        // dans les tables

        $dbSearch->from = "
FROM " . $this->table_name . "
        LEFT JOIN `bd_genre` USING(`ID_GENRE`)

        

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
        
        $where = " WHERE (bd_serie.id_serie in (select distinct id_serie from bd_tome where id_scenar in (".implode(",",$a_scenar)."))
                            OR bd_serie.id_serie in (select distinct id_serie from bd_tome where id_dessin in (".implode(",",$a_dessin).")))
                                and id_serie <> ".$p_serie;
        $order = " ORDER BY NBR_USER_ID_SERIE desc";
        $requete = $this->select().$where." group by id_serie ".$order." LIMIT 0,20";
        
        $resultat = Db_query($requete);
        
        
        $a_obj = array();

        while ($obj = Db_fetch_object($resultat)) {

            $a_obj[] = $obj;

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
