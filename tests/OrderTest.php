<?php
/**
 * @package ecommerce
 * @subpackage tests
 */
class OrderTest extends SapphireTest {
	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	
	protected $orig = array();

	function setUp() {
		parent::setUp();
		
		/*
		$this->orig['Product_site_currency'] = Product::site_currency();
		Product::set_site_currency('USD');
		
		$this->orig['Product_supported_currencies'] = Product::get_supported_currencies();
		Product::set_supported_currencies(array('EUR','USD','NZD'));
		
		$this->orig['Order_modifiers'] = Order::get_modifiers();
		Order::set_modifiers(array());
		*/
	}
	
	function tearDown() {
		parent::tearDown();
		
		/*
		Product::set_site_currency($this->orig['Product_site_currency']);
		Product::set_supported_currencies($this->orig['Product_supported_currencies']);
		Order::set_modifiers($this->orig['Order_modifiers']);
		*/
	}
	
	
	/* -------- OLD TESTS (to be removed) ------------------*/
	
	function old_testValidateProductCurrencies() {
		$productUSDOnly = $this->objFromFixture('Product', 'p1b');
		$orderInEUR = $this->objFromFixture('Order', 'open_order_eur');
	
		$invalidItem = new ProductOrderItem(null, null, $productUSDOnly, 1);
		$invalidItem->write();
		$orderInEUR->Items()->add($invalidItem);
		
		$validationResult = $orderInEUR->validate();
		$this->assertContains('No price found', $validationResult->message());
	}
	
	function old_testAllowedProducts() {
		$product1aNotAllowed = $this->objFromFixture('Product', 'p1a');
		$product2aUSD = $this->objFromFixture('Product', 'p2a');
		$product2bEURUSD = $this->objFromFixture('Product', 'p2b');
		
		$orderEUR = new Order();
		$orderEUR->Currency = 'EUR';
		$orderEUR->write();
		$this->assertNotContains($product2aUSD->ID, $orderEUR->AllowedProducts()->column('ID'));
		$this->assertNotContains($product1aNotAllowed->ID, $orderEUR->AllowedProducts()->column('ID'));
		$this->assertContains($product2bEURUSD->ID, $orderEUR->AllowedProducts()->column('ID'));
		
		$orderUSD = new Order();
		$orderUSD->Currency = 'USD';
		$orderUSD->write();
		$this->assertContains($product2aUSD->ID, $orderUSD->AllowedProducts()->column('ID'));
		$this->assertNotContains($product1aNotAllowed->ID, $orderUSD->AllowedProducts()->column('ID'));
		$this->assertContains($product2bEURUSD->ID, $orderUSD->AllowedProducts()->column('ID'));
	}
	
	function old_testSubtotalInDatabase() {
		$product1a = $this->objFromFixture('Product', 'p1a');
		$product1b = $this->objFromFixture('Product', 'p1b');
		
		// @todo Determine Order Currency automatically
		$order = new Order();
		$order->Currency = 'USD';
		$order->write();
		
		$item1a = new ProductOrderItem(null, null, $product1a, 2);
		$item1a->write();
		$order->Items()->add($item1a);
		$item1b = new ProductOrderItem(null, null, $product1b, 1);
		$item1b->write();
		$order->Items()->add($item1b);

		// 500 + 500 + 600
		$subtotal = $order->SubTotal;
		$this->assertEquals($subtotal->Amount, 1600);
		$this->assertEquals($subtotal->Currency, 'USD');
	}
	
	/**
	 * Test the lock status of an order.
	 */
	function old_testIsLocked() {
		$order = new Order();
		$order->write();
		
		$this->assertFalse($order->IsLocked());
		
		// order is still editable (it hasn't been checked out)
		$order->SystemStatus = Order::$statusTemporary;
		$order->write();
		$this->assertFalse($order->IsLocked());
		
		// order is still editable (it hasn't been checked out)
		$order->SystemStatus = Order::$statusDraft;
		$order->write();
		$this->assertFalse($order->IsLocked());
		
		// order is not editable (it has been checked out)
		$order->SystemStatus = Order::$statusAvailable;
		$order->write();
		$this->assertTrue($order->IsLocked());
		
		// order is not editable (it has been checked out)
		$order->SystemStatus = Order::$statusArchived;
		$order->write();
		$this->assertTrue($order->IsLocked());

		// order is not editable (it has been checked out)
		$order->SystemStatus = Order::$statusDeleted;
		$order->write();
		$this->assertTrue($order->IsLocked());
	}	
	
	function old_testProductOrderItems() {
		
		$product1a = $this->objFromFixture('Product', 'p1a');
		$product1b = $this->objFromFixture('Product', 'p1b');
		
		$order = new Order();
		$order->Currency = 'USD';
		$order->write();
		
		$item1a = new ProductOrderItem(null, null, $product1a, 2);
		$item1a->write();
		$order->Items()->add($item1a);
		$item1b = new ProductOrderItem(null, null, $product1b, 1);
		$item1b->write();
		$order->Items()->add($item1b);
		$item1c = new ProductOrderItem(null, null, $product1a, 1);
		$item1c->write();
		$order->Items()->add($item1c);

		$items = $order->ProductOrderItems();
		
		$testString = 'ProductList: ';
		
		foreach ($items as $item) {
			$testString .= $item->Product()->Title.";";
		}
		$this->assertEquals($testString, "ProductList: Product 1a;Product 1b;Product 1a;");
	}
}
?>