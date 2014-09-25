<?php
/**
 * Order Unit Tests
 *
 * @link Order
 * @package shop
 * @subpackage tests
 */
class OrderTest extends SapphireTest {

	public static $fixture_file = 'shop/tests/fixtures/shop.yml';

	public function setUp() {
		parent::setUp();
		ShopTest::setConfiguration();
		$this->mp3player = $this->objFromFixture('Product', 'mp3player');
		$this->mp3player->publish('Stage', 'Live');
		$this->socks = $this->objFromFixture('Product', 'socks');
		$this->socks->publish('Stage', 'Live');
		$this->beachball = $this->objFromFixture('Product', 'beachball');
		$this->beachball->publish('Stage', 'Live');
	}

	public function tearDown() {
		parent::tearDown();
		unset($this->mp3player);
		unset($this->socks);
		unset($this->beachball);
	}

	public function testCMSFields() {
		singleton('Order')->getCMSFields();
		$this->markTestIncomplete('assertions!');
	}

	public function testSearchFields() {
		singleton('Order')->scaffoldSearchFields();
		$this->markTestIncomplete('assertions!');
	}

	public function testDebug() {
		$order = $this->objFromFixture("Order", "cart");
		$order->debug();
		$this->markTestIncomplete('assertions!');
	}

	public function testOrderItems() {
		$order = $this->objFromFixture("Order", "paid");
		$items = $order->Items();
		$this->assertNotNull($items);
		$this->assertDOSEquals(array(
			array('ProductID' => $this->mp3player->ID,'Quantity' => 2, 'CalculatedTotal' => 400),
			array('ProductID' => $this->socks->ID, 'Quantity' => 1, 'CalculatedTotal' => 8)
		), $items);
		$this->assertEquals(3, $items->Quantity(), "Quantity is 3");
		$this->assertTrue($items->Plural(), "There is more than one item");
		$this->assertEquals(0.7, $items->Sum('Weight', true), "Total order weight sums correctly");
	}

	public function testTotals() {
		$order = $this->objFromFixture("Order", "paid");
		$this->assertEquals(408, $order->SubTotal(), "Subtotal is correct"); // 200 + 200 + 8
		$this->assertEquals(408, $order->GrandTotal(), "Grand total is correct");
		$this->assertEquals(200, $order->TotalPaid(), "Outstanding total is correct");
		$this->assertEquals(208, $order->TotalOutstanding(), "Outstanding total is correct");
	}

	public function testRounding() {
		//create an order with unrounded total
		$order = new Order(array(
			'Total' => 123.257323, //NOTE: setTotal isn't called here, so un-rounded data *could* get in to the object
			'Status' => 'Unpaid'
		));
		$order->Total = 123.257323; //setTotal IS called here
		$this->assertEquals(123.26, $order->Total(), "Check total rounds appropriately");
		$this->assertEquals(123.26, $order->TotalOutstanding(), "Check total outstanding rounds appropriately");
	}

	public function testPlacedOrderImmutability() {
	
		$order = $this->objFromFixture("Order", "paid");
		$processor = OrderProcessor::create($order)->placeOrder();
		$this->assertEquals(408, $order->Total(), "check totals");

		//make a changes to existing products
		$this->mp3player->BasePrice = 100;
		$this->mp3player->write();
		$this->socks->BasePrice = 20;
		$this->socks->write();

		//total doesn't change
		$this->assertEquals(408, $order->Total());
		$this->assertFalse($order->isCart());

		//item values don't change
		$items = $order->Items()
			//hack join to make thigns work
			->innerJoin(
				"Product_OrderItem",
				'"OrderItem"."ID" = "Product_OrderItem"."ID"'
			); 
		$this->assertNotNull($items);
		$this->assertDOSEquals(array(
			array('ProductID' => $this->mp3player->ID,'Quantity' => 2, 'CalculatedTotal' => 400),
			array('ProductID' => $this->socks->ID, 'Quantity' => 1, 'CalculatedTotal' => 8)
		), $items);

		$mp3player = $items->find('ProductID', $this->mp3player->ID);//join needed to provide ProductID
		$this->assertNotNull($mp3player, "MP3 player is in order");
		$this->assertEquals(200, $mp3player->UnitPrice(), "Unit price remains the same");
		$this->assertEquals(400, $mp3player->Total(), "Total remains the same");

		$socks = $items->find('ProductID', $this->socks->ID);
		$this->assertNotNull($socks, "Socks are in order");
		$this->assertEquals(8, $socks->UnitPrice(), "Unit price remains the same");
		$this->assertEquals(8, $socks->Total(), "Total remains the same");
	}

	public function testCanFunctions() {
		$order = $this->objFromFixture("Order", "cart");
		$order->calculate();
		$this->assertTrue($order->canPay(), "can pay when order is in cart");
		$this->assertFalse($order->canCancel(), "can't cancel when order is in cart");
		$this->assertFalse($order->canDelete(), "never allow deleting orders");
		$this->assertTrue($order->canEdit(), "orders can be edited by anyone");
		$this->assertFalse($order->canCreate(), "no body can create orders manually");

		$order = $this->objFromFixture("Order", "unpaid");
		$this->assertTrue($order->canPay(), "can pay an order that is unpaid");
		$this->assertTrue($order->canCancel());
		$this->assertFalse($order->canDelete(), "never allow deleting orders");

		$order = $this->objFromFixture("Order", "paid");
		$this->assertFalse($order->canPay(), "paid order can't be paid for");
		$this->assertFalse($order->canCancel(), "paid order can't be cancelled");
		$this->assertFalse($order->canDelete(), "never allow deleting orders");

		$this->markTestIncomplete('check other statuses');
	}

	public function testDelete() {
		$order = $this->objFromFixture("Order", "unpaid");
		$order->delete();
		$this->markTestIncomplete('assertions!');
	}

}
