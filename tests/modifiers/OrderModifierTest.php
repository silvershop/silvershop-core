<?php
/**
 * @package shop
 * @subpackage tests
 */

class OrderModifierTest extends FunctionalTest {

	static $fixture_file = 'shop/tests/fixtures/shop.yml';
	static $disable_theme = true;
	static $use_draft_site = true;

	function setUp() {
		parent::setUp();
		Order::set_modifiers(array(
			'FlatTaxModifier'
		),true);
		
		FlatTaxModifier::set_tax(0.25,"GST");
		$this->mp3player = $this->objFromFixture('Product', 'mp3player');
		$this->socks = $this->objFromFixture('Product', 'socks');
		$this->mp3player->publish('Stage','Live');
		$this->socks->publish('Stage','Live');
	}

	function testModifierCalculation(){
		$order = $this->createOrder();
		$order->calculate();		
		$this->assertEquals(510, $order->Total); //Total with 25% tax
	}
	
	/**
	 * Modifiers change/don't change when master list is updated.
	 */
	function todo_testUpdateModiferList(){
		
	}
	
	/**
	 * Make sure modifiers don't change, once added to the databse.
	 */
	function todo_testModifiersImmutable(){
		
	}
	
	function createOrder(){
		$order = new Order();
		$order->write();
		$item1a = $this->mp3player->createItem(2);
		$item1a->write();
		$order->Items()->add($item1a);
		$item1b = $this->socks->createItem();
		$item1b->write();
		$order->Items()->add($item1b);
		return $order;
	}

}