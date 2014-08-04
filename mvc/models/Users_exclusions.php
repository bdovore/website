<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Users_exclusions extends Bdo_Db_Line

{



    /**

     */

    public $table_name = 'users_exclusions';



    public $error = '';

    

    // initialisation

    public function __construct ($id = null)

    {

        if (is_array($id)) {

            $a_data = $id;

        }

        else {

            $a_data = array(

                    'USER_ID' => $id

            );

        }

        parent::__construct($this->table_name, $a_data);

    }



    public function select ()

    {

        return "SELECT users_exclusions.ID_TOME, BD_TOME.TITRE as TITRE_TOME,
                       users_exclusions.ID_SERIE, BD_SERIE.NOM as NOM_SERIE
            FROM users_exclusions inner join bd_tome using (id_tome)
                inner join bd_serie on users_exclusions.id_serie = bd_serie.id_serie
       `" . $this->table_name . "`

        

                ";

    }
    
    public function getListSerieExclu ($user_id) {
        /* 
         * Liste des séries avec au moins une exclusion pour un user donné
         */
        

        
            $query = "select distinct users_exclusions.id_serie as ID_SERIE, bd_serie.NOM as NOM_SERIE
                from users_exclusions inner join bd_serie using (id_serie)
                where user_id = ".intval($user_id) ." order by bd_serie.NOM";
            
            $resultat = Db_query($query);
            $a_obj = array();
            while ($obj = Db_fetch_object($resultat)) {

                $a_obj[] = $obj;

            }
            Db_free_result($resultat);
            
            
            return $a_obj;
    }
    
    public function getListSerieToComplete ($user_id) {
        /*
         * Liste des séries pour lesquels il y a au moins un album à completer
         */
        
        $query = "
            SELECT DISTINCT 
                    user_serie.id_serie as ID_SERIE,
                    user_serie.nom as NOM_SERIE
            FROM 
                    (
                            SELECT DISTINCT
                                    s.id_serie,
                                    s.nom
                            FROM 
                                    users_album ua 
                                    INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                                    INNER JOIN bd_tome t ON t.id_tome = en.id_tome
                                    INNER JOIN bd_serie s ON t.ID_SERIE=s.ID_SERIE 
                            WHERE 
                                    ua.user_id = ".intval($user_id)."
                                    AND NOT EXISTS (
                                                            SELECT NULL FROM users_exclusions ues
                                                            WHERE s.id_serie=ues.id_serie 
                                                            AND ues.id_tome = 0 
                                                            AND ues.user_id = ".intval($user_id)."
                                                    )
                            ) user_serie
                    INNER JOIN bd_tome t ON t.ID_SERIE=user_serie.ID_SERIE
                    INNER JOIN bd_edition en ON t.ID_EDITION=en.ID_EDITION
            WHERE
                            NOT EXISTS (
                                    SELECT NULL 
                                    FROM users_album ua
                                    INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
                                    WHERE 
                                    ua.user_id = ".intval($user_id)."
                                    AND t.id_tome=en.id_tome 
                            )
                            AND NOT EXISTS (
                                    SELECT NULL 
                                    FROM users_exclusions uet
                                    WHERE uet.user_id = ".intval($user_id)."
                                    AND t.id_tome=uet.id_tome
                            )
            ORDER BY user_serie.nom
            ";
         $resultat = Db_query($query);
        $a_obj = array();
            while ($obj = Db_fetch_object($resultat)) {

                $a_obj[] = $obj;

            }

            Db_free_result($resultat);
            
            return $a_obj;
    }
    
    
    public function addSerieExclude($user_id, $id_serie) {
        /*
         * Fonction pour ajouter une série à exclure pour un user donné
         */
        // on efface les anciennes références à la série
	$this->delSerieExclude($user_id,$id_serie);
        
        $query = "
	INSERT INTO users_exclusions (
	`user_id` ,`id_tome` ,`id_serie`
	) VALUES (
	'".intval($user_id)."', '0', '".intval($id_serie)."');";
        
         Db_query($query);
         return 1;
    }
    
    public function delSerieExclude($user_id, $id_serie) {
        /*
         * Fonction pour supprimer une série des exclusions
         */
        $query = "DELETE FROM users_exclusions WHERE user_id = ".intval($user_id)." AND id_serie = ".intval($id_serie);
        Db_query($query);
        
       
       
        return 1;
    }
    
    
    public function addAlbumExclude($user_id, $id_serie, $id_tome) {
        /*
         * Fonction pour ajouter une série à exclure pour un user donné
         */
        $query = "
			INSERT IGNORE INTO users_exclusions (
			`user_id` ,`id_tome` ,`id_serie`
			) VALUES (
			'".intval($user_id)."', '".intval($id_tome)."', '".intval($id_serie)."');";
        
        Db_query($query);
        
        return 1;
    }
    
    public function delAlbumExclude($user_id, $id_serie, $id_tome) {
        /*
         * Fonction pour ajouter une série à exclure pour un user donné
         */
        
    }
    
    
}
?>
