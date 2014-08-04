<?php


function AffDecalageOption($decalage)
{
	$decaltext = '';
	for ($i=0;$i<$decalage;$i++)
	{
		$decaltext .= '&nbsp;';
	}
	return $decaltext;
}

function makeUrl($url,$title=false)
{
	if ($title)
	{
		$a = htmlPrepaTexte($title);
	}
	else
	{
		$a = "\\0";
	}
	$url = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a target=_new href=\"\\0\">".$a."</a>",$url);
	return '<img src="'.CFG_URL_ROOT.'image/fleche_lien.gif" border=0>'.$url;
}

function mailtoUser($mail)
{
	if (is_array($mail))
	{
		$mail = implode(';',$mail);
	}
	return "<a href='mailto:".$mail."' title='".LANG_CONTACTBYMAIL."'><img src='".CFG_URL_ROOT."image/b_mail.gif' border=0 align=absmiddle></a>";
}

function CommunicatorToUser($mail)
{
	if (stristr($_SERVER['HTTP_USER_AGENT'],'MSIE'))
	{
		return "
		<span>
		<img border='0' src='http://one-directory.sso.francetelecom.fr/annuaire/pages/ima/communicator/imnunk.gif'
		onload=\"javascript:SPEEDIMNRC('".$mail."')\" id='imn8'	alt=''>
		</span>
		";
	}
	else
	{
		return false;
	}
}

function ficheIntrannuaire($mail)
{
	return "<A HREF='".CFG_LIEN_INTRANNUAIRE.$mail."' target=_new title='".LANG_FICHEINTRANNUAIRE."'><img src='".CFG_URL_ROOT."image/ico_fiche_intrannuaire.gif' border=0 align=absmiddle></a>";
}

function espace ($height=5,$noAff=false)
{
	$espace = "
	<table width='100%' border='0' cellspacing='0' cellpadding='0'>
		<tr>
			<td width='10'><img src='".CFG_URL_ROOT."image/vide1x1.gif' width='1' height='".$height."' border=0></td>
		</tr>
	</table>";

	if (!$noAff)
	{
		echo $espace;
		return true;
	}
	else
	{
		return $espace;
	}
}

function BoutonTd ($type, $name, $value, $action, $class, $menudroit=true)
{

	echo
	EspaceTD()
	."<td align=center>".
	Bouton ($type, $name, $value, $action, $class, $menudroit)
	."</td>"
	;
	return false;
}

function Bouton ($type, $name, $value, $action, $class, $menudroit=true)
{
	global $tab_button_clic_droit;

	//if ((CFG_READONLY == TRUE) and ($class == 'action_modification')) return '';

	// ---------------------------------------------------
	// préparation des boutons du clic droit
	if (($type == "button") and $menudroit)
	{
		$tab_button_clic_droit[] = array($value, $action);
	}
	// ---------------------------------------------------

	switch ($type)
	{
		case "submit" :
		case "reset" :
		case "button" :
			{
				$echo_aff = "<INPUT type=$type name='".$name."' value='".htmlPrepaTexte($value)."' OnClick=\"".$action."\"
				 onMouseOver='this.className=\"".$class."_on\"'
				 onMouseOut='this.className=\"".$class."_off\"'
				 class='".$class."_off'>";
				break;
			}
		case "table" :
			{
				$echo_aff = "
			<TABLE border='0' cellspacing='0' cellpadding='0'>
				<TR>
				<TD nowrap align=center onMouseOver='this.className=\"".$class."_on\"'
				 onMouseOut='this.className=\"".$class."_off\"'
				OnClick=\"".$action."\" class='".$class."_off'>".$value."</TD>
				</TR>
			</TABLE>
			";
				break;
			}	}
			return $echo_aff;
}


function LigneTitre ($class, $titre, $picto, $height=15)
{
	$height = 2;

	if (is_array($titre))
	{
		$surTitre = $titre[0];
		$titre = $titre[1];
	}

	if (issetNotEmpty($surTitre))
	{
		echo '<div class=cfg_small align=center>'.$surTitre.' </div>';
	}
	echo "
	<table align=center border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td align=center width=50%>".
	CadreVide ('navigation', "100%", $height)
	."</td>".
	EspaceTD()
	."
		<td align=center>
			<table align=center border=0 cellpadding=0 cellspacing=0 class='".$class."_titre'>
			<tr>
				".
	EspaceTD()
	."<td align=center nowrap>$titre
				&nbsp;</td>
			</tr>
			</table>
		</td>".EspaceTD();

	echo
	"<td align=center width=50%>".
	CadreVide ('null', "100%", $height)
	."</td>
	</tr>
	</table>
	";
}




function LigneTitre3 ($titre,$nomDiv="")
{
	espace();
	echo "
	<table ". ($nomDiv ? " style='cursor:pointer;' onclick=\"Block_None_Div('".$nomDiv."')\"" : '') . " align=center border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>";
	echo "
		<td>
		<table align=center border=0 cellpadding=0 cellspacing=0 class='cfg_titre'>
		<tr>
			<td align=center nowrap>&nbsp;
			$titre
			&nbsp;</td>
		</tr>
		</table>
		</td>
		";
	echo "
		<td align=center width=100%>".
	CadreVide ('cfg', "100%",13)."
		</td>
		";
	echo "	</tr>
	</table>
	";
}

function CadreVide ($class, $width, $height=15)
{
	if (strpos($width,"%")) $width_img = 1;
	else $width_img = $width;

	$class = "navigation";
	if ($class != "null") $class = "class='".$class."'";

	$echo_aff = "
	<table width=$width border=0 cellspacing=0 cellpadding=0 ".$class.">
		<tr>
		<td nowrap><img src='".CFG_URL_ROOT."image/vide1x1.gif' width=$width_img height=$height border=0></td>
	</tr>
	</table>
	";
	return $echo_aff;
}

function EspaceTD ($width=5)
{

	$echo_aff = "
	<td nowrap><img src='".CFG_URL_ROOT."image/vide1x1.gif' width=$width border=0></td>
	";
	return $echo_aff;
}

function EspaceTR ($width=5,$colspan=1)
{

	$echo_aff = "
	<tr>
	<td colspan=$colspan nowrap><img src='".CFG_URL_ROOT."image/vide1x1.gif' width=$width border=0></td>
	</tr>
	";
	return $echo_aff;
}


function TitreTR($titre,$typeTitre)
{


	switch ($typeTitre)
	{
		case 1 :
			echo EspaceTR(5,2)."
			<tr>
			<td class='cfg1' align=right width=".CFG_TD_WIDTH." nowrap><font size=2><b>".$titre."&nbsp;<b></font></td>
			<td width=100%>&nbsp;</td>
			</tr>\n";
			break;
		case 2 :
			echo "
			<tr>
			<td class='cfg0' align=right width=".CFG_TD_WIDTH." nowrap><font size=1><b>".$titre."&nbsp;<b></font></td>
			<td width=100%>&nbsp;</td>
			</tr>\n";
			break;
		case 3 :
			echo "
			<tr>
			<td>&nbsp;</td>
			<td><font size=1>&nbsp;".$titre."</font></td>
			</tr>\n";
			break;

		case 4 :
			echo "
			<tr>
			<td>&nbsp;</td>
			<td><font class='info_red'>&nbsp;".$titre."</font></td>
			</tr>\n";
			break;


	}
}

function htmlLineTitleTable($a_langField,$col_tri,$sens_tri,$formName='affichage')
{
	$txt = "<tr>\n";
	foreach ($a_langField as $champ)
	{
		$nom_champ = $champ->COLUMN_NAME;

		$asc_img = "off";
		$desc_img = "off";

		if (issetNotEmpty($champ->ORG_COLUMN_NAME))
		$col_table_champ_tri = $champ->ORG_COLUMN_NAME;
		else
		$col_table_champ_tri = $champ->COLUMN_NAME;

		if (isset($champ->TABLE_NAME) and !empty($champ->TABLE_NAME))
		$col_table_champ_tri = $champ->TABLE_NAME.".".$col_table_champ_tri;

		if ($col_tri == $col_table_champ_tri)
		{
			if ($sens_tri == "DESC") $asc_img = "on";
			if ($sens_tri == "ASC") $desc_img = "on";
		}

		$txt .= "
		<td class='cfgt' valign=top>
	       	<table border=0 width=100%>
	       	<tr>
		       	<td class='cfgt'>
		       	 $champ->TITRE_CHAMP</td>
		    	<td valign=top align=right>
			    	<table class=navigation border=0 cellspacing=0 cellpadding=2>
			    	<tr height=50%><td><a href='javascript:ChangeTriCol(\"".$col_table_champ_tri."\",\"DESC\",document.".$formName.")'><img src='".CFG_URL_ROOT."image/tri_asc_".$asc_img.".gif' border=0 title='".LANG_TRIDESC." $champ->LIB_CHAMP'></a></td></tr>
			    	<tr height=50%><td><a href='javascript:ChangeTriCol(\"".$col_table_champ_tri."\",\"ASC\",document.".$formName.")'><img src='".CFG_URL_ROOT."image/tri_desc_".$desc_img.".gif' border=0 title='".LANG_TRIASC." $champ->LIB_CHAMP'></a></td></tr>
			    	</table>
		    	</td>
	    	</tr>
	    	</table>
		</td>\n";
	}
	$txt .= "</tr>\n";

	return $txt;

}

function htmlLineTitleTable2($dbSearch)
{
	$txt = "<tr>\n";
	foreach ($dbSearch->a_langField as $champ)
	{
		$nom_champ = $champ->COLUMN_NAME;

		$asc_img = "off";
		$desc_img = "off";

		if (issetNotEmpty($champ->ORG_COLUMN_NAME))
		$col_table_champ_tri = $champ->ORG_COLUMN_NAME;
		else
		$col_table_champ_tri = $champ->COLUMN_NAME;

		//if (isset($champ->ORG_TABLE_NAME) and !empty($champ->ORG_TABLE_NAME))
		$col_table_champ_tri = $champ->TABLE_NAME.".".$col_table_champ_tri;

		if ($dbSearch->a_data['col_tri'] == $col_table_champ_tri)
		{
			if ($dbSearch->a_data['sens_tri'] == "DESC") $asc_img = "on";
			if ($dbSearch->a_data['sens_tri'] == "ASC") $desc_img = "on";
		}
		$txt .= '
		<td class="cfgt" valign=top>
	       	<table border=0 width=100%>
	       	<tr>
		       	<td class="cfgt" nowrap>'.$champ->TITRE_CHAMP.'</td>
		    	<td width=100%>
			    	<table class=navigation border=0 cellspacing=0 cellpadding=0  width=100%>
			    	<tr class="cfgt" height=10 onmouseover=\'this.className="cfg0"\' onmouseout=\'this.className="cfgt"\' width=50%><td align=right onMouseOver=\'self.status=this.title; return true\' title="'.LANG_TRIDESC.' '.$champ->LIB_CHAMP.'" onClick="ChangeTriCol(\''.$col_table_champ_tri.'\',\'DESC\',document.'.$dbSearch->formName.',\''.$dbSearch->formNameModeSubmit.'\')"><img src="'.CFG_URL_ROOT.'image/tri_asc_'.$asc_img.'.gif" border=0 title="'.LANG_TRIDESC.' '.$champ->LIB_CHAMP.'"></td></tr>
			    	<tr class="cfgt" height=10  onmouseover=\'this.className="cfg0"\' onmouseout=\'this.className="cfgt"\' width=50%><td align=right onMouseOver=\'self.status=this.title; return true\' title="'.LANG_TRIASC.' '.$champ->LIB_CHAMP.'" onClick="ChangeTriCol(\''.$col_table_champ_tri.'\',\'ASC\',document.'.$dbSearch->formName.',\''.$dbSearch->formNameModeSubmit.'\')"><img src="'.CFG_URL_ROOT.'image/tri_desc_'.$desc_img.'.gif" border=0 title="'.LANG_TRIASC.' '.$champ->LIB_CHAMP.'"></td></tr>
			    	</table>
		    	</td>
	    	</tr>
	    	</table>
		</td>';
	}
	$txt .= "</tr>\n";

	return $txt;

}


function bMeteo($id,$flagtitle=true)
{
	if ($id > 0)
	{
		return '<img src="'.CFG_URL_ROOT.'image/b_'.$id.'_color.gif" '.($flagtitle ? 'title="'.constant("LANG_METEO".$id).'" ' : "").' border=0 width=10>';
	}
	else
	{
		return '';
	}
}

function do_offset($level){
    $offset = "";             // offset for subarry 
    for ($i=1; $i<$level;$i++){
    $offset = $offset . "<td></td>";
    }
    return $offset;
}

function show_array($array, $level, $sub){
    if (is_array($array) == 1){          // check if input is an array
       foreach($array as $key_val => $value) {
           $offset = "";
           if (is_array($value) == 1){   // array is multidimensional
           echo "<tr>";
           $offset = do_offset($level);
           echo $offset . "<td>" . $key_val . "</td>";
           show_array($value, $level+1, 1);
           }
           else{                        // (sub)array is not multidim
           if ($sub != 1){          // first entry for subarray
               echo "<tr nosub>";
               $offset = do_offset($level);
           }
           $sub = 0;
           echo $offset . "<td main ".$sub." width=\"120\">" . $key_val . 
               "</td><td width=\"120\">" . $value . "</td>"; 
           echo "</tr>\n";
           }
       } //foreach $array
    }  
    else{ // argument $array is not an array
        return;
    }
}

function html_show_array($array){
  echo "<table cellspacing=\"0\" border=\"2\">\n";
  show_array($array, 1, 0);
  echo "</table>\n";
}