<?php
header('Status: 301 Moved Permanently', false, 301);   
header('Location: http://phone.bdovore.com/index.php?'.$_SERVER['QUERY_STRING']);
?>
