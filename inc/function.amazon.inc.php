<?php

/**
* Fonction retournant les infos albums d'une page amazon
*
* @param					type			description
* $URL_page					string	 		URL de la page HTML contenant l'info
* 
* Info retournée dans le tableau $alb_bdnet
* 1 : titre
* 2 : série
* 3 : dessinateur
* 4 : scénariste
* 5 : genre
* 6 : editeur
* 7 : date parution (au format SQL)
* 8 : url image
* 9 : numéro de tome
*/
function scan_alb_amazon($URL_page) {
	$file_content = file_get_contents ( $URL_page );
	
	// balise meta
	$pointeur_b = strpos ( $file_content, '<META name="keywords" content="' ) + 31;
	$pointeur_e = strpos ( $file_content, '>"', $pointeur_b );
	$alb_amazon [1] = substr ( $file_content, $pointeur_b, $pointeur_e - $pointeur_b );
	// Serie
	$pointeur_b = strpos ( $file_content, "<B>S&eacute;rie: </B>" ) + 21;
	$pointeur_b = strpos ( $file_content, ">", $pointeur_b ) + 1;
	$pointeur_e = strpos ( $file_content, "</A>", $pointeur_b );
	$alb_bdnet [2] = substr ( $file_content, $pointeur_b, $pointeur_e - $pointeur_b );
	// Dessinateur
	$pointeur_b = strpos ( $file_content, "<B>Dessinateur:</B>" ) + 19;
	$pointeur_b = strpos ( $file_content, ">", $pointeur_b ) + 1;
	$pointeur_e = strpos ( $file_content, "</A>", $pointeur_b );
	$alb_bdnet [3] = substr ( $file_content, $pointeur_b, $pointeur_e - $pointeur_b );
	// Scenariste
	$pointeur_b = strpos ( $file_content, "<B>Sc&eacute;nariste:</B>" ) + 25;
	$pointeur_b = strpos ( $file_content, ">", $pointeur_b ) + 1;
	$pointeur_e = strpos ( $file_content, "</A>", $pointeur_b );
	$alb_bdnet [4] = substr ( $file_content, $pointeur_b, $pointeur_e - $pointeur_b );
	// Genre
	$pointeur_b = strpos ( $file_content, "<B>Genre:</B>" ) + 25;
	$pointeur_b = strpos ( $file_content, ">", $pointeur_b ) + 1;
	$pointeur_e = strpos ( $file_content, "</A>", $pointeur_b );
	$alb_bdnet [5] = substr ( $file_content, $pointeur_b, $pointeur_e - $pointeur_b );
	// Editeur
	$pointeur_b = strpos ( $file_content, "<B>Editeur:</B>" ) + 15;
	$pointeur_b = strpos ( $file_content, ">", $pointeur_b ) + 1;
	$pointeur_e = strpos ( $file_content, "</A>", $pointeur_b );
	$alb_bdnet [6] = substr ( $file_content, $pointeur_b, $pointeur_e - $pointeur_b );
	// Date Parution
	if (strpos ( $file_content, "Paru en: " )) {
		$pointeur_b = strpos ( $file_content, "Paru en: " ) + 9;
		$pointeur_e = strpos ( $file_content, "<", $pointeur_b );
		$alb_bdnet [7] = cv_date ( substr ( $file_content, $pointeur_b, $pointeur_e - $pointeur_b ) );
	}
	// URL_Image
	$pointeur_b = strpos ( $file_content, "<TD WIDTH=\"150\" VALIGN=\"MIDDLE\"><img src=\"" ) + 42;
	$pointeur_e = strpos ( $file_content, "\"", $pointeur_b );
	$tmp_url = substr ( $file_content, $pointeur_b, $pointeur_e - $pointeur_b );
	if (strpos ( $file_content, "http" )) {
		$alb_bdnet [8] = $tmp_url;
	}
	// Tome
	$pointeur_b = strpos ( $file_content, "tome " ) + 5;
	$pointeur_e = strpos ( $file_content, "<", $pointeur_b );
	$alb_bdnet [9] = substr ( $file_content, $pointeur_b, $pointeur_e - $pointeur_b );
	
	// Identifie la présence d'un double scénariste ou non
	if (ereg ( "(.*),(.*)", $alb_bdnet [4], $des_list )) {
		$alb_bdnet [4] = $des_list [1];
		$alb_bdnet [10] = $des_list [2];
	} else {
		$alb_bdnet [10] = '';
	}
	
	// Identifie la présence d'un double dessinateur ou non
	if (ereg ( "(.*),(.*)", $alb_bdnet [3], $des_list )) {
		$alb_bdnet [3] = $des_list [1];
		$alb_bdnet [11] = $des_list [2];
	} else {
		$alb_bdnet [11] = '';
	}
	
	// Inclue le prix de l'album
	$pointeur_b = strpos ( $file_content, "Prix " ) + 14;
	$pointeur_e = strpos ( $file_content, " &euro;", $pointeur_b );
	$alb_bdnet [12] = substr ( $file_content, $pointeur_b, $pointeur_e - $pointeur_b );
	// elimine la virgule
	$alb_bdnet [12] = str_replace ( ",", ".", $alb_bdnet [12] );
	
	return $alb_bdnet;
}
function get_couv_url($URL_page, $isbn) {
	$file_content = @file_get_contents ( $URL_page );
	if ($file_content == "") {
		return "";
		exit ();
	}
	
	if (eregi ( "(http://images-eu.{10,30}/P/" . $isbn . ".{1,20}jpg)", $file_content, $url_list ))
		return $url_list [1];
	else
		return "";
}
