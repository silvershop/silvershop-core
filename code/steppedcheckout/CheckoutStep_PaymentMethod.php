<?php

class CheckoutStep_PaymentMethod extends CheckoutStep{
	
	static $allowed_actions = array(
		'paymentmethod',
		'PaymentMethodForm',
	);
	
	function paymentmethod(){
		$methods = Checkout::get()->getPaymentMethods();
		//skip this step if there is only one payment type
		if(count($methods) == 1){
			$this->setpaymentmethod(array(
				'PaymentMethod' => key($methods)
			), null);
			return;
		}
		
		return array(
			'Form' => $this->PaymentMethodForm()
		);
	}
	
	function PaymentMethodForm(){
		$checkout = Checkout::get();
		$fields = new FieldList();
		$methods = $checkout->getPaymentMethods();
		if(!empty($methods)){
			$defaultmethod = key($methods);
			$fields->push(new OptionsetField(
				'PaymentMethod','',$methods, $defaultmethod
			));
		}else{
			$fields->push(new LiteralField("nomethods",
				"<p class=\"message warning\">".
					_t("Checkout.NOMETHODS","No payment methods have been set up").
				"<p>")
			);
		}
		$actions = new FieldList(
			new FormAction("setpaymentmethod","Continue")
		);
		$validator = new RequiredFields('PaymentMethod');
		$form = new Form($this->owner,"PaymentMethodForm",$fields,$actions);
		$this->owner->extend('updatePaymentMethodForm',$form);
		return $form;
	}
	
	function setpaymentmethod($data, $form){
		if($checkout = Checkout::get()){
			$checkout->setPaymentMethod($data["PaymentMethod"]);
		}
		$this->owner->redirect($this->NextStepLink());
	}
	
	function getSelectedPaymentMethod(){
		if($checkout = Checkout::get()){
			return $checkout->getSelectedPaymentMethod(true);
		}
		return false;
	}
	
}