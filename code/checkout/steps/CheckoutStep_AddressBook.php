<?php

class CheckoutStep_AddressBook extends CheckoutStep_Address{

	private static $allowed_actions = array(
		'shippingaddress',
		'ShippingAddressForm',
		'setshippingaddress',
		'billingaddress',
		'BillingAddressForm',
		'setbillingaddress'
	);

	public function shippingconfig() {
		$config = CheckoutComponentConfig::create(ShoppingCart::curr());
		$config->addComponent(ShippingAddressBookCheckoutComponent::create());

		return $config;
	}

	public function billingconfig() {
		$config = CheckoutComponentConfig::create(ShoppingCart::curr());
		$config->addComponent(new BillingAddressBookCheckoutComponent());

		return $config;
	}

}
