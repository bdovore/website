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
        SELECT 
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
        
        	en.ID_EDITION,
        	en.IMG_COUV,
        	en.ean as EAN_EDITION, 
        	en.isbn as ISBN_EDITION, 
                en.DTE_PARUTION,
        
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
        	INNER JOIN bd_collection c ON en.id_collection = c.id_collection
        	INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur
        	 
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
        
        if (Bdo_Cfg::user()->minAccessLevel(2)) {
            // ajout des champs de la collection de l'utilisateur
            $select .= " , ua.id_edition as USER_EDITION,
                        ua.flg_pret FLG_PRET,
                        ua.nom_pret as NOM_PRET,
                        ua.email_pret as EMAIL_PRET,
                        ua.flg_dedicace as FLG_DEDICACE,
                        ua.flg_tete as FLG_TETE,
                        ua.comment as USER_COMMENT,
                        ua.date_ajout as DATE_AJOUT,
                        ua.flg_achat as FLG_ACHAT,
                       IFNULL(ua.date_achat,ua.date_ajout) as DATE_ACHAT,
                        ua.cote as COTE,
                        ua.flg_cadeau as FLG_CADEAU, 
                        ua.DTE_PARUTION as USER_EDITION_DTE_PARUTION,
                        ua.IMG_COUV as USER_EDITION_IMG_COUV,
                        ua.comment_edition as USER_EDITION_COMMENT
                ";
            $from .= " 
                    LEFT JOIN (
                        select users_album.id_edition, 
                                flg_pret, 
                                nom_pret, 
                                email_pret, 
                                flg_dedicace, 
                                flg_tete, 
                                users_album.comment,
                                date_ajout,
                                flg_achat,
                                date_achat,
                                cote,
                                flg_cadeau,
                                bd_edition.DTE_PARUTION,
                                bd_edition.IMG_COUV,
                                bd_edition.COMMENT as comment_edition,
                                bd_edition.id_tome
                           from users_album inner join bd_edition using (id_edition)
                           where users_album.user_id = ". intval($_SESSION['userConnect']->user_id) ."
                                group by bd_edition.id_tome) ua 
                               on ua.id_tome = bd_tome.id_tome 
                            
                            ";
            
        }
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

                           and ua.id_edition is null 
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

                            AND	ua.id_edition is null
                            AND NOT EXISTS (
                                    SELECT NULL 
                                    FROM users_exclusions uet
                                    WHERE uet.user_id = ". $user_id ."
                                    AND bd_tome.id_tome=uet.id_tome
                            )  
                    ";
            }
        
        $query .= "AND en.dte_parution >= DATE_SUB(NOW(), INTERVAL ". $nb_mois." MONTH)";
        $query .= " ORDER BY en.dte_parution";
        
        $query .= " limit ".(($page-1)*20).", 20";
        
        //echo $this->select().$query;
        return $this->load("c",$query);
        
    }
    
    
	public function getListAlbumToComplete($user_id, $id_serie=0) {

		$user_id = intval($user_id);
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
			ua.user_id = ".$user_id."
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
            )
            AND NOT EXISTS (
                    SELECT NULL 
                    FROM users_exclusions uet
                    WHERE uet.user_id = ".$user_id."
                    AND bd_tome.id_tome=uet.id_tome
            ) 
            order by s.tri, s.NOM, bd_tome.NUM_TOME";
        } else {
            $query = " WHERE s.id_serie = '".$id_serie."'
            AND
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
            order by en.dte_parution desc";
        }
        
      
         return $this->load("c",$query);
    }
    
    public function updateGenreForSerie($id_serie, $id_genre) {
        /*
         * Fonction de mise à jour du genre de tous les albums d'une série
         */
        if ($id_serie > 0 and $id_genre > 0) {
            $query = "UPDATE bd_tome SET";
            $query .= " `id_genre` = ".intVal($id_genre);
            $query .=" WHERE (`id_serie`=".intval($id_serie).");";
            Db_query($query);
            
        }
    }
    
    public function deleteEditionForAlbum($id_tome) {
         $query = "DELETE FROM bd_edition WHERE id_tome=" . intval($id_tome);
         Db_query($query);
    }
}
