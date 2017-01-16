<?php
if (isset($_GET['i'])) {
    phpinfo();
}
else {
    $_GET['error'] = '404';
    include 'erreur.php';
}