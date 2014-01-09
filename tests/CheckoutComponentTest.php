<?php

class CheckoutComponentTest extends SapphireTest {
	
	function testSinglePageConfig() {

		//start a new order
		$order = new Order();
		$order->write();

		$config = new SinglePageCheckoutComponentConfig($order);
		
		$components = $config->getComponents();
		//todo: assertions

		$fields = $config->getFormFields();
		//todo: assertions

		$required = $config->getRequiredFields();
		//todo: assertions

		//$validateData = $config->validateData($data);
		//todo: assertions
		
		$data = $config->getData();
		//todo: assertions

		$config->setData($data);
		//todo: assertions

		//TODO: test
		//form field generation
		//validate data
		//set data
		//get data
	}

	//test namespaced config

}