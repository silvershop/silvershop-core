<?php

class ShippingModifier extends OrderModifier{
	
	public static $singular_name = "Shipping";
	function i18n_singular_name() {
		return _t("ShippingModifier.SINGULAR", self::$singular_name);
	}
	
	function required(){
		return true; //TODO: make it optional
	}
	
	function requiredBeforePlace(){
		return true;
	}
	
}