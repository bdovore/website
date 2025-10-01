<?php namespace Wikidata\Property;
#[\AllowDynamicProperties] 
class PropertyQualifier {
	
	/**
	 * Class constructor
	 * @param object $qualifier StdClass object with qualifier
	 */
	public function __construct($qualifier) {
		$this->hash = $qualifier->hash;
		$this->snaktype = $qualifier->snaktype;
		$this->property = $qualifier->property;
		$this->datatype = $qualifier->datatype;
		
		if( $this->snaktype === 'novalue' OR !isset($qualifier->datavalue))
			$this->datavalue = new PropertyDatavalue('novalue');
		else
			$this->datavalue = new PropertyDatavalue($qualifier->datavalue);

	}

	/**
	 * Get property datavalue
	 * @return object /Property/PropertyDatavalue
	 */
	public function getDatavalue() {

		return $this->datavalue;

	}

}