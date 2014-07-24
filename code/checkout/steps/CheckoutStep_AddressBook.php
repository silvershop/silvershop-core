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
		$config = new CheckoutComponentConfig(ShoppingCart::curr());
		$config->addComponent(new ShippingAddressBookCheckoutComponent());

		return $config;
	}

	public function billingconfig() {
		$config = new CheckoutComponentConfig(ShoppingCart::curr());
		$config->addComponent(new BillingAddressBookCheckoutComponent());

		return $config;
	}

}