<?php

class CheckoutStep_Summary extends CheckoutStep{

	private static $allowed_actions = array(
		'summary',
		'ConfirmationForm',
	);

	public function summary() {
		$form = $this->ConfirmationForm();
		$this->owner->extend('updateConfirmationForm', $form);

		return array(
			'OrderForm' => $form
		);
	}

	public function ConfirmationForm() {
		$config = new CheckoutComponentConfig(ShoppingCart::curr(), false);
		$config->addComponent(new NotesCheckoutComponent());
		$config->addComponent(new TermsCheckoutComponent());
		$this->owner->extend('updateConfirmationComponentConfig', $config);

		$form = new PaymentForm($this->owner, "ConfirmationForm", $config);
		$form->setFailureLink($this->owner->Link('summary'));
		$this->owner->extend('updateConfirmationForm', $form);

		return $form;
	}

}
