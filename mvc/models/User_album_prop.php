<?php

/**
 *
 * @author Tom
 *        
 */

class User_album_prop extends Bdo_Db_Line
{
    /**
     */

    public $table_name = 'users_alb_prop';
    // variable qui gère si on selectionne juste les data de la table, ou si on prend avec les données des albums d'origine
    private $iswithalbum = false;
    
    
    public $error = '';

    // initialisation
    
    public function __construct ($id = null)
    {
        if (is_array($id)) {
            $a_data = $id;
        }
        else {
            $a_data = array(
                    'ID_PROPOSAL' => $id
            );
        }

        parent::__construct($this->table_name, $a_data);
    }
    
    public function setWithAlbumInfo($bool){
        if ($bool) {
            $this->iswithalbum = true;}
        else {
            $this->iswithalbum = false;
        } 
    }
    
    
    
    public function select ()
    {
        if ($this->iswithalbum) {
            /*
             * Selection des données user_ablum_prop + id et libellé des tables liées
             */
            $select = "SELECT 
		users_alb_prop.USER_ID, 
		users_alb_prop.ID_PROPOSAL, 
		users_alb_prop.ACTION, 
		users_alb_prop.TITRE, 
		users_alb_prop.NUM_TOME, 
		users_alb_prop.PRIX, 
		users_alb_prop.ID_SERIE, 
		users_alb_prop.SERIE, 
		bd_serie.NOM AS ACTUSERIE, 
		users_alb_prop.DTE_PARUTION, 
		users_alb_prop.ID_GENRE, 
		users_alb_prop.GENRE,
		bd_genre.LIBELLE AS ACTUGENRE, 
		users_alb_prop.ID_EDITEUR, 
		users_alb_prop.EDITEUR, 
		bd_editeur.NOM AS ACTUEDITEUR, 
		users_alb_prop.ID_SCENAR, 
		users_alb_prop.SCENAR, 
		bd_auteur.PSEUDO AS PSEUDO_SCENAR, 
		users_alb_prop.ID_SCENAR_ALT, 
		users_alb_prop.SCENAR_ALT, 
		bd_auteur_3.PSEUDO AS PSEUDO_SCENAR_ALT, 
		users_alb_prop.ID_DESSIN, 
		users_alb_prop.DESSIN,
		bd_auteur_1.PSEUDO AS PSEUDO_DESSIN, 
		users_alb_prop.ID_DESSIN_ALT, 
		users_alb_prop.DESSIN_ALT, 
		bd_auteur_4.PSEUDO AS PSEUDO_DESSIN_ALT, 
		users_alb_prop.ID_COLOR, 
		users_alb_prop.COLOR, 
		bd_auteur_2.PSEUDO AS PSEUDO_COLOR, 
		users_alb_prop.ID_COLOR_ALT, 
		users_alb_prop.COLOR_ALT, 
		bd_auteur_5.PSEUDO AS PSEUDO_COLOR_ALT, 
		users_alb_prop.DESCRIB_EDITION, 
		users_alb_prop.ID_COLLECTION, 
		users_alb_prop.COLLECTION, 
		bd_collection.NOM AS ACTUCOLLECTION, 
		users_alb_prop.HISTOIRE, 
		users_alb_prop.IMG_COUV,
		users_alb_prop.FLG_INT, 
		users_alb_prop.FLG_TYPE, 
		users_alb_prop.FLG_TT, 
		users_alb_prop.EAN, 
		users_alb_prop.ISBN, 
		users_alb_prop.PRIX, 
		users_alb_prop.DESCRIB_EDITION, 
		users_alb_prop.CORR_COMMENT, 
		users_alb_prop.STATUS,
                    users.USERNAME,
                    users.EMAIL
	FROM 
		users_alb_prop 
                INNER JOIN users using(user_id)
		LEFT JOIN bd_serie ON users_alb_prop.ID_SERIE = bd_serie.ID_SERIE 
		LEFT JOIN bd_genre ON users_alb_prop.ID_GENRE = bd_genre.ID_GENRE
		LEFT JOIN bd_editeur ON users_alb_prop.ID_EDITEUR = bd_editeur.ID_EDITEUR 
		LEFT JOIN bd_auteur ON users_alb_prop.ID_SCENAR = bd_auteur.ID_AUTEUR
		LEFT JOIN bd_auteur AS bd_auteur_1 ON users_alb_prop.ID_DESSIN = bd_auteur_1.ID_AUTEUR
		LEFT JOIN bd_auteur AS bd_auteur_2 ON users_alb_prop.ID_COLOR = bd_auteur_2.ID_AUTEUR
		LEFT JOIN bd_collection ON users_alb_prop.ID_COLLECTION = bd_collection.ID_COLLECTION
		LEFT JOIN bd_auteur as bd_auteur_3 ON users_alb_prop.ID_SCENAR_ALT = bd_auteur_3.ID_AUTEUR 
		LEFT JOIN bd_auteur as bd_auteur_4 ON users_alb_prop.ID_DESSIN_ALT = bd_auteur_4.ID_AUTEUR 
		LEFT JOIN bd_auteur as bd_auteur_5 ON users_alb_prop.ID_COLOR_ALT = bd_auteur_5.ID_AUTEUR ";
            return $select;
        }else {
        
        
        return "
        SELECT `ID_PROPOSAL`,
                    `USER_ID`,
                    `PROP_DTE`,
                    `ACTION`,
                    `NOTIF_MAIL`,
                    `ID_TOME`,
                    `ID_EDITION`,
                    `TITRE`,
                    `NUM_TOME`,
                    `FLG_INT`,
                    `FLG_TYPE`,
                    `ID_SERIE`,
                    `SERIE`,
                    `FLG_FINI`,
                    `DTE_PARUTION`,
                    `ID_GENRE`,
                    `GENRE`,
                    `ID_EDITEUR`,
                    `EDITEUR`,
                    `ID_SCENAR`,
                    `SCENAR`,
                    `ID_SCENAR_ALT`,
                    `SCENAR_ALT`,
                    `ID_DESSIN`,
                    `DESSIN`,
                    `ID_DESSIN_ALT`,
                    `DESSIN_ALT`,
                    `ID_COLOR`,
                    `COLOR`,
                    `ID_COLOR_ALT`,
                    `COLOR_ALT`,
                    `DESCRIB_EDITION`,
                    `ID_COLLECTION`,
                    `COLLECTION`,
                    `FLG_EO`,
                    `FLG_TT`,
                    `HISTOIRE`,
                    `IMG_COUV`,
                    `COMMENTAIRE`,
                    `PRIX`,
                    `EAN`,
                    `ISBN`,
                    `URL_BDNET`,
                    `URL_AMAZON`,
                    `STATUS`,
                    `VALIDATOR`,
                    `VALID_DTE`,
                    `CORR_COMMENT`,
                    `CORR_STATUT`,
                    users.USERNAME,
                    users.EMAIL

                    FROM " . $this->table_name . " INNER JOIN users using(user_id)
                    
                ";
        }
    }
    
    public function getUserStat($user_id){
        $user_prop_alb = Db_CountRow("SELECT * FROM users_alb_prop WHERE prop_type = 'AJOUT' and user_id=" . $user_id );
        $user_prop_corr = Db_CountRow("SELECT * FROM users_alb_prop WHERE prop_type = 'CORRECTION' and user_id=" . $user_id );

        return (array(
            "user_prop_alb" => $user_prop_alb,
           "user_prop_corr" => $user_prop_corr
        ));
    }
    
    public function supprProposition($id, $user_id) {
        $query = "UPDATE users_alb_prop SET `STATUS` = 98, `VALIDATOR`=".  Db_Escape_String($user_id)." , `VALID_DTE` = NOW() WHERE id_proposal=".intval($id);
        Db_query($query);
    }

    public function getAllStat() {
         $prop_alb = Db_CountRow("SELECT * FROM users_alb_prop WHERE prop_type = 'AJOUT' and status not in ( 98,99,1) ");
         $prop_corr = Db_CountRow("SELECT * FROM users_alb_prop WHERE prop_type = 'CORRECTION' and status not in ( 98,99,1)");
         $NBEDITION = Db_CountRow("SELECT * FROM bd_edition WHERE prop_status not in ( 98,99,1) ");
         return (array(
             "NBAJOUT" =>  $prop_alb,
             "NBCORRECTION" => $prop_corr,
             "NBEDITION" => $NBEDITION
         ));
    }
}

    
?>
