<?php

class CheckoutStep_Summary extends CheckoutStep{
	
	private static $allowed_actions = array(
		'summary',
		'ConfirmationForm',
	);
	
	protected function checkoutconfig(){
		$config = new CheckoutComponentConfig(ShoppingCart::curr(),false);
		$config->addComponent(new NotesCheckoutComponent());
		$config->addComponent(new TermsCheckoutComponent());

		return $config;
	}

	function summary(){
		$form = $this->ConfirmationForm();
		$this->owner->extend('updateConfirmationForm',$form);

		return array(
			'OrderForm' => $form 
		);
	}
	
	function ConfirmationForm(){
		$form = new CheckoutForm($this->owner,"ConfirmationForm",$this->checkoutconfig());
		$this->owner->extend('updateConfirmationForm',$form);
		
		return $form;
	}
	
}