<?php

class CheckoutStep_PaymentMethod extends CheckoutStep{
	
	static $allowed_actions = array(
		'paymentmethod',
		'PaymentMethodForm',
	);
	
	function paymentmethod(){
		//TODO: trigger automatic set & redirect if there is only one payment type
		return array(
			'Form' => $this->PaymentMethodForm()
		);
	}
	
	function PaymentMethodForm(){
		
		$checkout = Checkout::get();
		
		$fields = new FieldSet(new OptionsetField(
			'PaymentMethod','',$checkout->getPaymentMethods()->map('ClassName','Title'),$checkout->getPaymentMethods()->First()->ClassName
		));
		$actions = new FieldSet(
			new FormAction("setPaymentMethod","Continue")
		);
		$form = new Form($this->owner,"PaymentMethodForm",$fields,$actions);
		return $form;
	}
	
	function setPaymentMethod($data, $form){
		if($checkout = Checkout::get()){
			$checkout->setPaymentMethod($data["PaymentMethod"]);
		}
		Director::redirect($this->NextStepLink());
	}
	
	function getSelectedPaymentMethod(){
		if($checkout = Checkout::get()){
			
			return $checkout->getSelectedPaymentMethod();
		}
		return false;
	}
	
}