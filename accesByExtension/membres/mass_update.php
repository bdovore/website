<?php



include (BDO_DIR."inc/function.html.inc.php");
/*
var_dump_pre($_GET);
var_dump_pre($_POST);
*/
minAccessLevel(2);

// Variables générales
$nb = 20;
if ($first=='') {$first = 0;}

// Tableau de cles
$clerep[1] = "user_album.genre";
$clerep[2] = "user_album.serie";
$clerep[3] = "user_album.titre";

// tableaux de filtre
$filtre[1] = " AND users_album.flg_achat = 'O'";
$filtre[3] = " AND users_album.flg_pret = 'O'";
$filtre[2] = " AND users_comment.note IS NULL";
$filtre[4] = " AND users_comment.comment IS NULL";

// Tableau d'options
// Notes
$opt_notes[0][0] = "";
$opt_notes[0][1] = "";
for ($i = 1; $i <= 10; $i++)
{
    $opt_notes[$i][0] = $i;
    $opt_notes[$i][1] = $i;
}

// Tableaux $paramètres
// Intitulé,table contenant les données,Champ de la table correspondant,Presentation des données,valeurs dispo
$param = array(
array("Remarques","users_album","comment","textarea"),
array("Edition Originale","users_album","flg_tete","checkbox"),
array("Dedicace","users_album","flg_dedicace","checkbox"),
array("Prêts","users_album","flg_pret","checkbox"),
array("Emprunteur","users_album","nom_pret","textbox"),
array("Email Emprunteur","users_album","email_pret","textbox"),
array("Futur Achats","users_album","flg_achat","checkbox"),
array("Date Achat","users_album","date_achat","textbox"),
array("Prix Achat","users_album","cote","textbox"),
array("Cadeau","users_album","flg_cadeau","checkbox")
);

// Tableaux d'options
// Champs disponibles
$opt_champs[0][0] = 99;
$opt_champs[0][1] = "Aucun";
for ($nindex = 0; $nindex < sizeof($param); $nindex++)
{
    $opt_champs[$nindex+1][0] = $nindex;
    $opt_champs[$nindex+1][1] = $param[$nindex][0];
}

if (($act == "del") and isset($_GET["id_del"])) {
    // on supprime l'album de la collection
    $select = "delete from users_album where user_id=".$DB->escape($_SESSION["UserId"])." and id_edition =".$DB->escape($id_del);
    $DB->query($select);
}

if ($act == 'update')
{
    $i=0;
    while (isset($_POST["txtKey".$i]))
    {
        $myarray = "Values".$i;
        $cle = $_POST["txtKey".$i];
        $table = $param[$cle][1];
        for ($j=0; $j < count($idedition); $j++)
        {
            switch ($param[$cle][3])
            {
                case "textbox" :
                case "textarea" :
                case "listbox" :
                    $query = "UPDATE `".$param[$cle][1]."` SET `".$param[$cle][2]."` = ".sqlise(${$myarray}[$idedition[$j]],'text')."
                WHERE `id_edition` = ".$DB->escape($idedition[$j])." AND `user_id` =".$DB->escape($_SESSION["UserId"]);
                    break;
                case "checkbox" :
                    $query = "UPDATE `".$param[$cle][1]."` SET `".$param[$cle][2]."` = ".sqlise((${$myarray}[$idedition[$j]]=='checkbox' ? 'O' : 'N'),'text')."
                WHERE `id_edition` = ".$DB->escape($idedition[$j])." AND `user_id`=".$DB->escape($_SESSION["UserId"]);
                    break;
            }
            $DB->query ($query);
        }
        $i++;
    }
}

// vérifie si la variable display a été passée
$query_fields = "";
$query_filtre = "";

if (isset($display))
{
    // Structure de la variale display
    // 001122334455abcd
    // chifre : clés
    // lettre : filtre
    $nb_cles = 0;
    $cle = array();
    for ($i=0; $i <= 4; $i++)
    {
        $display_value = intval(substr($display,$i*2,2));
        if ($display_value != '99')
        {
            $cle[$i] =  $display_value;
            $nb_cles++;
            $query_fields .= ", ".$param[$cle[$i]][1].".".$param[$cle[$i]][2];
        }
    }
    // partie filtre
    for ($i=1; $i <= 4; $i++)
    {
        if (substr($display,9+$i,1) == 'V')
        $query_filtre .= $filtre[$i];
    }
}else{
    // récupère les clés qui ont été selectionnées
    $j = 0;
    $display = "";
    for ($i=0; $i<= 4; $i++)
    {
        $flg_new = true;

        for ($k=0; $k<=$j; $k++)
        {
            if (($_POST["lstCle".$i] == $cle[$k]) | ($_POST["lstCle".$i] == 99)) $flg_new = false;
        }
        if ($flg_new == true)
        {
            $cle[$j] = $_POST["lstCle".$i];
            if (strlen($cle[$j]) < 2)
            $display .= '0'.$cle[$j];
            else
            $display .= $cle[$j];
            $query_fields .= ", ".$param[$_POST["lstCle".$i]][1].".".$param[$_POST["lstCle".$i]][2];
            $j++;
        }
    }
    $nb_cles = $j;
    $display .= str_repeat('99',(5-$nb_cles));

    // Constitution du filtre
    $query_filtre ='';
    for ($i=1; $i <= 4; $i++)
    {
        if ($_POST["chkFiltre".$i] != "")
        {
            $query_filtre .= $filtre[$i];
            $display .= "V";
        }else{
            $display .= "W";
        }
    }
}

// filtre de recherche supplémentaire
$query_string = "";
if ($str_search) {

    $query_string.= "&str_search=".$str_search;
    if ($cb_album) {
        $query_filtre.=" AND LCASE(bd_tome.titre) like LCASE('%".$DB->escape($str_search)."%')";
        $checkalb = "checked";
        $query_string.="&cb_album=1";
    }
    if ($cb_serie) {
        $query_filtre.=" AND LCASE(bd_serie.nom) like LCASE('%".$DB->escape($str_search)."%')";
        $checkser = "checked";
        $query_string.="&cb_serie=1";
    }
    if ($cb_aut) {
        $query_filtre.=" AND (LCASE(dessinateur.pseudo) like LCASE('%".$DB->escape($str_search)."%') OR
                                        LCASE(scenar.pseudo) like LCASE('%".$DB->escape($str_search)."%'))";
        $checkaut = "checked";
        $query_string.="&cb_aut=1";

    }
}
else {
    $checkalb = "checked";
}

// Constitue la base d'albums à mettre à jours
$query = "
SELECT SQL_CALC_FOUND_ROWS
    bd_tome.id_tome,
    bd_edition.id_edition,
    bd_serie.id_serie,
    bd_tome.titre,
    bd_tome.num_tome,
    bd_serie.nom
    ".$query_fields."
FROM
    users_album
    INNER JOIN bd_edition ON users_album.id_edition=bd_edition.id_edition
    INNER JOIN bd_tome ON bd_edition.id_tome=bd_tome.id_tome
    INNER JOIN bd_serie ON bd_tome.id_serie=bd_serie.id_serie
    INNER JOIN bd_auteur scenar ON bd_tome.id_scenar=scenar.id_auteur
    INNER JOIN bd_auteur dessinateur ON bd_tome.id_dessin=dessinateur.id_auteur
    LEFT JOIN users_comment ON ((bd_tome.id_tome = users_comment.id_tome) AND (users_album.user_id = users_comment.user_id))
WHERE
    users_album.user_id = ".$DB->escape($_SESSION["UserId"])."
    ".$query_filtre."
ORDER BY nom, num_tome, titre
LIMIT ".($first).", ".$nb."
";
$DB->query ($query);

$resCount = mysql_query('SELECT FOUND_ROWS() as nb');
$rowCount = mysql_fetch_assoc($resCount);
$nb_alb = $rowCount['nb'];

// Creation d'une nouvelle instance Template
$t = new Template(BDO_DIR."public/templates");
// fichier à utiliser
$t->set_file(array(
"tpBody" => "user_mass_update.tpl",
"tpMenuColl" => "menu_coll.tpl",
"tpMenu" => "user.menu.tpl",
"tpBase" => "body.tpl"));
$t->set_var(array(
"ALBCHECK" => $checkalb,
"SERCHECK" => $checkser,
"AUTCHECK" => $checkaut,
"STRSEARCH" =>$str_search
));
// Affiche les titre
// on déclare le block à utiliser
$t->set_block('tpBody','TitleBlock','TBlock');
for ($i=0; $i < $nb_cles; $i++)
{
    $t->set_var(array(
    "TITRE" => $param[$cle[$i]][0],
    "KEYNAME" => "txtKey".$i,
    "KEYVALUE" => $cle[$i]
    ));

    //Affiche le block
    $t->parse ("TBlock", "TitleBlock",true);
}

// URL du form & delete

$urlform = BDO_URL."membres/mass_update.php?act=update&display=".$display."&first=".$first."&nb=".$nb.$query_string;
$urldelete = BDO_URL."membres/mass_update.php?act=del&display=".$display."&first=".$first."&nb=".$nb.$query_string;


// Affiche les données fixes
// on déclare le block à utiliser
$t->set_block("tpBody", "ColumnBlock", "CBlock");
$t->set_block("tpBody", "RowBlock", "RBlock");

while ($DB->next_record())
{   //Affiche les colonnes fixes
    $t->set_var (array(
    "IDTOME" => $DB->f ("id_tome"),
    "IDEDITION" => $DB->f ("id_edition"),
    "ALBSERIE" => stripslashes($DB->f ("nom")),
    "URLSERIE" => BDO_URL."membres/userserie.php?id_serie=".$DB->f ("id_serie"),
    "ALBTOME" => $DB->f ("num_tome"),
    "ALBTITRE" => stripslashes($DB->f ("titre")),
    //"URLTOME" => BDO_URL."membres/useralbum.php?id_tome=".$DB->f ("id_tome")."&id_edition=".$DB->f ("id_edition"),
    "URLTOME" => BDO_URL."membres/useralbum.php?id_edition=".$DB->f ("id_edition"),
    "URLDELETE" => $urldelete."&id_del=".$DB->f ("id_edition")
    ));

    // Affiche les colonnes variables
    $t->set_var('CBlock', '');
    for ($i=0; $i < $nb_cles; $i++)
    {
        // Suivant le type de variable a afficher
        switch($param[$cle[$i]][3])
        {
            case 'textbox' :
                $t->set_var("CHAMP",htmltag_textbox("Values".$i."[".$DB->f ("id_edition")."]", 8, stripslashes($DB->f ($param[$cle[$i]][2]))));
                break;
            case 'textarea' :
                $t->set_var("CHAMP",htmltag_textarea("Values".$i."[".$DB->f ("id_edition")."]", 20, 3, stripslashes($DB->f ($param[$cle[$i]][2]))));
                break;
            case 'checkbox' :
                $t->set_var("CHAMP",htmltag_checkbox("Values".$i."[".$DB->f ("id_edition")."]", 'checkbox', (($DB->f ($param[$cle[$i]][2]))=='O')));
                break;
            case 'listbox' :
                switch($param[$cle[$i]][4])
                {
                    case 'notes' :
                        $t->set_var("CHAMP",htmltag_listbox("Values".$i."[".$DB->f ("id_edition")."]", $opt_notes, ($DB->f ($param[$cle[$i]][2]))));
                        break;
                    default      :
                        $t->set_var("CHAMP",$DB->f ($param[$cle[$i]][2]));
                        break;
                }
                break;
            default :
                $t->set_var("CHAMP",$DB->f ($param[$cle[$i]][2]));
                break;
        }
        $t->parse("CBlock", "ColumnBlock", true);
    }
    $t->parse("RBlock", "RowBlock", true);
}


// Rempli le formulaire de filtre
for ($i=0; $i <= 4; $i++)
{
    if (!isset($cle[$i]))
    $t->set_var("OPTIONCLE".$i, GetOptionValue($opt_champs,99));
    else
    $t->set_var("OPTIONCLE".$i, GetOptionValue($opt_champs,$cle[$i]));
}

for ($i=1; $i <= 4; $i++)
{
    if (substr($display,9+$i,1) == 'V')
    $t->set_var("ISCHECKED".$i, "checked");
}

// Converti les variable generales
$t->set_var (array
("BARRENAVIGATION" => GetNavigationBar($first,$nb,$nb_alb,BDO_URL."membres/mass_update.php?display=".$display.$query_string),
"URLFORM" => $urlform
));

// assigne la barre de login
$t->set_var (array
("LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
"URLSITEIMAGE" => BDO_URL_IMAGE,
"URLSITEFORUM" => BDO_URL_FORUM,
"PAGETITLE" => "Mes Albums : formulaire"));
$t->parse("MENUCOLL","tpMenuColl");
$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->pparse("MyFinalOutput","tpBase");
