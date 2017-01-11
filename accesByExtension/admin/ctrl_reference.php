<?php


minAccessLevel(1);

ob_start();

set_time_limit(360);

mysql_connect(BDO_DB_HOST, BDO_DB_USER, BDO_DB_PWD);
mysql_select_db(BDO_DB_SID);


include_once (BDO_DIR . "inc/relation.inc.php");

$a_column = array();
$a_onlyCol = array();
$a_liaison2 = array();
foreach( $a_liaison as $liaison)
{
    if (!in_array($liaison['co'], $a_onlyCol)) $a_onlyCol[] = $liaison['co'];
    if (!in_array($liaison['cd'], $a_onlyCol)) $a_onlyCol[] = $liaison['cd'];
    $a_column[$liaison['co']] = $liaison['to'];
    $a_liaison2[$liaison['to']][$liaison['co']][] = array('td'=>$liaison['td'],'cd'=>$liaison['cd']);
}
/*
array('td'=>'users_list_carre','to'=>'bd_tome','cd'=>'id_tome','co'=>'ID_TOME'),
array('td'=>'users_alb_prop','to'=>'bd_tome','cd'=>'ID_TOME','co'=>'ID_TOME'),
*/


$a_onlyCol[] = 'ID_PROPOSAL';
$a_onlyCol[] = 'ISBN';
$a_onlyCol[] = 'EAN';
$a_onlyCol[] = 'TITRE';
/*
$query = "SELECT TABLE_NAME,COLUMN_NAME FROM db_column";
$resultat = mysql_query($query);
$a_table = array();
$a_column = array();
$a_tableByColumn = array();
while ($obj = mysql_fetch_object($resultat)) {
if (!in_array($obj->TABLE_NAME, $a_table)) $a_table[] = $obj->TABLE_NAME;
if (!in_array($obj->COLUMN_NAME, $a_column)) $a_column[] = $obj->COLUMN_NAME;
$a_tableByColumn[$obj->COLUMN_NAME][] = $obj->TABLE_NAME;
}
*/


echo '<h2>Recherche id de référence</h2>';


echo '<form name="formvalue" method="post">
<div>
Référence :
<select name="column_name">';
foreach( $a_column as $column_name=>$table_name)
{
    $selected = ($column_name == $_POST['column_name']) ? 'SELECTED' : '';
    echo '<option value="'.$column_name.'" '.$selected.' >'.$table_name.'.'.$column_name.'</option>';
}
echo '</select> = \' <input type="text" name="valeur" value="'.$_POST['valeur'].'"> \'
<br /><label for="viewQuery"><input type="checkbox" id="viewQuery" name="viewQuery" value="checked" '.$_POST['viewQuery'].'> voir les requêtes</label>
<br /><input type="submit" name="execformvalue" value="Chercher">
</div>
</form>';


if (isset($_POST['execformvalue']) and isset($_POST['valeur']))
{
    $co = $_POST['column_name'];

    $to = $a_column[$_POST['column_name']];

    $query = "SELECT * FROM `".$to."` WHERE `".$co."`= '".$_POST['valeur']."'";

    $resultat = mysql_query($query);
    $a_obj = array();
    while ($obj = mysql_fetch_object($resultat)) {
        $a_obj[] = $obj;
    }
    $nbr = mysql_num_rows($resultat);

    if ($nbr > 0)
    {
        echo '<h3>Recherche dans la table <i>'.$to.'</i> colonne <i>'.$co.'</i></h3>';
        echo $nbr . ' lignes.';
        tableOfFetchObj($a_obj, $a_onlyCol);
        if(isset($_POST['viewQuery']) and !empty($_POST['viewQuery']))
        {
            echo "<font size=2>" . $query . "</font>";
        }
        echo '<hr>';
    }
    foreach( $a_liaison2[$to][$co] as $a_d)
    {

        $query = "SELECT * FROM `".$a_d['td']."` WHERE `".$a_d['cd']."`= '".$_POST['valeur']."'";
        $resultat = mysql_query($query);
        $a_obj = array();
        while ($obj = mysql_fetch_object($resultat)) {
            $a_obj[] = $obj;
        }
        $nbr = mysql_num_rows($resultat);

        if ($nbr > 0)
        {
            echo '<h3>Recherche dans la table <i>'.$a_d['td'].'</i> colonne <i>'.$a_d['cd'].'</i></h3>';
            echo $nbr . ' lignes.';
            tableOfFetchObj($a_obj,$a_onlyCol);
            if(isset($_POST['viewQuery']) and !empty($_POST['viewQuery']))
            {
                echo "<font size=2>" . $query . "</font>";
            }
            echo '<hr>';
        }
    }

}


























// dossier contenant les templates
$t = new Template(BDO_DIR."public/templates");
// fichier à utiliser
$t->set_file(array(
"tpForm_Recherche" => "form_recherche.tpl",
"tpControl" => "control.tpl",
"tpBody" => "body.tpl"
));

$t->set_var("PAGE_OB",ob_get_clean());

$t->set_var("MENUBARRE",admin_menu());
$t->set_var("LOGINBARRE",GetIdentificationBar());
$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);

$t->parse("BODY","tpControl");
$t->parse("FORM_RECHERCHE","tpForm_Recherche");
$t->pparse("MyFinalOutput","tpBody");