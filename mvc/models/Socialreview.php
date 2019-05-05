<?php

class Socialreview extends Bdo_Db_Line
{
    /**
     */
    public $table_name = 'social_review';
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
            `id` ,
            (social.`USER_ID`*1209 + 951) as USER_ID,
            `source` ,
            `date_comment` ,
            `comment` ,
            users.username USERNAME
        FROM `" . $this->table_name . "` social"
                . " LEFT JOIN users on (users.user_id = social.USER_ID)";
    }
    
    
    
    
    
    public function getNbCommentForSource ( $source) {
        
        $where = " WHERE source = '".Db_Escape_String($source)."'";
        $query = "SELECT count(*) FROM social_review ".$where;
         $nb = Db_CountRow($query );
         return ($nb);
    }
}