<?php
function authentification() {
	global $DB;
	
	if (notIssetOrEmpty ( $_SESSION ["UserId"] )) {
		// classe automatiquement l'utilisateur au niveau public
		
		$_SESSION ["UserLevel"] = 5; // niveau publique
		$_SESSION ["UserId"] = null;
		$_SESSION ["UserName"] = null;
		
		// tentative de connexion automatique
		if (issetNotEmpty ( $_COOKIE ["username"] ) and issetNotEmpty ( $_COOKIE ["pass"] )) {
			$DB->query ( "
			SELECT user_id,level,username FROM users 
			WHERE username ='" . $DB->escape ( $_COOKIE ["username"] ) . "' 
			AND password='" . $DB->escape ( $_COOKIE ["pass"] ) . "' 
			AND level<98
			" );
			
			if ($DB->nf () != 1) {
				setcookie ( "username", "", time () - 3600 );
				setcookie ( "pass", "", time () - 3600 );
			} else {
				$DB->next_record ();
				$_SESSION ["UserLevel"] = $DB->f ( "level" );
				$_SESSION ["UserId"] = $DB->f ( "user_id" );
				$_SESSION ["UserName"] = $DB->f ( "username" );
				
				$DB->query ( "
				UPDATE users 
				SET nb_connect = (nb_connect + 1), last_connect = NOW() 
				WHERE user_id=" . $DB->escape ( $_SESSION ["UserId"] ) );
			}
		}
	}
}
function minAccessLevel($level = 0, $alert = true) {
	if (! isset($_SESSION['userConnect']->level) or ($_SESSION['userConnect']->level > $level)) {
		if ($alert) {
			alertLevel ( $level );
		}
		return false;
	}
	return true;
}
/*
 * function maxAccessLevel($level=0,$alert=true) { if
 * (!isset($_SESSION["UserId"]) or ($_SESSION["UserLevel"] > $level)) { if
 * ($alert) { alertLevel($level); } return false; } return true; }
 */
function alertLevel($level = false) {
	if (! isset ( $_SESSION ["UserId"] )) {
		echo GetMetaTag ( 3, "Vous devez vous enregistrer pour accéder à cette page.", (BDO_URL . "index.php") );
		exit ();
	}
	if ($level and ($_SESSION ["UserLevel"] > $level)) {
		if ($level == 1)
			echo GetMetaTag ( 3, "Vous devez être habilité pour accéder à cette page.", (BDO_URL . "index.php") );
		exit ();
	}
}

?>