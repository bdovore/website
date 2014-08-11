<?php

class Bdo_Db_Search extends Bdo_Db_Select
{
	public $a_filtreSearch=array();

	public function integreData($a_data)
	{
		$this->a_data = $a_data;

		$this->a_langField = array();

		foreach ($this->a_langField_default as $champ)
		{

			$ch_champ = "ch_".$champ->COLUMN_NAME;
			$column_name = $champ->COLUMN_NAME;

			if (issetNotEmpty($a_data[$ch_champ])
			and ('ID_' != substr($column_name,0,3))
			//	and ('FREE_ID_' != substr($column_name,0,8))
			and !in_array($column_name,$this->a_exclus_aff))
			{
				$this->a_langField[$column_name] = $champ;
			}

			if  (isset($champ->DATA_TYPE)
			and (in_array($champ->DATA_TYPE,array('date','datetime','timestamp')))
			and isset($a_data[$column_name."_d"])
			and ($a_data[$column_name."_d"] != ""))
			{
				$a_data[$column_name] = $a_data[$column_name."_d"];
			}

			if ('' != $a_data[$column_name])
			{

				if ($a_data[$column_name] == "NULL")
				{
					$this->where .= " AND ".$champ->TABLE_NAME.".".$column_name." IS NULL\n";
				}
				elseif ($a_data[$column_name] != "%")
				{
					$this->where .= $this->whereQueryCol($champ);
				}
				else
				{
					$a_data[$column_name] = "";
				}

				if ('' != $a_data[$column_name]) {

					$aff_champ = "aff_".$champ->COLUMN_NAME;
					if (isset($a_data[$aff_champ])) {
						$this->a_filtreSearch[$champ->COLUMN_NAME]->aff_champ = $a_data[$aff_champ];
						$this->a_filtreSearch[$champ->COLUMN_NAME]->val = $a_data[$column_name];
					}
					else if ('ID_' != substr($column_name,0,3)){
						$this->a_filtreSearch[$champ->COLUMN_NAME]->aff_champ = $column_name;
						$this->a_filtreSearch[$champ->COLUMN_NAME]->val = $a_data[$column_name];
					}

				}
			}
		}

		return $this->a_langField;
	}

	// ------------------------------=======================--------------------------------
	// fonction d'affichage des champs interne à une table principale en vue de recherche
	public function html_Field_Search ($column_name,$affichage_par_defaut=FALSE)
	{
		$html = '';
		$column = $this->a_langField_default[$column_name];

		$ch_column_name = "ch_".$column->COLUMN_NAME;
		$html_form_name = "affichage";

		if (isset($column->CHARACTER_MAXIMUM_LENGTH) and ($column->CHARACTER_MAXIMUM_LENGTH < 30) and ($column->CHARACTER_MAXIMUM_LENGTH > 0))
		{
			$size = "size=".$column->CHARACTER_MAXIMUM_LENGTH;
		}
		else
		{
			$size = "size=30";
		}

		$html .= "<tr><td class='cfg0' align=right valign=top nowrap>$column->TITRE_CHAMP ";

		if (!isset($column->TAB_CHECK_VALUE))
		$html .= $this->whriteOptionQuery ($column);

		$html .= " :&nbsp;</td>
	<td class='cfg0' nowrap valign=top>";

		switch ($column->DATA_TYPE)
		{
			case 'date' :
			case 'datetime' :
			case 'timestamp' :
				{
					// global ${$column_name."_d"},${$column_name."_f"},${"option_query_".$column_name};

					if ($column->DATA_TYPE == 'date')
					{
						switch ($_SESSION['ID_LANG'])
						{
							case '_FR' :
								$html .= " <i class=gris>JJ/MM/AAAA</i>";
								$formatDate = '%d/%m/%Y';
								break;
							default :
								$html .= " <i class=gris>YYYY-MM-DD</i>";
								$formatDate = '%Y-%m-%d';
						}
						$size = "size=10 maxlength=10";
						$showsTime = 'false';
					}
					else
					{
						switch ($_SESSION['ID_LANG'])
						{
							case '_FR' :
								$html .= " <i class=gris>JJ/MM/AAAA 00:00:00</i>";
								$formatDate = '%d/%m/%Y %H:%M:%S';
								break;
							default :
								$html .= " <i class=gris>YYYY-MM-DD 00:00:00</i>";
								$formatDate = '%Y-%m-%d %H:%M:%S';
						}
						$size = "size=19 maxlength=19";
						$showsTime = 'true';
					}

					$info_champ = "<img id='".$column_name."_d_bn' SRC='".CFG_URL_ROOT."image/ico_calendrier.gif' border=0 align='absmiddle' style='cursor:hand'>";
					$info_champ .=  "
<SCRIPT type=text/javascript>
Calendar.setup({
firstDay :    1,
showsTime : ".$showsTime." ,
inputField     :    '".$column_name."_d',      // id of the input field
ifFormat       :    '".$formatDate."',       // format of the input field
button         :    '".$column_name."_d_bn',   // trigger for the calendar (button ID)
singleClick    :    true,           // double-click mode
step           :    1                // show all years in drop-down boxes (instead of every other year as default)
});
</SCRIPT>";

					$html .= "
			<INPUT type=text id='".$column_name."_d' name='".$column_name."_d' value='".fr_date($this->a_data[$column_name."_d"])."' $size class=cfg onFocus=\"this.className='cfg_on'\" onBlur=\"this.className='cfg'\"> $info_champ";

					if ($this->a_data["option_query_".$column_name] == 6) $div_display = "block";
					else  $div_display = "none";

					$info_champ = "<img id='".$column_name."_f_bn' SRC='".CFG_URL_ROOT."image/ico_calendrier.gif' border=0 align='absmiddle' style='cursor:hand'>";

					$info_champ .=  "
<SCRIPT type=text/javascript>
Calendar.setup({
firstDay :    1,
showsTime : ".$showsTime." ,
inputField     :    '".$column_name."_f',      // id of the input field
ifFormat       :    '".$formatDate."',       // format of the input field
button         :    '".$column_name."_f_bn',   // trigger for the calendar (button ID)
singleClick    :    true,           // double-click mode
step           :    1                // show all years in drop-down boxes (instead of every other year as default)
});
</SCRIPT>";


					$html .= "<br /><div id='div_".$column_name."_f' style='display:".$div_display."'>
					".LANG_SEARCHOPTDATE7BIS."
					<INPUT type=text id='".$column_name."_f' name='".$column_name."_f' value='".fr_date($this->a_data[$column_name."_f"])."' $size class=cfg onFocus=\"this.className='cfg_on'\" onBlur=\"this.className='cfg'\"> $info_champ";

					break;
				}
			case 'time' :
				{
					//global ${$column_name."_d"},${$column_name."_f"},${"option_query_".$column_name};

					$html .= " <i class=gris>HH:mm:ss</i>";

					$size = "size=8 maxlength=8";

					$html .= "
			<INPUT type=text id='".$column_name."_d' name='".$column_name."_d' value='".$this->a_data[$column_name."_d"]."' $size class=cfg onFocus=\"this.className='cfg_on'\" onBlur=\"this.className='cfg'\">";

					if ($this->a_data["option_query_".$column_name] == 6) $div_display = "block";
					else  $div_display = "none";

					$html .= "<br /><div id='div_".$column_name."_f' style='display:".$div_display."'>
					".LANG_SEARCHOPTDATE7BIS."
					<INPUT type=text id='".$column_name."_f' name='".$column_name."_f' value='".$this->a_data[$column_name."_f"]."' $size class=cfg onFocus=\"this.className='cfg_on'\" onBlur=\"this.className='cfg'\">";

					break;
				}
			default :
				{
					//if (isset($column->INFO_CHAMP) and !empty($column->INFO_CHAMP)) $info_champ = "&nbsp;".$column->INFO_CHAMP;
					//else
					$info_champ = '';


					if ($affichage_par_defaut != 3)
					{

						if (isset($column->TAB_CHECK_VALUE))
						{
							$html .= "<select  id='".$column->COLUMN_NAME."' name='$column->COLUMN_NAME' class=cfg >";
							$html .= "<option value='' $select_option>".LANG_INDIFFERENT."</OPTION>\n";
							foreach ($column->TAB_CHECK_VALUE as $val_check)
							{
								$select_option = "";
								if ($this->a_data[$column_name] == $val_check) $select_option = "SELECTED";

								$html .= "<option value='$val_check' $select_option>".TransConstante($val_check)."</OPTION>\n";
							}
							$html .= "</select>".$info_champ;
						}
						else
						{
							$html .= "<INPUT type=text name='$column->COLUMN_NAME' value='".htmlPrepaTexte($this->a_data[$column_name])."' $size ";
							if($affichage_par_defaut == 2)  $html .= "maxlength='".$column->CHARACTER_MAXIMUM_LENGTH."' ";
							$html .= "class=cfg onFocus=\"this.className='cfg_on'\" onBlur=\"this.className='cfg'\">".$info_champ."";
						}
					}
				}
		}

		$html .= "</td>";

		if ($affichage_par_defaut != 2)
		{
			$html .= "
    		<td class='cfg0' nowrap>
    		<INPUT type=checkbox name='$ch_column_name' value='checked' ".$this->a_data[$ch_column_name].">
    		</td>";
		}else{
			$html .= "<td class='cfg0' nowrap></td>";
		}
		$html .= "</tr>";

		return $html;
	}

	// ------------------------------=======================--------------------------------
	// ------------------------------=======================--------------------------------
	// fonction d'affichage des champs externe à une table principale en vue de recherche
	public function html_Field_Search_Ext ($column_name,$aff_column_name,$affichage_par_defaut=1,$multiple=0)
	{
		$html = '';
		$column = $this->a_langField_default[$column_name];
		$aff_column = $this->a_langField_default[$aff_column_name];

		$ch_column_name = "ch_".$column->COLUMN_NAME;
		$ch_aff_column_name = "ch_".$aff_column->COLUMN_NAME;

		/**
		 * utilisation de Bdo_Cfg::schema() pour eviter le chargement du schema dans l'objet
		 */
		$schema = Bdo_Cfg::schema();
		$nom_aff_champ = $schema->verifAffNomChamp($aff_column->ORG_COLUMN_NAME);
		//$nom_aff_champ = $aff_column->COLUMN_NAME;

		$html .= "
		<tr>
		<td class='cfg0' align=right valign=top nowrap>$aff_column->TITRE_CHAMP :&nbsp;</td>
		<td class='cfg0' valign=top nowrap>
		";

		switch ($column->COLUMN_NAME)
		{
			case ("ID_WORKFLOW") :  $order_by = " ORDER BY NOM_ACL_RESOURCE"; break;
			default : $order_by = " ORDER BY $nom_aff_champ";
		}

		if (0 < $multiple)
		{
			$multi_select="size=".$multiple." multiple ";
			$name_multi_champ = "[]";
		}
		else
		{
			$multi_select = '';
			$name_multi_champ = '';
		}


		if(($column->COLUMN_NAME == 'ID_CX_LEVEL') and user::maxLevel(8))
		$order_by = " AND ID_CX_LEVEL<>9 ".$order_by;

		$htmlSelect = "";
		switch($column->COLUMN_NAME)
		{
			case 'ID_GAMME_VERSION' :
				{
					$dbSelectGammeVersion = cat_catalogue::dbSelectGammeVersion();

					$tab_ver = array();
					$tab_gamme = array();

					foreach ($dbSelectGammeVersion->a_dataQuery as $obj)
					{
						$tab_ver[$obj->ID_GAMME][$obj->ID_GAMME_VERSION] = $obj->GOROCO_GAMME_VERSION;
						$tab_gamme[$obj->ID_GAMME] = $obj->NOM_GAMME;
					}
					$decalage = 0;


					foreach ($tab_gamme as $id_gamme=>$nom_gamme)
					{
						if ($id_gamme != 0) {

							$htmlSelect .= "<optgroup label=\"".$nom_gamme."\">";
							$decalage += 2;
						}

						foreach ($tab_ver[$id_gamme] as $id_gamme_version=>$goroco_gamme_version) {
							$select_option = "";
							if ($this->a_data['ID_GAMME_VERSION'] == $id_gamme_version) $select_option = "SELECTED";
							$htmlSelect .= "<option value='".$id_gamme_version."' $select_option>".AffDecalageOption($decalage)."".$goroco_gamme_version."</OPTION>\n";
						}
						if ($id_gamme != 0) {
							$decalage -= 2;
							$htmlSelect .= "</optgroup>";
						}
					}

					break;


				}
			case 'ID_WORKFLOW' :
				{
					$query = "
					SELECT 
					wf_workflow.ID_WORKFLOW,
					wf_workflow.NOM_WORKFLOW".$_SESSION['ID_LANG']." as NOM_WORKFLOW,
					acl_resource.ID_ACL_RESOURCE,
					acl_resource.NOM_ACL_RESOURCE
					FROM
					wf_workflow
					INNER JOIN acl_resource USING(ID_ACL_RESOURCE)
					".(issetNotEmpty($this->a_data['ID_ACL_RESOURCE']) ? "WHERE acl_resource.ID_ACL_RESOURCE=".$this->a_data['ID_ACL_RESOURCE'] : " WHERE 1 " ) ."
					".$order_by
					;

					$resultat = Db_query($query);

					$a_objet = array();
					$a_pere = array();

					while ($obj = Db_fetch_object($resultat))
					{
						$a_objet[$obj->ID_ACL_RESOURCE][$obj->ID_WORKFLOW] = $obj;
						$a_pere[$obj->ID_ACL_RESOURCE] = $obj->NOM_ACL_RESOURCE;

					}
					foreach ($a_objet as $id_pere=>$a_fils)
					{
						if ($id_pere != 0)
						{
							$htmlSelect .= "<optgroup label=\"".TransConstante($a_pere[$id_pere])."\">";
						}
						foreach ($a_fils as $id_fils=>$o_fils)
						{
							$select_option = "";
							if ($this->a_data[$column_name] == $o_fils->$column_name) $select_option = "SELECTED";
							if (is_array($this->a_data[$column_name]))
							{
								if (in_array($o_fils->$column_name,$this->a_data[$column_name])) $select_option = "SELECTED";
							}
							$htmlSelect .= "<option value='".$o_fils->$column_name."' $select_option>".$o_fils->{$aff_column_name}."</OPTION>\n";
						}
						if ($id_pere != 0)
						{
							$htmlSelect .= "</optgroup>";
						}
					}

					break;
				}
			case 'ID_ACL_RESOURCE_ETAT' :
				{
					$query = "
					SELECT 
					acl_resource_etat.ID_ACL_RESOURCE_ETAT,
					acl_resource_etat.NOM_ACL_RESOURCE_ETAT".$_SESSION['ID_LANG']."
					FROM
					acl_resource_etat
					";
					$_resource = Bdo_Cfg::getVar("_resource");

					if (issetNotEmpty($_resource->ID_ACL_RESOURCE)) {
						$query .= "INNER JOIN wf_workflow_etat USING(ID_ACL_RESOURCE_ETAT)
						INNER JOIN wf_workflow USING(ID_WORKFLOW)
						WHERE wf_workflow.ID_ACL_RESOURCE=".$_resource->ID_ACL_RESOURCE ."
						GROUP BY acl_resource_etat.ID_ACL_RESOURCE_ETAT, acl_resource_etat.NOM_ACL_RESOURCE_ETAT".$_SESSION['ID_LANG']."
						ORDER BY wf_workflow_etat.ORDRE_WORKFLOW_ETAT";
					}
					else {
						$query .= " WHERE 1 ".$order_by;
					}


					$resultat = Db_query($query);
					while ($obj = Db_fetch_object($resultat))
					{
						$select_option = "";
						if ($this->a_data[$column_name] == $obj->$column_name) $select_option = "SELECTED";
						if (is_array($this->a_data[$column_name]))
						{
							if (in_array($obj->$column_name,$this->a_data[$column_name])) $select_option = "SELECTED";
						}

						$tab_nom_aff_champ = explode("\n",wordwrap( $obj->$nom_aff_champ, 60 ));
						$troispoint1 = "";
						$i=0;
						$nb_tab_nom_aff_champ = count($tab_nom_aff_champ);
						foreach($tab_nom_aff_champ as $text_nom_aff_champ)
						{
							$i++;
							if (($nb_tab_nom_aff_champ > 1) AND ($i<$nb_tab_nom_aff_champ))
							$troispoint2 = "...";
							else
							$troispoint2 = "";

							$htmlSelect .= "<option value='".$obj->$column_name."' $select_option>".$troispoint1.$text_nom_aff_champ.$troispoint1."</OPTION>\n";
							$troispoint1 = "...";
						}
					}
					Db_free_result($resultat);

					break;
				}
			case 'ID_SOFT_VERSION_OS' :
				{
					//					$column_name = 'ID_SOFT_VERSION';
					$dbSelectSoft = cat_catalogue::dbSelectSoft('OS');

					$tab_ver = array();
					$tab_os = array();

					foreach ($dbSelectSoft->a_dataQuery as $obj)
					{
						$tab_ver[$obj->ID_SOFT][$obj->ID_SOFT_VERSION] = $obj->GOROCO_SOFT_VERSION;
						$tab_os[$obj->ID_SOFT] = $obj->NOM_SOFT;
					}
					$decalage = 0;


					foreach ($tab_os as $id_soft=>$nom_soft)
					{
						if ($id_soft != 0) {

							$htmlSelect .= "<optgroup label=\"".$nom_soft."\">";
							$decalage += 2;
						}

						foreach ($tab_ver[$id_soft] as $id_soft_version=>$goroco_soft_version) {
							$select_option = "";
							if ($this->a_data['ID_SOFT_VERSION_OS'] == $id_soft_version) $select_option = "SELECTED";
							$htmlSelect .= "<option value='".$id_soft_version."' $select_option>".AffDecalageOption($decalage)."".$goroco_soft_version."</OPTION>\n";
						}
						if ($id_soft != 0) {
							$decalage -= 2;
							$htmlSelect .= "</optgroup>";
						}
					}

					break;
				}
			default:
				///modifxxx

				$query = "SELECT
				$column->ORG_COLUMN_NAME,
				$nom_aff_champ 
				FROM ".$aff_column->ORG_TABLE_NAME." WHERE 1 ".$order_by;

				$resultat = Db_query($query);
				while ($obj = Db_fetch_object($resultat))
				{
					$select_option = "";
					if ($this->a_data[$column_name] == $obj->$column_name) $select_option = "SELECTED";
					if (is_array($this->a_data[$column_name]))
					{
						if (in_array($obj->$column_name,$this->a_data[$column_name])) $select_option = "SELECTED";
					}

					$tab_nom_aff_champ = explode("\n",wordwrap( $obj->$nom_aff_champ, 60 ));
					$troispoint1 = "";
					$i=0;
					$nb_tab_nom_aff_champ = count($tab_nom_aff_champ);
					foreach($tab_nom_aff_champ as $text_nom_aff_champ)
					{
						$i++;
						if (($nb_tab_nom_aff_champ > 1) AND ($i<$nb_tab_nom_aff_champ))
						$troispoint2 = "...";
						else
						$troispoint2 = "";

						$htmlSelect .= "<option value='".$obj->$column_name."' $select_option>".$troispoint1.$text_nom_aff_champ.$troispoint2."</OPTION>\n";
						$troispoint1 = "...";
					}
				}
				Db_free_result($resultat);
		}


		$html .= "<select id='".$column_name."' name='".$column_name.$name_multi_champ."' ".$multi_select." class=cfg >";
		if (0 == $multiple)
		{
			$html .= "<option value=''>".LANG_INDIFFERENT."</OPTION>\n";
		}
		if ('NO' != $column->IS_NULLABLE)
		{
			if ($this->a_data[$column_name] == "NULL") $select_option = "SELECTED";
			$html .= "<option value='NULL' $select_option>".LANG_NONRENSEIGNE."</OPTION>\n";
		}

		$html .= $htmlSelect."</select>";

		if (0 < $multiple)
		{
			$html .= "<br />"."( ".LANG_USECTRLCLIC." )";
		}
		$html .= "</td>";

		if ($affichage_par_defaut != 2)
		{
			$html .= "
	  		<td class='cfg0' nowrap>
	  		<INPUT type=hidden name='aff_".$column->COLUMN_NAME."' value='".$aff_column->COLUMN_NAME."'>
	  		<INPUT type=hidden name='$ch_column_name' value='checked'>
	  		<INPUT type=checkbox name='$ch_aff_column_name' value='checked' ".$this->a_data[$ch_aff_column_name].">
	  		</td></tr>
	  		";
		}
		else{
			$html .= "<td class='cfg0' nowrap></td>";
		}
		$html .= "</tr>\n";
		return $html;

	}

	public function whriteOptionQuery ($obj_col)
	{
		$col_option_query = "option_query_".$obj_col->COLUMN_NAME;

		if (count($this->option_query($obj_col->DATA_TYPE)) > 1)
		{
			if (in_array($obj_col->DATA_TYPE,array('date','datetime','time','timestamp')))
			{
				$onchange_select = "OnChange=\"
			if (this.options.selectedIndex==6) getObj('div_".$obj_col->COLUMN_NAME."_f').style.display='block';
			else  getObj('div_".$obj_col->COLUMN_NAME."_f').style.display='none';\"";

			}
			else
			{
				$onchange_select = '';
			}

			$html_txt = '<select name="'.$col_option_query.'" '.$onchange_select.' class="cfg">';
			foreach ($this->option_query($obj_col->DATA_TYPE) as $val=>$txt)
			{
				if ($this->a_data[$col_option_query] == $val) $select="SELECTED";
				else $select="";

				$html_txt .=  "\n".'<option value="'.$val.'" '.$select.'>'.$txt.'</OPTION>';
			}

			$html_txt .= "\n</select>";
			return $html_txt;
		}
	}



	public function affFiltreSearch()
	{

		switch($this->formNameModeSubmit) {
			case 'form' : $modeSubmit = 'document.'. $this->formName . '.submit()'; break;
			case 'xhr' : $modeSubmit = 'ValidFormXhr(document.'. $this->formName . ')'; break;
		}

		$html_filtreSearch = LANG_CRITERE . " : <ul style='line-height:20px'>";

		foreach($this->a_filtreSearch as $column_name=>$o_column)
		{
			$selectInput = false;
			
			$column = $this->a_langField_default[$column_name];
			$col_option_query = "option_query_".$column_name;

			if (isset($this->a_data[$col_option_query])) {
				$lib_option_query = $this->option_query($this->a_langField_default[$column_name]->DATA_TYPE,$this->a_data[$col_option_query]);
			}
			else {
				$lib_option_query = ':';
			}
			$aff_column = $this->a_langField_default[$o_column->aff_champ];

			$a_val = array();
			if ($column_name != $o_column->aff_champ) {

				switch ($column->ORG_COLUMN_NAME){
					case 'ID_GAMME_VERSION' : {
						$query = "SELECT
						CONCAT(cat_gamme.NOM_GAMME,' ',cat_gamme_version.GOROCO_GAMME_VERSION) as NOM_GAMME
						FROM
						cat_gamme_version
						INNER JOIN cat_gamme USING(ID_GAMME)
						WHERE cat_gamme_version.ID_GAMME_VERSION=".$o_column->val;

						break;
					}
					case 'ID_SOFT_VERSION_OS' : {
						$query = "SELECT
						CONCAT(cat_soft.NOM_SOFT,' ',cat_soft_version.GOROCO_SOFT_VERSION) as NOM_SOFT_VERSION_OS
						FROM
						cat_soft_version
						INNER JOIN cat_soft USING(ID_SOFT)
						WHERE cat_soft_version.ID_SOFT_VERSION=".$o_column->val;
						break;
					}
					default: {
						$query = "SELECT ".
						$column->ORG_COLUMN_NAME.",
						".$aff_column->ORG_COLUMN_NAME ."
						FROM ".$aff_column->ORG_TABLE_NAME ."
						WHERE ".$column->ORG_COLUMN_NAME.(is_array($o_column->val) ? " IN(".implode(',',$o_column->val).")" : "='".$o_column->val."'");
					}
				}

				
				$resultat = Db_query($query);

				while ($obj = Db_fetch_object($resultat))
				{
					$a_val[] = $obj->{$aff_column->ORG_COLUMN_NAME};

				}
				$selectInput = true;

			}
			else {
				$a_val[] = $o_column->val;
			}

			$onClick = "onClick=\"document.".$this->formName.".daff.value=0;";
			$add_lib = '';
			if ($column->TAB_CHECK_VALUE or $selectInput) {
				$onClick .= "
				document.".$this->formName.".elements['".$column_name.(is_array($o_column->val)?'[]':'')."'].options[0].selected=true;//pour IE
				for (var i = 0; i < document.".$this->formName.".elements['".$column_name.(is_array($o_column->val)?'[]':'')."'].options.length; i++) {
				document.".$this->formName.".elements['".$column_name.(is_array($o_column->val)?'[]':'')."'].options[i].selected=false;
				}
				";
			}
			else if (in_array($column->DATA_TYPE,array('date','datetime','timestamp'))) {
				if ($this->a_data['option_query_'.$column_name] == 6) {
					$add_lib = '&nbsp;<i>'.LANG_SEARCHOPTDATE7BIS.'</i>&nbsp;[&nbsp;'.$this->a_data[$column_name.'_f'].'&nbsp;]';
				}
				$onClick .= "
				document.".$this->formName.".".$column_name."_d.value='';
				document.".$this->formName.".".$column_name."_f.value='';
				";
			}
			else
			{
				$onClick .= "document.".$this->formName.".".$column_name.".value='';";
			}
			
			$onClick .= $modeSubmit."\" title='".LANG_RETIRER."'";

			$html_filtreSearch .= "<li  onmouseover='this.className=\"cfg1\"' onmouseout='this.className=\"cfg0\"' ".$onClick." class='cfg0' style='display:inline;padding:3px;margin:0 5 0 0;cursor:pointer'>" . $aff_column->LIB_CHAMP . " 
			<i>".$lib_option_query."</i> [&nbsp;" . htmlPrepaTexte(TransConstante(implode(',',$a_val))).'&nbsp;]'
			.$add_lib.'</li>';
		}

		$html_filtreSearch .= "</ul>";
		// fin pour affichage des pages----------------------------------------------------------------------

		return $html_filtreSearch;

	}


}
