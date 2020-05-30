<?php

/**
 *
 * @author laurent
 *
 */
class Tome extends Bdo_Db_Line
{

    /**
     */
    public $table_name = 'bd_tome';

    public $error = '';
    var $default_select = "
        SELECT SQL_CALC_FOUND_ROWS
            bd_tome.ID_TOME,
            bd_tome.TITRE as TITRE_TOME,
            bd_tome.NUM_TOME,
            bd_tome.PRIX_BDNET,
            bd_tome.FLG_INT as FLG_INT_TOME,
            bd_tome.FLG_TYPE as FLG_TYPE_TOME,
            bd_tome.HISTOIRE as HISTOIRE_TOME,
            note_tome.NB_NOTE_TOME,
            note_tome.MOYENNE_NOTE_TOME,
            bd_edition_stat.NBR_USER_ID_TOME,

            s.ID_SERIE,
            s.NOM as NOM_SERIE,
            s.TRI as TRI,
            s.flg_fini as FLG_FINI,
            s.nb_tome as NB_TOME,
            s.HISTOIRE as HISTOIRE_SERIE,

            g.ID_GENRE,
            g.libelle as NOM_GENRE,
            g.ORIGINE,

            en.ID_EDITION,
            en.IMG_COUV,
            en.ean as EAN_EDITION,
            en.isbn as ISBN_EDITION,
            en.DTE_PARUTION,
            en.COMMENT as COMMENT_EDITION,
            c.ID_COLLECTION,
            c.nom as NOM_COLLECTION,

            er.ID_EDITEUR,
            er.nom as NOM_EDITEUR,
            concat_ws(' ',er.nom, year(en.DTE_PARUTION)) as NOM_EDITION,
            bd_tome.ID_SCENAR,
            sc.pseudo as scpseudo,
            bd_tome.ID_DESSIN,
            de.pseudo as depseudo,
            bd_tome.ID_COLOR,
            co.pseudo as copseudo,
            bd_tome.ID_SCENAR_ALT,
            sca.pseudo as scapseudo,
            bd_tome.ID_DESSIN_ALT,
            dea.pseudo as deapseudo,
            bd_tome.ID_COLOR_ALT,
            coa.pseudo as coapseudo";
    var $defaut_from="
            FROM bd_tome
            INNER JOIN bd_serie s ON bd_tome.id_serie = s.id_serie
            INNER JOIN bd_genre g ON s.id_genre = g.id_genre

            INNER JOIN bd_edition en ON bd_tome.id_edition = en.id_edition
            LEFT JOIN bd_collection c ON en.id_collection = c.id_collection
            LEFT JOIN bd_editeur er ON c.id_editeur = er.id_editeur

            LEFT JOIN bd_edition_stat ON bd_tome.id_edition = bd_edition_stat.ID_EDITION
            LEFT JOIN note_tome ON bd_tome.ID_TOME = note_tome.ID_TOME

            LEFT JOIN bd_auteur sc ON bd_tome.id_scenar = sc.id_auteur
            LEFT JOIN bd_auteur de ON bd_tome.id_dessin = de.id_auteur
            LEFT JOIN bd_auteur co ON bd_tome.id_color = co.id_auteur
            LEFT JOIN bd_auteur sca ON bd_tome.id_scenar_alt = sca.id_auteur
            LEFT JOIN bd_auteur dea ON bd_tome.id_dessin_alt = dea.id_auteur
            LEFT JOIN bd_auteur coa ON bd_tome.id_color_alt = coa.id_auteur
                ";

    // initialisation
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
        $select = $this->default_select;
        $from = $this->defaut_from;


        return $select.$from;
    }

    public function search ($a_data = array())
    {
        // --------------------------------------------------------------------
        // -------- Champs selectionnés par defaut --------
        if (empty($a_data)) $a_data = $_POST;
        if (! isset($a_data['validSubmitSearch'])) {
            $a_data['ch_titre'] = "checked";
            $a_data['ch_s_nom'] = "checked";
            $a_data['ch_libelle'] = "checked";
            $a_data['ch_ean'] = "checked";
            $a_data['ch_cnom'] = "checked";
            $a_data['ch_enom'] = "checked";
            $a_data['ch_LIBELLE'] = "checked";

        }

        $dbSearch = new Bdo_Db_Search();

        $dbSearch->select = "
            SELECT
                t.id_tome,
                t.titre,
                t.num_tome,
                t.prix_bdnet,
                t.flg_int,
                t.flg_type,
                t.histoire,
                t.nb_vote,
                t.moyenne,

                s.id_serie,
                s.nom s_nom,

                g.id_genre,
                g.libelle,

                en.id_edition,
                en.img_couv,
                en.ean,
                en.isbn,

                c.id_collection,
                c.nom cnom,

                er.id_editeur,
                er.nom enom,

                t.id_scenar,
                sc.pseudo as scpseudo,
                t.id_dessin,
                de.pseudo as depseudo,
                t.id_color,
                co.pseudo as copseudo,
                t.id_scenar_alt,
                sca.pseudo as scapseudo,
                t.id_dessin_alt,
                dea.pseudo as deapseudo,
                t.id_color_alt,
                coa.pseudo as coapseudo
                    ";

        // dans les tables
        $dbSearch->from = "
            FROM bd_tome t
                INNER JOIN bd_serie s ON t.id_serie = s.id_serie
                INNER JOIN bd_genre g ON s.id_genre = g.id_genre

                INNER JOIN bd_edition en ON t.id_edition = en.id_edition
                INNER JOIN bd_collection c ON en.id_collection = c.id_collection
                INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur

                LEFT JOIN bd_auteur sc ON t.id_scenar = sc.id_auteur
                LEFT JOIN bd_auteur de ON t.id_dessin = de.id_auteur
                LEFT JOIN bd_auteur co ON t.id_color = co.id_auteur
                LEFT JOIN bd_auteur sca ON t.id_scenar_alt = sca.id_auteur
                LEFT JOIN bd_auteur dea ON t.id_dessin_alt = dea.id_auteur
                LEFT JOIN bd_auteur coa ON t.id_color_alt = coa.id_auteur
                    ";

        $dbSearch->where = "WHERE 1";

        // dans l'ordre
        if ($a_data['daff'] == "") $a_data['daff'] = "0";
        if ($a_data['sens_tri'] == "") $a_data['sens_tri'] = "ASC";
        if ($a_data['col_tri'] == "") $a_data['col_tri'] = "t.titre";

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

    public function simil() {
        $selectTome = new Tome();

        $selectTome->load('c', "WHERE bd_tome.ID_TOME IN(SELECT ID_TOME_SIMIL FROM bd_tome_simil WHERE ID_TOME=".$this->ID_TOME.")");

        return $selectTome->dbSelect->a_dataQuery;
    }

    public function getUserActualite($mode=1,$nb_mois=1, $page=1) {
        /*
         * Focntion qui renvoie une liste d'album en dehors de la collection
         * parue depuis nb mois
         *
         * Mode : 1=Serie, 2=Auteur, 3=Intégrales/Coffret
         */

        $user_id = intval($_SESSION["userConnect"]->user_id);
        $mode = intval($mode);
        $nb_mois = intval($nb_mois);
        $page = intval($page);

        // Récupère le nombre d'albums
        if ($mode == 1)
        {
            $query = "

                INNER JOIN
                (
                    SELECT DISTINCT t.id_serie,s.nom,s.id_genre
                    FROM
                        users_album ua
                        INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                        INNER JOIN bd_tome t ON t.id_tome = en.id_tome
                        INNER JOIN bd_serie s ON s.id_serie = t.id_serie
                    WHERE
                        ua.user_id=".$user_id."
                        AND NOT EXISTS (
                                    SELECT NULL FROM users_exclusions ues
                                    WHERE s.id_serie=ues.id_serie
                                    AND ues.id_tome = 0
                                    AND ues.user_id = ".$user_id."
                                )
                ) list_serie ON bd_tome.id_serie=list_serie.id_serie

                WHERE
                    NOT EXISTS (
                        SELECT NULL
                        FROM users_album ua
                        INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
                        WHERE
                        ua.user_id = ".$user_id."
                        AND bd_tome.id_tome=en.id_tome
                    )
                    AND NOT EXISTS (
                        SELECT NULL
                        FROM users_exclusions uet
                        WHERE uet.user_id = ".$user_id."
                        AND bd_tome.id_tome=uet.id_tome
                    )
                            ";

        }
        elseif ($mode == 2) {

            // recherche des auteurs preferes

            // liste auteur
            $query = "


            WHERE
                    (bd_tome.id_scenar IN (SELECT id_auteur FROM users_list_aut WHERE user_id = ". $user_id .") OR
                        bd_tome.id_dessin IN (SELECT id_auteur FROM users_list_aut WHERE user_id = ". $user_id ."))

                AND NOT EXISTS (
                        SELECT NULL
                        FROM users_album ua
                        INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
                        WHERE
                        ua.user_id = ".$user_id."
                        AND bd_tome.id_tome=en.id_tome
                    )
                    AND NOT EXISTS (
                            SELECT NULL
                            FROM users_exclusions uet
                            WHERE uet.user_id = ". $user_id ."
                            AND bd_tome.id_tome=uet.id_tome
                    )
            ";
        }
        elseif ($mode == 3) {
            // liste integrale et coffrets
            $query = "
            WHERE
                (bd_tome.flg_int ='O' OR bd_tome.flg_type = 1)

                AND ua.id_edition is null
                AND NOT EXISTS (
                        SELECT NULL
                        FROM users_exclusions uet
                        WHERE uet.user_id = ". $user_id ."
                        AND bd_tome.id_tome=uet.id_tome
                )
            ";
        }

        if ($nb_mois > 0 ) { // cas des parutions passées
            $query .= "AND en.dte_parution >= DATE_SUB(NOW(), INTERVAL ". $nb_mois." MONTH) "
                    . "AND en.dte_parution <= NOW()";
        } else {
            // à paraitre
            $query .= "AND en.dte_parution >= NOW()";
        }
        $query .= " ORDER BY en.dte_parution";

        $query .= " limit ".(($page-1)*20).", 20";

        //echo $this->select().$query;
        return $this->load("c",$query);

    }

    public function getCountAlbumToComplete ($user_id) {
         $user_id = intval($user_id);
         $query = " , (
        SELECT DISTINCT
            s.*
        FROM
            users_album ua
            INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
            INNER JOIN bd_tome t ON t.id_tome = en.id_tome
            INNER JOIN bd_serie s ON t.ID_SERIE=s.ID_SERIE
        WHERE
            ua.user_id = ".$user_id." AND
           ua.flg_achat = 'N' 
            AND NOT EXISTS (
                        SELECT NULL FROM users_exclusions ues
                        WHERE s.id_serie=ues.id_serie
                        AND ues.id_tome = 0
                        AND ues.user_id = ".$user_id."
                    )
        ) s_lim WHERE
                s.id_serie = s_lim.id_serie AND
            NOT EXISTS (
                    SELECT NULL
                    FROM users_album ua
                    INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
                    WHERE
                    ua.user_id = ".$user_id."
                    AND bd_tome.id_tome=en.id_tome
                    AND ua.flg_achat = 'N'
            )
            AND NOT EXISTS (
                    SELECT NULL
                    FROM users_exclusions uet
                    WHERE uet.user_id = ".$user_id."
                    AND bd_tome.id_tome=uet.id_tome
            )";
         
         $qcount = "select count(*) $this->defaut_from $query";
         $nb = Db_CountRow($qcount);
         return $nb;
    }
    
    public function getListAlbumToComplete($user_id, $id_serie=0, $flg_achat= true, $page=1, $length=0, $order= "") {

        $user_id = intval($user_id);
        $id_serie = intval($id_serie);
        $limit = "";
        
        if ($order == ""){
            $order = "order by bd_tome.NUM_TOME desc, en.DTE_PARUTION desc";
        } 
        if ($length ) {
            // pagination
            $limit = " limit ".(($page - 1)*$length).", ".$length;
        }
        if (!$flg_achat) {
            $q_achat = " AND ua.flg_achat = 'N'";
        } 
        else {
            $q_achat = "";
        }
        if ($id_serie == 0) {
            $query = " , (
        SELECT DISTINCT
            s.*
        FROM
            users_album ua
            INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
            INNER JOIN bd_tome t ON t.id_tome = en.id_tome
            INNER JOIN bd_serie s ON t.ID_SERIE=s.ID_SERIE
        WHERE
            ua.user_id = ".$user_id." AND
           ua.flg_achat = 'N' 
            AND NOT EXISTS (
                        SELECT NULL FROM users_exclusions ues
                        WHERE s.id_serie=ues.id_serie
                        AND ues.id_tome = 0
                        AND ues.user_id = ".$user_id."
                    )
        ) s_lim WHERE
                s.id_serie = s_lim.id_serie AND
            NOT EXISTS (
                    SELECT NULL
                    FROM users_album ua
                    INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
                    WHERE
                    ua.user_id = ".$user_id."
                    AND bd_tome.id_tome=en.id_tome
                    ".$q_achat ."
            )
            AND NOT EXISTS (
                    SELECT NULL
                    FROM users_exclusions uet
                    WHERE uet.user_id = ".$user_id."
                    AND bd_tome.id_tome=uet.id_tome
            )
            $order $limit";
        } else {
            $query = " WHERE s.id_serie = '".$id_serie."'
            AND
            NOT EXISTS (
                    SELECT NULL
                    FROM users_album ua
                    INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
                    WHERE
                    ua.user_id = ".$user_id."
                    AND bd_tome.id_tome=en.id_tome ".$q_achat ."
            )
            AND NOT EXISTS (
                    SELECT NULL
                    FROM users_exclusions uet
                    WHERE uet.user_id = ".$user_id."
                    AND bd_tome.id_tome=uet.id_tome
            )
             $order $limit";
        }

        return $this->load("c",$query);
    }

    public function updateGenreForSerie($id_serie, $id_genre) {
        /*
         * Fonction de mise à jour du genre de tous les albums d'une série
         */
        if ($id_serie > 0 and $id_genre > 0) {
            $query = "UPDATE bd_tome SET";
            $query .= " `id_genre` = ".intval($id_genre);
            $query .=" WHERE (`id_serie`=".intval($id_serie).");";
            Db_query($query);
        }
    }

    public function deleteEditionForAlbum($id_tome) {
         $query = "DELETE FROM bd_edition WHERE id_tome=" . intval($id_tome);
         Db_query($query);
    }

    public static function getNbTotalTome() {
        // fournit le count des Tome en base
        $nb = Db_CountRow("SELECT * FROM bd_tome");
        return $nb;
    }

    public function getAlbumAsScenariste($id_auteur, $limit = "") {
        // filtre et renvoie la liste des albums d'un auteur en tant que scénariste
        $order = " order by NOM_SERIE, NUM_TOME";
        return $this->load("c"," WHERE bd_tome.id_scenar = ".intval($id_auteur). " OR bd_tome.id_scenar_alt = ".intval($id_auteur).$order." ".$limit);

    }

    public function getAlbumAsDessinateur($id_auteur, $limit = "") {
        // filtre et renvoie la liste des albums d'un auteur en tant que dessinateur

        $order = " order by NOM_SERIE, NUM_TOME";
        return $this->load("c"," WHERE bd_tome.id_dessin = ".intval($id_auteur). " OR bd_tome.id_dessin_alt = ".intval($id_auteur).$order." ".$limit);

    }

    public function getAlbumAsColoriste($id_auteur, $limit = "") {
        // filtre et renvoie la liste des albums d'un auteur en tant que coloriste

        $order = " order by NOM_SERIE, NUM_TOME";
        return $this->load("c"," WHERE bd_tome.id_color = ".intval($id_auteur). " OR bd_tome.id_color_alt = ".intval($id_auteur).$order." ".$limit);

    }

    public function getNbAlbumForAuteur($id_auteur,$activite=0) {
        // retourne le nombre d'album pour un auteur, et eventuellement pour l'activité donnée
        switch ($activite) {
            case 0:
                $where = " WHERE bd_tome.id_scenar = ".intval($id_auteur). " OR bd_tome.id_scenar_alt  = ".intval($id_auteur). " OR bd_tome.id_dessin = ".intval($id_auteur).
                 " OR bd_tome.id_dessin_alt = ".intval($id_auteur) . " OR bd_tome.id_color = ".intval($id_auteur). " OR bd_tome.id_color_alt = ".intval($id_auteur);
                break;
            case 1:
                // scenariste
                $where = " WHERE bd_tome.id_scenar = ".intval($id_auteur). " OR bd_tome.id_scenar_alt  = ".intval($id_auteur);
                break;

            case 2 :
                // dessinateur
                $where = " WHERE  bd_tome.id_dessin = ".intval($id_auteur)." OR bd_tome.id_dessin_alt = ".intval($id_auteur) ;

                break;

            case 3 :
                // coloriste
                $where = " WHERE bd_tome.id_color = ".intval($id_auteur). " OR bd_tome.id_color_alt = ".intval($id_auteur);
                break;
        }
        $query = "select count(*) from bd_tome ".$where; 
         $nb = Db_CountRow($query );

         return $nb;
    }

    public function getLastAlbumForAuteur($id_auteur, $nb=5) {
        // retourne les $nb derniers ajout dans la bae pour l'auteur
       $where = " WHERE bd_tome.id_scenar = ".intval($id_auteur). " OR bd_tome.id_scenar_alt  = ".intval($id_auteur). " OR bd_tome.id_dessin = ".intval($id_auteur).
                 " OR bd_tome.id_dessin_alt = ".intval($id_auteur) . " OR bd_tome.id_color = ".intval($id_auteur). " OR bd_tome.id_color_alt = ".intval($id_auteur);

       $order = " ORDER BY bd_tome.ID_TOME desc";
       return $this->load("c",$where.$order." limit 0,".intval($nb));
    }

    public function renameAlbum($id_tome, $nouv_titre) {
        $query = "UPDATE bd_tome SET titre = '".  Db_Escape_String($nouv_titre)."' WHERE id_tome = ".  intval($id_tome);
        return Db_query($query);
    }

}
