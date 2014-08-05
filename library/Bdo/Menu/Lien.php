<?php
class Bdo_Menu_Lien {

	public $title = null;
	public $link = null;
	public $target = null;
	public $privilege = null;
	public $lienPere = null;

	public $acl = null;

	public $a_fils = array();
	public $a_privilege = array();


	public function __construct($title, $link=null, $privilege=null, $target=null)
	{
		$this->acl = Bdo_Cfg::getVar('acl');
		$this->title = $title;
		$this->link = $link;

		if (!$privilege) {
			$this->extractPrivilege();
		}
		else {
			$this->privilege = $privilege;			
		}
		

		if ($this->privilege) $this->a_privilege[] = $this->privilege;
		$this->target = $target;
	}

	public function addFils(Bdo_Menu_Lien $lien) {
		$this->a_fils[] = $lien;
		$lien->lienPere = $this;

		if ($lien->privilege) {
			$this->suiviPrivilege($lien, $lien->privilege);
		}
		return $lien;
	}

	public function getFils() {
		return $this->a_fils;
	}

	/**
	 * fonction recursive pour la transmission des privileges au pere
	 *
	 */
	public function suiviPrivilege($lien, $privilege)
	{
		if ($lien->lienPere and !in_array($privilege, $lien->lienPere->a_privilege))
		{
			$lien->lienPere->a_privilege[] = $privilege;
			if ($lien->lienPere->lienPere) {
				$this->suiviPrivilege($lien->lienPere->lienPere, $lien->privilege);
			}
		}
	}


	public function extractPrivilege() {
		
		$a_uri = explode('?',$this->link);
		if (strpos($a_uri[0],CFG_RELATIVE_APPLI) === 0) {
			$a_uri[0] = str_ireplace(CFG_RELATIVE_APPLI, '',$a_uri[0]);
		}
		$a_resPriv = explode('/',$a_uri[0]);

		if ((2 == count($a_resPriv)) and ($this->acl->isResourcePrivilege($a_resPriv[0],$a_resPriv[1])))
		{
			$this->privilege = $a_resPriv[0].'.'.$a_resPriv[1];
		}
	}

}