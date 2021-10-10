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
}
