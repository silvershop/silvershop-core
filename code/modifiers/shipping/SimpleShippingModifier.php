<?php
/**
 * Flat shipping to specific countries.
 *
 * @package shop
 * @subpackage modifiers
 */
class SimpleShippingModifier extends ShippingModifier {

	static $db = array(
		'Country' => 'Text',
	);

	static $default_charge = 10;

	static function set_default_charge($defaultCharge) {
		self::$default_charge = $defaultCharge;
	}

	static $charges_by_country = array();

	/**
	 * Set shipping charges on a country by country basis.
	 * For example, SimpleShippingModifier::set_charges_for_countries(array(
	 *   'US' => 10,
	 *   'NZ' => 5,
	 * ));
	 * @param countryMap A map of 2-letter country codes
	 */
	static function set_charges_for_countries($countryMap) {
		self::$charges_by_country = array_merge(self::$charges_by_country, $countryMap);
	}
	
	function value($subtotal = null){
		$country = $this->Country();
		if($country && isset(self::$charges_by_country[$country])){
			return self::$charges_by_country[$country];
		}
		return self::$default_charge;
	}

	function TableTitle() {
		if($country = $this->Country()) {
			$countryList = Geoip::getCountryDropDown();
			return sprintf(_t("SimpleShippingModifier.SHIPTO","Ship to %s"),$countryList[$country]);
		} else {
			return parent::TableTitle();
		}
	}
	
	function Country(){
		if($order = $this->Order()){
			return ($order->UseShippingAddress && $order->ShippingCountry) ? $order->ShippingCountry : $order->Country;
		}
		return null;
	}

}