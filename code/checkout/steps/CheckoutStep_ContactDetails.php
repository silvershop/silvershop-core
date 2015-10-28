<?php

class CheckoutStep_ContactDetails extends CheckoutStep{

	private static $allowed_actions = array(
		'contactdetails',
		'ContactDetailsForm'
	);

	public function contactdetails() {
		$form = $this->ContactDetailsForm();
		if(
			ShoppingCart::curr() &&
			Config::inst()->get("CheckoutStep_ContactDetails", "skip_if_logged_in")
		){
			if(Member::currentUser() && !$form->getValidator()->validate()){
				Controller::curr()->redirect($this->NextStepLink());
				return;
			}
		}

		return array(
			'OrderForm' => $form
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
			new FormAction("checkoutSubmit", _t('CheckoutStep.CONTINUE', "Continue"))
		));
		$this->owner->extend('updateContactDetailsForm', $form);

		return $form;
	}

}
