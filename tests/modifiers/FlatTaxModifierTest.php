<?php

/**
* @package shop
* @subpackage tests
*
*/
class FlatTaxModifierTest extends FunctionalTest {

	protected static $fixture_file = 'shop/tests/fixtures/shop.yml';
	protected static $disable_theme = true;

	public function setUp() {
		parent::setUp();
		ShopTest::setConfiguration();
		Order::config()->modifiers = array(
			"FlatTaxModifier"
		);
		FlatTaxModifier::config()->name = "GST";
		FlatTaxModifier::config()->rate = 0.15;
		$this->cart = ShoppingCart::singleton();
		$this->mp3player = $this->objFromFixture('Product', 'mp3player');
		$this->mp3player->publish('Stage', 'Live');
	}

	public function testInclusiveTax() {
		FlatTaxModifier::config()->exclusive = false;
		$this->cart->clear();
		$this->cart->add($this->mp3player);
		$order = $this->cart->current();
		$order->calculate();
		$modifier = $order->Modifiers()
					->filter('ClassName', 'FlatTaxModifier')
					->first();
		$this->assertEquals(26.09, $modifier->Amount); //remember that 15% tax inclusive is different to exclusive
		$this->assertEquals(200, $order->GrandTotal());
	}

	public function testExclusiveTax() {
		FlatTaxModifier::config()->exclusive = true;
		$this->cart->clear();
		$this->cart->add($this->mp3player);
		$order = $this->cart->current();
		$order->calculate();
		$modifier = $order->Modifiers()
					->filter('ClassName', 'FlatTaxModifier')
					->first();
		$this->assertEquals(30, $modifier->Amount);
		$this->assertEquals(230, $order->GrandTotal());
	}

}
