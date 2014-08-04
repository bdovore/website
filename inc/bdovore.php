<?php
// FileName="Connection_php_mysql.htm"
// Type="MYSQL"
// HTTP="true"

include_once ("conf.inc.php");
$bdovore = mysql_connect ( BDO_DB_HOST, BDO_DB_USER, BDO_DB_PWD ) or die ( mysql_error () );
mysql_query("SET NAMES 'utf8'",$bdovore);
