<?php

class ViewableCartTest extends SapphireTest{

	public static $fixture_file = 'shop/tests/fixtures/shop.yml';
	public static $disable_theme = true;

	function setUp() {
		parent::setUp();
		ShopTest::setConfiguration();
		$this->objFromFixture("Product", "socks")->publish("Stage", "Live");
	}

	function testCart() {
		$cart = $this->objFromFixture("Order", "cart");
		ShoppingCart::singleton()->setCurrent($cart);
		$page = new Page_Controller();
		$this->assertEquals("$8.00", (string)$page->renderWith("CartTestTemplate"));
	}

}