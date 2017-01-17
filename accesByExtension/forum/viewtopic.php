<?php
header('Status: 301 Moved Permanently', false, 301);
header('Location: ' . BDO_URL_FORUM . 'viewtopic.php?'.$_SERVER['QUERY_STRING']);
?>
