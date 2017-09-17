<?php

/**
 *
 * @author tom
 *
 */
class Groupeserie extends Bdo_Db_Line {

    /**
     */
    public $table_name = 'groupe_serie';

    public $error = '';

    // initialisation
    public function __construct ($id = null)
    {
        if (is_array($id)) {
            $a_data = $id;
        }
        else {
            $a_data = array(
                    'ID_GROUPE' => $id
            );
        }
        parent::__construct($this->table_name, $a_data);
    }

    public function select ()
    {
        return "
        SELECT
        ID_GROUPE, ID_SERIE_1, ID_SERIE_2
        FROM `" . $this->table_name . "`
        ";
    }
    
    public function getSerieLiee($id_serie) {
        /*
         * Function pour récupérer les séries liées à une autre
         */
        $query = "SELECT ID_SERIE_1 as ID_SERIE, bd_serie.NOM as NOM_SERIE FROM groupe_serie INNER JOIN bd_serie on (ID_SERIE_1 = ID_SERIE) WHERE ID_SERIE_2 = ".intVal($id_serie). ""
                . " UNION ". ""
                . "SELECT ID_SERIE_2 as ID_SERIE, bd_serie.NOM FROM groupe_serie INNER JOIN bd_serie on (ID_SERIE_2 = ID_SERIE) WHERE ID_SERIE_1 = ".intVal($id_serie);
        
        $resultat = Db_query($query);


        $a_obj = array();

        while ($obj = Db_fetch_object($resultat)) {

            $a_obj[] = $obj;

        }

        return $a_obj;
    }

    public function deleteLiens($id_serie) {
        $query = "DELETE FROM groupe_serie WHERE ID_SERIE_1 = ".intVal($id_serie);
         $resultat = Db_query($query);
         
         $query = "DELETE FROM groupe_serie WHERE ID_SERIE_2 = ".intVal($id_serie);
         $resultat = Db_query($query);
         
         
    }
    
    public function addLiens($id_serie, $list_Id) {
       $query = "INSERT INTO groupe_serie (ID_SERIE_1, ID_SERIE_2) VALUES ";
       $virg = "";
        foreach ($list_Id as $id_serie2) {
           $query .= $virg." ( ".$id_serie. ", ".$id_serie2." ) ";
           $virg = ",";
       }
        $resultat = Db_query($query);
        return ($resultat);
         
    }
   
}
