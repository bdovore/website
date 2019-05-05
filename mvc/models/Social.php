<?php

class Social extends Bdo_Db_Line
{
    /**
     */
    public $table_name = 'social_action';
    public $error = '';

    // initialisation
    public function __construct ($id = null)
    {
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

    public function select ()
    {
        return "
        SELECT SQL_CALC_FOUND_ROWS 
            `TYPE_ACTION` ,
            social.`USER_ID` ,
            `SOURCE_USER` ,
            `SOURCE_ID_TOME` ,
            `COMMENT` ,
            users.username USERNAME
        FROM `" . $this->table_name . "` social"
                . " LEFT JOIN users on (users.user_id = social.USER_ID)";
    }
    
    public function getLikeForUser ($user_id , $id_tome = 0, $source_user = 0 ) {
        $where = " WHERE TYPE_ACTION = 'L' AND social.USER_ID = ".intVal($user_id);
        if ( $id_tome) {
            $where.= " AND SOURCE_ID_TOME = ".intVal($id_tome);
        }
        if ($source_user) {
            $where .=" AND SOURCE_USER = ".intVal($source_user);
        }
        return $this->load("c", $where);
    }
    
    public function deleteLike ($user_id, $id_tome, $source_user) {
        $user_id = intVal($user_id);
        $id_tome = intval($id_tome);
        $source_user = intval($source_user);
        $query = "DELETE FROM social_action WHERE USER_ID = $user_id AND SOURCE_ID_TOME = $id_tome AND SOURCE_USER = $source_user";
        return Db_query($query);
    }
    
    public function getNbLikeForComment ( $id_tome, $user_id_source) {
        
        $where = " WHERE TYPE_ACTION = 'L' AND SOURCE_ID_TOME = ".intVal($id_tome)
                . " AND SOURCE_USER = ".$user_id_source;
        $query = "SELECT count(*) FROM social_action ".$where;
         $nb = Db_CountRow($query );
         return ($nb);
    }
}