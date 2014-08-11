<?php header ( 'Content-Type: text/html; charset=UTF-8' );?>
<?php
include (BDO_DIR."inc/queryfunction.php");



$user = $_SESSION["userConnect"]->user_id; //idem
$output_array = array();

// on récupère l'info dans la base telle qu'elle existe avant l'action

$query_default = "
		SELECT 
			base.id_tome  b_tome
			, user.id_tome u_tome
			, exclu.id_tome e_tome
			, user.nedit u_nedit
			, user.achat u_flgachat
			, user.edition u_edition
		FROM 
			(SELECT 
				$idtome id_tome
			) base 
		LEFT JOIN 
			(SELECT 
				t.id_tome
				,count(u.id_edition) nedit
				,IF(count(u.id_edition)>1, 'M', flg_achat) achat
				,IF(count(u.id_edition)>1, 'M', u.id_edition) edition
			FROM 
				users_album u
				, bd_edition e
				, bd_tome t
			WHERE 
				u.id_edition = e.id_edition 
			AND e.id_tome = t.id_tome
			AND u.user_id = $user
			AND t.id_tome = $idtome
			GROUP BY t.id_tome
			) user ON base.id_tome=user.id_tome
		LEFT JOIN 
			(SELECT 
				id_tome 
			FROM 
				users_exclusions
			WHERE 
				user_id = $user 
			AND id_tome = $idtome
			) exclu ON base.id_tome=exclu.id_tome";
// $num = $DB->num_rows();
//echo $query_album;
$DB->query($query_default);
$DB->next_record();
$status_array = array( 
        'statut' => 'OK'
        , 'incollec' => (is_null($DB->f("u_tome")) ? 'N' : 'O')
        , 'exclu' => (is_null($DB->f("e_tome")) ? 'N' : 'O')
		, 'nedit' => $DB->f("u_nedit")
		, 'flgachat' => $DB->f("u_flgachat")
		, 'idtome' => $DB->f("b_tome")
		, 'idedition' => $DB->f("u_edition")
		);
		
//echo json_encode( $status_array );

if ($status_array['nedit'] > 1) {
	$output_array[] = array('statut' => 'nOK');
	echo json_encode( $output_array );
	exit;
}

switch ($action) {
	case 'af' :
	// on teste si l'album est dans la base
	if ($status_array['incollec']=="O") { // L'album est dans la base : on se content de flagguer ou non l'achat futur et la date
		if ($status_array['flgachat']=="O") { // L'album était en achat futur : on le déflaggue et on met la date du jour ?
			$query_action = "UPDATE 
								users_album
							 SET 
							    flg_achat='N'
							 WHERE
								user_id = '$user'
								AND id_edition = '$idedition'
							  ";

			
		}else{  // L'album n'était pas en achat futur
			$query_action = "UPDATE 
								users_album
							 SET 
							    flg_achat='O'
							 WHERE
								user_id = '$user'
								AND id_edition = '$idedition'
							  "; 

			
		}
	}else{ // L'album n'était pas dans la collection : on le rajoute en achat futur après l'avoir viré des exclusions
		$query_action = "DELETE FROM users_exclusions 
				WHERE 
					user_id = '$user'
				AND	id_tome = '$idtome';";
		$DB->query($query_action);		
		
		$query_action = "INSERT INTO users_album (
			user_id, 
			date_ajout,
			flg_achat, 
			id_edition
		) VALUES (
			'$user'
			,CURRENT_TIMESTAMP()
			,'O' 
			,(SELECT id_edition FROM bd_tome WHERE id_tome = $idtome)
		)"; 
	}
	break;
	
	Case 'addrem' :
	// On teste si l'album est dans la base
	if ($status_array['incollec']=="O") { // L'album est dans la base : on va le supprimer
		$query_action = "DELETE FROM users_album 
				WHERE 
					user_id = '$user'
				AND	id_edition = '$idedition';";
 	}else{ // L'album n'est pas dans la basen, on va le rajouter mais auparavant, on le retire des exclusions
		$query_action = "DELETE FROM users_exclusions 
				WHERE 
					user_id = '$user'
				AND	id_tome = '$idtome';";
		$DB->query($query_action);			
	
		$query_action = "INSERT INTO users_album (
				user_id, 
				date_ajout,
				flg_achat, 
				id_edition
			) VALUES (
				'$user'
				,CURRENT_TIMESTAMP()
				,'N' 
				,(SELECT id_edition FROM bd_tome WHERE id_tome = $idtome)
			)"; 
	}
	break;
	
	Case 'addrem' :
	// On teste si l'album est dans la base
	if ($status_array['incollec']=="O") { // L'album est dans la base : on va le supprimer
		$query_action = "DELETE FROM users_album 
				WHERE 
					user_id = '$user'
				AND	id_edition = '$idedition';";
 	}else{ // L'album n'est pas dans la basen, on va le rajouter
		$query_action = "INSERT INTO users_album (
				user_id, 
				date_ajout,
				flg_achat, 
				id_edition
			) VALUES (
				'$user'
				,CURRENT_TIMESTAMP()
				,'N' 
				,(SELECT id_edition FROM bd_tome WHERE id_tome = $idtome)
			)"; 
	}
	break;

	Case 'exclude' :
		if ($status_array['exclu']=="O") { // L'album est exclu, on va le réinclure
			$query_action = "DELETE FROM users_exclusions 
					WHERE 
						user_id = '$user'
					AND	id_tome = '$idtome';";
		}else{ // L'album est à exclure
			$query_action = "INSERT INTO users_exclusions (
					user_id, 
					id_tome,
					id_serie 
				) VALUES (
					'$user'
					,'$idtome'
					,(SELECT id_serie FROM bd_tome WHERE id_tome = $idtome)
				)"; 
		}
	break;

}
	
	
	
	$DB->query($query_action);
	//echo $query_action;


		
		

//echo "Requete de type $action recue de l'utilisateur ".$_SESSION["UserId"]. " sur l'album ".$idtome

/* on passe en réponse le nouveau statut de l'album dans la base, à savoir :
1. Si l'album est ou non dans la base (code: O/N)
2. Si l'album est ou non dans les exclusions (code : O/N)
3. Si l'album est dans les achats futurs (code : O/N)
3. Si l'album est comprend plusieurs éditions (code : le numbre d'éditions)*/

$DB->query($query_default);
$DB->next_record();

$output_array[] = array( 
        'statut' => 'OK'
        , 'incollec' => (is_null($DB->f("u_tome")) ? 'N' : 'O')
        , 'exclu' => (is_null($DB->f("e_tome")) ? 'N' : 'O')
		, 'nedit' => $DB->f("u_nedit")
		, 'flgachat' => $DB->f("u_flgachat")
		, 'idtome' => $DB->f("b_tome")
		, 'idedition' => $DB->f("u_edition")
		);
		
echo json_encode( $output_array );

?>