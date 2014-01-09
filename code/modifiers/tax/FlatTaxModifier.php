<?php
/**
 * Handles calculation of sales tax on Orders.
 *
 * @package shop
 * @subpackage modifiers
 */
class FlatTaxModifier extends TaxModifier {

	private static $db = array(
		'TaxType' => "Enum('Exclusive,Inclusive')" //deprecated
	);

	private static $name = "GST";
	private static $rate = 0.15;
	private static $exclusive = true;
	private static $includedmessage = "%.1f%% %s (inclusive)";
	private static $excludedmessage = "%.1f%% %s";
	
	function populateDefaults(){
		parent::populateDefaults();
		$this->Type = (self::config()->exclusive) ? 'Chargable' : 'Ignored';
	}

	/**
	 * Get the tax amount to charge on the order.
	 */
	function value($incoming) {
		$this->Rate = self::config()->rate;
		if(self::config()->exclusive)
			return $incoming * $this->Rate;
		return $incoming - round($incoming/(1+$this->Rate), Order::$rounding_precision); //inclusive tax requires a different calculation
	}

}