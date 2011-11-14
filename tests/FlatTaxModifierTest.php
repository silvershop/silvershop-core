<?php

/**
* @package ecommerce
* @subpackage tests
*
*/
class FlatTaxModifierTest extends FunctionalTest {

	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	static $disable_theme = true;

	function setUp(){
		parent::setUp();
		EcommerceTest::setConfiguration();
		Order::set_modifiers(array("FlatTaxModifer"),true);
		FlatTaxModifier::set_tax(0.15,"GST",true);

		$this->objFromFixture('Product', 'mp3player')->publish('Stage','Live');

		ShoppingCart::clear();
	}

	function testCalculations(){

		$mp3player = $this->objFromFixture('Product', 'mp3player');
		$this->get(ShoppingCart::add_item_link($mp3player->ID));
		$cart = ShoppingCart::current_order();
		$this->assertEquals($cart->Total(),215);
	}

	//is tax worked out correctly?

}