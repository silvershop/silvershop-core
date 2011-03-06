<?php

/**
 *@description: this is a Hack class that adds a few features to payment, needed to run e-commerce.
 *
 **/

class EcommercePaymentExtension extends Payment {


	public static function get_suppertod_methods() {
		$obj = singleton("EcommercePaymentExtension");
		return $obj->getSupportedMethods();
	}

	function getSupportedMethods() {
		return self::$supported_methods;
	}


}
