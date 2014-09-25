<?php

class CheckoutComponentTest extends SapphireTest {

	public function testSinglePageConfig() {

		ShopTest::setConfiguration();

		//start a new order
		$order = new Order();
		$order->write();

		$config = new SinglePageCheckoutComponentConfig($order);

		$components = $config->getComponents();
		//assertions!

		$fields = $config->getFormFields();
		//assertions!

		$required = $config->getRequiredFields();
		//assertions!

		//$validateData = $config->validateData($data);
		//assertions!

		$data = $config->getData();
		//assertions!

		$config->setData($data);
		//assertions!

		//form field generation
		//validate data
		//set data
		//get data
		$this->markTestIncomplete('Lots missing here');
	}


	//test namespaced config

}
