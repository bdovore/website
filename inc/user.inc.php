<?php
function openCollection($user_id) {
    global $DB;

    $query = "select username,open_collec from users where user_id=" . $DB->escape ( $user_id );
    $DB->query ( $query );
    $DB->next_record ();
    if ($DB->f ( "open_collec" ) != 'Y') {
        echo GetMetaTag ( 3, "Vous n'avez pas accès à la collection de cet utilisateur : vous allez être redirigé.", (BDO_URL . "index.php") );
        exit ();
    }
    return $DB->f ( "username" );
}
