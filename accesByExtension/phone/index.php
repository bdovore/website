<?php
header('Status: 301 Moved Permanently', false, 301);
header('Location: ' . BDO_PROTOCOL . '://phone.bdovore.com/index.php?'.$_SERVER['QUERY_STRING']);
?>
