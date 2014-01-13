<?php

class CheckoutStep_ContactDetails extends CheckoutStep{
	
	static $allowed_actions = array(
		'contactdetails',
		'ContactDetailsForm'
	);
	
	function contactdetails(){
		return array(
			'OrderForm' => $this->ContactDetailsForm()
		);
	}
	
	function ContactDetailsForm(){
		$form = new CheckoutForm($this->owner, 'ContactDetailsForm', $this->checkoutconfig());
		$form->setActions(new FieldList(
			new FormAction("setcontactdetails","Continue")
		));
		$this->owner->extend('updateContactDetailsForm',$form);

		return $form;
	}
	
	function setcontactdetails($data,$form){
		$this->checkoutconfig()->setData($form->getData());
		$this->owner->redirect($this->NextStepLink());
	}

	function checkoutconfig(){
		$config = new CheckoutComponentConfig(ShoppingCart::curr());
		$config->addComponent(new CustomerDetailsCheckoutComponent());
		return $config;
	}
	
}