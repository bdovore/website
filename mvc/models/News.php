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
        return "SELECT `news_id`, nt.`ID_NEWS_TYPE`, nt.NAME_NEWS_TYPE as label_news_type, `news_level`, `news_posteur`, `news_date`, `news_titre` , `news_text`, `news_on_date`, `news_off_date`, `USER_ID`
            FROM `news` INNER JOIN news_type nt using (ID_NEWS_TYPE) ";
    }

    public function getLastNewsPerType () {
        $query = "SELECT `news_id`, n.`ID_NEWS_TYPE`, `news_level`, `news_posteur`, `news_date`, `news_titre` , `news_text`, `news_on_date`, `news_off_date`, `USER_ID`
                FROM news n
                INNER JOIN (
                    SELECT ID_NEWS_TYPE, MAX(news_id) as last_id
                    FROM news
                    WHERE news_level = 5
                    GROUP BY ID_NEWS_TYPE
                ) as last ON n.ID_NEWS_TYPE = last.ID_NEWS_TYPE AND n.news_id = last.last_id
                ORDER BY news_id DESC
        ";
        $resultat = Db_query($query);

        $a_obj = array();
        while ($obj = Db_fetch_object($resultat)) {
            $a_obj[] = $obj;
        }
        return $a_obj;
    }

     public function getLastSecondNews ($limit) {
        // les news qui ne sont pas à la une
        $query = "SELECT `news_id`, n.`ID_NEWS_TYPE`, `news_level`, `news_posteur`, `news_date`, `news_titre` , `news_text`, `news_on_date`, `news_off_date`, `USER_ID`
                FROM news n
                LEFT JOIN (
                    SELECT ID_NEWS_TYPE, MAX(news_id) as last_id
                    FROM news
                    WHERE news_level = 5
                    GROUP BY ID_NEWS_TYPE
                ) as last ON n.ID_NEWS_TYPE = last.ID_NEWS_TYPE AND n.news_id = last.last_id
                WHERE last.last_id IS NULL 
                ORDER BY news_id DESC
                LIMIT 0, $limit
        ";
        $resultat = Db_query($query);

        $a_obj = array();
        while ($obj = Db_fetch_object($resultat)) {
            $a_obj[] = $obj;
        }
        return $a_obj;
    }
   
}
