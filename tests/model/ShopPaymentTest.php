<?php

class ShopPaymentTest extends SapphireTest{

	public function setUp(){
		parent::setUp();
		ShopTest::setConfiguration();
	}

	public function testPayment(){
		$payment = new Payment();
	}

}
