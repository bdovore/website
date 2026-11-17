<?php
require_once dirname(__DIR__) . '/inc/account_security.php';
$checks = 0; $failures = 0;
function accountAssert($condition, $message) { global $checks, $failures; $checks++; if (!$condition) { $failures++; fwrite(STDERR, "ECHEC: $message\n"); } }
$password = 'été BD';
$hash = AccountSecurity::hashPassword($password);
accountAssert(AccountSecurity::verifyPassword($password, (object) array('password_v2' => $hash)), 'mot de passe bcrypt');
accountAssert(!AccountSecurity::verifyPassword('incorrect', (object) array('password_v2' => $hash)), 'mauvais mot de passe');
accountAssert(AccountSecurity::verifyPassword('vieux', (object) array('password' => md5('vieux'))), 'anciens mots de passe toujours acceptés');
accountAssert(!AccountSecurity::verifyPassword('migrated', (object) array('password' => 'migrated')), 'sentinelle refusée comme secret');
accountAssert(AccountSecurity::passwordError('court') !== null, 'nouveaux mots de passe courts refusés');
accountAssert(AccountSecurity::passwordError(str_repeat('é', 37)) !== null, 'limite bcrypt en octets');
accountAssert(AccountSecurity::passwordError($password) === null, 'espaces et accents autorisés');
accountAssert(AccountSecurity::passwordError(array()) !== null, 'tableau refusé');
accountAssert(AccountSecurity::bearer(array()) === null, 'absence de Bearer distincte du Bearer invalide');
accountAssert(AccountSecurity::bearer(array('HTTP_AUTHORIZATION' => 'Bearer invalide')) === false, 'Bearer malformé refusé');
$token = 'bdo2_' . str_repeat('a', 64);
accountAssert(AccountSecurity::bearer(array('REDIRECT_HTTP_AUTHORIZATION' => 'Bearer ' . $token)) === $token, 'en-tête transmis par Apache');
accountAssert(AccountSecurity::webDestination(array('type' => 'edition', 'id_tome' => 12)) === 'proposition?type=EDITION&id_tome=12', 'destination édition construite');
accountAssert(AccountSecurity::webDestination(array('keyword' => 'A&B')) === 'proposition?keyword=A%26B', 'paramètres encodés');
foreach (array(array('type' => 'https://evil.example'), array('type' => 'edition', 'id_tome' => '1&redirect=evil'), array('keyword' => array())) as $input) {
    try { AccountSecurity::webDestination($input); accountAssert(false, 'destination invalide refusée'); } catch (InvalidArgumentException $e) { accountAssert(true, 'destination invalide refusée'); }
}
accountAssert(AccountSecurity::stamp((object) array('password_v2' => $hash)) !== AccountSecurity::stamp((object) array('password_v2' => 'changed')), 'changement de mot de passe invalide les sessions');
accountAssert(!AccountSecurity::emailVerified((object) array('email' => 'old@example.test')), 'adresse historique non vérifiée par défaut');
accountAssert(AccountSecurity::emailVerified((object) array('email' => 'CURRENT@example.test', 'email_verified_address' => 'current@example.test', 'email_verified_at' => '2026-11-17 12:00:00')), 'preuve liée à l’adresse courante sans distinction de casse');
accountAssert(!AccountSecurity::emailVerified((object) array('email' => 'new@example.test', 'email_verified_address' => 'old@example.test', 'email_verified_at' => '2026-11-17 12:00:00')), 'preuve de l’ancienne adresse refusée pour une nouvelle adresse');
echo "$checks contrôles, $failures échec(s).\n";
exit($failures ? 1 : 0);
