<?php
header('Status: 301 Moved Permanently', false, 301);
header('Location: http://forum.bdovore.com/index.php?'.$_SERVER['QUERY_STRING']);
?>
