<?php

class CheckoutStep_PaymentMethod extends CheckoutStep{
	
	static $allowed_actions = array(
		'paymentmethod',
		'PaymentMethodForm',
	);

	protected function checkoutconfig(){
		$config = new CheckoutComponentConfig(ShoppingCart::curr(), false);
		$config->addComponent(new PaymentCheckoutComponent());

		return $config;
	}
	
	function paymentmethod(){
		return array(
			'OrderForm' => $this->PaymentMethodForm()
		);
	}
	
	function PaymentMethodForm(){
		$form = new CheckoutForm($this->owner,"PaymentMethodForm", $this->checkoutconfig());
		$form->setActions(new FieldList(
			FormAction::create("setpaymentmethod","Continue")
		));
		$this->owner->extend('updateConfirmationForm',$form);
		
		return $form;
	}

	function setpaymentmethod($data, $form){
		$this->checkoutconfig()->setData($form->getData());
		return $this->owner->redirect($this->NextStepLink());
	}
	
}