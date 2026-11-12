<?php
require_once dirname(__DIR__) . '/inc/account_security.php';
$checks = 0; $failures = 0;
function accountAssert($condition, $message) { global $checks, $failures; $checks++; if (!$condition) { $failures++; fwrite(STDERR, "ECHEC: $message\n"); } }
$password = 'une phrase de passe sûre';
$hash = AccountSecurity::hashPassword($password);
accountAssert(AccountSecurity::verifyPassword($password, (object) array('password_v2' => $hash)), 'mot de passe bcrypt');
accountAssert(!AccountSecurity::verifyPassword('incorrect', (object) array('password_v2' => $hash)), 'mauvais mot de passe');
accountAssert(AccountSecurity::verifyPassword('ancien', (object) array('password' => md5('ancien'))), 'anciens mots de passe toujours acceptés');
accountAssert(!AccountSecurity::verifyPassword('migrated', (object) array('password' => 'migrated')), 'sentinelle refusée comme secret');
accountAssert(AccountSecurity::passwordError('trop court') !== null, 'nouveaux mots de passe courts refusés');
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
echo "$checks contrôles, $failures échec(s).\n";
exit($failures ? 1 : 0);
