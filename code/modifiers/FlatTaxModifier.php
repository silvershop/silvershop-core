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
class FlatTaxModifier extends OrderModifier {

	public static $db = array(
		'Rate' => 'Double',
		'TaxType' => "Enum('Exclusive,Inclusive')" //deprecated
	);
	
	public static $defaults = array(
		'Rate' => 0.15 //15% tax
	);
	
	public static $singular_name = "Flat Tax";
	function i18n_singular_name() { return _t("FlatTaxModifier.SINGULAR", self::$singular_name); }
	public static $plural_name = "Flat Taxes";
	function i18n_plural_name() { return _t("FlatTaxModifier.PLURAL", self::$plural_name); }

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
		if(self::$exclusive)
			return $this->Amount = $incoming * self::$rate;
		return $this->Amount = $incoming - ($incoming/(1+self::$rate)); //inclusive tax requires a different calculation
	}

}