<?php


minAccessLevel(0);

set_time_limit(600);


mysql_connect(BDO_DB_HOST, BDO_DB_USER, BDO_DB_PWD);
mysql_select_db(BDO_DB_SID);
	
echo '<pre>';

$query ="SELECT `TABLE_NAME`,`COLUMN_NAME`  FROM `db_column` 
WHERE `DATA_TYPE` IN ('varchar','text')
AND `TABLE_NAME` NOT IN ('users_album')
ORDER BY `TABLE_NAME`,`COLUMN_NAME`";

$a_table = array();
$resultat_sch = mysql_query($query);

while ($obj = mysql_fetch_object($resultat_sch))
{
    if (!in_array($obj->TABLE_NAME,$a_table))
    {
    	echo "<hr>".$obj->TABLE_NAME."\n";
        mysql_query("LOCK TABLES `".$obj->TABLE_NAME."` WRITE");
        $a_table[] = $obj->TABLE_NAME;
    }
    $query = "SELECT count(1) as nbr FROM `".$obj->TABLE_NAME."` WHERE `".$obj->COLUMN_NAME."` LIKE ('%\\\\\\%')";
    $resultat_cpt = mysql_query($query);
    $count = mysql_fetch_object($resultat_cpt);
    $nb = $count->nbr;
    
    if ($nb>0)
    {
	    echo "`".$obj->TABLE_NAME."` - `".$obj->COLUMN_NAME."` - ".$nb;
	    
	    if (isset($_GET['go']))
	    {
	        $query = "UPDATE `".$obj->TABLE_NAME."` SET `".$obj->COLUMN_NAME."`=REPLACE(`".$obj->COLUMN_NAME."`,'\\\\','')
	   		WHERE `".$obj->COLUMN_NAME."` LIKE ('%\\\\\\%')";
	        mysql_query($query);
	        echo " - " . mysql_affected_rows();
	    }
	    echo "\n";
    }

    
}
mysql_query("UNLOCK TABLES");
mysql_free_result($resultat_sch);

echo '</pre>';