<?php

class CheckoutStep_ContactDetails extends CheckoutStep{

	public static $allowed_actions = array(
		'contactdetails',
		'ContactDetailsForm'
	);

	public function contactdetails(){
		return array(
			'OrderForm' => $this->ContactDetailsForm()
		);
	}

	public function ContactDetailsForm(){
		$form = new CheckoutForm($this->owner, 'ContactDetailsForm', $this->checkoutconfig());
		$form->setActions(new FieldList(
			new FormAction("setcontactdetails","Continue")
		));
		$this->owner->extend('updateContactDetailsForm',$form);

		return $form;
	}

	public function setcontactdetails($data,$form){
		$this->checkoutconfig()->setData($form->getData());
		$this->owner->redirect($this->NextStepLink());
	}

	public function checkoutconfig(){
		$config = new CheckoutComponentConfig(ShoppingCart::curr());
		$config->addComponent(new CustomerDetailsCheckoutComponent());
		return $config;
	}

}
