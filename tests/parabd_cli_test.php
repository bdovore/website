<?php

require_once dirname(__DIR__) . '/mvc/models/ParabdService.php';
require_once dirname(__DIR__) . '/inc/util.inc.php';

$failures = 0;
$checks = 0;
function parabdAssert($condition, $message)
{
    global $failures, $checks;
    $checks++;
    if (!$condition) { $failures++; fwrite(STDERR, "ECHEC: $message\n"); }
}

parabdAssert(ParabdService::normalizeText('  L’Épée—des Étoiles! ') === 'l epee des etoiles', 'normalisation du titre');
parabdAssert(ParabdService::normalizeIdentifier('EAN13', '400-638 133393-1') === '4006381333931', 'normalisation EAN');
parabdAssert(ParabdService::normalizeIdentifier('MANUFACTURER_REF', ' ref. AB-12 ') === 'REFAB12', 'normalisation référence fabricant');

parabdAssert(ParabdService::isValidIdentifier('EAN13', '4006381333931'), 'EAN13 valide');
parabdAssert(!ParabdService::isValidIdentifier('EAN13', '4006381333932'), 'EAN13 invalide');
parabdAssert(ParabdService::isValidIdentifier('UPCA', '036000291452'), 'UPC-A valide');
parabdAssert(ParabdService::isValidIdentifier('ISBN10', '0306406152'), 'ISBN10 valide');
parabdAssert(ParabdService::isValidIdentifier('ISBN13', '9780306406157'), 'ISBN13 valide');

$date = ParabdService::parsePartialDate('2026');
parabdAssert($date['date'] === '2026-01-01' && $date['precision'] === 'YEAR', 'date année');
$date = ParabdService::parsePartialDate('2026-08');
parabdAssert($date['date'] === '2026-08-01' && $date['precision'] === 'MONTH', 'date mois');
$date = ParabdService::parsePartialDate('2026-08-09');
parabdAssert($date['precision'] === 'DAY', 'date jour');
try { ParabdService::parsePartialDate('2026-02-30'); parabdAssert(false, 'date invalide refusée'); } catch (ParabdException $expected) { parabdAssert(true, 'date invalide refusée'); }

$candidate = array('TITLE' => 'Statuette Le Chat', 'TYPE_ID' => 1, 'MANUFACTURER' => 'Pixi', 'RELEASE_DATE' => '2025-01-01', 'WIDTH_MM' => 100, 'HEIGHT_MM' => 200, 'DEPTH_MM' => 80);
$input = array('TITLE' => 'Statuette Le Chat', 'TYPE_ID' => 1, 'MANUFACTURER' => 'Pixi', 'RELEASE_DATE' => '2025-01-01', 'WIDTH_MM' => 103, 'HEIGHT_MM' => 198, 'DEPTH_MM' => 80);
$duplicate = ParabdService::duplicateLevel($candidate, $input);
parabdAssert($duplicate && $duplicate['level'] === 'STRONG', 'seuil doublon fort');
$input['TITLE'] = 'Chat'; $input['MANUFACTURER'] = 'Autre'; $input['common_relation'] = true;
$duplicate = ParabdService::duplicateLevel($candidate, $input);
parabdAssert($duplicate && $duplicate['level'] === 'POSSIBLE', 'rattachement commun possible');

$now = strtotime('2026-08-09 12:00:00');
parabdAssert(ParabdService::calculateTrust('2025-08-09 11:59:59', 5, 'NONE', $now), 'contributeur fiable');
parabdAssert(!ParabdService::calculateTrust('2025-08-10 00:00:00', 20, 'NONE', $now), 'ancienneté insuffisante');
parabdAssert(!ParabdService::calculateTrust('2020-01-01', 99, 'REVOKE', $now), 'révocation confiance');
parabdAssert(ParabdService::calculateTrust('2026-08-09', 0, 'GRANT', $now), 'forçage confiance');

$_SESSION = array();
$csrf = parabdCsrfToken('parabd-test');
parabdAssert(strlen($csrf) === 64 && parabdValidateCsrf('parabd-test', $csrf), 'jeton CSRF par périmètre');
parabdAssert(!parabdValidateCsrf('parabd-test', str_repeat('0', 64)), 'jeton CSRF incorrect refusé');

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
    $service = new ParabdService();
    $stored = $service->storeImage(array('tmp_name' => $sourcePath, 'name' => 'source.png', 'size' => filesize($sourcePath)), 123, 1);
    parabdAssert($stored['width'] === 1600 && $stored['height'] === 800, 'redimensionnement côté long');
    parabdAssert(is_file($stored['absolute_path']), 'image réencodée écrite');
    $oriented = imagecreatetruecolor(30, 20); $oriented = ParabdService::orientImage($oriented, 6);
    parabdAssert(imagesx($oriented) === 20 && imagesy($oriented) === 30, 'orientation EXIF'); imagedestroy($oriented);
    try { $service->storeImage(array('tmp_name' => $sourcePath, 'name' => 'mensonge.jpg', 'size' => filesize($sourcePath)), 124, 1); parabdAssert(false, 'extension MIME incohérente refusée'); } catch (ParabdException $expected) { parabdAssert(true, 'extension MIME incohérente refusée'); }
    $largePath = $tmpRoot . '/large.jpg'; file_put_contents($largePath, str_repeat('x', 5242881));
    try { $service->storeImage(array('tmp_name' => $largePath, 'name' => 'large.jpg', 'size' => filesize($largePath)), 125, 1); parabdAssert(false, 'fichier trop gros refusé'); } catch (ParabdException $expected) { parabdAssert(true, 'fichier trop gros refusé'); }
    $hugePath = $tmpRoot . '/huge.png';
    $ihdr = pack('NNCCCCC', 6001, 5000, 8, 2, 0, 0, 0); $chunk = 'IHDR' . $ihdr;
    file_put_contents($hugePath, "\x89PNG\r\n\x1a\n" . pack('N', strlen($ihdr)) . $chunk . pack('H*', hash('crc32b', $chunk)) . pack('N', 0) . 'IEND' . pack('H*', hash('crc32b', 'IEND')));
    try { $service->storeImage(array('tmp_name' => $hugePath, 'name' => 'huge.png', 'size' => filesize($hugePath)), 126, 1); parabdAssert(false, 'image de plus de 30 MP refusée'); } catch (ParabdException $expected) { parabdAssert(true, 'image de plus de 30 MP refusée'); }
    @unlink($stored['absolute_path']); @unlink($sourcePath);
    @unlink($largePath); @unlink($hugePath);
    @rmdir(dirname($stored['absolute_path'])); @rmdir(dirname(dirname($stored['absolute_path']))); @rmdir($tmpRoot);
}

echo $checks . " contrôles, " . $failures . " échec(s).\n";
exit($failures ? 1 : 0);
