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
    if (is_null($EAN)) return false;
    if (strlen($EAN) != 13) return false;
    // parse le code EAN
    for($i = 1; $i <= 13; $i ++) {
        $letter [$i] = substr ( $EAN, $i - 1, 1 );
    }
    // Calcule la clé sur 13 chiffres
    $key_sum = $letter [1] + 3 * $letter [2] + $letter [3] + 3 * $letter [4] + $letter [5] + 3 * $letter [6] + $letter [7] + 3 * $letter [8] + $letter [9] + 3 * $letter [10] + $letter [11] + 3 * $letter [12] + $letter [13];
    // Vérifie que lon a un multiple de 10
    if (($key_sum / 10) == intval ( $key_sum / 10 )) {
        return true;
    } else {
        return false;
    }
}

/**
 * Fonction de verification de code ISBN
 *
 * @param type $ISBN
 *          string(10)
 *
 */
function check_ISBN($ISBN) {
    if (is_null($ISBN)) return false;
    if (strlen($ISBN) != 10) return false;
    // parse le code ISBN
    for($i = 1; $i <= 10; $i ++) {
        $letter [$i] = (substr ( $ISBN, $i - 1, 1 ) == 'X' ? 10 : substr ( $ISBN, $i - 1, 1 ));
    }
    // Calcule la clé sur 9 chiffres
    $key_sum = 10 * $letter [1] + 9 * $letter [2] + 8 * $letter [3] + 7 * $letter [4] + 6 * $letter [5] + 5 * $letter [6] + 4 * $letter [7] + 3 * $letter [8] + 2 * $letter [9] + $letter [10];
    // Vérifie que lon a un multiple de 11
    if (($key_sum / 11) == intval ( $key_sum / 11 )) {
        return true;
    } else {
        return false;
    }
}
