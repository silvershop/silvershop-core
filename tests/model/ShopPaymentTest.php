<?php 

class ShopPaymentTest extends SapphireTest{
	
	function setUp(){
		parent::setUp();
		ShopTest::setConfiguration();
	}
	
	function testPayment(){
		$payment = new Payment();
	}
	
}