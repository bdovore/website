<?php

class Users_exclusions extends Bdo_Db_Line {

    public $table_name = 'users_exclusions';
    public $error = '';

    // initialisation
    public function __construct ($id = null) {
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

    public function select () {
        return "SELECT users_exclusions.ID_TOME, bd_tome.TITRE as TITRE_TOME,
                       users_exclusions.ID_SERIE, bd_serie.NOM as NOM_SERIE
                FROM users_exclusions inner join bd_tome using (id_tome)
                inner join bd_serie on users_exclusions.id_serie = bd_serie.id_serie" ;
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

    public function getListSerieExcluSource ($user_id, $id_serie = 0) {
      /*
       * Liste des séries avec au moins une exclusion pour un user donné
       * rend en plus la source de l'exclusion : 
       *   - serie pour toute la série
       *   - album pour un (ou des) album(s).
       *
       * Si une série est passée en paramètre, la fonction rend true si 
       * toute la série est exclue, false sinon
       */
      $query  = "select   users_exclusions.id_serie as ID_SERIE
                        , case max(id_tome) when 0 then 'serie' else 'album' end as SOURCE
                 from users_exclusions 
                 where user_id = ".intval($user_id);

      // Si on ne demande qu'une série, pas de group by.
      $query .= ($id_serie) ? "  and id_serie = ".intval($id_serie) : " group by id_serie";

      $resultat = Db_query($query);

      if (!$id_serie) {
        $a_obj = array();
        while ($obj = Db_fetch_object($resultat)) {
            $a_obj[] = $obj;
        }
      } else {
        $obj = Db_fetch_object($resultat);
        $a_obj = ($obj->SOURCE == 'serie');
      }
      Db_free_result($resultat);
      return $a_obj;
    }
        
    public function getListSerieToComplete ($user_id, $flg_achat=false) {
        /*
         * Liste des séries pour lesquels il y a au moins un album à completer
         */
        if ($flg_achat) {
            $q_achat = "AND  ua.flg_achat = 'N'";
        } else {
            $q_achat = "";
        }
        $query = "
            SELECT 
                    user_serie.id_serie as ID_SERIE,
                    user_serie.nom as NOM_SERIE, 
                    count(*) nb_album,
                  max(img_couv) as IMG_COUV_SERIE,
                  user_serie.ID_GENRE,
                  user_serie.NOM_GENRE,
                  user_serie.ORIGINE
                    
            FROM
                    (
                            SELECT DISTINCT
                                    s.id_serie,
                                    s.nom,
                                    `bd_genre`.`ID_GENRE`,

                    `bd_genre`.`LIBELLE` as `NOM_GENRE`,
                    `bd_genre`.`ORIGINE`
                            FROM
                                    users_album ua
                                    INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                                    INNER JOIN bd_tome t ON t.id_tome = en.id_tome
                                    INNER JOIN bd_serie s ON t.ID_SERIE=s.ID_SERIE
                                    INNER JOIN bd_genre ON s.ID_GENRE = bd_genre.ID_GENRE
                            WHERE
                                    ua.user_id = ".intval($user_id)."
                                    AND flg_achat = 'N' 
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
                                    AND t.id_tome=en.id_tome ".$q_achat ."
                            )
                            AND NOT EXISTS (
                                    SELECT NULL
                                    FROM users_exclusions uet
                                    WHERE uet.user_id = ".intval($user_id)."
                                    AND t.id_tome=uet.id_tome
                            )
            GROUP BY user_serie.id_serie, user_serie.nom
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

        $query = "INSERT INTO users_exclusions (
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

    public function getListTomesExclus ($user_id, $id_serie) {
      /*
       * Liste des tomes exclus pour une série donnée
       */
      $query = "select id_tome 
                from users_exclusions 
                where user_id = ".intval($user_id) ."
                  and id_serie = ".intval($id_serie) ."; 
               ";

      $resultat = Db_query($query);
      $a_obj = array();
      while ($obj = Db_fetch_object($resultat)) {
          $a_obj[] = $obj->id_tome;
      }
      Db_free_result($resultat);
      return $a_obj;
    }

    public function addAlbumExclude($user_id, $id_serie, $id_tome) {
      /*
       * Fonction pour ajouter un album aux exclusions d'un user donné
       */
      $query = "INSERT IGNORE INTO users_exclusions (
                  `user_id` ,`id_tome` ,`id_serie`
                ) VALUES (
                  '".intval($user_id)."', '".intval($id_tome)."', '".intval($id_serie)."');";

      Db_query($query);

      return 1;
    }

    public function delAlbumExclude($user_id, $id_serie, $id_tome) {
      /*
       * Fonction pour retirer un album des exclusions d'un user donné
       */
      $query = "delete from users_exclusions
                where user_id  = ".intval($user_id)."
                  and id_serie = ".intval($id_serie)."
                  and id_tome  = ".intval($id_tome)."
                ;";

      Db_query($query);

      return 1;
    }

    public function replaceIdTome($old_idtome, $new_idtome) {
      Db_query("UPDATE IGNORE users_exclusions SET `id_tome` = " . intval($new_idtome) . " WHERE `id_tome`=" . intval($old_idtome));

      return Db_affected_rows();
    }
    
    public function isExclu($user_id, $id_tome, $id_serie) {
        if ($id_tome) {
            $query = "SELECT count(*) nb FROM users_exclusions WHERE user_id  = ".intval($user_id)."
                      AND ((id_tome = 0 AND id_serie = ".intval($id_serie).")
                      OR id_tome  = ".intval($id_tome).")";
        } else {
            // test sur la série uniquement
             $query = "SELECT count(*) nb FROM users_exclusions WHERE user_id  = ".intval($user_id)."
                      AND id_tome = 0 AND id_serie = ".intval($id_serie);
        }
        $resultat = Db_query($query);
        $obj = Db_fetch_object($resultat);
        return ($obj->nb);
    }

  }
?>
