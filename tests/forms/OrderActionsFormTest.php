<?php

class OrderActionsFormTest extends SapphireTest{
	
	static $fixture_file = "shop/tests/fixtures/shop.yml";
	
	function testForm(){
		$controller = new CheckoutPage_Controller($this->objFromFixture("CheckoutPage", "checkout"));
		$order = ShoppingCart::curr(); //TODO: get pre-placed order
		$form = new OrderActionsForm($controller, $name ="OrderActionsForm", $order);
	}
	
}