<?php

class Userserie extends Bdo_Db_Line {

    public $table_name = 'bd_serie';
    public $error = '';

    // initialisation
    public function __construct ($id = null) {
        if (is_array($id)) {
            $a_data = $id; // par défaut : construire avec un tableau associatif id_serie, user_id
        }
        else {
            // sinon, constuire avec id_serie
            $a_data = array(
                    'ID_SERIE' => $id
            );
        }
        parent::__construct($this->table_name, $a_data);
    }

    /* Cette fonction rend l'ensemble des informations nécessaires
     * à l'affichage des séries d'un utilisateur.
     * ATTENTION : La couverture rendue est celle de la dernière BD
     *             de l'utilisateur pour la série concernée.
     *             C'est un choix !
     */
    public function select () {
        $select = " select   s.ID_SERIE
                           , s.nom as NOM_SERIE
                           , s.FLG_FINI as FLG_FINI_SERIE
                           , CASE s.FLG_FINI 
                               WHEN 0 then 'Fini' 
                               when 1 then 'En cours' 
                               when 2 then 'One Shot' 
                               when 3 then 'Interrompue/Abandonnée' 
                               ELSE '?' 
                             end as LIB_FLG_FINI_SERIE
                           , CASE 
                               WHEN s.NB_TOME > 0 THEN s.NB_TOME 
                               ELSE (select max(NUM_TOME) from bd_tome where id_serie = t.id_serie group by id_serie) 
                             END as NB_TOME
                           , s.NB_TOME as NB_TOME_FINAL
                           , s.TRI as TRI_SERIE
                           , s.HISTOIRE as HISTOIRE_SERIE
                           , g.ID_GENRE
                           , g.LIBELLE as NOM_GENRE
                           , es.NBR_USER_ID_SERIE
                           , count(distinct id_tome) as NB_ALBUM
                           , max(IMG_COUV) as IMG_COUV
                           , (select avg(MOYENNE_NOTE_TOME) 
                              from note_tome 
                              where id_tome in (select id_tome 
                                                from bd_edition e left join bd_tome t using (id_tome) 
                                                where t.id_serie = s.ID_SERIE) 
                             ) as NOTE_SERIE
                           , (select sum(NB_NOTE_TOME) 
                              from note_tome 
                              where id_tome in (select id_tome 
                                                from bd_edition e left join bd_tome t using (id_tome) 
                                                where t.id_serie = s.ID_SERIE) 
                             ) as NB_NOTE_SERIE";

        $from = " from bd_serie s
                  LEFT JOIN (SELECT ID_SERIE,NBR_USER_ID_SERIE FROM bd_edition_stat group by id_serie,NBR_USER_ID_SERIE) es using (ID_SERIE)
                  left join bd_genre g using (id_genre)
                  left join bd_tome t using (id_serie)
                  left join users_album u using (id_edition)
                  left join bd_edition e using (id_tome)";

        return $select.$from;
    }

}
?>
