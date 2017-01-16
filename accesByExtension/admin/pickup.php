<?php



minAccessLevel(5);

// Déclare le template à utiliser
$t = new Template(BDO_DIR."public/templates");
// fichier à utiliser
$t->set_file("tpBody","pickup.tpl");
// Block à utiliser
$t->set_block('tpBody','ResultBlock','RBlock');

//détermine la query à utiliser
switch ($cat){
    case "serie":   $query = "select id_serie as myID, nom as myName from bd_serie where nom like '%".$DB->escape($text)."%' or nom like '".$DB->escape($text)."' order by nom";
        break;
    case "genre":   $query = "select id_genre as myID, libelle as myName from bd_genre where libelle like '%".$DB->escape($text)."%' order by libelle";
        break;
    case "auteur":  $query = "select id_auteur as myID, pseudo as myName from bd_auteur where pseudo like '%".$DB->escape($text)."%' order by pseudo";
        break;
    case "editeur": $query = "select id_editeur as myID, nom as myName from bd_editeur where nom like '%".$DB->escape($text)."%' order by nom";
        break;
    case "collection":  $query = "select id_collection as myID, nom as myName from bd_collection where nom like '%".$DB->escape($text)."%' and id_editeur =".$DB->escape($IdEditeur)." order by nom";
        break;
    case "album":   $query = "select id_tome as myID, titre as myName from bd_tome where titre like '%".$DB->escape($text)."%' or titre like '".$DB->escape($text)."' order by titre";
        break;
    case "allcollec":   $query = "select id_collection as myID, nom as myName from bd_collection where nom like '%".$DB->escape($text)."%' order by nom";
        break;
}
// lance la query
$DB->query ($query);
//liste les users
while ($DB->next_record()){
    $t->set_var (array(
        "ID" => $DB->f("myID"),
        "FULLNAME" => htmlentities($DB->f("myName")),
        "RETURNURL" => htmlentities("javascript:changeSource('$ReturnDoc','$ReturnElement','".addslashes($DB->f("myName"))."','".$DB->f("myID")."')")
    ));
    $t->parse ("RBlock", "ResultBlock",true);
}

$t->set_var ("DOCTITRE","Choisir l'album");

// assigne la barre de login
$t->set_var (array("LOGINBARRE" => GetIdentificationBar()));
$t->pparse("BODY","tpBody");
