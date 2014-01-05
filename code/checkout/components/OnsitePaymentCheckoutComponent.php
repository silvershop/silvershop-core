<?php

use Omnipay\Common\Helper;

/**
 *
 * This component should only ever be used on SSL encrypted pages!
 */
class OnsitePaymentCheckoutComponent extends CheckoutComponent {

	function getFormFields(Order $order) {

		$gatewayfieldsfactory = new GatewayFieldsFactory(
			Session::get("Checkout.PaymentMethod"),
			array('Card')
		);

		return $gatewayfieldsfactory->getCardFields();
	}

	public function getRequiredFields(Order $order){
		return GatewayInfo::required_fields(Session::get("Checkout.PaymentMethod"));
	}

	public function validateData(Order $order, array $data){
		$result = new ValidationResult();
		//TODO: validate credit card data
		if(!Helper::validateLuhn($data['number'])){
			$result->error('Credit card is invalid');
			throw new ValidationException($result);
		}

	}

	public function getData(Order $order){
		return array();
	}

	public function setData(Order $order, array $data){

		//create payment?

	}

}