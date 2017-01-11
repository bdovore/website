<?php
header('Status: 301 Moved Permanently', false, 301);
include_once (BDO_DIR."inc/bdovore.php");

include (BDO_DIR."inc/queryfunction.php");
// on récupere le titre de la série pour construire la nouvelle URL unique
$query = "select NOM from bd_serie where id_serie =".intval($_GET["id_serie"]);
$DB->query ($query);
$DB->next_record();
$titre = $DB->f("NOM");

$url = BDO_URL."serie-bd-".intval($_GET["id_serie"])."-".clean_url($titre);
header('Location: '.$url);
?>
