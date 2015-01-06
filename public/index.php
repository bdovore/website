<?php

include_once ('..' . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'conf.inc.php');

if (stristr(BDO_DIR_REDIRECT, '.php')) {
    //Ancien système (non-MVC)
    if (file_exists(BDO_DIR_REDIRECT)) {
        include (BDO_DIR . 'inc' . DS . 'template.php');
        include (BDO_DIR_REDIRECT);
    }
    else {
        $_GET['error'] = '404';
        include 'erreur.php';
    }
}
else {
    //MVC
    include_once (BDO_DIR . 'config' . DS . 'bootstrap.php');
}
