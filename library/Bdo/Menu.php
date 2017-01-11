<?php

class Bdo_Menu {

	public $user = null;
	public $html = null;
	public $id_menu = null;
	public $orientation = 'mh';

	public $a_item = array();
	public $a_ariane = array();
	public $addAriane = null;
	public $test = null;

	public function __construct($id_menu)
	{
		$this->id_menu = $id_menu;
		$this->user = Bdo_Cfg::user();
	}


	public function __toString()
	{
		return $this->html;
	}

	public function addItem(Bdo_Menu_Lien $lien) {
		$this->a_item[] = $lien;
		return $lien;
	}


	public function addHtml($txt)
	{
		// attention : l'ajout d'un retour chariot change le premier element des li .
		$this->html .= $txt;
	}

	public function ariane($lien)
	{
		$this->a_ariane[] = $lien;
		if ($lien->lienPere)
		{
			$this->ariane($lien->lienPere);
		}
	}

	public function CreaLien(Bdo_Menu_Lien $lien)
	{
		if (!$this->isAllowed($lien)) {
			return false;
		}

		$this->addHtml('<li>');

		if ($lien->link) {

			// recherche pour le fil d'ariane
			if ($lien->link == Bdo_Cfg::getVar('baseAriane')) {
				$this->a_ariane = array();
				$this->ariane($lien);
			}

			$this->addHtml('<a href="'.$lien->link.'" target="'.$lien->target.'">' . $lien->title . '</a>');
		}
		else {
			$this->addHtml($lien->title);
		}

		if ($lien->getFils()) {
			$this->addHtml('<ul>');
			$this->addHtml('<!--[if lte IE 6.5]><iframe class=tom></iframe><![endif]-->');
			foreach($lien->getFils() as $lienFils) {
				$this->CreaLien($lienFils);
			}
			$this->addHtml('</ul>');
		}

		$this->addHtml('</li>');
	}


	public function generate()
	{
		$this->html= '<div id="ancre_menu' . $this->id_menu . '" class="ancre_menu"></div>';
		$this->addHtml('<ul id="menu" style="display:none"></ul>');
		$this->addHtml('<ul id="'.$this->id_menu.'" style="display:none">');

		foreach($this->a_item as $item) {
			$this->CreaLien($item);
		}

		// -- fin du menu
		$this->addHtml('</ul>');
		// initialisation du menu theme choisi
		$this->addHtml('<SCRIPT language=javascript>initMenu("' . $this->id_menu . '","' . $this->orientation . '");</SCRIPT>');

		return $this->html;
		//echo $this->test;
	}

	public function isAllowed($lien) {
		$this->test .= "<br />---".$lien->title;

		if (empty($lien->a_privilege)) {
			$this->test .= "<br />____out____";
			return true;
		}


		foreach($lien->a_privilege as $privilege){
			if ($this->user->isAllowed($privilege)) {
				$this->test .= "<br />____".htmlentities($privilege)."____";
				return true;
			}
		}

		return false;
	}

	public function generateAriane()
	{

		$a_fil = array_reverse($this->a_ariane);

		if($this->addAriane) {
			$a_fil[] = (object) array('link'=>null, 'title'=>$this->addAriane);
		}

		$a_link = array();
		$i = 0;
		$a_link[] = ' > <a href="'.BDO_URL.'">'.LANG_ACCUEIL.'</a>';
		foreach ($a_fil as $lien) {
			$i++;

			if (($i != count($a_fil) and $lien->link)) {
				$a_link[] = '<a href="'.$lien->link.'">'.$lien->title.'</a>';
			}
			else {
				$a_link[] = $lien->title;
			}
		}

		return implode(' > ' ,$a_link);
	}

}