<?php
/**
* Fonction de calcul de clé EAN sur texte
*
* @param        type
* $EAN        string(12)
*/
function cle_EAN($EAN) {

    // parse le code EAN
    for($i = 1; $i <= 12; $i ++) {
        $letter [$i] = substr ( $EAN, $i - 1, 1 );
    }
    // Calcule la clé sur 12 chiffres
    $key_sum = $letter [1] + 3 * $letter [2] + $letter [3] + 3 * $letter [4] + $letter [5] + 3 * $letter [6] + $letter [7] + 3 * $letter [8] + $letter [9] + 3 * $letter [10] + $letter [11] + 3 * $letter [12];
    // Calcule le multiple de 10 à atteindre
    $key_obj = intval ( $key_sum / 10 + 1 ) * 10;
    // Calcule la clé
    if (($key_sum / 10) == intval ( $key_sum / 10 )) {
        return 0;
    } else {
        return ($key_obj - $key_sum);
    }
}

/**
 * Fonction de calcul de clé ISBN sur texte
 *
 * @param type $ISBN
 *          string(9)
 *
 */
function cle_ISBN($ISBN) {

    // parse le code ISBN
    for($i = 1; $i <= 9; $i ++) {
        $letter [$i] = substr ( $ISBN, $i - 1, 1 );
    }
    // Calcule la clé sur 9 chiffres
    $key_sum = 10 * $letter [1] + 9 * $letter [2] + 8 * $letter [3] + 7 * $letter [4] + 6 * $letter [5] + 5 * $letter [6] + 4 * $letter [7] + 3 * $letter [8] + 2 * $letter [9];
    // Calcule le multiple de 10 à atteindre
    $key_obj = intval ( $key_sum / 11 + 1 ) * 11;
    // Calcule la clé
    if (($key_sum / 11) == intval ( $key_sum / 11 )) {
        return 0;
    } else {
        if (($key_obj - $key_sum) == 10) {
            return "X";
        } else {
            return ($key_obj - $key_sum);
        }
    }
}

/**
 * Fonction de conversion de code ISBN en code EAN
 *
 * @param type $ISBN
 *          string(10)
 *
 */
function ISBN_to_EAN($ISBN) {
    $ISBN = substr ( $ISBN, 0, 9 );
    return "978" . $ISBN . cle_EAN ( "978" . $ISBN );
}

/**
 * Fonction de conversion de code EAN en code ISBN
 *
 * @param type $EAN
 *          string(13)
 *
 */
function EAN_to_ISBN($EAN) {
    $EAN = substr ( $EAN, 3, 9 );
    return $EAN . cle_ISBN ( $EAN );
}

/**
 * Fonction de verification de code EAN
 *
 * @param type $EAN
 *          string(13)
 *
 */
function check_EAN($EAN) {
    // Vérifie que $EAN n'est pas nul
    if (is_null($EAN)) return false;
    
    // Vérifie que $EAN a exactement 13 caractères
    if (strlen($EAN) != 13) return false;
    
    // Vérifie que chaque caractère est bien un chiffre
    if (!ctype_digit($EAN)) return false;
    
    // Parse le code EAN
    $key_sum = 0;
    for($i = 1; $i <= 12; $i++) {
        $digit = (int) substr($EAN, $i - 1, 1);
        
        // Alternance des coefficients 1 et 3
        $key_sum += ($i % 2 === 0) ? 3 * $digit : $digit;
    }

    // Calcule le dernier chiffre de contrôle
    $check_digit = (10 - ($key_sum % 10)) % 10;

    // Vérifie que le dernier chiffre correspond à la clé de contrôle
    return (int) substr($EAN, 12, 1) === $check_digit;
}

/**
 * Fonction de verification de code ISBN
 *
 * @param type $ISBN
 *          string(10)
 *
 */
function check_ISBN($ISBN) {
    // Vérifie que $ISBN n'est pas nul
    if (is_null($ISBN)) return false;
    
    // Vérifie que $ISBN a exactement 10 caractères
    if (strlen($ISBN) != 10) return false;

    // Vérifie que chaque caractère est bien un chiffre ou 'X' (seulement possible en 10ème position)
    for ($i = 0; $i < 9; $i++) {
        if (!ctype_digit($ISBN[$i])) {
            return false;
        }
    }
    
    // Le dernier caractère peut être un chiffre ou un 'X'
    if (!ctype_digit($ISBN[9]) && $ISBN[9] != 'X') {
        return false;
    }
    
    // Calcule la clé sur 9 chiffres, avec gestion du 'X' en tant que 10
    $key_sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $key_sum += (10 - $i) * (int)$ISBN[$i];
    }

    // Gestion de la dernière lettre (10ème position)
    $last_digit = ($ISBN[9] == 'X') ? 10 : (int)$ISBN[9];
    $key_sum += $last_digit;

    // Vérifie que la somme est un multiple de 11
    return $key_sum % 11 === 0;
}

