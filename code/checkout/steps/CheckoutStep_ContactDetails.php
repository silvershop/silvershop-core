<?php

class CheckoutStep_ContactDetails extends CheckoutStep{

	public static $allowed_actions = array(
		'contactdetails',
		'ContactDetailsForm'
	);

	public function contactdetails() {
		return array(
			'OrderForm' => $this->ContactDetailsForm()
		);
	}

	public function ContactDetailsForm() {
		$cart = ShoppingCart::curr();
		if(!$cart){
			return false;
		}
		$config = new CheckoutComponentConfig(ShoppingCart::curr());
		$config->addComponent(new CustomerDetailsCheckoutComponent());
		$form = new CheckoutForm($this->owner, 'ContactDetailsForm', $config);
		$form->setRedirectLink($this->NextStepLink());
		$form->setActions(new FieldList(
			new FormAction("checkoutSubmit", "Continue")
		));
		$this->owner->extend('updateContactDetailsForm', $form);

		return $form;
	}

}
