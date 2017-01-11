<?php


minAccessLevel(1);

ob_start();


set_time_limit(360);

mysql_connect(BDO_DB_HOST, BDO_DB_USER, BDO_DB_PWD);
mysql_select_db(BDO_DB_SID);

include_once (BDO_DIR . "inc/relation.inc.php");

echo '<h2>Bilan des relations par référenceur</h2>';

if (!isset($_POST['tableOrigine'])) $_POST['tableOrigine'] = array();

$a_td = array();
foreach( $a_liaison as $a_lien)
{
	if (!in_array($a_lien['td'],$a_td)) $a_td[] = $a_lien['td'];
}

echo '<form name="formrelation" method="post">
<div>
Table référenceur :
<select name="tableOrigine[]" >';
foreach( $a_td as $_td)
{
	$selected = (in_array($_td,$_POST['tableOrigine'])) ? 'SELECTED' : '';
	echo '<option value="'.$_td.'" '.$selected.' >'.$_td.'</option>';
}
echo '</select>
<br /><label for="onlyDiff"><input type="checkbox" id="onlyDiff" name="onlyDiff" value="checked" '.$_POST['onlyDiff'].'> voir seulement les écarts</label>
<br /><label for="viewQuery"><input type="checkbox" id="viewQuery" name="viewQuery" value="checked" '.$_POST['viewQuery'].'> voir les requêtes</label>
<br /><input type="submit" name="execformrelation" value="Tester les relations">
</div>
</form>';


if (isset($_POST['execformrelation']))
{

	foreach( $a_liaison as $a_lien)
	{
		if (in_array($a_lien['td'],$_POST['tableOrigine']))
		{
			$to = $a_lien['to'];
			$td = $a_lien['td'];
			$co = $a_lien['co'];
			$cd = $a_lien['cd'];


			$query = "SELECT distinct(`".$cd."`) FROM `".$td."`
	    	WHERE `".$cd."` NOT IN ( SELECT `".$co."` FROM `".$to."` )
	    	AND `".$cd."` IS NOT NULL";
			/*
	    	AND `".$cd."`!= ''
	    	AND `".$cd."`!= 0";
*/
			$result = mysql_query($query);
			$nbr = mysql_num_rows($result);


			if(!isset($_POST['onlyDiff']) or (isset($_POST['onlyDiff']) and (0 < $nbr)))
			{

				if (0 < $nbr)
				{
					$a_obj = array();
					While ($obj = mysql_fetch_object($result))
					{
						$a_obj[] = $obj;
					}
				}


				echo  "<hr>Valeur de la colonne [".$cd."] table [<font color=blue><b>".$td."</b></font>]
            <br />inconnues dans la table de référence [<font color=blue><b>".$to."</b></font>] colonne [".$co."]";
				if (0 < $nbr)
				{
					echo  "<br /><font color=red><b>nbr = " . $nbr ."</b></font><br />";

					echo '<table border=1><tr><td colspan=10 class="entete_admin">'.$cd.'</td></tr><tr>';
					$k = 0;
					foreach($a_obj as $obj){
						if (!($k++%10)) echo '</tr><tr>';
						echo '<td class="data_admin">'.$obj->$cd.'</td>';
					}
					echo '</tr></table>';

				}
				else
				{
					echo  "<br /><font color=gray><b>nbr = " . $nbr ."</b></font><br />";
				}
				if(isset($_POST['viewQuery']) and !empty($_POST['viewQuery']))
				{
					echo "<font size=2>" . $query . "</font>";
				}
			}


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