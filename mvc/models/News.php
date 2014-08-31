<?php

/**
 *
 * @author laurent
 *        
 */
class News extends Bdo_Db_Line
{

    public $table_name = 'news';

    /**
     */
    public function __construct ($id = null)
    {
        if (is_array($id)) {
            $a_data = $id;
        }
        else {
            $a_data = array(
                    'news_id' => $id
            );
        }
        parent::__construct($this->table_name, $a_data);
    }
    
    public function select() {
        return "SELECT `news_id`, `ID_NEWS_TYPE`, `news_level`, `news_posteur`, `news_date`, `news_titre` , `news_text`, `news_on_date`, `news_off_date`, `USER_ID`
            FROM `news` ";
    }

}
