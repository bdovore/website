<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * @author Tom
 *
 * Classe ppur manipuler les données perso sur les albums dans la collection de l'utilisateur
 * + ajout d'album dans la collection, sélection, etc.
 *
 * TODO : finir la méthode index, reprendre les méthodes lastachat, carre etc. dans user pour les mettre ici
 *         - refaire guest en utilisant la class useralbum
 */
class Useralbum extends Bdo_Db_Line
{

    /**
     */
    private $withUserComment = false;
    public $table_name = 'users_album';

    public $error = '';

    // initialisation
    public function __construct ($id = null) {
        if (is_array($id)) {
            $a_data = $id; // par défaut : construire avec un tableau associatif id_edition, user_id
        }
        else {
            // sinon, constuire avec id_edition
            $a_data = array(
                    'ID_EDITION' => $id
            );
        }
        parent::__construct($this->table_name, $a_data);
    }

    public function setWithUserComment ($b) {
        $this->withUserComment = $b;
    }

    public function select () {
        $select = "
                  SELECT
                          bd_tome.ID_TOME, ".
                          ( Bdo_Cfg::getVar("explicit") ? "en.IMG_COUV," : " IF (en.FLG_EXPLICIT, CONCAT('?source=',en.IMG_COUV), en.IMG_COUV) as IMG_COUV,  ") ."
                          bd_tome.TITRE as TITRE_TOME,
                          s.nom as NOM_SERIE,
                          bd_tome.NUM_TOME,
                          concat_ws(' ',er.nom, year(en.DTE_PARUTION)) as NOM_EDITION,
                          er.nom as NOM_EDITEUR,
                          c.nom as NOM_COLLECTION,
                          sc.pseudo as scpseudo,
                          de.pseudo as depseudo,
                          ua.date_ajout as DATE_AJOUT,
                          ua.user_id as USER_ID,
                          ua.flg_pret as FLG_PRET,
                          ua.nom_pret as NOM_PRET,
                          ua.email_pret as EMAIL_PRET,
                          ua.flg_dedicace as FLG_DEDICACE,
                          ua.flg_tete as FLG_TETE,
                          ua.comment,

                          ua.flg_achat as FLG_ACHAT,
                          IFNULL(ua.date_achat, ua.date_ajout) as DATE_ACHAT,
                          ua.cote,
                          ua.flg_cadeau as FLG_CADEAU,
                          ua.FLG_LU as FLG_LU,
                          ua.FLG_NUM,


                      bd_tome.PRIX_BDNET,
                      bd_tome.FLG_INT as FLG_INT_TOME,
                      bd_tome.FLG_TYPE as FLG_TYPE_TOME,
                      bd_tome.HISTOIRE as HISTOIRE_TOME,

                          s.ID_SERIE,

                          s.FLG_FINI,
                      g.ID_GENRE,
                      g.libelle as NOM_GENRE,
                      g.ORIGINE, 
                      en.ID_EDITION,
                      en.DTE_PARUTION,
                      en.ean as EAN_EDITION,
                      en.isbn as ISBN_EDITION,

                          c.ID_COLLECTION,


                      er.ID_EDITEUR,


                      bd_tome.ID_SCENAR,

                      bd_tome.ID_DESSIN,

                      bd_tome.ID_COLOR,
                      co.pseudo as copseudo,
                      bd_tome.ID_SCENAR_ALT,
                      sca.pseudo as scapseudo,
                      bd_tome.ID_DESSIN_ALT,
                      dea.pseudo as deapseudo,
                      bd_tome.ID_COLOR_ALT,
                      coa.pseudo as coapseudo,
                          DATE_FORMAT(IFNULL(ua.date_achat, ua.date_ajout),'%Y') as annee_achat,

                          DATE_FORMAT(IFNULL(ua.date_achat, ua.date_ajout),'%m') as mois_achat,
                          note_tome.NB_NOTE_TOME,
            note_tome.MOYENNE_NOTE_TOME";

        $from = "
                FROM users_album ua
                INNER JOIN bd_edition en ON ua.id_edition = en.id_edition
                INNER JOIN bd_tome ON  en.id_tome = bd_tome.id_tome
                INNER JOIN bd_serie s ON bd_tome.id_serie = s.id_serie
                INNER JOIN bd_genre g ON s.id_genre = g.id_genre

                LEFT JOIN bd_collection c ON en.id_collection = c.id_collection
                LEFT JOIN bd_editeur er ON c.id_editeur = er.id_editeur
                LEFT JOIN note_tome ON bd_tome.ID_TOME = note_tome.ID_TOME
                LEFT JOIN bd_auteur sc ON bd_tome.id_scenar = sc.id_auteur
                LEFT JOIN bd_auteur de ON bd_tome.id_dessin = de.id_auteur
                LEFT JOIN bd_auteur co ON bd_tome.id_color = co.id_auteur
                LEFT JOIN bd_auteur sca ON bd_tome.id_scenar_alt = sca.id_auteur
                LEFT JOIN bd_auteur dea ON bd_tome.id_dessin_alt = dea.id_auteur
                LEFT JOIN bd_auteur coa ON bd_tome.id_color_alt = coa.id_auteur";

        if ($this->withUserComment) {
            $select .= ",
                uco.NOTE USER_NOTE,
                uco.COMMENT USER_COMMENT";
            $from .= "
                 LEFT JOIN users_comment uco on (uco.USER_ID = ua.user_id and uco.ID_TOME = bd_tome.ID_TOME) ";
        }
        return $select.$from;
    }

    private function getReqAuteur($user_id,$champ,$search="") {
      $req = "select t.".$champ." as auteur
              from users_album u
              inner join bd_edition e using (id_edition)
              inner join bd_tome t using (id_tome)
              inner join bd_genre g using (id_genre)
              inner join bd_auteur a on (id_auteur = ".$champ.")
              where ".$champ." <> 0
                and u.user_id = " . $user_id ."
                and pseudo not in ('<n&b>','<indéterminé>','Collectif')";
      if ($search) {
        $search = ($search[0] == '^') ? substr($search,1) : '%' . $search;
        $search = (substr($search,-1) == '$') ? substr($search,0,-1) : $search . '%'; 
        $req .= " and ( pseudo like '". $search ."' or nom like '". $search ."' or prenom like '". $search ."' ) ";
      }
      
          return $req;
    }

    public function getStatistiques($user_id,$stat="all",$auteur="",$origin="",$travail="",$search="") {
        // fonction qui renvoit les statistiques d'une collection
        // Charge les statistisques
        $nbcoffrets = 0;
        $nbintegrales = 0;
        $nbfuturs_achats = 0;
        $nbeditidion = 0;
        $nbtomes = 0;
        $nbseries = 0;
        $nbauteurs = 0;
        if ($stat == "all" or $stat=="album") {
            $query = "

            select
                    count(*) as countofedition,
                    count(distinct t.id_serie) as countofserie,
                    count(distinct t.id_tome) as countofalb
            from
                    users_album u
                    INNER JOIN bd_edition en ON en.id_edition = u.id_edition
                    INNER JOIN bd_tome t ON t.id_tome = en.id_tome
                    inner join bd_serie s using (id_serie)
                    inner join bd_genre g on g.id_genre = s.id_genre
            where
                    u.user_id=" . $user_id . "
                and u.flg_achat='N'
            ";

            if ($origin !== "")
              $query .= " and g.origine = '" . $origin . "'" ;

            // FRED : Pour les albums de Ma Collection, le nombre d'albums avec un critère de recherche (searchvalue) 
            //        ne vient pas de cette fonction. Seule les séries passent par là.
            //        Donc, pour le moment, on considère qu'un $search renseigné ne concerne que les séries...
            if($search) {
              $search = ($search[0] == '^') ? substr($search,1) : '%' . $search;
              $search = (substr($search,-1) == '$') ? substr($search,0,-1) : $search . '%'; 
              $query .= " and ( s.nom like '". $search ."' ) ";
            }

            if ($auteur <> "")
              $query .= "and (   id_scenar = ".$auteur." or id_scenar_alt = ".$auteur." 
                              or id_dessin = ".$auteur." or id_dessin_alt = ".$auteur." 
                              or id_color = ".$auteur." or id_color_alt = ".$auteur.")";

            $resultat = Db_query($query);

            $obj = Db_fetch_object($resultat);

            $nbtomes = $obj->countofalb;
            
            $nbeditidion = $obj->countofedition;

            $nbseries = $obj->countofserie;

            Db_free_result($resultat);

        }

        // Futurs achats

        if ($stat == "all" or $stat == "achat") {

            $nbfuturs_achats = Db_CountRow("select * from users_album u "
                    . "INNER JOIN bd_edition en ON en.id_edition = u.id_edition

                    INNER JOIN bd_tome t ON t.id_tome = en.id_tome where u.user_id=" . $user_id . " and u.flg_achat='O'");
        }



        // Inégrales
        if ($stat == "all" or $stat == "integrale") {
            $nbintegrales = Db_CountRow("

            select * from

                    users_album u

                    INNER JOIN bd_edition en ON en.id_edition = u.id_edition

                    INNER JOIN bd_tome t ON t.id_tome = en.id_tome

            where

                    u.user_id=" . $user_id . "

                    and t.flg_int = 'O'

                    and flg_achat='N'

            ");
        }


        // coffrets
        if ($stat == "all" or $stat == "coffret") {
            $nbcoffrets  = Db_CountRow("

            select * from

                    users_album u

                    INNER JOIN bd_edition en ON en.id_edition = u.id_edition

                    INNER JOIN bd_tome t ON t.id_tome = en.id_tome

            where

                    u.user_id=" . $user_id . "

                    and t.flg_type = '1'

                    and flg_achat='N'

            ");
        }
        
        // Auteurs
        if ($stat == "all" or $stat == "auteur") {

          if ($origin == "")
            $where = " ";
          else
            $where = " and g.origine in ('" . implode("','",$origin) . "')" ;

          $req = "";
          if (is_array($travail)) {
            if (in_array("Scénariste",$travail)) {
              $req .= ($req) ? " union " : "";
              $req .=             $this->getReqAuteur($user_id,'id_scenar',$search) . $where;
              $req .= " union " . $this->getReqAuteur($user_id,'id_scenar_alt',$search) . $where;
            }

            if (in_array("Dessinateur",$travail)) {
              $req .= ($req) ? " union " : "";
              $req .=             $this->getReqAuteur($user_id,'id_dessin',$search) . $where;
              $req .= " union " . $this->getReqAuteur($user_id,'id_dessin_alt',$search) . $where;
            }

            if (in_array("Coloriste",$travail)) {
              $req .= ($req) ? " union " : "";
              $req .=             $this->getReqAuteur($user_id,'id_color',$search) . $where;
              $req .= " union " . $this->getReqAuteur($user_id,'id_color_alt',$search) . $where;
            }
            $req  = "
          select distinct auteur
          from (" . $req . "
            ) a 
          ";
          
          $nbauteurs  = Db_CountRow($req);
          } else {
              $nbauteurs = 0;
          }

          
        }
        
        $a_result = array(
            "nbcoffrets" => $nbcoffrets,
            "nbintegrales" => $nbintegrales,
            "nbfuturs_achats" => $nbfuturs_achats,
            "nbeditions" => $nbeditidion,
            "nbtomes" => $nbtomes,
            "nbseries" => $nbseries,
            "nbauteurs" => $nbauteurs

        );

        return ($a_result);

    }

    public function lastAchat ($user_id, $limit = 5) {
         $where = " where
            ua.user_id=" . $user_id . "
            and ua.flg_achat='N'
                 order by IFNULL(ua.date_achat,ua.date_ajout) desc
            limit 0,".$limit;

         return $this->load("c", $where);


    }

    public function lastFuturAchat ($user_id, $limit = 5) {
         $where = " where

            ua.user_id=" . $user_id . "

            and ua.flg_achat='O'
                 order by ua.date_ajout desc

            limit 0,".$limit;

         return $this->load("c", $where);


    }

    public function getValorisation($user_id) {
        /*
         * Effectue la requête pour obtenir les détail de la valorisation de la collection
         * Retour :
         * - nb_album : nombre d'album
         * - valorisation : valorisation de la collection
         * -
         */
        $query = "select round(sum((case when ua.cote > 0 then ua.cote
                                    when bd_tome.prix_bdnet > 0 then prix_bdnet
                                    when bd_tome.flg_int = 'O' then val_int else
            val_alb end) + IF (bd_tome.flg_type = 1 AND u.val_cof_type = 0 , val_cof, 0)),2)  as valorisation,
                count(*) nb_album,
                        count(IF((ua.cote > 0),1,null)) nb_val_user,
                        count(IF((ua.cote = 0 or ua.cote is null) and bd_tome.prix_bdnet > 0,1,null)) as nb_val_bdovore,
                        count(IF((ua.cote = 0 or ua.cote is null) and (bd_tome.prix_bdnet = 0 OR bd_tome.prix_bdnet is null),1,null)) as nb_val_defaut,
                        count(IF(bd_tome.flg_type = 1,1,null)) as nb_coffret,

                        round(sum(IF((ua.cote > 0),ua.cote,0)),2) as val_user,
                        round(sum(IF((ua.cote = 0 or ua.cote is null) and bd_tome.prix_bdnet > 0,bd_tome.prix_bdnet,0)),2) as val_bdovore,
                        round(sum(IF((ua.cote = 0 or ua.cote is null) and (bd_tome.prix_bdnet = 0 OR bd_tome.prix_bdnet is null),IF(flg_int = 'O',val_int ,val_alb) , 0)),2) as val_defaut,
                        round(sum(IF(bd_tome.flg_type = 1 and u.val_cof_type = 0 ,val_cof,0)),2) as val_coffret
                from users_album ua inner join users u using(user_id)
                inner join bd_edition on ua.id_edition = bd_edition.id_edition
                inner join bd_tome on bd_tome.id_tome = bd_edition.id_tome
                where ua.user_id = ".intval($user_id) ." and
                ua.flg_achat = 'N'";
        $resultat = Db_query($query);

         $obj = Db_fetch_object($resultat);

         return $obj;
    }

    public function Carre($user) {
        /*
         * Fonction qui renvoie les albums du carré magique de l'utilisateur
         *
         */
        // Selections des 9 albums les mieux notés

        if ($user->CARRE_TYPE == 0) {

            $query = "
              SELECT
                  t.ID_TOME,
                  t.TITRE as TITRE_TOME,".
                 ( Bdo_Cfg::getVar("explicit") ? "en.IMG_COUV," : " IF (en.FLG_EXPLICIT, CONCAT('?source=',en.IMG_COUV), en.IMG_COUV) as IMG_COUV,  ") ."
                  en.ID_EDITION
              FROM
                  users_album ua
                  INNER JOIN bd_edition en ON en.id_edition = ua.id_edition
                  LEFT JOIN users_comment uc ON  uc.id_tome = en.id_tome AND uc.user_id = ua.user_id
                  INNER JOIN bd_tome t ON t.id_tome = en.id_tome
              WHERE
                  ua.user_id=" . $user->user_id . "
                  and ua.flg_achat='N'
              ORDER BY uc.note desc
              LIMIT 0,9";
        }

        // Selections du carre magique
        else {
            $query = "
              select
                  t.ID_TOME,
                  t.TITRE as TITRE_TOME,".
                  ( Bdo_Cfg::getVar("explicit") ? "en.IMG_COUV," : " IF (en.FLG_EXPLICIT, CONCAT('?source=',en.IMG_COUV), en.IMG_COUV) as IMG_COUV,  ") ."
                  t.ID_EDITION
              from
                  users_list_carre ulc
                  INNER JOIN bd_tome t ON t.id_tome = ulc.id_tome
                  INNER JOIN bd_edition en ON en.id_edition = t.id_edition
              where
                  ulc.user_id=" . $user->user_id . "
              ORDER BY ulc.rang
              limit 0,9
              ";
        }
        $resultat = Db_query($query);

        return Db_fetch_all_obj($resultat, 'ID_TOME');
    }

    public function deleteTome($id_tome) {
        /*
         * Suppresio d'id tome dans users_album
         */
        Db_query("DELETE users_album.* FROM users_album INNER JOIN bd_edition USING(id_edition)
                  WHERE bd_edition.`id_tome`=" . intval($id_tome));

        return Db_affected_rows();

    }

    public function replaceEditionFromTome($id_tome,$id_edition) {
        /*
         * Suppresion d'id tome : on transfert les édition existantes d'un album vers une éditoin par défaut, si l'album n'est pas déjà référencé
         */
        Db_query("UPDATE IGNORE users_album INNER JOIN bd_edition using(id_edition)
                  SET id_edition = ". intval($id_edition)." WHERE  bd_edition.id_tome = ". intval($id_tome));

        return Db_affected_rows();

    }

    public function replaceEditionFromEdition($source_id,$dest_id) {
        /*
         * Suppresion d'id tome : on transfert les édition existantes d'un album vers une éditoin par défaut, si l'album n'est pas déjà référencé
         */
        Db_query("UPDATE IGNORE users_album
                  SET id_edition = ". intval($dest_id)." WHERE  id_edition = ". intval($source_id));

        return Db_affected_rows();

    }
    
    public function addSerieForUser($id_serie, $user_id, $flg_achat = 'N') {
        /*
         * Ajout de l'ensemble des albums d'une série dans la collection du user connecté
         */
        if ($flg_achat <> "N") {
            $flg_achat = "O";
           
        }
        $query = "INSERT INTO users_album (user_id, id_edition, flg_pret, nom_pret, email_pret, flg_dedicace, flg_tete, comment, date_ajout, flg_achat, date_achat, cote, flg_cadeau, FLG_LU) "
                . "select  ".intVal($user_id) .", bd_tome.id_edition, 'N', null, null, 'N', 'N', null, now(), '".$flg_achat."', ".(($flg_achat == "N") ? "now()," : "null," ) ." null, 'N', 'N' 
                    from bd_tome LEFT JOIN users_album on (bd_tome.id_edition = users_album.id_edition and users_album.user_id = ".intVal($user_id) .")
                    WHERE  bd_tome.id_serie = ".intVal($id_serie) ." AND users_album.id_edition is null "
                . "";
         Db_query($query);
        return Db_affected_rows();
    }
    
    public function getUserSerie ($user_id, $page=1, $length=10, $search = "", $origin= "",$auteur = "", $liste = "", $complet="", $from=0) {
      if ($auteur <> "")
        $whereAut = "and (   id_scenar = ".$auteur." or id_scenar_alt = ".$auteur." 
                          or id_dessin = ".$auteur." or id_dessin_alt = ".$auteur." 
                          or id_color = ".$auteur." or id_color_alt = ".$auteur.")";
      else
        $whereAut = "";
      $select = "
        SELECT `bd_serie`.`ID_SERIE`
             , `bd_serie`.`NOM` as `NOM_SERIE`
             , `bd_serie`.`FLG_FINI` as `FLG_FINI_SERIE`
             , CASE bd_serie.FLG_FINI WHEN 0 then 'Fini' when 1 then 'En cours' when 2 then 'One Shot' when 3 then 'Interrompue' ELSE '?' end LIB_FLG_FINI_SERIE
             , CASE WHEN bd_serie.NB_TOME > 0 THEN bd_serie.NB_TOME ELSE count(distinct bd_tome.NUM_TOME) END  as `NB_TOME`
             , bd_serie.NB_TOME as NB_TOME_FINAL
             , `bd_serie`.`TRI` as `TRI_SERIE`
             , `bd_serie`.`HISTOIRE` as `HISTOIRE_SERIE`
             , `bd_genre`.`ID_GENRE`
             , `bd_genre`.`LIBELLE` as `NOM_GENRE`             
             , count(distinct bd_tome.ID_TOME) as NB_ALBUM ,".
             (Bdo_Cfg::getVar('explicit') ? "max(img_couv)" : " max(IF (bd_edition.FLG_EXPLICIT, CONCAT('?source=',bd_edition.IMG_COUV), bd_edition.IMG_COUV)) " )  ." as IMG_COUV_SERIE
             , avg(MOYENNE_NOTE_TOME) NOTE_SERIE
             , sum(NB_NOTE_TOME) NB_NOTE_SERIE
             , USER_SERIE.NB_USER_ALBUM
             , COUNT(USER_EXCLU.id_serie) > 0 IS_EXCLU
             , bd_genre.ORIGINE
        FROM bd_serie 
        INNER JOIN (select bd_tome.id_serie, count(*) NB_USER_ALBUM 
                    from users_album 
                    inner join bd_edition using (id_edition) 
                    inner join bd_tome using (id_tome)
                    where flg_achat = 'N'" .$whereAut."
                      and users_album.user_id = ".$user_id ." group by id_serie) USER_SERIE on USER_SERIE.id_serie = bd_serie.id_serie
        LEFT JOIN `bd_genre` USING(`ID_GENRE`)
       LEFT JOIN (select id_serie from users_exclusions where user_id = ". $user_id." and id_tome = 0) USER_EXCLU on ( USER_EXCLU.id_serie = bd_serie.ID_SERIE)
        LEFT JOIN bd_tome on bd_tome.ID_SERIE = bd_serie.ID_SERIE
        LEFT JOIN bd_edition using (id_edition)
        LEFT JOIN note_tome on (bd_tome.ID_TOME =note_tome.ID_TOME)
        ";

      $where = " WHERE 1 ";
      if ($origin <> "") {
        $where .= " and bd_genre.ORIGINE = '".$origin ."'";
      }

      if($search) {
        $search = ($search[0] == '^') ? substr($search,1) : '%' . $search;
        $search = (substr($search,-1) == '$') ? substr($search,0,-1) : $search . '%'; 
        $where .= " and ( bd_serie.nom like '". $search ."' ) ";
      }

      if ($liste <> "") {
        $where .= " and `bd_serie`.`ID_SERIE` in (" . $liste . ") ";
      }

      $group= "
         group by bd_serie.nom, bd_serie.ID_SERIE "
           
      ;
      $limit = "LIMIT ".($from + ($page - 1)*$length).", ".$length;
      if ($complet == "N") {
          $having = " HAVING NB_USER_ALBUM < NB_ALBUM  ";
      } else if ($complet == "O") {
           $having = " HAVING NB_USER_ALBUM >= NB_ALBUM  ";
      } else {
          $having = "";
      }
      $query = $select.$where.$group.$having.$limit;
      $resultat = Db_query($query);
      $obj = Db_fetch_all_obj($resultat,"ID_SERIE");

      return $obj;
    }

    public function getUserAuteur ($user_id, $page=1, $length=10, $search = "", $origin = "",$travail="") {
      $select = " 
        select auteur
            , pseudo
            , nom
            , prenom
            , img_aut
            , sum(gbd) as gbd
            , sum(gcomics) as gcomics
            , sum(gmangas) as gmangas
            , count(distinct id_tome) as nbtomes
            , sum(scenar) as scenar
            , sum(dessin) as dessin
            , sum(color) as color
        from (
            select t.id_scenar as auteur, pseudo, nom, prenom, img_aut, t.id_tome
                , case g.origine when 'BD'     then 1 else 0 end gbd
                , case g.origine when 'Mangas' then 1 else 0 end gmangas
                , case g.origine when 'Comics' then 1 else 0 end gcomics
                , 1 as scenar
                , 0 as dessin
                , 0 as color
            from users_album u
            inner join bd_edition e using (id_edition)
            inner join bd_tome t using (id_tome)
            inner join bd_auteur a on (id_auteur = id_scenar)
            inner join bd_genre g using (id_genre)
            where id_scenar <> 0
              and u.user_id = " . $user_id . "

            union
            
            select t.id_scenar_alt as auteur, pseudo, nom, prenom, img_aut, t.id_tome
                , case g.origine when 'BD'     then 1 else 0 end gbd
                , case g.origine when 'Mangas' then 1 else 0 end gmangas
                , case g.origine when 'Comics' then 1 else 0 end gcomics
                , 1 as scenar
                , 0 as dessin
                , 0 as color
            from users_album u
            inner join bd_edition e using (id_edition)
            inner join bd_tome t using (id_tome)
            inner join bd_genre g using (id_genre)
            inner join bd_auteur a on (id_auteur = id_scenar_alt)
            where id_scenar_alt <> 0
              and u.user_id = " . $user_id . "
            
            union
            
            select t.id_dessin as auteur, pseudo, nom, prenom, img_aut, t.id_tome
                , case g.origine when 'BD'     then 1 else 0 end gbd
                , case g.origine when 'Mangas' then 1 else 0 end gmangas
                , case g.origine when 'Comics' then 1 else 0 end gcomics
                , 0 as scenar
                , 1 as dessin
                , 0 as color
            from users_album u
            inner join bd_edition e using (id_edition)
            inner join bd_tome t using (id_tome)
            inner join bd_genre g using (id_genre)
            inner join bd_auteur a on (id_auteur = id_dessin)
            where id_dessin <> 0
              and u.user_id = " . $user_id . "
            
            union
            
            select t.id_dessin_alt as auteur, pseudo, nom, prenom, img_aut, t.id_tome
                , case g.origine when 'BD'     then 1 else 0 end gbd
                , case g.origine when 'Mangas' then 1 else 0 end gmangas
                , case g.origine when 'Comics' then 1 else 0 end gcomics
                , 0 as scenar
                , 1 as dessin
                , 0 as color     
            from users_album u
            inner join bd_edition e using (id_edition)
            inner join bd_tome t using (id_tome)
            inner join bd_genre g using (id_genre)
            inner join bd_auteur a on (id_auteur = id_dessin_alt)
            where id_dessin_alt <> 0
              and u.user_id = " . $user_id . "
            
            union
            
            select t.id_color as auteur, pseudo, nom, prenom, img_aut, t.id_tome
                , case g.origine when 'BD'     then 1 else 0 end gbd
                , case g.origine when 'Mangas' then 1 else 0 end gmangas
                , case g.origine when 'Comics' then 1 else 0 end gcomics
                , 0 as scenar
                , 0 as dessin
                , 1 as color
            from users_album u
            inner join bd_edition e using (id_edition)
            inner join bd_tome t using (id_tome)
            inner join bd_genre g using (id_genre)
            inner join bd_auteur a on (id_auteur = id_color)
            where id_color <> 0
              and u.user_id = " . $user_id . "
            
            union
            
            select t.id_color_alt as auteur, pseudo, nom, prenom, img_aut, t.id_tome
                , case g.origine when 'BD'     then 1 else 0 end gbd
                , case g.origine when 'Mangas' then 1 else 0 end gmangas
                , case g.origine when 'Comics' then 1 else 0 end gcomics
                , 0 as scenar
                , 0 as dessin
                , 1 as color
            from users_album u
            inner join bd_edition e using (id_edition)
            inner join bd_tome t using (id_tome)
            inner join bd_genre g using (id_genre)
            inner join bd_auteur a on (id_auteur = id_color_alt)
            where id_color_alt <> 0
              and u.user_id = " . $user_id . "
        ) a
      ";
      
      $where = " WHERE left(pseudo,1) REGEXP '[[:alnum:]]' and pseudo <> 'Collectif'";

      $type = "";
      if (in_array("BD",$origin))                                   $type .= " gbd > 0 "  ;
      if (in_array("Comics",$origin)) { if ($type) $type .= " or "; $type .= " gcomics > 0 "; }
      if (in_array("Mangas",$origin)) { if ($type) $type .= " or "; $type .= " gmangas > 0 "; }
      $where .= " and (" .$type.") ";

      $type = "";
      if (in_array("Scénariste",$travail))                                $type .= " scenar > 0 "  ;
      if (in_array("Dessinateur",$travail)) { if ($type) $type .= " or "; $type .= " dessin > 0 "; }
      if (in_array("Coloriste",$travail))   { if ($type) $type .= " or "; $type .= " color > 0 "; }
      $where .= " and (" .$type.") ";

      if($search) {
        $search = ($search[0] == '^') ? substr($search,1) : '%' . $search;
        $search = (substr($search,-1) == '$') ? substr($search,0,-1) : $search . '%'; 
        $where .= " and ( pseudo like '". $search ."' or nom like '". $search ."' or prenom like '". $search ."' ) ";
      }
      
      $order= "
        group by auteur,pseudo,nom,prenom,img_aut
        order by pseudo
        LIMIT ".(($page - 1)*$length).", ".$length
      ;
      $query = $select.$where.$order;
      $resultat = Db_query($query);
      $obj = Db_fetch_all_obj($resultat);

      return $obj;
    }
    
    function isSerieInCollection ($id_serie, $user_id) {
        // test si l'utilisateur possède au moins un album dans la collection et renvoi le nombre d'album
        $query = "select count(*) as nb"
                . " from users_album "
                . " inner join bd_edition using (id_edition) "
                . " INNER JOIN bd_tome using (id_tome) "
                . " WHERE bd_tome.id_serie = ".intval($id_serie) 
                . " AND flg_achat = 'N' AND"
                . " users_album.user_id =  ". $user_id ;
       $resultat = Db_query($query);
        $obj = Db_fetch_all_obj($resultat);  
        return ($obj[0]->nb);
    }
    
    function getStatByGenre ($user_id) {
        $query = "
            SELECT
                g.ID_GENRE,
                g.libelle,
                count(en.id_edition) as nbtome
            FROM
                users_album u
                INNER JOIN bd_edition en ON en.id_edition = u.id_edition
                INNER JOIN bd_tome t ON t.id_tome = en.id_tome
                INNER JOIN bd_serie s ON t.id_serie = s.id_serie
                INNER JOIN bd_genre g ON s.id_genre = g.id_genre
            WHERE
                t.flg_type = 0
                AND u.flg_achat = 'N'
                AND u.user_id =".intval($user_id)."
            GROUP BY g.libelle, g.Id_GENRE
            ORDER BY nbtome DESC";
        $resultat = Db_query($query);
        $obj = Db_fetch_all_obj($resultat);

      return $obj;
    }
    
    function getStatByEditeur ($user_id) {
        $query = "
        SELECT
            er.id_editeur,
            er.nom,
            count(en.id_edition) as nbtome
        FROM
            users_album u
            INNER JOIN bd_edition en ON en.id_edition = u.id_edition
            INNER JOIN bd_editeur er ON er.id_editeur = en.id_editeur
            INNER JOIN bd_tome t ON t.id_tome = en.id_tome
        WHERE
            t.flg_type = 0
            AND u.flg_achat = 'N'
            AND u.user_id =" . intval($user_id) . "
        GROUP BY er.nom, er.id_editeur
        ORDER BY nbtome DESC
        ";
        $resultat = Db_query($query);
        $obj = Db_fetch_all_obj($resultat);

      return $obj;
    }
    
    function getStatByNotes ($user_id) {
        $query = "
        SELECT note, count(*) as nbnotes FROM users_comment
        WHERE user_id =". intval($user_id) ." AND note IS NOT NULL
        GROUP BY note
        ORDER BY note
        ";
        $resultat = Db_query($query);
        $stat = array();
        $obj = Db_fetch_object($resultat);
        for ($i = 1; $i <= 10 ; $i++) {
          if ($obj->note ?? -1 == $i) {
              $stat[] = $obj;
              $obj = Db_fetch_object($resultat);
          }  else {
              $stat[] = array("note" => $i, "nbnotes" => 0);
              
          }
        }
        

      return $stat;
        
    }
    
    function getAuteurFavoris ($user_id) {
        $query = "SELECT id_auteur, pseudo, sum(score) as score
            FROM (
                SELECT
                    s.id_auteur,
                    s.pseudo,
                    sum(IF(id_scenar = id_dessin, 1.5, 1)) as score
                FROM
                    users_album u
                    INNER JOIN bd_edition en ON en.id_edition = u.id_edition
                    INNER JOIN bd_tome t ON t.id_tome = en.id_tome
                    INNER JOIN bd_auteur s ON t.id_scenar = s.id_auteur
                    WHERE
                    flg_achat = 'N' AND
                    u.user_id = ". intval($user_id)." 
                GROUP BY id_auteur, s.pseudo
               UNION 
                SELECT
                    s.id_auteur,
                    s.pseudo,
                    count(*) as score
                FROM
                    users_album u
                    INNER JOIN bd_edition en ON en.id_edition = u.id_edition
                    INNER JOIN bd_tome t ON t.id_tome = en.id_tome
                    INNER JOIN bd_auteur s ON t.id_dessin = s.id_auteur
                WHERE
                    flg_achat = 'N' AND
                    u.user_id = ". intval($user_id)." AND id_scenar <> id_dessin
                    GROUP BY id_auteur, s.pseudo) scoreaut
            GROUP BY id_auteur, pseudo
            ORDER BY score desc
            ";
        $resultat = Db_query($query);
        $obj = Db_fetch_all_obj($resultat);

      return $obj;
    }
}
?>
