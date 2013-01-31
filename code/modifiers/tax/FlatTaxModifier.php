<?php
/**
 * Handles calculation of sales tax on Orders.
 *
 * If you would like to make your own tax calculator,
 * create a subclass of this and enable it by using
 * {@link Order::set_modifiers()} in your project
 * _config.php file.
 *
 * Sample configuration in your _config.php:
 *
 * <code>
 *	//rate , name, isexclusive
 * 	FlatTaxModifier::set_tax(0.15, 'GST', false);
 * </code>
 *
 * @package shop
 * @subpackage modifiers
 */
class FlatTaxModifier extends TaxModifier {

	public static $db = array(
		'TaxType' => "Enum('Exclusive,Inclusive')" //deprecated
	);

	protected static $name = null;
	protected static $rate = null;
	protected static $exclusive = null;

	static $includedmessage = "%.1f%% %s (inclusive)";
	static $excludedmessage = "%.1f%% %s";
	
	function populateDefaults(){
		parent::populateDefaults();
		$this->Type = (self::$exclusive) ? 'Chargable' : 'Ignored';
	}
	
	static function set_tax($rate, $name = null, $exclusive = true) {
		self::$rate = $rate;
		self::$name = (string)$name;
		self::$exclusive = (bool)$exclusive;
	}

	/**
	 * Get the tax amount to charge on the order.
	 *
	 */
	function value($incoming) {
		$this->Rate = self::$rate;
		if(self::$exclusive)
			return $incoming * $this->Rate;
		return $incoming - round($incoming/(1+$this->Rate),Order::$rounding_precision); //inclusive tax requires a different calculation
	}

}