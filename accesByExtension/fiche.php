<?php

header('Status: 301 Moved Permanently', false, 301);  
header('Location: '.BDO_URL."alum/fiche?id_tome=".$_GET["alb_id"]);
