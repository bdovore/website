<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Images extends Bdo_Controller {
    
    public function Couv () {
        $img = getVal("source", "default.png");
        
        $file = BDO_DIR_COUV.$img;
        if (file_exists($file)) {
            $image = new Imagick($file);

        } else {
            $image_url = BDO_URL_COUV.$img;
            $imageBlob = file_get_contents($image_url);
 
            $image = new Imagick();
            $image->readImageBlob($imageBlob); 

        }
       header("Content-Type: image/jpeg");
       $image->blurImage(0,10);
       echo $image;  
    }

    public function Parabd () {
        $source = str_replace('\\', '/', getVal('source', ''));
        if (!preg_match('#^\d{3}/\d+/PBD-\d{6}-\d{2}\.(?:jpg|png|webp)$#i', $source)) {
            http_response_code(404);
            return;
        }

        $root = realpath(BDO_DIR_PARABD);
        $file = realpath(rtrim(BDO_DIR_PARABD, DS) . DS . str_replace('/', DS, $source));
        if (!$root || !$file || strpos($file, $root . DS) !== 0 || !is_file($file)) {
            http_response_code(404);
            return;
        }

        $info = @getimagesize($file);
        $mime = $info && isset($info['mime']) ? $info['mime'] : '';
        if (!in_array($mime, array('image/jpeg', 'image/png', 'image/webp'), true)) {
            http_response_code(415);
            return;
        }
        $image = @imagecreatefromstring(file_get_contents($file));
        if (!$image) {
            http_response_code(415);
            return;
        }
        header('Content-Type: ' . $mime);
        header('Cache-Control: private, max-age=3600');
        for ($pass = 0; $pass < 14; $pass++) imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
        if ($mime === 'image/png') imagepng($image, null, 6);
        elseif ($mime === 'image/webp' && function_exists('imagewebp')) imagewebp($image, null, 82);
        else imagejpeg($image, null, 85);
        imagedestroy($image);
    }
}
