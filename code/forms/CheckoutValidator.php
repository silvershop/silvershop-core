<?php

/**
 * Order validator makes sure everything is set correctly
 * and in place before an order can be placed.
 */
class CheckoutValidator extends RequiredFields{

	function php($data){
		$valid =  parent::php($data);	
		$checkout = Checkout::get();
		if(!$checkout->getSelectedPaymentMethod(false)){
			$valid = false;
			$this->form->sessionMessage("Payment method required", "bad"); //TODO: this message isn't showing up
			$this->errors[] = true;
		}
		return $valid;
	}
	
	function javascript(){
		return "";
	}
	
}