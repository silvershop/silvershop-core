<?php
/**
 * SubTotal modifier provides a way to display subtotal within the list of modifiers.
 * @package shop
 * @subpackage modifiers
 */
class SubTotalModifier extends OrderModifier {
	
	public static $defaults = array(
		'Type' => 'Ignored'
	);
	
	public static $singular_name = "Sub Total";
	function i18n_singular_name() {
		return _t("SubTotalModifier.SINGULAR", self::$singular_name);
	}
	public static $plural_name = "Sub Totals";
	function i18n_plural_name() {
		return _t("SubTotalModifier.PLURAL", self::$plural_name);
	}
	
	function value($incoming){
		return $this->Amount = $incoming;
	}
	
}
