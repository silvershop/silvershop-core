<?php

/**
* @package shop
* @subpackage tests
*
*/
class FlatTaxModifierTest extends FunctionalTest {

	static $fixture_file = 'shop/tests/ecommerce.yml';
	static $disable_theme = true;

	function setUp(){
		parent::setUp();
		ShopTest::setConfiguration();
		Order::set_modifiers(array("FlatTaxModifier"),true);
		$this->cart = ShoppingCart::getInstance();
		$this->mp3player = $this->objFromFixture('Product', 'mp3player');
		$this->mp3player->publish('Stage','Live');
	}

	function testInclusiveTax(){
		FlatTaxModifier::set_tax(0.15,"GST",false);
		$this->cart->clear();
		$this->cart->add($this->mp3player);
		$order = $this->cart->current();
		$this->assertEquals($order->Total(),200);
	}
	
	function testExclusiveTax(){
		FlatTaxModifier::set_tax(0.15,"GST",true);
		$this->cart->clear();
		$this->cart->add($this->mp3player);
		
		$order = $this->cart->current();
		//TODO: check modifier ammount (should be $30)
		//$this->assertEquals($modifier->Amount,30);
		$this->assertEquals($order->Total(),230);
	}

}