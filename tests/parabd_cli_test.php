<?php

require_once dirname(__DIR__) . '/mvc/models/ParabdRules.php';
require_once dirname(__DIR__) . '/mvc/models/ParabdImageStorage.php';
require_once dirname(__DIR__) . '/inc/util.inc.php';

$failures = 0;
$checks = 0;
function parabdAssert($condition, $message)
{
    global $failures, $checks;
    $checks++;
    if (!$condition) { $failures++; fwrite(STDERR, "ECHEC: $message\n"); }
}

parabdAssert(ParabdRules::normalizeText('  L’Épée—des Étoiles! ') === 'l epee des etoiles', 'normalisation du titre');
parabdAssert(ParabdRules::normalizeIdentifier('EAN13', '400-638 133393-1') === '4006381333931', 'normalisation EAN');
parabdAssert(ParabdRules::normalizeIdentifier('MANUFACTURER_REF', ' ref. AB-12 ') === 'REFAB12', 'normalisation référence fabricant');

parabdAssert(ParabdRules::defaultAuthorRole(array('FLG_DESSIN'=>1,'FLG_DESIGN'=>1,'FLG_SCULPT'=>1,'FLG_PEINT'=>1)) === 'ILLUSTRATOR', 'priorité du rôle dessinateur');
parabdAssert(ParabdRules::defaultAuthorRole(array('FLG_DESSIN'=>0,'FLG_DESIGN'=>1,'FLG_SCULPT'=>1,'FLG_PEINT'=>1)) === 'DESIGNER', 'priorité du rôle designer');
parabdAssert(ParabdRules::defaultAuthorRole(array('FLG_DESSIN'=>0,'FLG_DESIGN'=>0,'FLG_SCULPT'=>1,'FLG_PEINT'=>1)) === 'SCULPTOR', 'priorité du rôle sculpteur');
parabdAssert(ParabdRules::defaultAuthorRole(array('FLG_DESSIN'=>0,'FLG_DESIGN'=>0,'FLG_SCULPT'=>0,'FLG_PEINT'=>1)) === 'PAINTER', 'rôle peintre');
parabdAssert(ParabdRules::defaultAuthorRole(array()) === '', 'absence de rôle automatique sans flag');

parabdAssert(ParabdRules::isValidIdentifier('EAN13', '4006381333931'), 'EAN13 valide');
parabdAssert(!ParabdRules::isValidIdentifier('EAN13', '4006381333932'), 'EAN13 invalide');
parabdAssert(ParabdRules::isValidIdentifier('UPCA', '036000291452'), 'UPC-A valide');
parabdAssert(ParabdRules::isValidIdentifier('ISBN10', '0306406152'), 'ISBN10 valide');
parabdAssert(ParabdRules::isValidIdentifier('ISBN13', '9780306406157'), 'ISBN13 valide');

$date = ParabdRules::parsePartialDate('2026');
parabdAssert($date['date'] === '2026-01-01' && $date['precision'] === 'YEAR', 'date année');
$date = ParabdRules::parsePartialDate('2026-08');
parabdAssert($date['date'] === '2026-08-01' && $date['precision'] === 'MONTH', 'date mois');
$date = ParabdRules::parsePartialDate('2026-08-09');
parabdAssert($date['precision'] === 'DAY', 'date jour');
try { ParabdRules::parsePartialDate('2026-02-30'); parabdAssert(false, 'date invalide refusée'); } catch (ParabdException $expected) { parabdAssert(true, 'date invalide refusée'); }

parabdAssert(ParabdRules::positiveInt('356') === 356, 'dimension entière valide');
try { ParabdRules::positiveInt('356.5'); parabdAssert(false, 'dimension décimale refusée'); } catch (ParabdException $expected) { parabdAssert(true, 'dimension décimale refusée'); }

$candidate = array('TITLE' => 'Statuette Le Chat', 'TYPE_ID' => 1, 'MANUFACTURER' => 'Pixi', 'PUBLISHER' => 'Dupuis', 'RELEASE_DATE' => '2025-01-01', 'WIDTH_MM' => 100, 'HEIGHT_MM' => 200, 'DEPTH_MM' => 80);
$input = array('TITLE' => 'Statuette Le Chat', 'TYPE_ID' => 1, 'MANUFACTURER' => 'Pixi', 'PUBLISHER' => 'Dupuis', 'RELEASE_DATE' => '2025-01-01', 'WIDTH_MM' => 103, 'HEIGHT_MM' => 198, 'DEPTH_MM' => 80, 'common_relation' => true);
$duplicate = ParabdRules::duplicateLevel($candidate, $input);
parabdAssert($duplicate && $duplicate['level'] === 'STRONG', 'seuil doublon fort');
$input['TITLE'] = 'Chat'; $input['MANUFACTURER'] = 'Autre'; $input['common_relation'] = true;
$duplicate = ParabdRules::duplicateLevel($candidate, $input);
parabdAssert($duplicate === null, 'rattachement commun seul insuffisant');
$input['TITLE'] = 'Statuette du Chat';
$input['common_relation'] = false;
$duplicate = ParabdRules::duplicateLevel($candidate, $input);
parabdAssert($duplicate && $duplicate['level'] === 'POSSIBLE', 'titre proche, même type et même éditeur possible');
$input['TITLE'] = 'Statuette Le Chat'; $input['PUBLISHER'] = 'Autre';
$duplicate = ParabdRules::duplicateLevel($candidate, $input);
parabdAssert($duplicate && $duplicate['level'] === 'POSSIBLE', 'titre identique et même type possibles sans information complémentaire');
$input['TYPE_ID'] = 2;
parabdAssert(ParabdRules::duplicateLevel($candidate, $input) === null, 'titre identique mais type différent insuffisant');
$candidateExlibris = array('TITLE' => 'Ex-libris Spider-man (exclusivité BDfugue)', 'TYPE_ID' => 2, 'PUBLISHER' => '');
$inputExlibris = array('TITLE' => 'Ex-libris Astérix (exclusifivté BDFugue)', 'TYPE_ID' => 2, 'PUBLISHER' => '', 'common_relation' => false);
parabdAssert(ParabdRules::duplicateLevel($candidateExlibris, $inputExlibris) === null, 'termes génériques de titres insuffisants');
$fourreuxTitles = array('le fourreux', 'Loisel - le Le fourreux', 'le fourreux de loisel', 'Fourreux');
foreach ($fourreuxTitles as $leftIndex => $leftTitle) {
    foreach (array_slice($fourreuxTitles, $leftIndex + 1) as $rightTitle) {
        $fourreuxDuplicate = ParabdRules::duplicateLevel(
            array('TITLE' => $leftTitle, 'TYPE_ID' => 1, 'PUBLISHER' => ''),
            array('TITLE' => $rightTitle, 'TYPE_ID' => 1, 'PUBLISHER' => '', 'common_relation' => false)
        );
        parabdAssert($fourreuxDuplicate && $fourreuxDuplicate['level'] === 'POSSIBLE', 'noyau Fourreux commun : ' . $leftTitle . ' / ' . $rightTitle);
    }
}
$numberedDuplicate = ParabdRules::duplicateLevel(
    array('TITLE' => 'Test fusion 1', 'TYPE_ID' => 1, 'PUBLISHER' => ''),
    array('TITLE' => 'Test fusion 37', 'TYPE_ID' => 1, 'PUBLISHER' => '', 'common_relation' => false)
);
parabdAssert($numberedDuplicate && $numberedDuplicate['level'] === 'POSSIBLE' && in_array('Numérotation différente', $numberedDuplicate['reasons'], true), 'noyau commun avec numérotation différente');
$genericDuplicate = ParabdRules::duplicateLevel(
    array('TITLE' => 'Statuette Astérix', 'TYPE_ID' => 1, 'PUBLISHER' => ''),
    array('TITLE' => 'Statuette Tintin', 'TYPE_ID' => 1, 'PUBLISHER' => '', 'common_relation' => false)
);
parabdAssert($genericDuplicate === null, 'type générique commun insuffisant');
$candidateBoundary = array('TITLE' => 'abcdefghij', 'TYPE_ID' => 3, 'PUBLISHER' => 'Delcourt');
$inputBoundary = array('TITLE' => 'abcdefghiX', 'TYPE_ID' => 3, 'PUBLISHER' => 'Autre', 'common_relation' => true);
parabdAssert(ParabdRules::duplicateLevel($candidateBoundary, $inputBoundary)['level'] === 'STRONG', 'seuil fort inclusif à 90');
$candidateBoundary['TITLE'] = 'abcde'; $inputBoundary['TITLE'] = 'abcdf'; $inputBoundary['PUBLISHER'] = 'Delcourt'; $inputBoundary['common_relation'] = false;
parabdAssert(ParabdRules::duplicateLevel($candidateBoundary, $inputBoundary) === null, 'seuil possible strictement supérieur à 80');

$now = strtotime('2026-08-09 12:00:00');
parabdAssert(ParabdRules::calculateTrust('2025-08-09 11:59:59', 5, 'NONE', $now), 'contributeur fiable');
parabdAssert(!ParabdRules::calculateTrust('2025-08-10 00:00:00', 20, 'NONE', $now), 'ancienneté insuffisante');
parabdAssert(!ParabdRules::calculateTrust('2020-01-01', 99, 'REVOKE', $now), 'révocation confiance');
parabdAssert(ParabdRules::calculateTrust('2026-08-09', 0, 'GRANT', $now), 'forçage confiance');

$_SESSION = array();
$csrf = parabdCsrfToken('parabd-test');
parabdAssert(strlen($csrf) === 64 && parabdValidateCsrf('parabd-test', $csrf), 'jeton CSRF par périmètre');
parabdAssert(!parabdValidateCsrf('parabd-test', str_repeat('0', 64)), 'jeton CSRF incorrect refusé');

parabdAssert(!ParabdImageStorage::isPublicRemoteIp('127.0.0.1'), 'URL image locale IPv4 refusée');
parabdAssert(!ParabdImageStorage::isPublicRemoteIp('192.168.1.10'), 'URL image privée IPv4 refusée');
parabdAssert(!ParabdImageStorage::isPublicRemoteIp('::1'), 'URL image locale IPv6 refusée');
parabdAssert(ParabdImageStorage::isPublicRemoteIp('8.8.8.8'), 'URL image publique acceptée');

if (!defined('BDO_URL')) define('BDO_URL', '/');
if (!defined('BDO_PARABD_ENABLED')) define('BDO_PARABD_ENABLED', true);
require_once dirname(__DIR__) . '/mvc/views/helpers/pagination.php';
$pagination = new Pagination();
$_SESSION['userConnect'] = (object) array('level' => 1);
foreach (array('/adminparabd/edit?id=1', '/adminparabd/queues?status=pending') as $adminParabdUrl) {
    $_SERVER['REQUEST_URI'] = $adminParabdUrl;
    $adminMenu = $pagination->menuAdmin();
    parabdAssert(strpos($adminMenu, '<li class="active"><a href="/adminparabd">Para-BD</a>') !== false && substr_count($adminMenu, 'class="active"') === 1, 'onglet Para-BD actif sur ' . $adminParabdUrl);
}
$_SERVER['REQUEST_URI'] = '/admin/user?id=1';
$adminMenu = $pagination->menuAdmin();
parabdAssert(strpos($adminMenu, '<li class="active"><a href="/admin/user">Utilisateurs</a>') !== false && substr_count($adminMenu, 'class="active"') === 1, 'sous-page admin la plus précise active');
$_SESSION['userConnect']->level = 2;
parabdAssert(!parabdMenuVisible() && strpos($pagination->menuCollection(), '>Para-BD</a>') === false, 'menu Para-BD masqué au-delà de BDO_PARABD_MIN_LEVEL');
unset($_SESSION['userConnect']);
parabdAssert(!parabdMenuVisible(), 'menu Para-BD masqué aux visiteurs');

if (extension_loaded('gd') && extension_loaded('fileinfo')) {
    $tmpRoot = sys_get_temp_dir() . '/bdovore-parabd-test-' . getmypid();
    if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
    if (!defined('BDO_DIR_PARABD')) define('BDO_DIR_PARABD', $tmpRoot . DS);
    if (!defined('BDO_PARABD_MAX_UPLOAD_BYTES')) define('BDO_PARABD_MAX_UPLOAD_BYTES', 5242880);
    if (!defined('BDO_PARABD_MAX_IMAGE_PIXELS')) define('BDO_PARABD_MAX_IMAGE_PIXELS', 30000000);
    mkdir($tmpRoot, 0775, true);
    $sourcePath = $tmpRoot . '/source.png';
    $image = imagecreatetruecolor(2000, 1000);
    imagefilledrectangle($image, 0, 0, 1999, 999, imagecolorallocate($image, 20, 90, 180));
    imagepng($image, $sourcePath); imagedestroy($image);
    $imageStorage = new ParabdImageStorage();
    $stored = $imageStorage->store(array('tmp_name' => $sourcePath, 'name' => 'source.png', 'size' => filesize($sourcePath)), 123, 1);
    parabdAssert($stored['width'] === 1600 && $stored['height'] === 800, 'redimensionnement côté long');
    parabdAssert(is_file($stored['absolute_path']), 'image réencodée écrite');
    $oriented = imagecreatetruecolor(30, 20); $oriented = ParabdImageStorage::orient($oriented, 6);
    parabdAssert(imagesx($oriented) === 20 && imagesy($oriented) === 30, 'orientation EXIF'); imagedestroy($oriented);
    try { $imageStorage->store(array('tmp_name' => $sourcePath, 'name' => 'mensonge.jpg', 'size' => filesize($sourcePath)), 124, 1); parabdAssert(false, 'extension MIME incohérente refusée'); } catch (ParabdException $expected) { parabdAssert(true, 'extension MIME incohérente refusée'); }
    $largePath = $tmpRoot . '/large.jpg'; file_put_contents($largePath, str_repeat('x', 5242881));
    try { $imageStorage->store(array('tmp_name' => $largePath, 'name' => 'large.jpg', 'size' => filesize($largePath)), 125, 1); parabdAssert(false, 'fichier trop gros refusé'); } catch (ParabdException $expected) { parabdAssert(true, 'fichier trop gros refusé'); }
    $hugePath = $tmpRoot . '/huge.png';
    $ihdr = pack('NNCCCCC', 6001, 5000, 8, 2, 0, 0, 0); $chunk = 'IHDR' . $ihdr;
    file_put_contents($hugePath, "\x89PNG\r\n\x1a\n" . pack('N', strlen($ihdr)) . $chunk . pack('H*', hash('crc32b', $chunk)) . pack('N', 0) . 'IEND' . pack('H*', hash('crc32b', 'IEND')));
    try { $imageStorage->store(array('tmp_name' => $hugePath, 'name' => 'huge.png', 'size' => filesize($hugePath)), 126, 1); parabdAssert(false, 'image de plus de 30 MP refusée'); } catch (ParabdException $expected) { parabdAssert(true, 'image de plus de 30 MP refusée'); }
    @unlink($stored['absolute_path']); @unlink($sourcePath);
    @unlink($largePath); @unlink($hugePath);
    @rmdir(dirname($stored['absolute_path'])); @rmdir(dirname(dirname($stored['absolute_path']))); @rmdir($tmpRoot);
}

echo $checks . " contrôles, " . $failures . " échec(s).\n";
exit($failures ? 1 : 0);
