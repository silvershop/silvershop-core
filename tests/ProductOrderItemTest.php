<?php
/**
 * @package ecommerce
 * @subpackage tests
 */
class ProductOrderItemTest extends FunctionalTest {

	static $fixture_file = 'ecommerce/tests/ecommerce.yml';

	static $disable_theme = true;
	static $orig = array();

	function setUp() {
		parent::setUp();
		EcommerceTest::setConfiguration();

		$this->objFromFixture('Product', 'mp3player')->publish('Stage','Live');
		$this->objFromFixture('Product', 'socks')->publish('Stage','Live');
		$this->objFromFixture('Product', 'beachball')->publish('Stage','Live');
		$this->objFromFixture('Product', 'hdtv')->publish('Stage','Live');

		$this->objFromFixture('CheckoutPage', 'checkout')->publish('Stage','Live');
	}

	function testProductVersionDoesntChangeInOrder() {

		$productSocks = $this->objFromFixture('Product', 'socks');

		//add item to cart
		$this->get(ShoppingCart::add_item_link($productSocks->ID));

		$currentorder = ShoppingCart::current_order();
		$itembefore = DataObject::get_one("Product_OrderItem","\"OrderID\" = ".$currentorder->ID);

		$this->assertEquals($itembefore->Total(),8);

		//update product details
		$productSocks->Price = 9;
		$productSocks->write();

		$this->assertEquals($itembefore->Total(),9); //total is updated whilst item is still in cart

		//check cart product details are not updated
		//place order
		//update product details
		//check order details are not updated
	}

	/**
	 * Tries to create an order item with a non-existent version.
	 */
	function testProductVersionDoesNotExist(){

		$currentorder = ShoppingCart::current_order();
		$brokenitem = new Product_OrderItem(
			array(
				"ProductID" => $productSocks->ID,
				"ProductVersion" => 99999 //non existent version
			)
		);
		$this->assertEquals($brokenitem->UnitPrice(),null); //TODO: what should happen here???
	}

	function tearDown() {
		parent::tearDown();

		/*
		Product::set_site_currency($this->orig['Product_site_currency']);
		Product::set_supported_currencies($this->orig['Product_supported_currencies']);
		*/
	}


	/* --------------------- OLD TESTS (to be removed or rewritten) -----------------------*/

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