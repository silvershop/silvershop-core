<?php

class CheckoutHelperTest extends SapphireTest{
	
	static $fixture_file = array(
		'shop/tests/fixtures/Cart.yml',
		'shop/tests/fixtures/Addresses.yml',
		//'shop/tests/fixtures/'
	);
	
	function setUp(){
		parent::setUp();
		$this->cart = $this->objFromFixture("Order", "cart1");
		$this->shippingaddress = $this->objFromFixture("Address", "wnz6012");
		$this->checkout = new Checkout($this->cart);
	}
	
	function testSetUpShippingAddress(){		
		$this->checkout->setShippingAddress($this->shippingaddress);
		//address was successfully added
		//don't allow adding bad addressses
	}
	
	function testSetShippingMethod(){
		$this->checkout->setShippingMethod(new ShippingMethod());
	}
	
	function testSetPaymentMethod(){
		//$this->checkout->		
	}
		
	//don't allow going to fill out data if dependent data doesn't exist
	
	//only allow complete when all required order data is present
	
}