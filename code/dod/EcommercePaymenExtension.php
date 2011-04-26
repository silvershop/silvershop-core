<?php

/**
 * @description: This is a Hack class that adds a few features to payment, needed to run e-commerce.
 * Eventually this class will be deleted.
 *
 * @package: ecommerce
 * @sub-package: payment
 *
 **/

class EcommercePaymentExtension extends Payment {


	/**
	 *@return Array (e.g. 		'ChequePayment' => 'Cheque Payment Option'
	 **/
	public static function get_suppertod_methods() {
		$obj = singleton("EcommercePaymentExtension");
		return $obj->getSupportedMethods();
	}

	/**
	 *@return Array (e.g. 		'ChequePayment' => 'Cheque Payment Option'
	 **/
	function getSupportedMethods() {
		return self::$supported_methods;
	}


}
