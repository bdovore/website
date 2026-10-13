<?php

/**
 * Tests CLI du lot A (garde commune des mutations de l'admin du catalogue) :
 * jetons CSRF, decision de statut HTTP, isolation des perimetres.
 * Aucune base ni fichier destructif n'est touche.
 */

require_once dirname(__DIR__) . '/inc/util.inc.php';

$failures = 0;
$checks = 0;
function adminAssert($condition, $message)
{
    global $failures, $checks;
    $checks++;
    if (!$condition) { $failures++; fwrite(STDERR, "ECHEC: $message\n"); }
}

$_SESSION = array();

// Jeton du perimetre par defaut
$token = adminCsrfToken();
adminAssert(is_string($token) && strlen($token) === 64 && ctype_xdigit($token), 'jeton CSRF admin de 64 hexa');
adminAssert($token === adminCsrfToken(), 'jeton CSRF admin stable dans la session');
adminAssert(!empty($_SESSION['admin_csrf']['admin-catalogue']), 'jeton stocke dans le perimetre admin-catalogue');
adminAssert(empty($_SESSION['parabd_csrf']), 'aucune fuite vers le perimetre parabd');

// Validation du jeton
adminAssert(adminValidateCsrf('admin-catalogue', $token), 'jeton valide accepte');
adminAssert(!adminValidateCsrf('admin-catalogue', str_repeat('0', 64)), 'jeton incorrect refuse');
adminAssert(!adminValidateCsrf('admin-catalogue', ''), 'jeton vide refuse');
adminAssert(!adminValidateCsrf('admin-catalogue', null), 'jeton absent refuse');
adminAssert(!adminValidateCsrf('admin-catalogue', array($token)), 'jeton non chaine refuse');
adminAssert(!adminValidateCsrf('perimetre-inconnu', $token), 'perimetre inconnu refuse');

// Isolation des perimetres
$tokenAutre = adminCsrfToken('autre-perimetre');
adminAssert($tokenAutre !== $token, 'jeton distinct par perimetre');
adminAssert(!adminValidateCsrf('admin-catalogue', $tokenAutre), 'jeton d\'un autre perimetre refuse dans admin-catalogue');
adminAssert(!adminValidateCsrf('autre-perimetre', $token), 'jeton admin-catalogue refuse dans un autre perimetre');
$tokenParabd = parabdCsrfToken('write');
adminAssert(!adminValidateCsrf('admin-catalogue', $tokenParabd), 'jeton parabd refuse dans admin-catalogue');
adminAssert(!parabdValidateCsrf('write', $token), 'jeton admin refuse dans parabd');

// Champ de formulaire
$field = adminCsrfField();
adminAssert(strpos($field, '<input type="hidden" name="csrf_token"') === 0 && strpos($field, $token) !== false, 'champ cache CSRF genere');
$fieldAutre = adminCsrfField('autre-perimetre');
adminAssert(strpos($fieldAutre, $tokenAutre) !== false && strpos($fieldAutre, $token) === false, 'champ cache lie au perimetre demande');

// Garde commune : statut HTTP selon la methode et le jeton
adminAssert(adminMutationHttpStatus('GET', $token) === 405, 'mutation en GET refusee en 405');
adminAssert(adminMutationHttpStatus('get', $token) === 405, 'mutation en GET (casse indifferente) refusee en 405');
adminAssert(adminMutationHttpStatus('HEAD', $token) === 405, 'mutation en HEAD refusee en 405');
adminAssert(adminMutationHttpStatus('POST', '') === 403, 'POST sans jeton refuse en 403');
adminAssert(adminMutationHttpStatus('POST', str_repeat('0', 64)) === 403, 'POST avec jeton invalide refuse en 403');
adminAssert(adminMutationHttpStatus('POST', $token) === null, 'POST avec jeton valide accepte');
adminAssert(adminMutationHttpStatus('POST', $tokenAutre) === 403, 'POST avec jeton d\'un autre perimetre refuse en 403');
adminAssert(adminMutationHttpStatus('POST', $token, 'admin-catalogue') === null, 'perimetre explicite accepte');

// Reinitialisation : une session vide refuse tout
$_SESSION = array();
adminAssert(!adminValidateCsrf('admin-catalogue', $token), 'jeton d\'une session precedente refuse apres reinitialisation');
adminAssert(adminMutationHttpStatus('POST', $token) === 403, 'POST refuse en 403 apres reinitialisation de session');
adminAssert(adminMutationHttpStatus('POST', adminCsrfToken()) === null, 'nouveau jeton genere puis accepte');

echo $checks . " contrôles, " . $failures . " échec(s).\n";
exit($failures ? 1 : 0);
