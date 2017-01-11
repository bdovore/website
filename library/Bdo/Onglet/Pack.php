<?php
class Bdo_Onglet_Pack
{
	var $id_pack = 0;
	var $nb_onglet = 0;
	var $abscisseDefaultOnglet = 30;
	var $tab_onglet;
	var $javaScript = '';

	function __construct()
	{
		if (!isset($_SESSION['id_pack_onglet'])) $_SESSION['id_pack_onglet'] = 0;
		$this->id_pack = $_SESSION['id_pack_onglet'];
		$_SESSION['id_pack_onglet']++;

		$this->addJavascript('tab_assoc_div['.$this->id_pack.']= new Array;');
	}

	function addOnglet($onglet)
	{
		$this->tab_onglet[$onglet->nom] = $onglet;

		if (0 == $this->nb_onglet)
		{
			$onglet->setAbscisse($this->abscisseDefaultOnglet);
		}

		$this->addJavascript('
		tab_div['.$onglet->id.']= new Array;
		tab_IdDivByNom["'.$onglet->nom.'"]="'.$onglet->id.'";
		tab_div['.$onglet->id.']["div_name"]="div_'.$onglet->id.'";
		tab_div['.$onglet->id.']["id_view"]="id_vue_'.$onglet->id.'";
		tab_div['.$onglet->id.']["cfg_td_id"]="cfg_td_'.$onglet->id.'";
		tab_div['.$onglet->id.']["id_pack"]='.$this->id_pack.';
		tab_assoc_div['.$this->id_pack.']['.$this->nb_onglet.'] = '.$onglet->id.';
		');

		$this->nb_onglet++;
		return $onglet;
	}

	function initOnglet($nom,$text,$fstyle="cfgonglet")
	{
		return $this->addOnglet(new Bdo_Onglet($nom,$text,$fstyle));
	}

	function addJavascript($jvs)
	{
		$this->javaScript .= "\n".$jvs;
	}

	function affPack()
	{
	    $html = '';

		$flagDisplay= false;
		foreach ($this->tab_onglet as $onglet)
		{
			if ($onglet->display == 'block')
				$flagDisplay= true;
		}
		if (!$flagDisplay)
		{
			reset($this->tab_onglet);
			$firstOnglet = current($this->tab_onglet);
			$firstOnglet->changeDisplay();
		}
		$html .= '
		<form name="packOngletDivSession'.$this->id_pack.'" id="packOngletDivSession'.$this->id_pack.'" action="'.CFG_URL_ROOT.'design/xhr_divonglet" method="POST" onsubmit="ValidFormXhr(this); return false;">
		<input type="hidden" name="ongletPageInclude" value="'.$_SESSION['INCLUDE_PAGE'].'">
		<input type="hidden" name="ongletPageName" value="">
		<div id="ddpackOngletDivSession'.$this->id_pack.'" style="display:none"></div>
		</form>

	  	<table cellSpacing=0 cellPadding=0 border=0>
	  	<tr>
	  	';

		foreach ($this->tab_onglet as $onglet)
		{
			if (0 != $onglet->abscisse)
			$html .= "<td class=".$onglet->style."_line_b><img src='".CFG_URL_ROOT."image/vide1x1.gif' width=".$onglet->abscisse." height=1></td>\n";

			$html .= "
	  		<td>
	  		<div id='onglet_div_".$onglet->id."' style='DISPLAY: block' onClick='javascript:showPref(".$onglet->id.");document.packOngletDivSession".$this->id_pack.".ongletPageName.value=\"".$onglet->nom."\";ValidFormXhr(document.packOngletDivSession".$this->id_pack.")'>
	  			<table border=0 cellpadding=0 cellspacing=0 width=100%>
	  			<tr><td><img src='".CFG_URL_ROOT."image/vide1x1.gif' height=1></td></tr>
	  			<tr><td id='cfg_td_".$onglet->id."' class='".$onglet->css_onglet_out."' onmouseover='ChClassName(this,\"\")' onmouseout='ChClassName(this,\"\")' align=center nowrap>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$onglet->text."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>
	  			</table>
	  		</div>
	  		</td>
	  		";
		}

		$html .= "
  		<td valign=top width=100% class=".$onglet->style."_line_b><img src='".CFG_URL_ROOT."image/vide1x1.gif' width=1 height=1></td>
	  	</tr>
	  	</table>
	  	<table border=0 cellpadding=5 cellspacing=0 width=100%>
	  	<tr>
	  		<td width=100% class='".$onglet->style."_line_rbl'>
	  	";


		foreach($this->tab_onglet as $onglet)
		{
			$html .= "
	  		<input type=hidden name='vue_".$onglet->id."' id='id_vue_".$onglet->id."' value='vue_".$onglet->id."'>
	  		<div id='div_".$onglet->id."' style='DISPLAY:".$onglet->display."'>
	  		";

			$html .= $onglet->affOnglet();

			$html .= "\n</div>\n";
		}
		$html .= "
  		</td>
  	</tr>
  	</table>
  	";
		return $html;
	}

	function blockDiv($nameOnglet)
	{
		foreach ($this->tab_onglet as $onglet)
		{
			if ($onglet->nom == $nameOnglet)
				$onglet->changeDisplay('block');
			else
				$onglet->changeDisplay('none');
		}
	}

	function affJavascript()
	{
		foreach ($this->tab_onglet as $onglet)
		{
			$this->addJavascript('tab_arbo_div["'.$onglet->nom.'"]="'.$onglet->arbo.'";');
		}
		return $this->javaScript;
	}

}
