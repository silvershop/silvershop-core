<?php

class CheckoutStep_PaymentMethod extends CheckoutStep{
	
	static $allowed_actions = array(
		'paymentmethod',
		'PaymentMethodForm',
	);
	
	function paymentmethod(){
		$methods = Checkout::get()->getPaymentMethods();
		if($methods->Count() == 1){ //skip step if there is only one payment type
			$this->setpaymentmethod(array(
				'PaymentMethod' => $methods->First()->ClassName
			), null);
			return;
		}
		return array(
			'Form' => $this->PaymentMethodForm()
		);
	}
	
	function PaymentMethodForm(){
		$checkout = Checkout::get();
		$fields = new FieldList(new OptionsetField(
			'PaymentMethod','',$checkout->getPaymentMethods()->map('ClassName','Title'),$checkout->getPaymentMethods()->First()->ClassName
		));
		$actions = new FieldList(
			new FormAction("setpaymentmethod","Continue")
		);
		$validator = new RequiredFields('PaymentMethod');
		$form = new Form($this->owner,"PaymentMethodForm",$fields,$actions);
		return $form;
	}
	
	function setpaymentmethod($data, $form){
		if($checkout = Checkout::get()){
			$checkout->setPaymentMethod($data["PaymentMethod"]);
		}
		Controller::curr()->redirect($this->NextStepLink());
	}
	
	function getSelectedPaymentMethod(){
		if($checkout = Checkout::get()){
			return $checkout->getSelectedPaymentMethod();
		}
		return false;
	}
	
}