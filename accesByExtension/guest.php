<?php
header('Status: 301 Moved Permanently', false, 301);
header('Location: '.BDO_URL."guest?".$_SERVER['QUERY_STRING']);

