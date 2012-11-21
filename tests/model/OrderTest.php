<?php
/**
 * Order Unit Tests
 *  
 * @link Order
 * @package shop
 * @subpackage tests
 */
class OrderTest extends SapphireTest {

	static $fixture_file = 'shop/tests/fixtures/shop.yml';

	function setUp() {
		parent::setUp();
		ShopTest::setConfiguration();
		$this->mp3player = $this->objFromFixture('Product', 'mp3player');
		$this->mp3player->publish('Stage','Live');
		$this->socks = $this->objFromFixture('Product', 'socks');
		$this->socks->publish('Stage','Live');
		$this->beachball = $this->objFromFixture('Product', 'beachball');
		$this->beachball->publish('Stage','Live');
	}
	
	function tearDown(){
		parent::tearDown();
		unset($this->mp3player);
		unset($this->socks);
		unset($this->beachball);
	}
	
	function testCMSFields(){
		singleton('Order')->getCMSFields();
	}
	
	function testSearchFields(){
		singleton('Order')->scaffoldSearchFields();
	}
	
	function testDebug(){
		singleton('Order')->debug();
	}
	
	function testProductOrderItems() {
		$order = self::createOrder();
		$items = $order->Items();
		$this->assertNotNull($items);
		$this->assertDOSEquals(array(
			array('ProductID' => $this->mp3player->ID,'Quantity' => 2, 'CalculatedTotal' => 400),
			array('ProductID' => $this->socks->ID, 'Quantity' => 1, 'CalculatedTotal' => 8)
		), $items);
		$this->assertEquals($items->Quantity(),3);
		$this->assertTrue($items->Plural());
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
	
	function testPlacedOrderImmutability(){
		//create order
		$order = self::createOrder();
		
		//place order
		$processor = OrderProcessor::create($order)->placeOrder();
		
		//check totals
		$this->assertEquals($order->Total(),408);
		
		//make a changes to existing products
		$this->mp3player->BasePrice = 100;
		$this->mp3player->write();
		$this->socks->BasePrice = 20;
		$this->socks->write();
		
		//total doesn't change
		$this->assertEquals($order->Total(),408);
		$this->assertFalse($order->isCart());
		
		//item values don't change
		$items = $order->Items();
		$this->assertNotNull($items);
		$this->assertDOSEquals(array(
			array('ProductID' => $this->mp3player->ID,'Quantity' => 2, 'CalculatedTotal' => 400),
			array('ProductID' => $this->socks->ID, 'Quantity' => 1, 'CalculatedTotal' => 8)
		), $items);
		
		$mp3player = $items->find('ProductID',$this->mp3player->ID);
		$this->assertNotNull($mp3player);
		$this->assertEquals($mp3player->UnitPrice(),200,"Unit price remains the same");
		$this->assertEquals($mp3player->Total(),400,"");
		
		$socks = $items->find('ProductID',$this->socks->ID);
		$this->assertNotNull($socks);
		$this->assertEquals($socks->UnitPrice(),8);
		$this->assertEquals($socks->Total(),8);
	}
	
	/**
	 * Helper for creating an order
	 * Total should be $408.00
	 */
	function createOrder(){
		$order = new Order();
		$order->write();
		$item1a = $this->mp3player->createItem(2);
		$item1a->write();
		$order->Items()->add($item1a);
		$item1b = $this->socks->createItem();
		$item1b->write();
		$order->Items()->add($item1b);
		
		//add a payment
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
	
	function testCanFunctions(){
		$order = $this->createOrder();
		//order is in cart
		$this->assertTrue($order->canPay()); //can pay when order is in cart
		$this->assertFalse($order->canCancel()); //can't cancel when order is in cart
		$this->assertFalse($order->canDelete()); //never allow deleting
		//canCreate
		//canEdit
		
		//TODO: modify order, and retest all can functions
	}

}