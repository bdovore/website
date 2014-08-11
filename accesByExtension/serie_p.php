<?php header ( 'Content-Type: text/html; charset=UTF-8' );?>
<?php

	include (BDO_DIR."inc/queryfunction.php");

	// Recupère les albums contenant la proposition
	$query = "SELECT id_serie, nom FROM bd_serie WHERE nom LIKE '%$term%' LIMIT 0,100";
	$DB->query($query);
	////echo $query;
	
	// On déclare le tableau de réponse et on le rempli
	$output_array = array();
	while ($DB->next_record()){
	$output_array[] = array( 
        'id' => $DB->f("id_serie")
        , 'label' => $DB->f("nom")
        , 'value' => $DB->f("nom")
		);
	}
	echo json_encode( $output_array );
?>
