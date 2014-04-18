<?php

class ShoppingCartTest extends SapphireTest{

	public static $fixture_file = 'shop/tests/fixtures/shop.yml';
	public static $disable_theme = true;
	public static $use_draft_site = false;

	public function setUp() {
		parent::setUp();
		ShopTest::setConfiguration(); //reset config
		$this->cart = ShoppingCart::singleton();
		$this->product = $this->objFromFixture('Product', 'mp3player');
		$this->product->publish('Stage', 'Live');
	}

	public function testAddToCart() {
		$this->assertTrue((boolean)$this->cart->add($this->product), "add one item");
		$this->assertTrue((boolean)$this->cart->add($this->product), "add another item");
		$item = $this->cart->get($this->product);
		$this->assertEquals($item->Quantity, 2, "quantity is 2");
	}

	public function testRemoveFromCart() {
		$this->assertTrue((boolean)$this->cart->add($this->product), "add item");
		$this->assertTrue($this->cart->remove($this->product), "item was removed");
		$item = $this->cart->get($this->product);
		$this->assertFalse($item, "item not in cart");
		$this->assertFalse($this->cart->remove($this->product), "try remove non-existent item");
	}

	public function testSetQuantity() {
		$this->assertTrue((boolean)$this->cart->setQuantity($this->product, 25), "quantity set");
		$item = $this->cart->get($this->product);
		$this->assertEquals($item->Quantity, 25, "quantity is 25");
	}

	public function testClear() {
		//$this->assertFalse($this->cart->current(),"there is no cart initally");
		$this->assertTrue((boolean)$this->cart->add($this->product), "add one item");
		$this->assertTrue((boolean)$this->cart->add($this->product), "add another item");
		$this->assertEquals($this->cart->current()->class, "Order", "there a cart");
		$this->assertTrue($this->cart->clear(), "clear the cart");
		$this->assertFalse($this->cart->current(), "there is no cart");
	}

	public function testProductVariations() {
		$this->loadFixture('shop/tests/fixtures/variations.yml');
		$ball1 = $this->objFromFixture('ProductVariation', 'redlarge');
		$ball2 = $this->objFromFixture('ProductVariation', 'redsmall');

		$this->assertTrue((boolean)$this->cart->add($ball1), "add one item");
		$this->assertTrue((boolean)$this->cart->add($ball2), "add another item");
		$this->assertTrue($this->cart->remove($ball1), "remove first item");
		$this->assertFalse($this->cart->get($ball1), "first item not in cart");
		$this->assertNotNull($this->cart->get($ball1), "second item is in cart");
	}
}
