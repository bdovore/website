<?php

class Bdo_Db_Select
{
	public $queryFull = null;

	public $select = null;
	public $from = null;
	public $where = null;
	public $groupby = null;
	public $orderby = null;

	public $limit = null;
	public $limitIdLine = null;
	public $limitNbrLine = null;

	public $a_data = array();

	public $nbLineResult = 0;
        public $nbLineTotal = 0;

	public $a_langField = array();
	public $a_langField_default = array();
	public $a_exclus_aff = array();

	public $formName = 'affichage';
	public $formNameModeSubmit = 'form'; //form, xhr, iframe
	public $flag_affNumPage=false;

	public $a_dataQuery = array();

	public $entite = array();


	public function __construct($queryFull=null)
	{
		$this->schema_name = Bdo_Cfg::schema()->schema_name;

		//$this->init_option_query();

		if ($queryFull) $this->setQuery($queryFull);
	}

	public function setQuery($queryFull)
	{
		$this->queryFull = $queryFull;

		$this->select = null;
		$this->from = null;
		$this->where = null;
		$this->groupby = null;
	}

	public function query_a()
	{
		if ($this->queryFull)
		{
			return $this->queryFull;
		}
		else {
			return $this->select.' '.$this->from.' '.$this->where.' '.$this->groupby.' ';
		}
	}

	public function query_c()
	{
		if ($this->queryFull)
		{
			return $this->queryFull;
		}
		else {
			$this->init_limit();

			// mysql : cas union : Le mot clé SQL_CALC_FOUND_ROWS doit apparaître dans le premier SELECT de l'UNION.
			return  preg_replace('/select/i','SELECT SQL_CALC_FOUND_ROWS', $this->query_a(), 1) . $this->orderby . $this->limit;
		}

	}

	public function __toString()
	{
		return $this->query_c();
	}


	public function init_limit()
	{
		// pour affichage des pages--------------------------------------------
		//nbre de lignes par page
		if (isset($this->a_data['baff']) and !empty($this->a_data['baff'])) {
			$this->limitNbrLine = $this->a_data['baff'] + 0;
		}
		else {
			$this->limitNbrLine = BDO_NBLINEBYPAGE_DEFAULT;
		}

		// premiere ligne de la recherche
		if (isset($this->a_data['daff']) and !empty($this->a_data['daff'])) {
			$this->limitIdLine = $this->a_data['daff'] + 0;
		}
		else {
			$this->limitIdLine=0;
		}

		if (($this->limitNbrLine < BDO_NBLINEBYPAGE_MIN) OR ($this->limitNbrLine > BDO_NBLINEBYPAGE_MAX)) {
			$this->limitNbrLine = BDO_NBLINEBYPAGE_DEFAULT;
		}

		$this->limit = " LIMIT ".$this->limitIdLine.",".$this->limitNbrLine;

		return $this->limit;
	}

	public function init_orderBy()
	{
		if (empty($this->queryFull) and empty($this->orderby))
		{
			// gestion de l'ordre de tri
			//dans l'ordre
			if (issetNotEmpty($this->a_data['sens_tri']) and issetNotEmpty($this->a_data['col_tri']))
			{
				$this->orderby = "ORDER BY ".Bdo_Cfg::schema()->verifAffNomChamp($this->a_data['col_tri'])." ".$this->a_data['sens_tri'];
			}
		}
	}

	public function infoQuery($resultat=null)
	{

		if (!$resultat) {
			$resultat = Db_query($this->select.' '.$this->from.' '.$this->where.' '.$this->groupby.' limit 0,0');
		}

		//$finfo = mysqli_fetch_fields($resultat);
		$finfo = $resultat->fetch_fields();
		Db_free_result($resultat);

		if (!is_array($finfo)) $finfo = array();

		$tab_liste_champ = array();

		foreach ($finfo as $col)
		{
			$champ->COLUMN_NAME = $col->name;
			$champ->TABLE_NAME = $col->table;

			$champ->ORG_COLUMN_NAME = $col->orgname;
			$champ->ORG_TABLE_NAME = $col->orgtable;

			$champ->TITRE_CHAMP = $col->name;
			$champ->LIB_CHAMP = $col->name;

			$champ->TITRE_TDWIDTH = (isset($forceTdWith)) ? $forceTdWith : BDO_TD_WIDTH;

			$champ->QUERY_COMPARE = '';
			$champ->INFO_CHAMP = '';
			$champ->DESC_CHAMP = '';
			$champ->DATA_TYPE = '';
			$champ->COLUMN_DEFAULT = '';

			$champ->EXTRA_CHAMP = '';

			if (empty($col->orgname))
			{
				$champ->CHARACTER_MAXIMUM_LENGTH = 0;
				$champ->DATA_TYPE = '';
				$champ->IS_NULLABLE = '';
			}

			if (isset(Bdo_Cfg::schema()->dbColumn[$col->orgtable][$col->orgname]))
			{
				$champ->CHARACTER_MAXIMUM_LENGTH = Bdo_Cfg::schema()->dbColumn[$col->orgtable][$col->orgname]->CHARACTER_MAXIMUM_LENGTH;
				$champ->DATA_TYPE = Bdo_Cfg::schema()->dbColumn[$col->orgtable][$col->orgname]->DATA_TYPE;
				$champ->IS_NULLABLE = Bdo_Cfg::schema()->dbColumn[$col->orgtable][$col->orgname]->IS_NULLABLE;
				$champ->COLUMN_DEFAULT = Bdo_Cfg::schema()->dbColumn[$col->orgtable][$col->orgname]->COLUMN_DEFAULT;

				if (in_array($champ->DATA_TYPE, array('enum','set')))

				{
					$valeurs = preg_replace("#(?:enum|set)\('([^\)].*)'\)$#i", "$1", Bdo_Cfg::schema()->dbColumn[$col->table][$col->name]->COLUMN_TYPE);
					$champ->TAB_CHECK_VALUE = explode("','", $valeurs);
				}

				// attention type [float unsigned] au lieu de [float]
				if ($champ->DATA_TYPE == 'float unsigned') $champ->DATA_TYPE = 'float';
			}

			// initialisation du tableau de noms de colonnes pour la recherche dans la table lang_field
			if (stripos($col->name,'ID_') !== 0)
			{
				$lang = substr($col->name,-3);
				if (isset($_SESSION['BDO_A_LANG'][$lang]))
				{
					$addcolname = "'". substr($col->name,0,-3) . "'" ;
					if (!in_array($addcolname,$tab_liste_champ))
					{
						$tab_liste_champ[] = $addcolname;
					}
					$champ->MODIFY_CHAMP = '<a href="javascript:showLightbox(\'iframe\',\'admin/modify_champ?COLUMN_NAME='.substr($col->name,0,-3).'\',680,350)">[#]</a>';
				}
				else
				{
					$tab_liste_champ[] = "'". $col->name . "'" ;
					$champ->MODIFY_CHAMP = '<a href="javascript:showLightbox(\'iframe\',\'admin/modify_champ?COLUMN_NAME='.$col->name.'\',680,350)">[#]</a>';
				}
			}

			$this->a_langField_default[$col->name] = $champ;

			unset($champ);
		}

		unset($finfo);

		// --------------------------=======================-----------------------------

		if (!empty($tab_liste_champ))
		{
			foreach ($this->a_langField_default as $col)
			{
				$lang = substr($col->COLUMN_NAME,-3);
				if (isset($_SESSION['BDO_A_LANG'][$lang]))
				{
					$colname = substr($col->COLUMN_NAME,0,-3);
					$addLang= '('.$_SESSION['BDO_A_LANG'][$lang]->{'NOM_LANG'.$_SESSION['ID_LANG']}.')';

				}
				else
				{
					$colname = $col->COLUMN_NAME;
					$addLang = '';
				}

				if (isset(Bdo_Cfg::schema()->a_langField[$colname]))
				{

					$o_col = Bdo_Cfg::schema()->a_langField[$colname];

					$this->a_langField_default[$col->COLUMN_NAME]->LIB_CHAMP = $o_col->TITRE_CHAMP
					. ((!empty($addLang) and (!stristr($o_col->TITRE_CHAMP,$addLang))) ? $addLang : '');

					$this->a_langField_default[$col->COLUMN_NAME]->TITRE_CHAMP = htmlPrepaTexte($this->a_langField_default[$col->COLUMN_NAME]->LIB_CHAMP);

					$this->a_langField_default[$col->COLUMN_NAME]->INFO_CHAMP = htmlPrepaTexte($o_col->INFO_CHAMP) ;
					$this->a_langField_default[$col->COLUMN_NAME]->DESC_CHAMP = htmlPrepaTexte($o_col->DESC_CHAMP);
					$this->a_langField_default[$col->COLUMN_NAME]->EXTRA_CHAMP = $o_col->EXTRA_CHAMP ;

					if ($this->a_langField_default[$col->COLUMN_NAME]->DESC_CHAMP != '')
					{
						Bdo_Cfg::getVar('view')->a_htmlEndFile[] = "
        		<div ID='DESC_CHAMP_".$col->COLUMN_NAME."_titre' style='display:none'>".$this->a_langField_default[$col->COLUMN_NAME]->TITRE_CHAMP."</div>
        		<div ID='DESC_CHAMP_".$col->COLUMN_NAME."_corps' style='display:none'>".NlToBrWordWrap($o_col->DESC_CHAMP)."</div>";

						$this->a_langField_default[$col->COLUMN_NAME]->TITRE_CHAMP = "<font style='cursor:help' onMouseOver=\"Affiche_Info('"
						.$col->COLUMN_NAME."','desc','cfg')\" onMouseOut='Cache_Info()'>"
						.$this->a_langField_default[$col->COLUMN_NAME]->TITRE_CHAMP."</font>";
					}
				}
			}
		}

		// ------ fin Recherche informations colonnes dans la table lang_field ------
		// --------------------------=======================-----------------------------
		if (User::minAccessLevel(0))
		{
			foreach ($this->a_langField_default as $col)
			{
				if (isset($this->a_langField_default[$col->COLUMN_NAME]->MODIFY_CHAMP))
				{
					$this->a_langField_default[$col->COLUMN_NAME]->TITRE_CHAMP = $this->a_langField_default[$col->COLUMN_NAME]->MODIFY_CHAMP
					. $this->a_langField_default[$col->COLUMN_NAME]->TITRE_CHAMP;
				}
			}
		}

		return $this->a_langField_default;
	}

	public function addData($a_data)
	{
		$this->a_data = $a_data;
	}

	public function integreData($a_data=array())
	{

		$this->addData($a_data);
		foreach ($this->a_langField_default as $column)
		{
			if (('ID_' != substr($column->COLUMN_NAME,0,3))
			and ('FREE_ID_' != substr($column->COLUMN_NAME,0,8))
			and !in_array($column->COLUMN_NAME,$this->a_exclus_aff))
			{
				$this->a_langField[$column->COLUMN_NAME] = $column;
			}
		}
		return $this->a_langField;
	}

	public function exec()
	{
		$this->init_orderBy(); // ajout de l'order by au plus pres de l'execution
		$resultat = Db_query($this->query_c());

		$this->nbLineResult = Db_CountRow($resultat);


		$this->a_dataQuery = array();
		while ($obj = Db_fetch_object($resultat))
		{
			$this->a_dataQuery[] = $obj;
		}

		if ($this->queryFull) {
			$this->infoQuery($resultat);
		}
		else {
			$resCount = Db_query('SELECT FOUND_ROWS() as nb');
			$rowCount = Db_fetch_object($resCount);
			$this->nbLineTotal = $rowCount->nb;

			// protection d'affichage
			if($this->limitIdLine > $this->nbLineTotal)
			{
				$this->a_data['daff']=0;
				$this->exec();
			}
		}

		//Bdo_Cfg::schema() = null;
		return $this->a_dataQuery;

	}

	public function execNoLimit()
	{
		$this->init_orderBy(); // ajout de l'order by au plus pres de l'execution
		$resultat = Db_query($this->query_a() . $this->orderby);

		$this->nbLineResult = Db_CountRow($resultat);


		$this->a_dataQuery = array();
		while ($obj = Db_fetch_object($resultat))
		{
			$this->a_dataQuery[] = $obj;
		}

		$this->infoQuery($resultat);


		return $this->a_dataQuery;

	}


	public function affNumPage()
	{


		switch($this->formNameModeSubmit) {
			case 'form' : $modeSubmit = 'document.'. $this->formName . '.submit()'; break;
			case 'xhr' : $modeSubmit = 'ValidFormXhr(document.'. $this->formName . ')'; break;
		}
		$html_num_page_total = '';
		if ($this->a_filtreSearch and !$this->flag_affNumPage) {
			$html_num_page_total = $this->affFiltreSearch() ;
		}
		$this->flag_affNumPage=true;

		$html_num_page = '';

		//nbre de pages
		$nbpage=ceil($this->nbLineTotal/$this->limitNbrLine);

		$html_num_page_total .= "
		<table border=0 cellpadding=2 cellspacing=0 width=100% class=cfg>
		<tr>
		<td width=25% class=navigation nowrap>&nbsp;".LANG_NBRESULT." : ".$this->nbLineTotal."</td>
		<td width=50% class=navigation align=center>
		";

		$nbAffNum = 2; // avant et apres

		$numPage=ceil($this->limitIdLine/$this->limitNbrLine);

		$a = '';
		$b = '';

		// pour affichage des pages----------------------------------------------------------------------
		if ($nbpage > (($nbAffNum*2)+1))
		{
			$tab_numPage[$numPage] = $numPage;

			// avant
			$numMinPage = $numPage - $nbAffNum;
			// après
			$numMaxPage = $numPage + $nbAffNum;

			if ($numMinPage < 0 ) $x = $nbAffNum+$numMinPage;
			else $x=$nbAffNum;

			if ($numMaxPage >= $nbpage) $y = $nbAffNum+$nbpage-$numMaxPage-1;
			else $y=$numMaxPage-$numPage;

			if ($x < $nbAffNum) $y = ($nbAffNum-$x) + $y;
			if ($y < $nbAffNum) $x = ($nbAffNum-$y) + $x;

			for($i=($numPage-$x);$i<$numPage;$i++)
			{
				$tab_numPage[$i] = $i;
			}

			for($i=$numPage+1;$i<$numPage+1+$y;$i++)
			{
				$tab_numPage[$i] = $i;
			}

			if ((($numPage+1)-$x) >= $nbAffNum)
			{
				$t = floor(($numPage+1)/2)-1;
				if (!isset($tab_numPage[$t])) $tab_numPage[$t] = '...';
				else if (!isset($tab_numPage[$t-1])) $tab_numPage[$t-1] = '...';
			}

			if ((($numPage)+$y) < $nbpage)
			{
				$t = floor(($numPage)+($nbpage-($numPage))/2);
				if (!isset($tab_numPage[$t])) $tab_numPage[$t] = '...';
				else if (!isset($tab_numPage[$t+1]) and (($t+1) < $nbpage)) $tab_numPage[$t+1] = '...';
			}

			ksort($tab_numPage);
		}
		else {
			for($i=0;$i<$nbpage;$i++)
			{
				$tab_numPage[$i] = $i;
			}

		}
		if ($nbpage > 1)
		{
			//	for($i=$x;$i<$y;$i++)
			foreach($tab_numPage as $i=>$aff)
			{
				$numPage = $i+1;

				if (is_numeric($aff))
				{
					$affNumPage = $numPage;

					if (empty($a))
					{
						$av_point = " ";
						$ap_point = ".";
					}
					else
					{
						$av_point = ".";
						$ap_point = " ";
					}
				}
				else
				{
					$av_point = " ";
					$ap_point = " ";
					$affNumPage = $aff;
				}


				$papage = $i * $this->limitNbrLine;
				if ($this->limitIdLine==$papage)
				{
					$html_num_page .= "<font color='#FF0000'><b>".$numPage."</b></font>";

					if ($numPage < $nbpage)
					{
						$a = "&nbsp;<a style='cursor:hand' onClick='document.".$this->formName.".daff.value=".($numPage*$this->limitNbrLine).";".$modeSubmit."' onMouseOver=\"self.status=this.title; return true\" title='".LANG_SUIVANT."' class=navigation> &gt;</a>&nbsp;
					<a style='cursor:hand' onClick='document.".$this->formName.".daff.value=".(($nbpage-1)*$this->limitNbrLine).";".$modeSubmit."' onMouseOver=\"self.status=this.title; return true\" title='".LANG_PAGELAST."' class=navigation> &gt;&gt;</a>";
					}
					if ($i > 0)
					{
						$b = "<a style='cursor:hand' onClick='document.".$this->formName.".daff.value=0;".$modeSubmit."' onMouseOver=\"self.status=this.title; return true\" title='".LANG_PAGEFIRST."' class=navigation>&lt;&lt; </a>&nbsp;
					<a style='cursor:hand' onClick='document.".$this->formName.".daff.value=".(($i-1)*$this->limitNbrLine).";".$modeSubmit."' onMouseOver=\"self.status=this.title; return true\" title='".LANG_PRECEDENT."' class=navigation>&lt; </a>&nbsp;";
					}
				}
				else
				{
					$html_num_page .= " ".$av_point . " <a style='cursor:hand' onClick='document.".$this->formName.".daff.value=$papage;".$modeSubmit."' onMouseOver=\"self.status=this.title; return true\"  title='".LANG_PAGE." ".$numPage."' class=navigation>".$affNumPage."</a> ".$ap_point." ";
				}
			}

			$html_num_page = $b." \n".$html_num_page;
			$html_num_page .= $a;
			$html_num_page .= "\n";
		}
		$html_num_page_total .= $html_num_page."
		<td width=25% class=navigation align=right>$this->limitNbrLine ".LANG_NBLIGNEPARPAGE."</td>
		</tr>
		</table>
		";
		$html_num_page=$html_num_page_total;
		// fin pour affichage des pages----------------------------------------------------------------------

		return $html_num_page;

	}

	function whereQueryCol($column)
	{
		$column_name = $column->COLUMN_NAME;

		$col_option_query = "option_query_".$column->COLUMN_NAME;
		$where_query_col = "";

		// assure la compatibilité avec la version précédente
		$this->a_data[$col_option_query] = $this->a_data[$col_option_query] + 0;

		if ($column->QUERY_COMPARE == "")
		{
			if ($column->TABLE_NAME != "")
			{
				$column->QUERY_COMPARE = $column->TABLE_NAME.".";
			}

			if (($column->ORG_TABLE_NAME == $column->TABLE_NAME) and ($column->ORG_COLUMN_NAME != $column->COLUMN_NAME)) {
				$column->QUERY_COMPARE .= $column->ORG_COLUMN_NAME;
			}
			else if ($column->ORG_TABLE_NAME != $column->TABLE_NAME) {
				$column->QUERY_COMPARE .= $column->ORG_COLUMN_NAME;
			}
			else {
				$column->QUERY_COMPARE .= $column->COLUMN_NAME;
			}

		}

		// modif pour affichage filtre
		if ($column->QUERY_COMPARE == "") return $where_query_col;


		switch ($this->a_data[$column_name])
		{
			case "NULL" : $where_query_col = " AND ".$column->QUERY_COMPARE." IS NULL"; break;
			case "NOT NULL" : $where_query_col = " AND ".$column->QUERY_COMPARE." IS NOT NULL"; break;
			default :
				{
					switch ($column->DATA_TYPE)
					{
						case 'time' :
							{
								if (($this->a_data[$column_name."_d"]!= "") and PrepaTime($this->a_data[$column_name."_d"]))
								{
									if ($this->a_data[$col_option_query] < 6)
									{
										$this->a_data[$column_name] = $this->a_data[$column_name] + 0;

										$QUERY_COMPARE_format_time = "TIME_FORMAT(".$column->QUERY_COMPARE.", '%H:%i:%s')";

										$where_query_col = " AND ".$QUERY_COMPARE_format_time.$this->option_query('int',$this->a_data[$col_option_query]).PrepaTime($this->a_data[$column_name."_d"]);
									}
									else if (PrepaTime($this->a_data[$column_name."_f"]))
									{
										if ($this->a_data[$column_name."_f"] != "")
										$where_query_col = " AND ".$column->QUERY_COMPARE." BETWEEN (".PrepaTime($this->a_data[$column_name."_d"]).") AND (".PrepaTime($this->a_data[$column_name."_f"]).")";
									}
									else
									{
										$this->a_data[$column_name."_f"] = "";
									}
								}
								else
								{
									$this->a_data[$column_name."_d"] = "";
								}
								break;
							}
						case 'date' :
						case 'datetime' :
						case 'timestamp' :
							{
								if (($this->a_data[$column_name."_d"]!= "") and PrepaDate($this->a_data[$column_name."_d"]))
								{
									if ($this->a_data[$col_option_query] < 6)
									{
										$this->a_data[$column_name] = $this->a_data[$column_name] + 0;

										switch(strlen($this->a_data[$column_name."_d"]))
										{
											case 8	 : $QUERY_COMPARE_format_date = "DATE_FORMAT(".$column->QUERY_COMPARE.", '%y-%m-%d')"; break;
											case 10	 : $QUERY_COMPARE_format_date = "DATE_FORMAT(".$column->QUERY_COMPARE.", '%Y-%m-%d')"; break;
											case 16	 : $QUERY_COMPARE_format_date = "DATE_FORMAT(".$column->QUERY_COMPARE.", '%Y-%m-%d %H:%i')"; break;
											case 19	 : $QUERY_COMPARE_format_date = "DATE_FORMAT(".$column->QUERY_COMPARE.", '%Y-%m-%d %H:%i:%s')"; break;
										}

										$where_query_col = " AND ".$QUERY_COMPARE_format_date.$this->option_query('int',$this->a_data[$col_option_query]).PrepaDate($this->a_data[$column_name."_d"]);
									}
									else if (PrepaDate($this->a_data[$column_name."_f"].' 23:59:59'))
									{
										if ($this->a_data[$column_name."_f"] != "")
										$where_query_col = " AND ".$column->QUERY_COMPARE." BETWEEN (".PrepaDate($this->a_data[$column_name."_d"]).") AND (".PrepaDate($this->a_data[$column_name."_f"].' 23:59:59').")";
									}
									else
									{
										$this->a_data[$column_name."_f"] = "";
									}
								}
								else
								{
									$this->a_data[$column_name."_d"] = "";
								}
								break;
							}
						case 'int':
						case 'smallint':
						case 'tinyint':
						case 'bigint':
						case 'mediumint':
						case 'decimal':
						case 'float':
						case 'real':
							{
								if (is_array($this->a_data[$column_name]))
								{
									if ($this->a_data[$column_name][0] != "")
									$where_query_col = " AND ".$column->QUERY_COMPARE." IN (".implode(",",$this->a_data[$column_name]).")\n";
								}
								else
								{
									if ($this->a_data[$col_option_query] < 6)
									{
										$this->a_data[$column_name] = $this->a_data[$column_name] + 0;

										if (1 == $this->a_data[$col_option_query])
										{
											$where_query_col = $column->QUERY_COMPARE."".$this->option_query('int',$this->a_data[$col_option_query]).$this->a_data[$column_name]."\n";
											$where_query_col = " AND (".$where_query_col." OR ".$column->QUERY_COMPARE." IS NULL)\n";
										}
										else
										{
											$where_query_col = " AND  ".$column->QUERY_COMPARE."".$this->option_query('int',$this->a_data[$col_option_query]).$this->a_data[$column_name]."\n";
										}

									}
									else
									{
										$tab_val = explode(";",$this->a_data[$column_name]);
										$tab_val[0] = $tab_val[0] + 0;
										$tab_val[1] = $tab_val[1] + 0;
										$where_query_col = " AND ".$column->QUERY_COMPARE." BETWEEN ".$tab_val[0]." AND  ".$tab_val[1]."\n";
									}
								}
								break;
							}

						case "set":
						case "enum":
						case "char":
						case "varchar":
						case "string":
						case "blob":
						case 'tinytext' :
						case 'text' :
						case 'mediumtext' :
						case 'longtext' :
							{

								$flag_for_null = 0;
								$opt_not = '';
								switch ($this->a_data[$col_option_query])
								{
									case 1:
										{
											$flag_for_null = 1;
											$opt_not = "NOT";
										}
									case 0: $pourcent_1="%"; $pourcent_2="%" ;break;
									case 3: $opt_not = "NOT";
									case 2: $pourcent_1=""; $pourcent_2="%" ;break;
									case 5: $opt_not = "NOT";
									case 4: $pourcent_1="%"; $pourcent_2="" ;break;
									case 7:
										{
											$flag_for_null = 1;
											$opt_not = "NOT";
										}
									case 6: $pourcent_1=""; $pourcent_2="" ;break;
								}
								//					$where_query_col = " UPPER(".$column->QUERY_COMPARE.") ".$opt_not." LIKE UPPER('".Db_Escape_String($pourcent_1.htmlPrepaTexte($this->a_data[$column_name]).$pourcent_2)."')\n";
								$where_query_col = " UPPER(".$column->QUERY_COMPARE.") ".$opt_not." LIKE UPPER('".Db_Escape_String($pourcent_1.$this->a_data[$column_name].$pourcent_2)."')\n";
								if (1 == $flag_for_null)
								{
									$where_query_col = " AND (".$where_query_col." OR ".$column->QUERY_COMPARE." IS NULL)\n";
								}
								else
								{
									$where_query_col = " AND".$where_query_col;
								}
							}
							break;

					}
				}
		}

		return $where_query_col;
	}


	public function html_Field ($column_name,$affichage_par_defaut=FALSE)
	{
		$html = '';

		if(!$this->entite)
		{
			$this->entite = $this->a_dataQuery[0];
		}
		$entite = $this->entite;

		$column = $this->a_langField_default[$column_name];

		// prise en compte de la valeur id_'nomtable' à un pour les valeurs de colonnes FREE_
		// création du nom de la colonne ID si nomchamp commence par NOM_
		if ((mb_strpos($column_name,'NOM_') === 0))
		{
			$columnNameId = 'ID_'.substr($column_name,4);
			// si égale 1 affichage de la valeur de la colonne FREE_ si elle existe
			if (isset($entite->{$columnNameId})
			and ($entite->{$columnNameId} == 1)
			and property_exists($entite,'FREE_'.$columnNameId))
			{
				$entite->{$column_name} = $entite->{'FREE_'.$columnNameId};
			}
		}


		if ($affichage_par_defaut)
		{
			$html .= "
		<tr>
		<td class='cfgch' align=right valign=top nowrap width=".$column->TITRE_TDWIDTH.">";

			$html .= $column->TITRE_CHAMP."&nbsp;:&nbsp;";

			$html .= "
		</td>
		<td width='100%' class='cfgch' valign=top >";
		}
		if ((mb_strpos($column_name,'NOM_') === 0))
		{
			$emailForNom = 'EMAIL_'.substr($column_name,4);
		}
		else
		{
			$emailForNom = 'EMAIL_';
		}
		//		if (!empty($entite->{$column_name})) // attention a la valeur 0

		// traduction globales des constantes

		if (!empty($entite->{$column_name}))
		{
			$entite->{$column_name} = TransConstante($entite->{$column_name});
		}

		$valueAff = '';

		if ("" != ($entite->{$column_name}.""))
		{
			$valueAff = $entite->{$column_name};
		}
		else if (issetNotEmpty($this->a_data[$column_name]))
		{
			$valueAff = $this->a_data[$column_name];
		}
		else {
			$valueAff = '';
		}



		if ("" != $valueAff)
		{

			if (isset($column->DATA_TYPE) and in_array($column->DATA_TYPE,array('date','datetime','timestamp'))
			and ($valueAff != saisieObligatoire()))
			{
				$valueAff = fr_date($valueAff,$column->DATA_TYPE);
			}
			// cas d'une adresse http
			if ((mb_strpos($valueAff,'http://') === 0)
			and ($column_name!='LINK_CURL_CRON')
			and ($column_name!='FILENAME_JOIN')
			)
			{
				$html .= makeUrl($valueAff);
			}
			// cas d'une adresse mail
			else if (mb_strpos($column_name,'EMAIL_') === 0)
			{
				$html .= "<A HREF='mailto:".$valueAff."'>".NlToBrWordWrap($valueAff)."</a>";
			}
			// cas d'un bouton meteo
			else if (mb_strpos($column_name,'METEO_') === 0)
			{
				$html .= bMeteo($valueAff,false);
			}
			// cas sans wrap ni htmlspecialchar
			else if ((mb_strpos($column_name,'MSG') === 0) or($valueAff == saisieObligatoire()))
			{
				$html .= $valueAff;
			}
			else
			{
				$html .= NlToBrWordWrap($valueAff);
			}
		}
		else
		{
			$html .= '&nbsp;';
		}

		if (isset($column->INFO_CHAMP) and !empty($column->INFO_CHAMP))
		{
			$html .= "&nbsp;".$column->INFO_CHAMP;
		}
		affPopupInfo($column_name);

		if ($affichage_par_defaut)
		{
			$html .= "
		</td>
		</tr>\n";
		}
		return $html;
	}

	public function html_Field_Modif ()
	{
		$html = '';

		if(!$this->entite)
		{
			$this->entite = $this->a_dataQuery[0];
		}
		$entite = $this->entite;

		if (isset($entite->NOM_MODIF_USER) and isset($entite->TSMP_MODIF_USER)) {
			$html .= '
			<table border=0 width=100% cellpadding=2 cellspacing=2>
			<tr>
			<td class="gris" align="right" valign="top" nowrap>' . '
			Modifié par '.$entite->NOM_MODIF_USER.'&nbsp;' . '
			le '.fr_date($entite->TSMP_MODIF_USER,'datetime') . '
			</td>
			</tr>
			</table>
			';
		}
		return $html;

	}

	// ------------------------------=======================--------------------------------
	// fonction d'affichage des champs externe à une table principale en vue de modification
	public function html_Field_Edit_Ext ($column_name,$aff_column_name,$affichage_par_defaut=FALSE,$obligatoire=null,$addEtoileRouge=FALSE,$multiple=0)
	{
		$html = '';

		if(!$this->entite)
		{
			$this->entite = $this->a_dataQuery[0];
		}
		$entite = $this->entite;


		$column = $this->a_langField_default[$column_name];
		$aff_column = $this->a_langField_default[$aff_column_name];

		if (!is_null($obligatoire)) {
			if ($obligatoire === TRUE) $column->IS_NULLABLE = 'NO';
			else  $column->IS_NULLABLE = 'YES';
		}
		/**
		 * utilisation de Bdo_Cfg::schema() pour eviter le chargement du schema dans l'objet
		 */
		$schema = Bdo_Cfg::schema();

		$aff_column->COLUMN_NAME = $schema->verifAffNomChamp($aff_column->COLUMN_NAME);

		// ! : rajout empty pour admin/edit
		if ((!isset($this->a_data[$column_name]) or (empty($this->a_data[$column_name]))) and is_object($entite) and isset($entite->$column_name))
		$this->a_data[$column_name] = $entite->$column_name;

		if ($affichage_par_defaut)
		{
			$html .= "<tr>\n";
			$html .= "<td class='cfgch' align=right valign=top nowrap width=".$column->TITRE_TDWIDTH.">".$aff_column->TITRE_CHAMP."&nbsp;";
			if (('NO' == $column->IS_NULLABLE) or ($addEtoileRouge)) $html .= "<font class='info_red'>*</font>";

			$html .= ":&nbsp;</td>\n";
			$html .= "<td width='100%' class='cfgch' valign=top nowrap>";
		}


		$onchange = "";
		$onchangeFree = "";

		// recherche de la colonne FREE_ pour la saisie libre
		//property_exists($entite,'FREE_'.$column_name)
		if (isset($this->a_langField_default["FREE_".$column_name]))
		{
			$onchange = "div=getObj('div_".$column_name."_1'); if(this.options[this.selectedIndex].value==1) {div.style.display = 'block'}else{div.style.display = 'none'}";
			$onchangeFree = 1;
		}


		switch ($column->COLUMN_NAME)
		{
			case ("ID_ACL_RESOURCE_ETAT") :  $order_by = " ORDER BY ORDRE_ETAT"; break;
			case ("ID_WORK_ETAT") :  $order_by = " ORDER BY ORDRE_WORK_ETAT"; break;
			case ("ID_MIGRATION") :  $onchange = "div=getObj('div_id_migration_1'); if(this.options[this.selectedIndex].value==2) {div.style.display = 'block'}else{div.style.display = 'none'}"; break;

			default :
				if ($affichage_par_defaut != 2) $order_by = "ORDER BY ".$aff_column->ORG_COLUMN_NAME;
				else $order_by = "ORDER BY ".$column->ORG_COLUMN_NAME;

		}

		if(($column->COLUMN_NAME == 'ID_CX_LEVEL') and user::maxLevel(8))
		$order_by = " AND ID_CX_LEVEL<>9 ".$order_by;

		if (!empty($onchange))
		{
			$onchange = "onChange=\"".$onchange."\"";
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

		$htmlSelect = "<select name='".$column_name.$name_multi_champ."' ".$multi_select." class=cfg ".$onchange.">";
		if ('NO' != $column->IS_NULLABLE) {
			$htmlSelect .= "<option value=''>".LANG_VOTRECHOIX."</OPTION>\n";
		}

		switch($column->COLUMN_NAME)
		{
			case 'ID_WORKFLOW' :
				{
					$query = "
					SELECT 
					wf_workflow.ID_WORKFLOW,
					wf_workflow.NOM_WORKFLOW".$_SESSION['ID_LANG']." as NOM_WORKFLOW,
					acl_resource.ID_ACL_RESOURCE,
					acl_resource.CODE_ACL_RESOURCE,
					acl_resource.NOM_ACL_RESOURCE
					FROM
					wf_workflow
					INNER JOIN acl_resource USING(ID_ACL_RESOURCE)
					".(issetNotEmpty($this->a_data['CODE_ACL_RESOURCE']) ? "WHERE acl_resource.CODE_ACL_RESOURCE='".$this->a_data['CODE_ACL_RESOURCE']."'" : "" ) ."
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
			case 'ID_CHANTIER' :
			case 'ID_CHANTIER_NEXT' :
				{
					$dbSelectChantier = rng_chantier::dbSelectChantierActif();

					$a_chantierByFamille = array();
					$a_familleChantier = array();

					foreach ($dbSelectChantier->a_dataQuery as $obj)
					{
						$a_chantierByFamille[$obj->ID_CHANTIER_FAMILLE][$obj->ID_CHANTIER] = $obj->NOM_CHANTIER;
						$a_familleChantier[$obj->ID_CHANTIER_FAMILLE] = $obj->NOM_CHANTIER_FAMILLE;
					}
					$decalage = 0;


					foreach ($a_familleChantier as $id_chantier_famille=>$nom_chantier_famille)
					{

						$htmlSelect .= "<optgroup label=\"".$nom_chantier_famille."\">";
						$decalage += 2;

						foreach ($a_chantierByFamille[$id_chantier_famille] as $id_chantier=>$nom_chantier) {
							$select_option = "";
							if ($this->a_data[$column_name] == $id_chantier) $select_option = "SELECTED";
							$htmlSelect .= "<option value='".$id_chantier."' $select_option>".AffDecalageOption($decalage)."".$nom_chantier."</OPTION>\n";
						}

						$decalage -= 2;
						$htmlSelect .= "</optgroup>";
					}

					break;
				}

			default :
				{
					$query = "
					SELECT $column->ORG_COLUMN_NAME,
					$aff_column->ORG_COLUMN_NAME
					FROM $aff_column->ORG_TABLE_NAME 
					WHERE 1 ".$order_by
					;

					$resultat = Db_query($query);
					while ($obj = Db_fetch_object($resultat))
					{
						$select_option = "";

						if (is_array($this->a_data[$column_name]))
						{
							if (in_array($obj->{$column->ORG_COLUMN_NAME},$this->a_data[$column_name])) $select_option = "SELECTED";
						}
						else if ($this->a_data[$column_name] == $obj->{$column->ORG_COLUMN_NAME})
						{
							$select_option = "SELECTED";
						}

						$htmlSelect .= "<option value='".$obj->{$column->ORG_COLUMN_NAME}."' $select_option>".TransConstante($obj->{$aff_column->ORG_COLUMN_NAME})."</OPTION>\n";
					}
					break;
				}

		}
		$htmlSelect .= "</select>";

		Db_free_result($resultat);


		$html .= $htmlSelect;

		// $html .=_pre($query);
		if (0 < $multiple)
		{
			$html .= "<br />"."( ".LANG_USECTRLCLIC." )";
		}

		if (isset($aff_column->INFO_CHAMP) and !empty($aff_column->INFO_CHAMP))
		{
			$html .= "&nbsp;".$aff_column->INFO_CHAMP;
		}
		affPopupInfo($column->COLUMN_NAME);

		if ($affichage_par_defaut)
		{
			$html .= "</td>\n";
			$html .= "</tr>\n";
		}

		if (!empty($onchangeFree))
		{
			if (!isset($this->a_data[$column_name])
			or ($this->a_data[$column_name] == 1))
			{
				$display = 'block';
			}
			else
			{
				$display = 'none';
			}

			$html .= "<tr><td colspan=2><div id='div_".$column_name."_1' style='display:".$display."'>
			<table align=center border=0 cellpadding=0 cellspacing=0 width=100%>
			";
			$html .= $this->html_Field_Edit("FREE_".$column_name,1,false);
			$html .= "</table></div>
			</td></tr>";	

		}
		return $html;

	}
	// ------------------------------=======================--------------------------------

	// ------------------------------=======================--------------------------------
	// fonction d'affichage des champs interne à une table principale en vue de modification

	//	function html_Field_Edit ($entite, $column,$affichage_par_defaut=FALSE,$obligatoire=FALSE,$maxSizeInput=40)
	public function html_Field_Edit ($column_name,$affichage_par_defaut=FALSE,$obligatoire=FALSE,$maxSizeInput=40)
	{
		$html = '';

		if(!$this->entite)
		{
			$this->entite = $this->a_dataQuery[0];
		}
		$entite = $this->entite;

		$column = $this->a_langField_default[$column_name];

		if ($obligatoire == TRUE) $column->IS_NULLABLE = "NO";

		// ! : rajout empty pour admin/edit
		if ((!isset($this->a_data[$column_name]) or (empty($this->a_data[$column_name]))) and is_object($entite) and isset($entite->$column_name))
		$this->a_data[$column_name] = $entite->$column_name;

		//$html .= "<INPUT type=hidden name='flagpost_".$column->COLUMN_NAME."' value='1'>";
		if ($affichage_par_defaut)
		{
			$html .= "<tr>\n";
			$html .= "<td class='cfgch' align=right valign=top nowrap width=".$column->TITRE_TDWIDTH.">".$column->TITRE_CHAMP."&nbsp;";
			if ('NO' == $column->IS_NULLABLE) $html .= "<font class='info_red'>*</font>";

			$html .= ":&nbsp;</td>\n";
			$html .= "<td width='100%' class='cfgch' valign=top nowrap>";
		}

		if (isset($column->TAB_CHECK_VALUE))
		{
			if ($column->DATA_TYPE == 'set')
			{
				$multi_select="size=5".$multiple." multiple ";
				$name_multi_champ = "[]";
			}
			else
			{
				$multi_select = '';
				$name_multi_champ = '';
			}


			$html .= "<select name='".$column->COLUMN_NAME.$name_multi_champ."' ".$multi_select." class=cfg >";
			if ('NO' != $column->IS_NULLABLE)
			{
				$html .= "<option value=''>".LANG_NONRENSEIGNE."</OPTION>\n";
				//				$html .= "<option value='NULL'>".LANG_NONRENSEIGNE."</OPTION>\n";
			}

			foreach ($column->TAB_CHECK_VALUE as $val_check)
			{
				$select_option = "";
				switch ($column->DATA_TYPE)
				{
					case 'enum' : {
						if ($this->a_data[$column_name] == $val_check) {
							$select_option = "SELECTED";
						}
						break;
					}
					case 'set' : {
						$a_tt = array();
						if (!empty($this->a_data[$column_name]))
						{
							$a_tt = explode(',',$this->a_data[$column_name]);
						}
						if (in_array($val_check, $a_tt))
						{
							$select_option = "SELECTED";
						}


						break;
					}
				}


				$html .= "<option value='$val_check' $select_option>".TransConstante($val_check)."</OPTION>\n";
			}
			$html .= "</select>";
		}
		else
		{
			if (isset($column->DATA_TYPE))
			switch ($column->DATA_TYPE)
			{
				case 'tinyint' :
				case 'mediumint' :
				case 'smallint' :
				case 'int' :
				case 'bigint' :
				case 'decimal' :
				case 'float':
				case 'real' :
					{
						$html .= "<INPUT type=text name='$column->COLUMN_NAME' value='".htmlPrepaTexte($this->a_data[$column_name])."' size=15 class=cfg onFocus=\"this.className='cfg_on'\" onBlur=\"this.className='cfg'\">";
						break;
					}
				case 'date' :
				case 'datetime' :
				case 'timestamp' :
					{

						if ($column->DATA_TYPE == 'date')
						{
							switch ($_SESSION['ID_LANG'])
							{
								case '_FR' :
									$infoFormat = " <i class=gris>JJ/MM/AAAA</i>";
									$formatDate = '%d/%m/%Y';
									break;
								default :
									$infoFormat = " <i class=gris>YYYY-MM-DD</i>";
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
									$infoFormat = " <i class=gris>JJ/MM/AAAA 00:00:00</i>";
									$formatDate = '%d/%m/%Y %H:%M:%S';
									break;
								default :
									$infoFormat = " <i class=gris>YYYY-MM-DD 00:00:00</i>";
									$formatDate = '%Y-%m-%d %H:%M:%S';
							}
							$size = "size=19 maxlength=19";
							$showsTime = 'true';
						}



						$html .= "<input type=text id='$column->COLUMN_NAME' name='$column->COLUMN_NAME' value='".fr_date($this->a_data[$column_name],$column->DATA_TYPE)."' ".$size." class=cfg onFocus=\"this.className='cfg_on'\" onBlur=\"this.className='cfg'\">
						<img id='".$column->COLUMN_NAME."_bn' SRC='".BDO_URL_ROOT."image/ico_calendrier.gif' alt='Choisir une date' border=0 align='absmiddle' style='cursor:hand'>" . $infoFormat;

						$html .= "
<script type=text/javascript>
Calendar.setup({
firstDay :    1,
showsTime : ".$showsTime." ,
inputField     :    '".$column->COLUMN_NAME."',      // id of the input field
ifFormat       :    '".$formatDate."',       // format of the input field
button         :    '".$column->COLUMN_NAME."_bn',   // trigger for the calendar (button ID)
singleClick    :    true,           // double-click mode
step           :    1                // show all years in drop-down boxes (instead of every other year as default)
});
</script>";

						break;
					}
				case 'time' :
					{
						$html .= "<INPUT type=text name='$column->COLUMN_NAME' value='".substr($this->a_data[$column_name],0,5)."' size=5 maxlength=5 class=cfg onFocus=\"this.className='cfg_on'\" onBlur=\"this.className='cfg'\"> <i class=gris>hh:mm</i>";
						break;
					}
				case 'char' :
				case 'varchar' :
				case 'string' :
					{

						if ($column->CHARACTER_MAXIMUM_LENGTH < $maxSizeInput) $size = $column->CHARACTER_MAXIMUM_LENGTH;
						else $size = $maxSizeInput;

						if (isset($column->EXTRA_CHAMP) and ('password' == $column->EXTRA_CHAMP))
						{
							$size = 10;
							$column->CHARACTER_MAXIMUM_LENGTH = 10;
							$this->a_data[$column_name] = '';
						}

						$html .= "<INPUT type=text name='$column->COLUMN_NAME' value='".htmlPrepaTexte($this->a_data[$column_name])."' size=".$size." maxlength=".$column->CHARACTER_MAXIMUM_LENGTH." class=cfg onFocus=\"this.className='cfg_on'\" onBlur=\"this.className='cfg'\">";

						break;
					}
				case 'tinytext' :
				case 'text' :
				case 'mediumtext' :
				case 'longtext' :
				case 'blob' :
					{
						if ($column->CHARACTER_MAXIMUM_LENGTH < $maxSizeInput) $size = $column->CHARACTER_MAXIMUM_LENGTH;
						else $size = $maxSizeInput;

						if (strpos($column->COLUMN_NAME,'MSG_') === 0)
						{
							include_once BDO_DIR."public/script/fckeditor/fckeditor.php";
							$html .= '</td><td>&nbsp;</td></tr><tr><td colspan=2>';
							$sBasePath = BDO_URL.'script/fckeditor/' ;
							$oFCKeditor = new FCKeditor($column->COLUMN_NAME) ;
							$oFCKeditor->Config['DefaultLanguage']		= strtolower(substr($_SESSION['ID_LANG'],1));
							$oFCKeditor->BasePath	= $sBasePath ;
							$oFCKeditor->Config['SkinPath'] = $sBasePath . 'editor/skins/office2003/';
							$oFCKeditor->Config['EditorAreaCSS'] = $sBasePath . 'editor/css/fck_editorarea_selfcare.css';
							$oFCKeditor->ToolbarSet = 'SelfCare';
							$oFCKeditor->Height = 270;
							$oFCKeditor->Value		= $this->a_data[$column_name] ;
							$html .= $oFCKeditor->CreateHtml() ;
						}
						else
						{
							$html .= "<TEXTAREA name='".$column->COLUMN_NAME."' cols=70 rows=7 class=cfg onFocus=\"this.className='cfg_on'\" onBlur=\"this.className='cfg'\">".htmlPrepaTexte($this->a_data[$column_name])."</TEXTAREA>";
						}
						break;
					}
				default : $html .= $column->DATA_TYPE;
			}
		}

		if (isset($column->INFO_CHAMP) and !empty($column->INFO_CHAMP))
		{
			$html .= "&nbsp;".$column->INFO_CHAMP;
		}
		affPopupInfo($column->COLUMN_NAME);

		if ($affichage_par_defaut)
		{
			$html .= "</td>\n";
			$html .= "</tr>\n";
		}
		return $html;
	}
	// ------------------------------=======================--------------------------------

	// fonction d'affichage des champs avec lien
	//	function Affich_Champ_Lien ($entite,$champ,$lien,$affichage_par_defaut=FALSE)
	public function html_Field_Link ($column_name,$lien,$affichage_par_defaut=FALSE)
	{
		$html = '';
		if(!$this->entite)
		{
			$this->entite = $this->a_dataQuery[0];
		}
		$entite = $this->entite;

		$column = $this->a_langField_default[$column_name];

		$html .= "
		<tr>
		<td class='cfgch' align=right valign=top nowrap width=".$column->TITRE_TDWIDTH.">";

		$html .= $column->TITRE_CHAMP."&nbsp;:&nbsp;";

		$html .= "
		</td>
		<td width='100%' class='cfgch' valign=top nowrap>";

		$html .= '<a class="cfg" href="'.$lien.'">'.nl2br($entite->{$column_name}).'</a>';

		$html .= "
		</td>
		</tr>\n";

		return $html;
	}



	public function option_query($type, $key=null)
	{
		//-------------------------------------------------------------------------------
		// definition des options de recherche par type d'entite

		switch ($type) {
			case 'decimal' :
			case 'float' :
			case 'bigint' :
			case 'smallint' :
			case 'tinyint' :
			case 'mediumint' :
			case 'real' :
			case 'int' : {
				$a_val = array(
				"=",
				"<>",
				"<=",
				">=",
				"<",
				">",
				"a;b (entre a et b)",
				);
				break;
			}
			case 'datetime' :
			case 'date' :
			case 'time' :
			case 'timestamp' : {
				$a_val = array(
				LANG_SEARCHOPTDATE1, // =
				LANG_SEARCHOPTDATE2, // <>
				LANG_SEARCHOPTDATE3, // <=
				LANG_SEARCHOPTDATE4, // >=
				LANG_SEARCHOPTDATE5, // <
				LANG_SEARCHOPTDATE6, // >
				LANG_SEARCHOPTDATE7, // a <= ... <= b
				);
				break;
			}
			case 'string' :
			case 'varchar' :
			case 'char' :
			case 'tinytext' :
			case 'text' :
			case 'mediumtext' :
			case 'longtext' :
			case 'blob' :
			case 'enum' :
			case 'set' : {
				$a_val = array(
				LANG_SEARCHOPTMOT1,
				LANG_SEARCHOPTMOT2,
				LANG_SEARCHOPTMOT3,
				LANG_SEARCHOPTMOT4,
				LANG_SEARCHOPTMOT5,
				LANG_SEARCHOPTMOT6,
				LANG_SEARCHOPTMOT7,
				LANG_SEARCHOPTMOT8,
				);
				break;
			}
		}

		if (!is_null($key)) {
			return $a_val[$key];
		}
		else {
			return $a_val;
		}


	}
}


