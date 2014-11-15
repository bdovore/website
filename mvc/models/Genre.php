<?php



/**
 *
 * @author laurent
 *        
 */

class Genre extends Bdo_Db_Line

{



    /**

     */

    public $table_name = 'bd_genre';



    public $error = '';

    

    // initialisation

    public function __construct ($id = null)

    {

        if (is_array($id)) {

            $a_data = $id;

        }

        else {

            $a_data = array(

                    'ID_GENRE' => $id

            );

        }

        parent::__construct($this->table_name, $a_data);

    }



    public function select ()

    {

        return "
        SELECT 
            `ID_GENRE` , 
            `LIBELLE` , 
            `ORIGINE`
        FROM  `" . $this->table_name . "`
        ";

    }



    public function search ($a_data = array())

    {

        // --------------------------------------------------------------------

        // -------- Champs selectionnés par defaut --------

        if (empty($a_data)) $a_data = $_POST;

        if (! isset($a_data['validSubmitSearch'])) {

            $a_data['ch_LIBELLE'] = "checked";

            $a_data['ch_ORIGINE'] = "checked";

        }

        

        $dbSearch = new Bdo_Db_Search();

        

        $dbSearch->select = "
        SELECT 
            `ID_GENRE` , 

            `LIBELLE` , 

            `ORIGINE`
        ";

        

        // dans les tables

        $dbSearch->from = "
FROM " . $this->table_name . "
";

        

        $dbSearch->where = "WHERE 1";

        

        // dans l'ordre

        if ($a_data['daff'] == "") $a_data['daff'] = "0";

        if ($a_data['sens_tri'] == "") $a_data['sens_tri'] = "ASC";

        if ($a_data['col_tri'] == "") $a_data['col_tri'] = $this->table_name . ".LIBELLE";

        

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

    

    public function all() {

        // liste des genres

        return $this->load('c', " WHERE 1 ORDER BY `bd_genre`.`LIBELLE` ASC");

    }
    
    public function bd(){
        
        return $this->load('c'," WHERE ORIGINE = 'BD' ORDER BY `bd_genre`.`LIBELLE` ASC");
        
    }
    
    public function mangas() {
         return $this->load('c'," WHERE ORIGINE = 'Mangas' ORDER BY `bd_genre`.`LIBELLE` ASC");
    }
    
    public function comics () {
         return $this->load('c'," WHERE ORIGINE = 'Comics' ORDER BY `bd_genre`.`LIBELLE` ASC");
    }
    
    public function getNbSerieForGenre($id_genre) {
        /*
         * Prend un id genre et fournit en sortie le nombre de série concernées par le genre
         */
        $query = "select count(*) as nbseries from bd_serie where id_genre =" . intval($id_genre);
        $resultat = Db_query($query);
        $obj = Db_fetch_object($resultat);
        
        return $obj->nbseries;
    }
    

}
