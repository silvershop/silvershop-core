<?php
/**
 * Order Unit Tests
 *  
 * @link Order
 * @package shop
 * @subpackage tests
 */
class OrderTest extends SapphireTest {

	static $fixture_file = 'shop/tests/shop.yml';

	function setUp() {
		parent::setUp();
		ShopTest::setConfiguration();
		$this->mp3player = $this->objFromFixture('Product', 'mp3player');
		$this->socks = $this->objFromFixture('Product', 'socks');
		$this->mp3player->publish('Stage','Live');
		$this->socks->publish('Stage','Live');
	}
	
	function testProductOrderItems() {
		$order = self::createOrder();
		$items = $order->Items();
		$this->assertNotNull($items);
		$this->assertDOSEquals(array(
			array('ProductID' => $this->mp3player->ID,'Quantity' => 2, 'CalculatedTotal' => 400),
			array('ProductID' => $this->socks->ID, 'Quantity' => 1, 'CalculatedTotal' => 8)
		), $items);
	}
	
	function testSubtotal() {
		$order = self::createOrder();
		$this->assertEquals($order->SubTotal(), 408, "Subtotal is correct"); // 200 + 200 + 8
	}
	
	function testGrandTotal(){
		$order = self::createOrder();
		$this->assertEquals($order->GrandTotal(), 408,"Grand total is correct");
	}

	function testTotalPaid(){
		$order = self::createOrder();
		$this->assertEquals($order->TotalPaid(), 200,"Outstanding total is correct");
	}
	
	function testTotalOutstanding(){
		$order = self::createOrder();
		$this->assertEquals($order->TotalOutstanding(), 208,"Outstanding total is correct");
	}
	
	/**
	 * Helper for creating an order
	 */
	function createOrder(){
		$order = new Order();
		$order->write();
		$item1a = $this->mp3player->createItem(2);
		$order->Attributes()->add($item1a);
		$item1b = $this->socks->createItem();
		$order->Attributes()->add($item1b);
		
		$payment = new Payment();
		$payment->OrderID = $order->ID;
		$payment->AmountAmount = 200;
		$payment->AmountCurrency = 'USD';
		$payment->Status = 'Success';
		$payment->write();
		
		$order->calculate();
		$order->write();
		
		return $order;
	}

}