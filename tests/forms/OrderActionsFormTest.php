<?php

class OrderActionsFormTest extends SapphireTest{
	
	static $fixture_file = "shop/tests/fixtures/shop.yml";
	
	function testForm(){

		$order = $this->objFromFixture("Order","unpaid");
		OrderManipulation::add_session_order($order);

		$controller = new CheckoutPage_Controller(
			$this->objFromFixture("CheckoutPage", "checkout")
		);

		$form = $controller->ActionsForm();

		$form->doPayment($data,$form);

		//test if you can manipulate any order

	}
	
}