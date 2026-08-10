<?php

require_once __DIR__ . '/ParabdException.php';

/** Filesystem and remote-image boundary for Para-BD. */
class ParabdImageStorage
{
    public function store($file, $itemId, $sequence)
    {
        if (!isset($file['tmp_name']) || (!is_uploaded_file($file['tmp_name']) && PHP_SAPI !== 'cli' && empty($file['_parabd_remote']))) throw new ParabdException('VALIDATION_ERROR', 'Fichier uploadé invalide.', array('visual' => 'Upload invalide.'));
        $maxBytes = defined('BDO_PARABD_MAX_UPLOAD_BYTES') ? BDO_PARABD_MAX_UPLOAD_BYTES : 5242880;
        $actualSize = @filesize($file['tmp_name']);
        if ($actualSize === false || $actualSize <= 0 || $actualSize > $maxBytes) throw new ParabdException('VALIDATION_ERROR', 'Le visuel dépasse 5 Mo ou est vide.', array('visual' => '5 Mo maximum.'));
        $info = @getimagesize($file['tmp_name']);
        if (!$info || empty($info[0]) || empty($info[1])) throw new ParabdException('VALIDATION_ERROR', 'Le fichier n’est pas une image valide.', array('visual' => 'Image invalide.'));
        if ($info[0] * $info[1] > (defined('BDO_PARABD_MAX_IMAGE_PIXELS') ? BDO_PARABD_MAX_IMAGE_PIXELS : 30000000)) throw new ParabdException('VALIDATION_ERROR', 'Le visuel dépasse 30 mégapixels.');
        $allowed = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!isset($allowed[$mime]) || $info['mime'] !== $mime) throw new ParabdException('VALIDATION_ERROR', 'Format image non autorisé ou MIME incohérent.');
        $extension = strtolower(pathinfo(isset($file['name']) ? $file['name'] : '', PATHINFO_EXTENSION));
        if ($extension === 'jpeg') $extension = 'jpg';
        if ($extension !== $allowed[$mime]) throw new ParabdException('VALIDATION_ERROR', 'L’extension du fichier ne correspond pas à son contenu.');
        $source = @imagecreatefromstring(file_get_contents($file['tmp_name']));
        if (!$source) throw new ParabdException('VALIDATION_ERROR', 'Impossible de décoder le visuel.');
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($file['tmp_name']);
            $source = self::orient($source, isset($exif['Orientation']) ? intval($exif['Orientation']) : 1);
        }
        $sourceWidth = imagesx($source); $sourceHeight = imagesy($source);
        $ratio = min(1, 1600 / max($sourceWidth, $sourceHeight));
        $width = max(1, intval(round($sourceWidth * $ratio))); $height = max(1, intval(round($sourceHeight * $ratio)));
        $target = imagecreatetruecolor($width, $height);
        if ($mime === 'image/png') {
            imagealphablending($target, false); imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127); imagefilledrectangle($target, 0, 0, $width, $height, $transparent);
        }
        imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);
        $shard = sprintf('%03d', intval($itemId / 1000));
        $folder = rtrim(BDO_DIR_PARABD, DS) . DS . $shard . DS . intval($itemId) . DS;
        if (!is_dir($folder) && !mkdir($folder, 0775, true) && !is_dir($folder)) throw new RuntimeException('Impossible de créer le répertoire Para-BD.');
        $filename = sprintf('PBD-%06d-%02d.%s', $itemId, $sequence, $allowed[$mime]);
        $absolute = $folder . $filename;
        if ($mime === 'image/webp' && !function_exists('imagewebp')) throw new ParabdException('VALIDATION_ERROR', 'Le support WebP est indisponible sur ce serveur.');
        $ok = $mime === 'image/png' ? imagepng($target, $absolute, 6) : ($mime === 'image/webp' ? imagewebp($target, $absolute, 85) : imagejpeg($target, $absolute, 85));
        imagedestroy($source); imagedestroy($target);
        if (!$ok) throw new RuntimeException('Impossible d’enregistrer le visuel Para-BD.');
        return array('absolute_path' => $absolute, 'relative_path' => $shard . '/' . intval($itemId) . '/' . $filename, 'mime' => $mime, 'width' => $width, 'height' => $height);
    }

    public static function orient($image, $orientation)
    {
        if ($orientation === 2 || $orientation === 4 || $orientation === 5 || $orientation === 7) imageflip($image, IMG_FLIP_HORIZONTAL);
        if ($orientation === 3 || $orientation === 4) $image = imagerotate($image, 180, 0);
        elseif ($orientation === 5 || $orientation === 6) $image = imagerotate($image, -90, 0);
        elseif ($orientation === 7 || $orientation === 8) $image = imagerotate($image, 90, 0);
        return $image;
    }

    public static function isPublicRemoteIp($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    private function publicAddressesForHost($host)
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) return self::isPublicRemoteIp($host) ? array($host) : array();
        $addresses = array();
        if (function_exists('dns_get_record')) {
            $records = @dns_get_record($host, DNS_A | DNS_AAAA);
            if (is_array($records)) foreach ($records as $record) {
                if (!empty($record['ip'])) $addresses[] = $record['ip'];
                if (!empty($record['ipv6'])) $addresses[] = $record['ipv6'];
            }
        }
        if (!$addresses) {
            $ipv4 = @gethostbynamel($host);
            if (is_array($ipv4)) $addresses = $ipv4;
        }
        return array_values(array_filter(array_unique($addresses), array(__CLASS__, 'isPublicRemoteIp')));
    }

    private function absoluteRedirectUrl($base, $location)
    {
        if (preg_match('#^https?://#i', $location)) return $location;
        $parts = parse_url($base);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) return '';
        $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . intval($parts['port']) : '');
        if (strpos($location, '//') === 0) return $parts['scheme'] . ':' . $location;
        if (strpos($location, '/') === 0) return $origin . $location;
        $path = isset($parts['path']) ? $parts['path'] : '/';
        return $origin . preg_replace('#/[^/]*$#', '/', $path) . $location;
    }

    public function download($url)
    {
        if (!function_exists('curl_init')) throw new ParabdException('VALIDATION_ERROR', 'L’import d’image par URL est indisponible sur ce serveur.');
        $maxBytes = defined('BDO_PARABD_MAX_UPLOAD_BYTES') ? BDO_PARABD_MAX_UPLOAD_BYTES : 5242880;
        $currentUrl = trim((string) $url);
        for ($redirect = 0; $redirect <= 3; $redirect++) {
            if (strlen($currentUrl) > 1000 || !filter_var($currentUrl, FILTER_VALIDATE_URL)) throw new ParabdException('VALIDATION_ERROR', 'URL d’image invalide.', array('visual_url' => 'URL HTTP(S) valide attendue.'));
            $parts = parse_url($currentUrl);
            $scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
            $host = isset($parts['host']) ? strtolower($parts['host']) : '';
            $port = isset($parts['port']) ? intval($parts['port']) : ($scheme === 'https' ? 443 : 80);
            if (!in_array($scheme, array('http','https'), true) || $host === '' || !empty($parts['user']) || !empty($parts['pass']) || !in_array($port, array(80,443), true)) throw new ParabdException('VALIDATION_ERROR', 'URL d’image invalide ou protocole non autorisé.', array('visual_url' => 'URL HTTP(S) publique attendue.'));
            $addresses = $this->publicAddressesForHost($host);
            if (!$addresses) throw new ParabdException('VALIDATION_ERROR', 'L’adresse de l’image est locale, privée ou introuvable.', array('visual_url' => 'Adresse publique requise.'));

            $body = ''; $location = ''; $tooLarge = false;
            $resolvedAddress = strpos($addresses[0], ':') !== false ? '[' . $addresses[0] . ']' : $addresses[0];
            $curl = curl_init($currentUrl);
            curl_setopt_array($curl, array(
                CURLOPT_FOLLOWLOCATION => false, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_TIMEOUT => 20,
                CURLOPT_USERAGENT => 'BDovore-ParaBD-Image/1.0', CURLOPT_PROXY => '', CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_RESOLVE => array($host . ':' . $port . ':' . $resolvedAddress),
                CURLOPT_WRITEFUNCTION => function ($handle, $chunk) use (&$body, &$tooLarge, $maxBytes) {
                    if (strlen($body) + strlen($chunk) > $maxBytes) { $tooLarge = true; return 0; }
                    $body .= $chunk; return strlen($chunk);
                },
                CURLOPT_HEADERFUNCTION => function ($handle, $header) use (&$location) {
                    if (stripos($header, 'Location:') === 0) $location = trim(substr($header, 9));
                    return strlen($header);
                }
            ));
            $ok = curl_exec($curl); $status = intval(curl_getinfo($curl, CURLINFO_RESPONSE_CODE)); $error = curl_error($curl); curl_close($curl);
            if ($tooLarge) throw new ParabdException('VALIDATION_ERROR', 'L’image distante dépasse 5 Mo.', array('visual_url' => '5 Mo maximum.'));
            if ($status >= 300 && $status < 400 && $location !== '') { $currentUrl = $this->absoluteRedirectUrl($currentUrl, $location); continue; }
            if (!$ok || $status < 200 || $status >= 300 || $body === '') throw new ParabdException('VALIDATION_ERROR', 'Impossible de télécharger l’image distante' . ($error ? ' : ' . $error : '.'), array('visual_url' => 'Téléchargement impossible.'));

            $tmp = tempnam(sys_get_temp_dir(), 'parabd-url-');
            if (!$tmp || file_put_contents($tmp, $body) === false) throw new RuntimeException('Impossible de préparer l’image distante.');
            $finfo = new finfo(FILEINFO_MIME_TYPE); $mime = $finfo->file($tmp);
            $extensions = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
            if (!isset($extensions[$mime])) { @unlink($tmp); throw new ParabdException('VALIDATION_ERROR', 'L’URL ne désigne pas une image JPEG, PNG ou WebP.'); }
            return array('tmp_name' => $tmp, 'name' => 'image-distante.' . $extensions[$mime], 'size' => strlen($body), 'error' => UPLOAD_ERR_OK, '_parabd_remote' => true);
        }
        throw new ParabdException('VALIDATION_ERROR', 'L’image distante effectue trop de redirections.');
    }
}

