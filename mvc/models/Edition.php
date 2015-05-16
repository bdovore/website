<?php

/**
 *
 * @author laurent
 *        
 */
class Edition extends Bdo_Db_Line
{

    /**
     */
    public $table_name = 'bd_edition';

    public $error = '';
    
    // initialisation
    public function __construct ($id = null)
    {
        if (is_array($id)) {
            $a_data = $id;
        }
        else {
            $a_data = array(
                    'ID_EDITION' => $id
            );
        }
        parent::__construct($this->table_name, $a_data);
    }

    public function select ()
    {
        /* gestion des sélection en fonction de la connexion utilisateur
        * si l'utilisateur est connecté et appel par id_edtion, on fait la jointure gauche avec les editions
         * dans la collection de l'utilisateur
         * 
         * Usage : fait pour l'appel de la fiche album depuis la collection de l'utilisateur
         * Dans ce cas, on vérifie si cette édition est dans la collection 
         */
        $select = "
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
        	bd_edition_stat.NBR_USER_ID_EDITION as NBR_USER_ID, 
                
                s.ID_SERIE, 
        	s.nom as NOM_SERIE, 
                s.FLG_FINI,
        
        	g.ID_GENRE, 
        	g.libelle as NOM_GENRE, 
        
        	bd_edition.ID_EDITION,
        	bd_edition.IMG_COUV,
        	bd_edition.ean as EAN_EDITION, 
        	bd_edition.isbn as ISBN_EDITION, 
                bd_edition.DTE_PARUTION as DATE_PARUTION_EDITION, 
                bd_edition.FLAG_DTE_PARUTION,
                bd_edition.COMMENT as COMMENT_EDITION, 
                bd_tome.id_edition as ID_EDITION_DEFAULT, 
                c.ID_COLLECTION,
        	c.nom as NOM_COLLECTION,
        	
        	er.ID_EDITEUR,
        	er.nom as NOM_EDITEUR, 
                concat_ws(' ',er.nom, year(bd_edition.DTE_PARUTION)) as NOM_EDITION,
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
        	coa.pseudo as coapseudo,
                bd_edition.USER_ID, 
                bd_edition.PROP_DTE, 
                bd_edition.PROP_STATUS,
                bd_edition.VALIDATOR,
                bd_edition.VALID_DTE,
                us.username USERNAME,
                us.email EMAIL,
                valid_user.username VALIDATOR_USERNAME";
        $from = "
        FROM bd_edition
        	INNER JOIN bd_tome ON bd_tome.id_tome = bd_edition.id_tome
                INNER JOIN bd_serie s ON bd_tome.id_serie = s.id_serie
        	INNER JOIN bd_genre g ON s.id_genre = g.id_genre
        	
        	LEFT JOIN bd_collection c ON bd_edition.id_collection = c.id_collection
        	LEFT JOIN bd_editeur er ON c.id_editeur = er.id_editeur
        	 
        	LEFT JOIN bd_edition_stat ON bd_edition.id_edition = bd_edition_stat.ID_EDITION
        	LEFT JOIN note_tome ON bd_tome.ID_TOME = note_tome.ID_TOME
                
                LEFT JOIN bd_auteur sc ON bd_tome.id_scenar = sc.id_auteur
        	LEFT JOIN bd_auteur de ON bd_tome.id_dessin = de.id_auteur 
        	LEFT JOIN bd_auteur co ON bd_tome.id_color = co.id_auteur
        	LEFT JOIN bd_auteur sca ON bd_tome.id_scenar_alt = sca.id_auteur
        	LEFT JOIN bd_auteur dea ON bd_tome.id_dessin_alt = dea.id_auteur
        	LEFT JOIN bd_auteur coa ON bd_tome.id_color_alt = coa.id_auteur
                LEFT JOIN users us on us.user_id = bd_edition.USER_ID 
                LEFT JOIN users valid_user on valid_user.user_id = bd_edition.VALIDATOR
                ";
       
        
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
FROM bd_edition en
	INNER JOIN bd_tome t ON t.id_edition = en.id_edition
    INNER JOIN bd_serie s ON t.id_serie = s.id_serie
	INNER JOIN bd_genre g ON s.id_genre = g.id_genre
	
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
    
    public function deleteTome($id_tome) {
        /* 
         * Supprime les éditions liés à cet id_tome
         */
        Db_query("DELETE FROM bd_edition WHERE id_tome=" . intval($id_tome));
        return Db_affected_rows();
        
    }
    
    public function replaceIdTome($old_idtome, $new_idtome) {
           Db_query("UPDATE IGNORE bd_edition SET ID_TOME = " . intval($new_idtome) . " WHERE ID_TOME=" . intval($old_idtome));
        
        return Db_affected_rows();
       }
       
    public function replaceIdEditeur($source_id, $dest_id) {
        // Focntion qui remplace un editeur par un autre dans toutes les éditions
         Db_query("UPDATE bd_edition SET id_editeur = " . intval($dest_id) . " where id_editeur = " . intval($source_id));
        
        return Db_affected_rows();
    }   
}
