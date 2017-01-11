<?php
/*
 * This class extends DB_Sql whose copyright follows Session Management for PHP3
 * Copyright (c) 1998-2000 NetUSE AG Boris Erdmann, Kristian Koehntopp $Id:
 * db_mysql.inc,v 1.11 2002/08/07 19:33:57 layne_weathers Exp $
 */

include_once ("db_mysql.php");
class DB_phpBB extends DB_Sql {

	/* public: constructor */
	function DB_phpBB($query = "") {
		$this->Host = FORUM_DB_HOST;
		$this->Database = FORUM_DB_SID;
		$this->User = FORUM_DB_USER;
		$this->Password = FORUM_DB_PWD;

		$this->query ( $query );
	}
}