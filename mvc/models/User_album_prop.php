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

    public function select ()
    {
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
                    `CORR_STATUT`

                    FROM " . $this->table_name . "
                ";
    }
    
    public function getUserStat($user_id){
        $user_prop_alb = Db_CountRow("SELECT * FROM users_alb_prop WHERE prop_type = 'AJOUT' and user_id=" . $user_id );
        $user_prop_corr = Db_CountRow("SELECT * FROM users_alb_prop WHERE prop_type = 'CORRECTION' and user_id=" . $user_id );

        return (array(
            "user_prop_alb" => $user_prop_alb,
           "user_prop_corr" =>  $user_prop_corr
        ));
    }
    
    public function supprProposition($id, $user_id) {
        $query = "UPDATE users_alb_prop SET `STATUS` = 98, `VALIDATOR`=".  Db_Escape_String($user_id)." , `VALID_DTE` = NOW() WHERE id_proposal=".intVal($id);
        Db_query($query);
    }

    public function getAllStat() {
         $prop_alb = Db_CountRow("SELECT * FROM users_alb_prop WHERE prop_type = 'AJOUT' and status = 0 ");
         $prop_corr = Db_CountRow("SELECT * FROM users_alb_prop WHERE prop_type = 'CORRECTION' and status = 0");
         
         return (array(
             "NBAJOUT" =>  $prop_alb,
             "NBCORRECTION" => $prop_corr
         ));
    }
}

    
?>
