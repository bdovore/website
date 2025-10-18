<?php

/**
 *
 * @author laurent
 *
 */
class Newstype extends Bdo_Db_Line
{

    public $table_name = 'news_type';

    /**
     */
    public function __construct ($id = null)
    {
        if (is_array($id)) {
            $a_data = $id;
        }
        else {
            $a_data = array(
                    'ID_NEWS_TYPE' => $id
            );
        }
        parent::__construct($this->table_name, $a_data);
    }

    public function select() {
        return "SELECT `ID_NEWS_TYPE`, NAME_NEWS_TYPE as label_news_type
            FROM news_type ";
    }

}
