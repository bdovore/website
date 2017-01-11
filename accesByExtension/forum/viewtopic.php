<?php
header('Status: 301 Moved Permanently', false, 301);
header('Location: http://forum.bdovore.com/viewtopic.php?'.$_SERVER['QUERY_STRING']);
?>
