<?php

/**
 *
 * @author laurent
 *        
 */

class Bdo_Debug {
		public $a_exec_time = array();
	
		public $CEcountQuery = 0;
		public $CEtabQuery = array();
		public $CEtabQueryOcc = array();
	
		public $CEinfoVar = array();
	
	
		private static $instance;
	
	
		public function __construct() {
		}
	
		public static function getInstance() {
			if(!isset(self::$instance)) {
				self::$instance = new Bdo_Debug();
			}
			return self::$instance;
		}
	
	
		public static function saveInfoVar($var,$nom_var,$lib='')
		{
			Bdo_Debug::getInstance();
			self::$instance->infoVar[$nom_var][] = (object) array('var'=>$var,"lib"=>$lib);
		}
	
		public static function viewInfoVar()
		{
			Bdo_Debug::getInstance();
	
			$debugInfoVar_div = new Bdo_Onglet('debugInfoVar',"Variables",'cfgonglet');
			$debugInfoVar_div->setWidth('100%');
	
			$html = "
				<div class='cadre1'>;
			        
		<table border=1 cellpadding=3 cellspacing=0 >
		<tr>
		<td >
		<font face=verdana size=1>
		";
			foreach(self::$instance->infoVar as $nom_var=>$a_var)
			{
				foreach($a_var as $o_var)
				{
					$html .= self::AfficheTableauVars($o_var->var,$nom_var,$o_var->lib);
				}
			}
	
			$html .= "
		</font>
		</td>
		</tr>
		</table>
			        </div>
		";
	
	
			$debugInfoVar_div->addElement($html);
			return $debugInfoVar_div->vueOnglet();
		}
	
		public static function AfficheTableauVars($var,$nom_var,$lib)
		{
			$html = "<HR width=100><b>".($lib ? $lib : $nom_var)." :</b>";
			if (!empty($var))
			{
				$html .= self::AfficheVal($var,$nom_var);
			}
			return $html;
		}
	
		public static function AfficheVal($val,$txt_tab)
		{
			$html = '';
			switch (gettype($val))
			{
				case "array" :
					{
						foreach ($val as $key2=>$val2)
						{
							$html .= self::AfficheVal($val2,$txt_tab.'["'.$key2.'"]');
						}
						break;
					}
				case "object" :
					{
						$a_ovars = get_object_vars($val);
						foreach($a_ovars as $key2=>$val2)
						{
							$html .= self::AfficheVal($val2,$txt_tab.'->'.$key2);
						}
						break;
					}
				case "string" : $html .=  "<br />".$txt_tab."= \"".$val."\" (".gettype($val).")"; break;
				default : $html .=  "<br />".$txt_tab."= ".htmlentities($val,ENT_QUOTES)." (".gettype($val).")"; break;
			}
			return $html;
		}
	
		public static function execTime($msg=false)
		{
			Bdo_Debug::getInstance();
	
			$count_exec_time = count(self::$instance->a_exec_time);
	
			if(!$msg) $msg = "Flag Temp n°".$count_exec_time;
	
			self::$instance->a_exec_time[$count_exec_time]['time'] = microtime(true);
			self::$instance->a_exec_time[$count_exec_time]['msg'] = $msg;
		}
	
		public static function affExecTime()
		{
			Bdo_Debug::getInstance();
	
			self::execTime("Visualisation des temps");
	
			$count_exec_time = count(self::$instance->a_exec_time);
	
			$debugExecTime_div = new Bdo_Onglet('debugExecTime',"Temp total d'éxecution = ".round((self::$instance->a_exec_time[($count_exec_time-1)]['time'] - self::$instance->a_exec_time[0]['time']),5),'cfgonglet');
			$debugExecTime_div->setWidth('100%');
	
			$txt = "<div class='cadre1'><U>Calcul des intervales de temps d'éxecution</U>";
			for ($i=1;$i<$count_exec_time;$i++)
			{
				$txt .= "<br />entre ".self::$instance->a_exec_time[$i-1]['msg']." et " . self::$instance->a_exec_time[$i]['msg'] ."
			= ".round((self::$instance->a_exec_time[$i]['time'] - self::$instance->a_exec_time[$i-1]['time']),5);
			}
			$txt .= "</div>";
			$debugExecTime_div->addElement($txt);
			return $debugExecTime_div->vueOnglet();
		}
	
	
		public static function bilanQuery()
		{
			$debugBilanQuery_div = new Bdo_Onglet('debugBilanQuery',self::$instance->countQuery.' requetes executees / '.count(self::$instance->a_query).' requetes uniques','cfgonglet');
			$debugBilanQuery_div->setWidth('100%');
			$txt = '
            <style>
			div.cephenix1 {color:#EEEEEE ; width:700 ; margin:2px ; padding:5px ; font-family:Verdana ; font-size:12px ; font-weight:bold ; background-color:#880000 ; text-align:center}
			div.cephenix2 {color:#000000 ; width:700 ; margin:2px ; padding:2px ; font-family:Verdana ; font-size:11px ; border: 1px solid #880000 ; background-color:#EEEEEE ; text-align:left}
			div.cephenix3 {color:#FFFFFF ; width:700 ; margin:2px ; padding:2px ; font-family:Verdana ; font-size:12px ; font-weight:bold ; border: 1px solid #880000 ; background-color:#DD3C00 ; text-align:left}
			pre.cephenix {color:#000000 ; font-family:Verdana ; font-size:10px ; text-align:left}
			</style>
            ';
	
			foreach (self::$instance->a_query as $idQ=>$a_query)
			{
				$txt .= '<div class=cephenix2><pre class=cephenix>'.$a_query['query'].'</pre>
                <i style="background-color:#DDDDDD">'.$a_query['numrows'].' lignes impactees
                <br />executee en '.round($a_query['exectime'],5).' secondes</i>';
				if (self::$instance->countQueryOcc[$idQ] > 1)
				{
					$txt .= '<div class=cephenix3>Requete executee '.self::$instance->countQueryOcc[$idQ].' fois</div>';
				}
				$txt .= '</div>';
			}
			$debugBilanQuery_div->addElement($txt);
			return $debugBilanQuery_div->vueOnglet();
		}
	
	
		public static function increCountQuery()
		{
			Bdo_Debug::getInstance();
			$this->CEcountQuery++;
		}
	
	
		public static function addQuery($a_requete)
		{
			Bdo_Debug::getInstance();
	
			$idQ = md5($a_requete['query']);
	
			self::$instance->countQuery++;
			if (!isset(self::$instance->countQueryOcc[$idQ])) self::$instance->countQueryOcc[$idQ]=0;
			self::$instance->countQueryOcc[$idQ]++;
			self::$instance->a_query[$idQ] = $a_requete;
		}
	
		public static function viewInclude()
		{
			Bdo_Debug::getInstance();
			$included_files = get_included_files();
	
			$mem = memory_get_usage();
	
			$debugIncludeFile_div = new Bdo_Onglet('debugIncludeFile',count($included_files)." fichiers inclus / ".affFormatByteDown($mem,3,2)." de mémoire utilisée",'cfgonglet');
			$debugIncludeFile_div->setWidth('100%');
			$txt .= "<div class='cadre1'>";
			foreach($included_files as $filename)
			{
				$filename = str_replace ('\\','/',$filename);
				$filename = str_replace (CFG_DIR_ROOT,'',$filename);
	
				$txt .= '<br />'. $filename;
			}
			$txt .= "</div>";
				
			$debugIncludeFile_div->addElement($txt);
			return $debugIncludeFile_div->vueOnglet();
	
		}
	
	
	}

?>