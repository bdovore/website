<?php

// création de la classe de manipulation des tables
class Element {
	var $field; // Variable type tableau contenant les champs de la table
	var $primary_keys; // Variable type table contenant les clés primaires, ie_
	                   // les champs utilisés
	var $debug; // Variable de débug permettant d'afficher les conditions
	            
	// ------------- ci dessous, variables normalement privées ou réservées aux
	            // power users
	var $table; // Variable contenant la table de travail
	var $has_fetch = false; // Indique si une édition a été fetché
	var $primary_keys_value; // Contient la valeur des différentes clés
	                         
	// Et ci-dessous les variables à ne pas toucher du tout !
	var $field_ori; // Valeurs originales de fetch
	                
	// Instancie la classe en fonction de la table
	function Element($whichtable) {
		// Initialise la classe
		// on construit une variable field qui va contenir les champs
		// disponibles dans la table bd_edition
		$this->table = $whichtable;
		$DB = new DB_sql ();
		$mytable = $DB->metadata ( $table = $whichtable, $full = false );
		foreach ( $mytable as $key => $value ) {
			$this->field [$value ["name"]] = '';
			$this->field [$value ["name"]] = '';
		}
		$this->primary_keys [0] = $mytable [0] ["name"];
	}
	
	// fetch récupère une édition déjà existante
	function fetch($primkeys) {
		$this->primary_keys_value = $primkeys;
		$condition = $this->build_prim_keys ( $primkeys );
		
		$DB = new DB_sql ();
		$query = "SELECT * FROM $this->table WHERE $condition;";
		if ($this->debug == "Oui")
			echo $query;
		$DB->query ( $query );
		$DB->next_record ();
		
		foreach ( $this->field as $key => $value ) {
			$this->field [$key] = $DB->f ( $key );
			$this->field_ori [$key] = $DB->f ( $key );
		}
		$this->has_fetch = true;
	}
	
	// Fonction construisant la condition sur la ou les clés primaires
	function build_prim_keys($primkeys) {
		// Si la valeur passée n'est pas un tableau, on le transforme en tableau
		if (! is_array ( $primkeys )) {
			$temp = $primkeys;
			unset ( $primkeys );
			$primkeys [0] = $temp;
		}
		
		// Verifie si on a passé une valeur pour chaque clé primaire
		if (count ( $this->primary_keys ) != count ( $primkeys ))
			$this->halt ( "1" );
			
			// Construit la condition
		$i = 0;
		do {
			$condition .= $this->primary_keys [$i] . " = " . "'" . $primkeys [$i] . "' AND ";
			$i ++;
		} while ( $i < count ( $this->primary_keys ) );
		
		// on ote les 5 derniers caractères
		return substr ( $condition, 0, strlen ( $condition ) - 5 );
	}
	
	// Ajoute un champ
	function insert() {
		$champ_modif = 0;
		$champs = "(";
		$valeurs = "(";
		foreach ( $this->field as $key => $value ) {
			// Vérifie si il y a eu un changement sur les champs
			if ($value != '') {
				$champ_modif ++;
				$champs .= "`" . $key . "`, ";
				$valeurs .= "'$value', ";
			}
		}
		
		// Construction de la query;
		if ($champ_modif == 0)
			$this->halt ( "4" );
		$champs = substr ( $champs, 0, strlen ( $champs ) - 2 ) . ") ";
		$valeurs = substr ( $valeurs, 0, strlen ( $valeurs ) - 2 ) . ")";
		$query = "INSERT INTO $this->table $champs VALUES $valeurs;";
		
		if ($this->debug == "Oui") {
			echo $query;
		} else {
			$DB = new DB_sql ();
			$DB->query ( $query );
			return mysql_insert_id ();
		}
	}
	
	// Update l'édition fetchée ou autre si forçage
	function update() {
		if (! $this->has_fetch)
			$this->halt ( "2" );
		$condition = $this->build_prim_keys ( $this->primary_keys_value );
		$champ_modif = 0;
		$query_mid = "SET ";
		
		$DB = new DB_sql ();
		
		// compte le nombre de champs modifiés
		foreach ( $this->field as $key => $value ) {
			// Vérifie si il y a eu un changement sur les champs
			if (stripslashes ( $value ) != $this->field_ori [$key]) {
				$champ_modif ++;
				$query_mid .= "`" . $key . "` = '" . $DB->escape ( $value ) . "', ";
			}
		}
		
		$query_mid = substr ( $query_mid, 0, strlen ( $query_mid ) - 2 ) . " ";
		
		// construction et execution de la query
		if ($champ_modif == 0)
			$this->halt ( "3" );
		$query = "UPDATE `$this->table` " . $query_mid . "WHERE $condition LIMIT 1;";
		if ($this->debug == "Oui")
			echo $query;
		else
			$DB->query ( $query );
	}
	
	// Efface l'edition fetchée ou autre si forçage
	function delete() {
		if (! $this->has_fetch)
			$this->halt ( "2" );
		$condition = $this->build_prim_keys ( $this->primary_keys_value );
		$query = "DELETE FROM `$this->table` WHERE $condition;";
		if ($this->debug == "Oui")
			echo $query;
		else
			$DB = new DB_sql ();
		$DB->query ( $query );
	}
	
	// Fonction de type débug affichant les principales variables de classe
	function display() {
		echo "<-- Variables de classe --><br>";
		echo "Table : " . $this->table . "<br>";
		echo "Flag debug : " . $this->debug . "<br>";
		echo "Flag has_flecth : " . $this->has_fetch . "<br>";
		echo "<-- Clés primaires --><br>";
		foreach ( $this->primary_keys as $key ) {
			echo "Clé : $key<br>";
		}
		echo "<-- Valeur clés primaires --><br>";
		foreach ( $this->primary_keys_value as $key ) {
			echo "Clé : $key<br>";
		}
		echo "<-- Valeur field --><br>";
		foreach ( $this->field as $key => $value ) {
			echo "$key : $value<br>";
		}
		echo "<-- Valeur field_ori --><br>";
		foreach ( $this->field_ori as $key => $value ) {
			echo "$key : $value<br>";
		}
	}
	function halt($errnum) {
		switch ($errnum) {
			case "1" :
				echo "Le nombre de clés ne correspond pas";
				break;
			case "2" :
				echo "Aucune clé primaire de séléctionnée - Commencez par faire un fetch";
				break;
			case "3" :
				echo "Aucun champ modifié - Update impossible";
				break;
			case "4" :
				echo "Aucun champ inséré - Insert impossible";
				break;
		}
		die ( "<br>Erreur fatale" );
	}
}