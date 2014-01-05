<?php

class PaymentCheckoutComponent extends CheckoutComponent{
	
	public function getFormFields(Order $order){
		$fields = new FieldList();
		$gateways = GatewayInfo::get_supported_gateways();
		if(count($gateways) > 1){
			$fields->push(
				new OptionsetField(
					'PaymentMethod',
					_t("Checkout","Payment Type"),
					$gateways,
					array_keys($gateways)
				)
			);
		}

		return $fields;
	}

	public function validateData(Order $order, array $data){
		$result = new ValidationResult();
		$paymentmethod = isset($data['PaymentMethod']) ? $data['PaymentMethod'] : null;
		if(!$paymentmethod){
			$result->error("Payment method not provided", "PaymentMethod");
			throw new ValidationException($result);
		}
		$methods = GatewayInfo::get_supported_gateways();
		if(!isset($methods[$paymentmethod])){
			$result->error("Gateway not supported", "PaymentMethod");
			throw new ValidationException($result);
		}
	}

	public function getData(Order $order){
		return array(
			'PaymentMethod' => Checkout::get($order)->getSelectedPaymentMethod()
			Session::get("Checkout.PaymentMethod")
		);
	}

	public function setData(Order $order, array $data){
		if(isset($data['PaymentMethod'])){
			Checkout::get($order)->setPaymentMethod($data['PaymentMethod'])
		}	
	}

}