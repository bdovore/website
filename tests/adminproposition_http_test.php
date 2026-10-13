<?php

/**
 * Tests CLI du lot A (garde commune des mutations de l'admin des propositions) :
 * jetons CSRF du perimetre admin-proposition, decision de statut HTTP,
 * isolation par rapport aux perimetres admin-catalogue et parabd.
 * Aucune base ni fichier destructif n'est touche.
 */

require_once dirname(__DIR__) . '/inc/util.inc.php';

$failures = 0;
$checks = 0;
function adminPropAssert($condition, $message)
{
    global $failures, $checks;
    $checks++;
    if (!$condition) { $failures++; fwrite(STDERR, "ECHEC: $message\n"); }
}

$_SESSION = array();

// Jeton du perimetre des propositions
$token = adminCsrfToken('admin-proposition');
adminPropAssert(is_string($token) && strlen($token) === 64 && ctype_xdigit($token), 'jeton CSRF propositions de 64 hexa');
adminPropAssert($token === adminCsrfToken('admin-proposition'), 'jeton CSRF propositions stable dans la session');
adminPropAssert(!empty($_SESSION['admin_csrf']['admin-proposition']), 'jeton stocke dans le perimetre admin-proposition');

// Validation du jeton
adminPropAssert(adminValidateCsrf('admin-proposition', $token), 'jeton valide accepte');
adminPropAssert(!adminValidateCsrf('admin-proposition', str_repeat('0', 64)), 'jeton incorrect refuse');
adminPropAssert(!adminValidateCsrf('admin-proposition', ''), 'jeton vide refuse');
adminPropAssert(!adminValidateCsrf('admin-proposition', null), 'jeton absent refuse');
adminPropAssert(!adminValidateCsrf('admin-proposition', array($token)), 'jeton non chaine refuse');
adminPropAssert(!adminValidateCsrf('perimetre-inconnu', $token), 'perimetre inconnu refuse');

// Isolation des perimetres
$tokenCatalogue = adminCsrfToken(); // perimetre admin-catalogue par defaut
$tokenParabd = parabdCsrfToken('write');
adminPropAssert($tokenCatalogue !== $token, 'jeton distinct du perimetre admin-catalogue');
adminPropAssert(!adminValidateCsrf('admin-proposition', $tokenCatalogue), 'jeton admin-catalogue refuse dans admin-proposition');
adminPropAssert(!adminValidateCsrf('admin-catalogue', $token), 'jeton admin-proposition refuse dans admin-catalogue');
adminPropAssert(!adminValidateCsrf('admin-proposition', $tokenParabd), 'jeton parabd refuse dans admin-proposition');
adminPropAssert(!parabdValidateCsrf('write', $token), 'jeton propositions refuse dans parabd');

// Champ de formulaire
$field = adminCsrfField('admin-proposition');
adminPropAssert(strpos($field, '<input type="hidden" name="csrf_token"') === 0 && strpos($field, $token) !== false, 'champ cache CSRF genere pour les propositions');

// Garde commune : statut HTTP selon la methode et le jeton
adminPropAssert(adminMutationHttpStatus('GET', $token, 'admin-proposition') === 405, 'mutation en GET refusee en 405');
adminPropAssert(adminMutationHttpStatus('get', $token, 'admin-proposition') === 405, 'mutation en GET (casse indifferente) refusee en 405');
adminPropAssert(adminMutationHttpStatus('HEAD', $token, 'admin-proposition') === 405, 'mutation en HEAD refusee en 405');
adminPropAssert(adminMutationHttpStatus('POST', '', 'admin-proposition') === 403, 'POST sans jeton refuse en 403');
adminPropAssert(adminMutationHttpStatus('POST', str_repeat('0', 64), 'admin-proposition') === 403, 'POST avec jeton invalide refuse en 403');
adminPropAssert(adminMutationHttpStatus('POST', $token, 'admin-proposition') === null, 'POST avec jeton valide accepte');
adminPropAssert(adminMutationHttpStatus('POST', $tokenCatalogue, 'admin-proposition') === 403, 'POST avec jeton d\'un autre perimetre refuse en 403');
adminPropAssert(adminMutationHttpStatus('POST', $tokenParabd, 'admin-proposition') === 403, 'POST avec jeton parabd refuse en 403');

// Reinitialisation : une session vide refuse tout
$_SESSION = array();
adminPropAssert(!adminValidateCsrf('admin-proposition', $token), 'jeton d\'une session precedente refuse apres reinitialisation');
adminPropAssert(adminMutationHttpStatus('POST', $token, 'admin-proposition') === 403, 'POST refuse en 403 apres reinitialisation de session');
adminPropAssert(adminMutationHttpStatus('POST', adminCsrfToken('admin-proposition'), 'admin-proposition') === null, 'nouveau jeton genere puis accepte');

echo $checks . " contrôles, " . $failures . " échec(s).\n";
exit($failures ? 1 : 0);
