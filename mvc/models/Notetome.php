<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Notetome extends Bdo_Db_Line

{



    /**

     */

    public $table_name = 'note_tome';



    public $error = '';



    // initialisation
    // liste des commentaires pour un album donné
    public function __construct ($id = null)

    {

        if (is_array($id)) {

            $a_data = $id;

        }

        else {

            $a_data = array(

                    'ID_TOME' => $id

            );

        }

        parent::__construct($this->table_name, $a_data);

    }



    public function select ()

    {


        return "
        SELECT ID_TOME,
                MOYENNE_NOTE_TOME,
                NB_NOTE_TOME
         FROM note_tome
               ";

    }


     public function update_stat($id_tome) {
        /*
         * Fonction de mise à jour des statistiques de note d'un album
         * Se calcul après chaque ajout de note / commentaire
         *
         */

        $query= "replace into note_tome (id_tome, MOYENNE_NOTE_TOME , NB_NOTE_TOME )
                select id_tome,  sum(note)/count(*) moyenne, count(*) nb_note from users_comment where id_tome=".$id_tome;

        $resultat = Db_query($query);


        return($resultat);
    }



}
?>
