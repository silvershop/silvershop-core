<?php
/**
 * Handles calculation of sales tax on Orders on
 * a per-country basis.
 *
 * @package shop
 * @subpackage modifiers
 */
class GlobalTaxModifier extends TaxModifier {

	private static $db = array(
		'Country' => 'Varchar'
	);

	private static $countryrates = array();
	
	function value($incoming){
		$rate = $this->Type == "Chargable" ?
			$this->Rate() :
			round(1 - (1 / (1 + $this->Rate())),Order::$rounding_precision);
		return $incoming * $rate;	
	}
	
	function Rate(){
		if(isset(self::$rates_by_country[$this->Country()])) {
			return $this->Rate = self::$rates_by_country[$this->Country()];
		}
		$defaults = $this->stat('defaults');
		return $this->Rate = $defaults['Rate'];
	}

	protected function Country() {
		return ShopMember::find_country();
	}

	function TableTitle() {
		$country = ($this->Country()) ? " for ".$this->Country()." " : "";
		return parent::TableTitle().$country.
			($this->Type == "Chargable" ? '' : _t("GlobalTaxModifier.INCLUDED",' (included in the above price)'));
	}

}