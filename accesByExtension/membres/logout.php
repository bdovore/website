<?php
include_once("../../inc/conf.inc.php" );

$_SESSION = array();
session_destroy();

// on efface le cookie
setcookie ("username","",time()+3600,"/");
setcookie ("pass","",time()+3600,"/");

header("Location:".BDO_URL."index.php");
