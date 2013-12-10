<?php

class NestedCheckoutTest extends SapphireTest{
	
	static $fixture_file = 'shop/tests/fixtures/pages/NestedCheckout.yml';
	
	function setUp(){
		parent::setUp();
		$this->checkoutpage = $this->objFromFixture('CheckoutPage', 'checkout');
	}
	
	function testNestedCheckoutForm(){
		
		$this->assertEquals(Director::baseURL().'shop/checkout/', CheckoutPage::find_link(), 'Link is: ' . CheckoutPage::find_link());
	}
	
}