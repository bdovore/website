<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Comment extends Bdo_Db_Line

{



    /**

     */

    public $table_name = 'users_comment';



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



    public function select ($apercu = false, $parDate = false)

    {


        $texte = $apercu
            ? 'LEFT(c.`COMMENT`, 300) AS COMMENT, CHAR_LENGTH(c.`COMMENT`) > 300 AS COMMENT_SUITE'
            : 'c.`COMMENT`';
        // Sans filtre par genre, partir des avis les plus récents permet de s'arrêter à la limite.
        $ordreJointures = $parDate ? 'STRAIGHT_JOIN' : '';
        $indexDate = $parDate ? 'FORCE INDEX FOR ORDER BY (DTE_POST)' : '';

        return "
        SELECT $ordreJointures (c.`user_id`*1209 + 951) as user_id ,
               c.`ID_TOME` ,
               c.`NOTE` ,
               $texte ,
               c.`DTE_POST` ,
               u.`username` ,
               bd_tome.titre as TITRE_TOME,
               bd_tome.NUM_TOME,
               bd_edition.IMG_COUV,
                s.ID_SERIE,
            s.nom as NOM_SERIE
        FROM `users_comment` c $indexDate INNER JOIN users u using(user_id)
               inner join bd_tome using (id_tome)
               inner join bd_edition using (id_edition)
               inner join bd_serie s using (id_serie)
              inner join bd_genre g on (bd_tome.id_genre = g.id_genre)
               ";

    }

    public function loadApercus(string $where, bool $parDate): Bdo_Db_Select
    {
        $resultats = new Bdo_Db_Select($this->select(true, $parDate) . ' ' . $where);
        $resultats->exec();
        return $resultats;
    }

    public function replaceIdTome($old_idtome, $new_idtome) {
         /*
         * Suppresio d'id tome dans users_album
         */
        Db_query("UPDATE IGNORE users_comment SET ID_TOME = " . intval($new_idtome) . " WHERE ID_TOME =" . intval($old_idtome));

        return Db_affected_rows();
    }

    public function setCommentNull ($user_id, $id_tome) {
        $query = "update users_comment set COMMENT = '' where user_id = ".intVal($user_id)." and id_tome = ".intVal($id_tome);
        Db_query($query);
        return Db_affected_rows();
    }


}
?>
