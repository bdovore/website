<?php

/*
 * Liste des auteurs favoris
 */

class Users_list_aut extends Bdo_Db_Line

{



    public $table_name = 'users_list_aut';



    /**

     */

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

       public function select(){
           return " SELECT USER_ID, bd_auteur.ID_AUTEUR, 
               bd_auteur.PSEUDO, bd_auteur.PRENOM, bd_auteur.NOM
               FROM users_list_aut inner join bd_auteur using(id_auteur)
               
            ";
           
       }

       public function majListAuteur($user_id, $listauteur) {
           /* 
            * fonction pour mettre à jour la liste des auteurs favoris d'un user
            * En entrée :
            *  @user_id : identifiant du user
            *  @listauteur : tableau listant les identifiant d'auteur favoris pour ce user
            */
           
           $query = "delete from ".$this->table_name ." where user_id = " . $user_id;
           Db_query($query);
           $values = " ";
           $sep = "";
           foreach ($listauteur as $idauteur) {
               if ($idauteur > 0) {
                $values .= $sep." ( ".$user_id .", ".$idauteur . ")";
                $sep = ",";
               }
           }
           $query = "INSERT INTO users_list_aut (USER_ID, ID_AUTEUR) VALUES ".$values;
           Db_query($query);
       }


}
?>
