<?php

/**
 * tableau des relations cles etrangeres
 * 
 * to = table origine (reference)
 * td = table destination
 * co = colonne origine (reference)
 * cd = colonne destination
 */

$a_liaison = array (
		array (
				'td' => 'bd_collection',
				'to' => 'bd_editeur',
				'cd' => 'ID_EDITEUR',
				'co' => 'ID_EDITEUR' 
		),
		array (
				'td' => 'bd_edition',
				'to' => 'bd_editeur',
				'cd' => 'ID_EDITEUR',
				'co' => 'ID_EDITEUR' 
		),
		array (
				'td' => 'users_alb_prop',
				'to' => 'bd_editeur',
				'cd' => 'ID_EDITEUR',
				'co' => 'ID_EDITEUR' 
		),
		
		array (
				'td' => 'bd_tome',
				'to' => 'bd_auteur',
				'cd' => 'ID_SCENAR',
				'co' => 'ID_AUTEUR' 
		),
		array (
				'td' => 'bd_tome',
				'to' => 'bd_auteur',
				'cd' => 'ID_DESSIN',
				'co' => 'ID_AUTEUR' 
		),
		array (
				'td' => 'bd_tome',
				'to' => 'bd_auteur',
				'cd' => 'ID_COLOR',
				'co' => 'ID_AUTEUR' 
		),
		array (
				'td' => 'bd_tome',
				'to' => 'bd_auteur',
				'cd' => 'ID_SCENAR_ALT',
				'co' => 'ID_AUTEUR' 
		),
		array (
				'td' => 'bd_tome',
				'to' => 'bd_auteur',
				'cd' => 'ID_DESSIN_ALT',
				'co' => 'ID_AUTEUR' 
		),
		array (
				'td' => 'bd_tome',
				'to' => 'bd_auteur',
				'cd' => 'ID_COLOR_ALT',
				'co' => 'ID_AUTEUR' 
		),
		array (
				'td' => 'users_alb_prop',
				'to' => 'bd_auteur',
				'cd' => 'ID_SCENAR',
				'co' => 'ID_AUTEUR' 
		),
		array (
				'td' => 'users_alb_prop',
				'to' => 'bd_auteur',
				'cd' => 'ID_DESSIN',
				'co' => 'ID_AUTEUR' 
		),
		array (
				'td' => 'users_alb_prop',
				'to' => 'bd_auteur',
				'cd' => 'ID_COLOR',
				'co' => 'ID_AUTEUR' 
		),
		array (
				'td' => 'users_alb_prop',
				'to' => 'bd_auteur',
				'cd' => 'ID_SCENAR_ALT',
				'co' => 'ID_AUTEUR' 
		),
		array (
				'td' => 'users_alb_prop',
				'to' => 'bd_auteur',
				'cd' => 'ID_DESSIN_ALT',
				'co' => 'ID_AUTEUR' 
		),
		array (
				'td' => 'users_alb_prop',
				'to' => 'bd_auteur',
				'cd' => 'ID_COLOR_ALT',
				'co' => 'ID_AUTEUR' 
		),
		array (
				'td' => 'users_list_aut',
				'to' => 'bd_auteur',
				'cd' => 'id_auteur',
				'co' => 'ID_AUTEUR' 
		),
		
		array (
				'td' => 'bd_edition',
				'to' => 'bd_collection',
				'cd' => 'ID_COLLECTION',
				'co' => 'ID_COLLECTION' 
		),
		array (
				'td' => 'users_alb_prop',
				'to' => 'bd_collection',
				'cd' => 'ID_COLLECTION',
				'co' => 'ID_COLLECTION' 
		),
		
		array (
				'td' => 'users_alb_prop',
				'to' => 'bd_edition',
				'cd' => 'ID_EDITION',
				'co' => 'ID_EDITION' 
		),
		array (
				'td' => 'bd_tome',
				'to' => 'bd_edition',
				'cd' => 'ID_EDITION',
				'co' => 'ID_EDITION' 
		),
		
		array (
				'td' => 'users_album',
				'to' => 'bd_edition',
				'cd' => 'id_edition',
				'co' => 'ID_EDITION' 
		),
		array (
				'td' => 'bd_tome',
				'to' => 'bd_genre',
				'cd' => 'ID_GENRE',
				'co' => 'ID_GENRE' 
		),
		array (
				'td' => 'bd_serie',
				'to' => 'bd_genre',
				'cd' => 'ID_GENRE',
				'co' => 'ID_GENRE' 
		),
		array (
				'td' => 'users_alb_prop',
				'to' => 'bd_genre',
				'cd' => 'ID_GENRE',
				'co' => 'ID_GENRE' 
		),
		
		array (
				'td' => 'users_exclusions',
				'to' => 'bd_serie',
				'cd' => 'id_serie',
				'co' => 'ID_SERIE' 
		),
		array (
				'td' => 'bd_tome',
				'to' => 'bd_serie',
				'cd' => 'ID_SERIE',
				'co' => 'ID_SERIE' 
		),
		array (
				'td' => 'serie_comment',
				'to' => 'bd_serie',
				'cd' => 'id_serie',
				'co' => 'ID_SERIE' 
		),
		array (
				'td' => 'users_alb_prop',
				'to' => 'bd_serie',
				'cd' => 'ID_SERIE',
				'co' => 'ID_SERIE' 
		),
		
		array (
				'td' => 'bd_edition',
				'to' => 'users',
				'cd' => 'VALIDATOR',
				'co' => 'user_id' 
		),
		array (
				'td' => 'bd_edition',
				'to' => 'users',
				'cd' => 'USER_ID',
				'co' => 'user_id' 
		),
		array (
				'td' => 'users_list_aut',
				'to' => 'users',
				'cd' => 'user_id',
				'co' => 'user_id' 
		),
		array (
				'td' => 'users_exclusions',
				'to' => 'users',
				'cd' => 'user_id',
				'co' => 'user_id' 
		),
		array (
				'td' => 'users_list_carre',
				'to' => 'users',
				'cd' => 'user_id',
				'co' => 'user_id' 
		),
		array (
				'td' => 'users_comment',
				'to' => 'users',
				'cd' => 'USER_ID',
				'co' => 'user_id' 
		),
		array (
				'td' => 'users_album',
				'to' => 'users',
				'cd' => 'user_id',
				'co' => 'user_id' 
		),
		array (
				'td' => 'users_alb_prop',
				'to' => 'users',
				'cd' => 'USER_ID',
				'co' => 'user_id' 
		),
		array (
				'td' => 'users_alb_prop',
				'to' => 'users',
				'cd' => 'VALIDATOR',
				'co' => 'user_id' 
		),
		array (
				'td' => 'serie_comment',
				'to' => 'users',
				'cd' => 'user_id',
				'co' => 'user_id' 
		),
		
		array (
				'td' => 'bd_edition',
				'to' => 'bd_tome',
				'cd' => 'ID_TOME',
				'co' => 'ID_TOME' 
		),
		
		array (
				'td' => 'users_exclusions',
				'to' => 'bd_tome',
				'cd' => 'id_tome',
				'co' => 'ID_TOME' 
		),
		array (
				'td' => 'users_comment',
				'to' => 'bd_tome',
				'cd' => 'ID_TOME',
				'co' => 'ID_TOME' 
		),
		array (
				'td' => 'users_list_carre',
				'to' => 'bd_tome',
				'cd' => 'id_tome',
				'co' => 'ID_TOME' 
		),
		array (
				'td' => 'users_alb_prop',
				'to' => 'bd_tome',
				'cd' => 'ID_TOME',
				'co' => 'ID_TOME' 
		) 
)
;