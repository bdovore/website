<?php
define('BDO_DIR', dirname(__DIR__) . '/'); define('BDO_URL', 'https://www.bdovore.test/'); define('BDO_MOBILE_API_ENABLED', false); define('BDO_SOCIAL_ENABLED', false);
require_once BDO_DIR . 'inc/util.inc.php'; require_once BDO_DIR . 'vendor/autoload.php';
$_SESSION = array(); $checks = 0; $failures = 0;
function pageAssert($condition, $message) { global $checks, $failures; $checks++; if (!$condition) { $failures++; fwrite(STDERR, "ECHEC: $message\n"); } }
function accountPage($path, $values) { $view = (object) $values; ob_start(); require BDO_DIR . 'mvc/views/views_controllers/' . $path . '.phtml'; return ob_get_clean(); }
$input = '<script>alert(1)</script>"';
$login = accountPage('compte/connexion', array('login_error' => $input, 'login_identifier' => $input, 'login_pseudo' => '', 'login_needs_pseudo' => false));
pageAssert(strpos($login, '<script>') === false && strpos($login, '&lt;script&gt;') !== false, 'identifiant et message de connexion échappés');
pageAssert(strpos($login, 'name="csrf_token"') !== false && strpos($login, 'method="post"') !== false, 'connexion protégée par formulaire POST et CSRF');
pageAssert(strpos($login, 'Continuer avec Google') === false, 'boutons sociaux masqués avant configuration');
pageAssert(strpos($login, 'compte/forgotlogin') !== false, 'pseudo oublié accessible directement à la connexion');
$recovery = accountPage('compte/forgotlogin', array('recovery_sent' => '', 'recovery_error' => $input, 'recovery_email' => $input));
pageAssert(strpos($recovery, '<script>') === false && strpos($recovery, '&lt;script&gt;') !== false, 'récupération échappe les erreurs et l’adresse saisie');
pageAssert(strpos($recovery, 'name="csrf_token"') !== false && strpos($recovery, 'type="email"') !== false && strpos($recovery, 'method="post"') !== false, 'récupération demande uniquement un e-mail avec POST et CSRF');
$sent = accountPage('compte/forgotlogin', array('recovery_sent' => 'Si cette adresse est associée à un compte actif, un e-mail a été envoyé.'));
pageAssert(strpos($sent, 'role="status"') !== false && strpos($sent, '<form') === false && strpos($sent, 'compte/connexion') !== false && strpos($sent, 'compte/forgotpass') !== false, 'confirmation du rappel permet la connexion et la récupération du mot de passe');
$shared = accountPage('compte/connexion', array('login_error' => '', 'login_identifier' => 'shared@example.test', 'login_pseudo' => $input, 'login_needs_pseudo' => true));
pageAssert(strpos($shared, 'Pseudo de la collection') !== false && strpos($shared, '<script>') === false, 'précision du pseudo rendue et échappée');
$signup = accountPage('compte/inscription', array('validInscription' => false, 'ERRORTEXT' => $input, 'USERNAME' => $input, 'EMAIL' => $input));
pageAssert(strpos($signup, '<script>') === false, 'inscription échappe les valeurs utilisateur');
pageAssert(strpos($signup, 'name="NewUser"') !== false && strpos($signup, 'autocomplete="new-password"') !== false && strpos($signup, 'name="csrf_token"') !== false, 'inscription moderne conserve pseudo, autocomplétion et CSRF');
pageAssert(strpos($signup, 'activé après sa confirmation') !== false, 'inscription annonce la confirmation obligatoire');
$confirmation = accountPage('compte/confirmation', array('purpose' => 'email', 'confirmation_error' => $input));
pageAssert(strpos($confirmation, '<script>') === false && strpos($confirmation, 'L’adresse actuelle reste utilisée') !== false && strpos($confirmation, 'name="csrf_token"') !== false, 'confirmation explique le changement différé et échappe l’erreur');
$result = accountPage('compte/resultat', array('account_error' => false, 'account_message' => $input, 'account_result_target' => 'compte', 'account_result_link' => 'Revenir au profil'));
pageAssert(strpos($result, '<script>') === false && strpos($result, 'role="status"') !== false && strpos($result, 'Revenir au profil') !== false, 'résultat de confirmation visible et échappé');
$choices = accountPage('authsocial/continue', array('social_error' => '', 'social_identity' => (object) array('provider' => 'google', 'email' => 'user@example.test'), 'social_accounts' => array(array('user_id' => 7, 'username' => $input, 'level' => 2)), 'social_pending_registration' => null));
pageAssert(strpos($choices, '<script>') === false && strpos($choices, 'name="user_id" value="7"') !== false, 'choix de collection échappé et lié à un identifiant');
pageAssert(strpos($choices, 'Associer un compte BDovore existant') !== false && strpos($choices, 'Créer un nouveau compte BDovore') !== false, 'association et nouvelle inscription distinguées');
echo "$checks contrôles des pages de compte, $failures échec(s).\n"; exit($failures ? 1 : 0);
