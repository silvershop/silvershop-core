<?php

class AddProductFormTest extends SapphireTest{
	
	static $fixture_file = "shop/tests/fixtures/shop.yml";
	
	function testForm(){
	
		$controller = new Product_Controller($this->objFromFixture("Product", "socks"));
		$form = new AddProductForm($controller);
		$form->setMaximumQuantity(10);
		//TODO: test can't go over max quantity
		$data = array(
			'Quantity' => 4
		);
		$form->addtocart($data, $form);
		//TODO: check quantity
	}
	
}