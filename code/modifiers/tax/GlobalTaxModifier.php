<?php
/**
 * Handles calculation of sales tax on Orders on
 * a per-country basis.
 * 
 * Sample configuration in your _config.php:
 *
 * <code>
 * 	TaxModifier::set_for_country('NZ', 0.125, 'GST', 'inclusive');
 * 	TaxModifier::set_for_country('UK', 0.175, 'VAT', 'exclusive');
 * </code>
 *
 * @package shop
 * @subpackage modifiers
 */
class GlobalTaxModifier extends TaxModifier {

	public static $db = array(
		'Country' => 'Varchar'
	);

	protected static $names_by_country;
	protected static $rates_by_country;
	protected static $excl_by_country;

	/**
	 * Set the tax information for a particular country.
	 * By default, no tax is charged.
	 *
	 * @param $country string The two-letter country code
	 * @param $rate float The tax rate, eg, 0.125 = 12.5%
	 * @param $name string The name to give to the tax, eg, "GST"
	 * @param $inclexcl string "inclusive" if the prices are tax-inclusive.
	 * 						"exclusive" if tax should be added to the order total.
	 */
	static function set_for_country($country, $rate, $name, $inclusive = false) {
		self::$names_by_country[$country] = $name;
		self::$rates_by_country[$country] = $rate;
		if($inclusive) {
			self::$excl_by_country[$country] = false;
		}else{
			self::$excl_by_country[$country] = true;
		}
	}
	
	function value($incoming){
		$rate = $this->Type == "Chargable" ? $this->Rate() : round(1 - (1 / (1 + $this->Rate())),Order::$rounding_precision);
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
		return $this->Country = EcommerceRole::find_country();
	}

	function TableTitle() {
		return parent::TableTitle()." for ".$this->Country()." ".($this->Type == "Chargable" ? '' : _t("GlobalTaxModifier.INCLUDED",' (included in the above price)'));
	}

}