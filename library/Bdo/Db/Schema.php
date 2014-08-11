<?php

/**
 *
 * @author laurent
 *        
 */
class Bdo_Db_Schema {
	
	/**
	 */
	private $forceCache=true;

	public $cache='';
	public $schema= BDO_DB_SID;

	public $dbColumn = array();
	public $dbConstraint = array();

	public $a_langField = array();

	public function __construct($schema = null)
	{
		if (!empty($schema)) {
			$this->schema = $schema;
		}

		$this->load();
		$this->loadLang();

	}
	private function load()
	{
		if (BDO_CACHE_ENABLED or $this->forceCache) {

			$this->cache = new Bdo_Cache ($this->schema.'_schema.serial');

			if ($a_cache = $this->cache->load()) {

				$this->dbColumn = $a_cache['dbColumn'];
				$this->dbConstraint = $a_cache['dbConstraint'];

				return true;
			}
		}
/*
		$query  = "
		SELECT
		`TABLE_NAME`,
		  `COLUMN_NAME`,
		  `ORDINAL_POSITION`,
		  `COLUMN_DEFAULT`,
		  `IS_NULLABLE`,
		  `DATA_TYPE`,
		  `CHARACTER_MAXIMUM_LENGTH`,
		  `CHARACTER_OCTET_LENGTH`,
		  `NUMERIC_PRECISION`,
		  `NUMERIC_SCALE`,
		  `CHARACTER_SET_NAME`,
		  `COLLATION_NAME`,
		  `COLUMN_TYPE`,
		  `COLUMN_KEY`,
		  `EXTRA`,
		  `PRIVILEGES`,
		  `COLUMN_COMMENT`
		FROM `information_schema`.`COLUMNS`
		WHERE `TABLE_SCHEMA` IN ('".$this->schema."')
		AND `TABLE_NAME` NOT LIKE('faq_%')";
		*/
		$query  = "
		SELECT 
		`TABLE_NAME`,
		  `COLUMN_NAME`,
		  `ORDINAL_POSITION`,
		  `COLUMN_DEFAULT`,
		  `IS_NULLABLE`,
		  `DATA_TYPE`,
		  `CHARACTER_MAXIMUM_LENGTH`,
		  `COLUMN_TYPE`,
		  `COLUMN_KEY`,
		  `EXTRA`
		FROM `information_schema`.`COLUMNS` 
		WHERE `TABLE_SCHEMA` IN ('".$this->schema."')
		AND `TABLE_NAME` NOT LIKE('faq_%')";

		$resultat = Db_query($query);
		while ($obj = Db_fetch_object($resultat))
		{
			if (in_array($obj->DATA_TYPE, array('enum','set')))
			{
				$valeurs = preg_replace("#(?:enum|set)\('([^\)].*)'\)$#i", "$1", $obj->COLUMN_TYPE);
				$obj->TAB_CHECK_VALUE = explode("','", $valeurs);
			}

			// attention type [float unsigned] au lieu de [float]
			if ($obj->DATA_TYPE == 'float unsigned') $obj->DATA_TYPE = 'float';

			$this->dbColumn[$obj->TABLE_NAME][$obj->COLUMN_NAME] = $obj;
		}
		Db_free_result($resultat);

		$query  = "
		SELECT 
			`KEY_COLUMN_USAGE`.`TABLE_NAME` , 
			`KEY_COLUMN_USAGE`.`COLUMN_NAME` , 
			`KEY_COLUMN_USAGE`.`CONSTRAINT_NAME` ,
			`TABLE_CONSTRAINTS`.`CONSTRAINT_TYPE`, 
			`COLUMNS`.`IS_NULLABLE`,
			`KEY_COLUMN_USAGE`.`REFERENCED_TABLE_NAME` , 
			`KEY_COLUMN_USAGE`.`REFERENCED_COLUMN_NAME` 
		FROM 
			`information_schema`.`TABLE_CONSTRAINTS` 
			INNER JOIN `information_schema`.`KEY_COLUMN_USAGE` USING ( `TABLE_NAME` , `CONSTRAINT_NAME` , `CONSTRAINT_SCHEMA` ) 
			LEFT JOIN `information_schema`.`COLUMNS` ON  
				`KEY_COLUMN_USAGE`.`TABLE_NAME`=`COLUMNS`.`TABLE_NAME`
				AND `KEY_COLUMN_USAGE`.`COLUMN_NAME`=`COLUMNS`.`COLUMN_NAME`
				AND `KEY_COLUMN_USAGE`.`TABLE_SCHEMA`=`COLUMNS`.`TABLE_SCHEMA`
		WHERE 
			`TABLE_CONSTRAINTS`.`CONSTRAINT_SCHEMA` IN ('".$this->schema."')
			AND `KEY_COLUMN_USAGE`.`TABLE_NAME` NOT LIKE('faq_%')";
		$resultat = Db_query($query);
		while ($obj = Db_fetch_object($resultat))
		{
			$this->dbConstraint[$obj->TABLE_NAME][$obj->CONSTRAINT_NAME][$obj->COLUMN_NAME] = $obj;
		}
		Db_free_result($resultat);


		if (BDO_CACHE_ENABLED or $this->forceCache) {
			$a_cache = array(
			"dbColumn" => $this->dbColumn,
			"dbConstraint" => $this->dbConstraint,
			);

			$this->cache->save($a_cache);
		}
	}

	/**
	 * charge la traduction et les options de la table lang_field
	 */
	public function loadLang()
	{
		$resultat = Db_query("
		SELECT
		`lang_field`.`COLUMN_NAME`,
		`lang_field`.`TITRE_CHAMP`,
		`lang_field`.`EXTRA_CHAMP`,
		`lang_field`.`INFO_CHAMP`,
		`lang_field`.`DESC_CHAMP`
		FROM `lang_field`
		WHERE `lang_field`.`ID_LANG`='".$_SESSION['ID_LANG']."'
		");

		$this->a_langField = array();
		while ($obj = Db_fetch_object($resultat))
		{
			$this->a_langField[$obj->COLUMN_NAME] = $obj;
		}

		foreach ($this->dbColumn as $table_name=>$a_columnTable)
		{
			foreach ($a_columnTable as $column_name=>$obj)
			{
				$lang = substr($column_name,-3);
				if (isset($_SESSION['CFG_A_LANG'][$lang])) {
					$column_name_lang = substr($column_name,0,-3);
				}
				else {
					$column_name_lang = $column_name;
				}

				if (isset($this->a_langField[$column_name_lang]))	{
					$this->dbColumn[$table_name][$column_name]->TITRE_CHAMP = $this->a_langField[$column_name_lang]->TITRE_CHAMP;
					$this->dbColumn[$table_name][$column_name]->EXTRA_CHAMP = $this->a_langField[$column_name_lang]->EXTRA_CHAMP;
					$this->dbColumn[$table_name][$column_name]->INFO_CHAMP = $this->a_langField[$column_name_lang]->INFO_CHAMP;
					$this->dbColumn[$table_name][$column_name]->DESC_CHAMP = $this->a_langField[$column_name_lang]->DESC_CHAMP;
				}
				else {
					$this->dbColumn[$table_name][$column_name]->TITRE_CHAMP = $column_name;
				}
			}
		}

	}

	public function is_table($table_name)
	{
		if (isset($this->dbColumn[$table_name]) and !empty($this->dbColumn[$table_name])) {
			return true;
		}
		else {
			return false;
		}
	}

	public function is_column($column_name,$table_name=null)
	{
		if ($table_name and isset($this->dbColumn[$table_name][$column_name])) {
			return true;
		}
		else {
			foreach($this->dbColumn as $a_column)
			{
				if (isset($a_column[$column_name]))	{
					return true;
				}
			}
		}
		return false;
	}

	public function getColumn($column_name,$table_name=null)
	{
		if ($table_name and isset($this->dbColumn[$table_name][$column_name])) {
			return $this->dbColumn[$table_name][$column_name];
		}
		else {
			foreach($this->dbColumn as $a_column)
			{
				if (isset($a_column[$column_name]))	{
					return $a_column[$column_name];
				}
			}
		}
		return false;
	}

	// vérification du nom de champ affichage pour la langue
	function verifAffNomChamp($column_name)
	{
		if (stristr($column_name, '.'))
		{
			$a = explode('.',$column_name);
			if (2 == count($a))
			{
				$table_name = $a[0];
				$column_name = $a[1];
			}
			else {
				exit('verifAffNomChamp() : bad argument type!');
			}
		}
		else
		{
			$table_name = '';
		}

		if ($this->is_column($column_name.$_SESSION['ID_LANG'],$table_name))
		{
			$column_name .= $_SESSION['ID_LANG'];
		}

		return ($table_name ? $table_name.'.' : '').$column_name;
	}


	// recherche des infos dans lang_field pour une colonne
	public function ColInfoChamp($column)
	{
		$column->TITRE_CHAMP = $column->COLUMN_NAME ;

		if ($obj = $this->a_langField[$column->COLUMN_NAME])
		{
			$obj->LIB_CHAMP = $obj->TITRE_CHAMP;
			$obj->TITRE_CHAMP = htmlPrepaTexte($obj->TITRE_CHAMP);

			if ($obj->DESC_CHAMP != '')
			{
				cfg::getVar('view')->a_htmlEndFile[] = "<div ID='DESC_CHAMP_".$obj->COLUMN_NAME."_titre' STYLE='display:none'>".$obj->TITRE_CHAMP."</div><div ID='DESC_CHAMP_".$obj->COLUMN_NAME."_corps' STYLE='display:none'>".nl2br($obj->DESC_CHAMP)."</div>";
				$obj->TITRE_CHAMP = "<font style='cursor:help' onMouseOver=\"Affiche_Info('".$obj->COLUMN_NAME."','desc','cfg')\" onMouseOut='Cache_Info()'>$obj->TITRE_CHAMP</font>";
			}

			$column->LIB_CHAMP = $obj->LIB_CHAMP ;
			$column->TITRE_CHAMP = $obj->TITRE_CHAMP ;
			$column->INFO_CHAMP = htmlPrepaTexte($obj->INFO_CHAMP) ;
			$column->DESC_CHAMP = htmlPrepaTexte($obj->DESC_CHAMP);
			$column->EXTRA_CHAMP = $obj->EXTRA_CHAMP ;
		}

		$user = Bdo_Cfg::user();
		if (user::minLevel() or $user->isAllowed('admin.modify_champ'))
		{
			$column->TITRE_CHAMP = '<a href="javascript:showLightbox(\'iframe\',\'admin/modify_champ?COLUMN_NAME='.$column->COLUMN_NAME.'\',680,350)">[#]</a>'.$column->TITRE_CHAMP;
			//			$column->TITRE_CHAMP = "<a href='javascript:ModifyChamp(\"".$column->COLUMN_NAME."\")'>[#]</a>".$column->TITRE_CHAMP;
		}
		return $column;
	}
	
	public function searchForeignKey() {
		
	}
	
	public function searchRefDepend() {
		
	}
}

?>