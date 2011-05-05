<?php
/**
 * @package ecommerce
 * @subpackage tests
 */
class ProductOrderItemTest extends SapphireTest {
	
	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	
	static $disable_theme = true;
	
	static $use_draft_site = true;
	
	static $orig = array();
	
	function setUp() {
		parent::setUp();
		
		/*
		$this->orig['Product_site_currency'] = Product::site_currency();//fails here
		Product::set_site_currency('USD');
		
		$this->orig['Product_supported_currencies'] = Product::get_supported_currencies();
		Product::set_supported_currencies(array('EUR','USD','NZD'));
		*/
	}
	
	function tearDown() {
		parent::tearDown();
		
		/*
		Product::set_site_currency($this->orig['Product_site_currency']);
		Product::set_supported_currencies($this->orig['Product_supported_currencies']);
		*/
	}
	
	
	/* --------------------- OLD TESTS (to be removed) -----------------------*/

	function old_testProductPriceVersionDoesntChangeInOrder() {
		$poi1a = $this->objFromFixture('ProductOrderItem', 'poi1a');
		$origVersion = $poi1a->Product()->Version;
		
		$this->assertEquals($poi1a->UnitPrice->getAmount(), 500);
		
		$product1a = $this->objFromFixture('Product', 'p1a');
		$price1aUSD = $product1a->PriceByCurrency('USD');
		$price1aUSD->Money->setAmount(1000); // was 500 in fixture
		$price1aUSD->write();
		
		$this->assertEquals($poi1a->UnitPrice->getAmount(), 500);
		
		$this->assertEquals($poi1a->Product()->Version, $origVersion);
	}

	function old_testProductVersion() {
		$poi1a = $this->objFromFixture('ProductOrderItem', 'poi1a');
		$origProductTitle = $poi1a->Product()->Title;
		
		$product1a = $this->objFromFixture('Product', 'p1a');
		$product1a->Title = 'Changed';
		$product1a->write();
		
		$this->assertTrue(
			$poi1a->Product()->Version < $product1a->Version,
			"Order item sticks to older product version"
		);
		
		$this->assertNotEquals(
			$origProductTitle,
			$product1a->Title,
			"Order item gets properties of older version"
		);
	}

	function old_testValidateFailsWhenProductInWrongCurrency() {
		$product2aUSD = $this->objFromFixture('Product', 'p2a');
		$product2bEURUSD = $this->objFromFixture('Product', 'p2b');
		
		$order = new Order();
		$order->Currency = 'EUR';
		$order->write();
		$orderitem = new ProductOrderItem();
		$orderitem->write();
		$orderitem->OrderID = $order->ID;
		
		$this->assertType('ValidationResult', $orderitem->validate());
	}

	function old_testConstructorSetsProductRelationInMemory() {
		$product = $this->objFromFixture('Product', 'p1a');
		
		$orderitem = new ProductOrderItem(null, null, $product, 1);
		$this->assertNotNull($orderitem->Product());
		$this->assertEquals($orderitem->Product()->ID, $product->ID);
	}
	
	function old_testUnitPriceWithoutOrder() {
		$product = $this->objFromFixture('Product', 'p1a');
		
		$orderitem = new ProductOrderItem(null, null, $product, 1);

		$this->assertEquals($orderitem->UnitPrice->Amount, 500);
		$this->assertEquals($orderitem->UnitPrice->Currency, 'USD');
	}

	function old_testTotalWithQuantity() {
		$product = $this->objFromFixture('Product', 'p1a');
		
		$orderitem = new ProductOrderItem(null, null, $product, 2);
		$this->assertEquals($orderitem->Total->Amount, 1000);
	}
	
	function old_testTotalWithZeroQuantity() {
		$product = $this->objFromFixture('Product', 'p1a');
		
		$orderitem = new ProductOrderItem(null, null, $product, 0);
		$this->assertEquals($orderitem->Total->Amount, 0);
	}
	
	function old_testUnitPriceWithOrder() {
		$product = $this->objFromFixture('Product', 'p1a');
		$order = new Order();
		$order->Currency = 'EUR';
		$order->write();
		
		// @todo Currently you can't add items to an order directly
		$orderitem = new ProductOrderItem(null, null, $product, 1);
		$orderitem->OrderID = $order->ID;
		$orderitem->write();

		$this->assertEquals($orderitem->UnitPrice->Amount, 420);
		$this->assertEquals($orderitem->UnitPrice->Currency, 'EUR');
	}

}

?>