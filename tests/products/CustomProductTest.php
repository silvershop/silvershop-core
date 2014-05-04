<?php

/**
 * @package shop
 * @subpackage tests
 */
class CustomProductTest extends FunctionalTest {

	public static $fixture_file = 'shop/tests/fixtures/customproduct.yml';

	protected $extraDataObjects = array(
		"CustomProduct",
		"CustomProduct_OrderItem"
	);

	public function setUp() {
		parent::setUp();
		$this->thing = $this->objFromFixture("CustomProduct", "thing");
	}

	public function testCustomProduct() {
		$cart = ShoppingCart::singleton();

		$options1 = array('Color' => 'Green','Size' => 5,'Premium' => true);
		$this->assertTrue((bool)$cart->add($this->thing, 1, $options1), "add to customisation 1 to cart");
		$item = $cart->get($this->thing, $options1);

		$this->assertTrue((bool)$item, "item with customisation 1 exists");
		$this->assertEquals(1, $item->Quantity);

		$this->assertTrue((bool)$cart->add($this->thing, 2, $options1), "add another two customisation 1");
		$item = $cart->get($this->thing, $options1);
		$this->assertEquals(3, $item->Quantity, "quantity has updated correctly");
		$this->assertEquals("Green", $item->Color);
		$this->assertEquals(5, $item->Size);
		$this->assertEquals(1, $item->Premium); //should be true?

		$this->assertFalse($cart->get($this->thing), "try to get a non-customised product");

		$options2 =  array('Color' => 'Blue','Size' => 6, 'Premium' => false);
		$this->assertTrue((bool)$cart->add($this->thing, 5, $options2), "add customisation 2 to cart");
		$item = $cart->get($this->thing, $options2);
		$this->assertTrue((bool)$item, "item with customisation 2 exists");
		$this->assertEquals(5, $item->Quantity);

		$options3 = array('Color' => 'Blue');
		$this->assertTrue((bool)$cart->add($this->thing, 1, $options3), "add a sub-variant of customisation 2");
		$item = $cart->get($this->thing, $options3);

		$this->assertTrue((bool)$cart->add($this->thing), "add product with no customisation");
		$item = $cart->get($this->thing);

		$order = $cart->current();
		$items = $order->Items();
		$this->assertEquals(4, $items->Count(), "4 items in cart");

		//remove
		$cart->remove($this->thing, 2, $options2);
		$item = $cart->get($this->thing, $options2);
		$this->assertNotNull($item, 'item exists in cart');
		$this->assertEquals(3, $item->Quantity);

		$cart->clear();

		//set quantity
		$options4 = array('Size' => 12, 'Color' => 'Blue');
		$resp = $cart->setQuantity($this->thing, 5, $options4);

		$item = $cart->get($this->thing, $options4);
		$this->assertTrue((bool)$item, 'item exists in cart');

		$this->assertEquals(5, $item->Quantity, "quantity is 5");

		$this->markTestIncomplete("what about default values that have been set");
		//test by using urls
		//add a partial match		
	}

}

/**
 * @package shop
 * @subpackage tests
 */
class CustomProduct extends DataObject implements Buyable, TestOnly {

	private static $db = array(
		'Title' => 'Varchar',
		'Price' => 'Currency'
	);

	private static $order_item = 'CustomProduct_OrderItem';

	public function createItem($quantity = 1, $filter = array()) {
		$itemclass = self::config()->order_item;
		$item = new $itemclass();
		$item->ProductID = $this->ID;
		if($filter){
			$item->update($filter);
		}
		return $item;
	}

	public function canPurchase($member = null, $quantity = 1) {
		return $this->Price > 0;
	}

	public function sellingPrice() {
		return $this->Price;
	}

}

/**
 * @package shop
 * @subpackage tests
 */
class CustomProduct_OrderItem extends OrderItem implements TestOnly {

	private static $db = array(
		'Color' => "Enum('Red,Green,Blue','Red')",
		'Size' => 'Int',
		'Premium' => 'Boolean'
	);

	private static $defaults = array(
		'Color' => 'Red',
		'Premium' => false
	);

	private static $has_one = array(
		'Product' => 'CustomProduct',
		'Recipient' => 'Member'
	);

	private static $buyable_relationship = "Product";

	private static $required_fields = array(
		'Color',
		'Size',
		'Premium',
		'Recipient'
	);

	public function UnitPrice() {
		if($product = $this->Product()){
			return $product->Price;
		}
		return 0;
	}

}
