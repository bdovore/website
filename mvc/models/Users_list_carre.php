<?php

/*
 * Liste des auteurs favoris
 */

class Users_list_carre extends Bdo_Db_Line

{



    public $table_name = 'users_list_carre';



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



       public function majListCarre($user_id, $listalbum) {
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
           $rang = 1;
           foreach ($listalbum as $idtome) {
               if ($idtome > 0) {
                $values .= $sep." ( ".$user_id .", ".intval($idtome) . "," . $rang .")";
                $sep = ",";
                $rang++;
               }
           }
           $query = "INSERT INTO users_list_carre (user_id, id_tome, rang) VALUES ".$values;
           Db_query($query);
       }

       public function replaceIdTome($old_idtome, $new_idtome) {
           Db_query("UPDATE IGNORE users_list_carre SET `id_tome` = " . intval($new_idtome) . " WHERE `id_tome`=" . intval($old_idtome));

        return Db_affected_rows();
       }

}
?>
