<?php namespace Wikidata\Value;
#[\AllowDynamicProperties] 
class QuantityValue {
	
	/**
	 * Class constructor
	 * @param object $value StdClass object with quantity value
	 */
	public function __construct($value) {

		$this->amount = $value->amount;
		$this->unit = $value->unit;
		$this->upperBound = isset($value->upperBound) ? $value->upperBound : null;
		$this->lowerBound = isset($value->lowerBound) ? $value->lowerBound : null;

	}

	/**
	 * Get value
	 * @return object Return object with all data
	 *
	 * TODO: prepare value to display
	 */
	public function getValue() {

		return $this;

	}

}
