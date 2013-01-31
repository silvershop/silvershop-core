<?php
/**
 * Pickup the order from the store.
 * @package shop
 * @subpackage shipping
 */
class PickupShippingModifier extends ShippingModifier{
	
	static $defaults = array(
		'Type' => 'Ignored'
	);
	
	static $casting = array(
		'TableValue' => 'CanBeFreeCurrency'
	);
	
	public static $singular_name = "Pick Up Shipping";
	function i18n_singular_name() { return _t("PickupShippingModifier.SINGULAR", "Pick up from store"); }

	
}