<?php

class CheckoutPageTest extends FunctionalTest{

	static $fixture_file = 'shop/tests/fixtures/shop.yml';
	static $disable_theme = true;
	static $use_draft_site = true;

	protected $controller;
	
	function setUp() {
		parent::setUp();
		ShopTest::setConfiguration();
	}

	function testActionsForm(){
		$order = $this->objFromFixture("Order","unpaid");
		OrderManipulation::add_session_order($order);
		$this->get("/checkout/order/".$order->ID);

		//make payment action
		$this->post("/checkout/order/ActionsForm",array(
			'OrderID' => $order->ID,
			'PaymentMethod' => 'Dummy',
			'action_dopayment' => 'submit'
		));

		//cancel action
		$this->post("/checkout/order/ActionsForm",array(
			'OrderID' => $order->ID,
			'action_docancel' => 'submit'
		));
	}

	//log user in
	//set the current order
	//visit order page
	
	function testPayment(){
		
		//make payment
		$data = array(
			'name' => 'Joe Bloggs',
			'number' => '4242424242424242',
			'expiryMonth' => '',
			'expiryYear' => '',
			'cvv' => '123'
		);

		//$form->loadDataFrom($data);
		//$form->submitPayment($data, $form);

		//$payment = $order->Payments()->first();
		//$this->assertEquals("Dummy",$payment->Gateway);
	}

}