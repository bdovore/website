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
                    'NEWS_ID' => $id
            );
        }
        parent::__construct($this->table_name, $a_data);
    }


}
