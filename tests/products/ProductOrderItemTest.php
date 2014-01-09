<?php
/**
 * @package shop
 * @subpackage tests
 */
class ProductOrderItemTest extends FunctionalTest {

	static $fixture_file = 'shop/tests/fixtures/shop.yml';

	static $disable_theme = true;
	static $orig = array();
	
	/**
	 * Create and publish some products.
	 */
	function setUp() {
		parent::setUp();
		ShopTest::setConfiguration();
		
		$this->mp3player = $this->objFromFixture('Product', 'mp3player');
		$this->socks = $this->objFromFixture('Product', 'socks');
		$this->beachball = $this->objFromFixture('Product', 'beachball');
		$this->hdtv = $this->objFromFixture('Product', 'hdtv');
		
		$this->mp3player->publish('Stage','Live');
		$this->socks->publish('Stage','Live');
		$this->beachball->publish('Stage','Live');
		$this->hdtv->publish('Stage','Live');
		
		$this->cart = ShoppingCart::singleton();
	}
	
	function testEmptyItem(){
		$emptyitem = $this->mp3player->Item();
		$this->assertEquals($emptyitem->Quantity,0);
	}
	
	/**
	 * Test product updates. These may be caused by an admin, causing everyone's cart to update.
	 * @TODO: this feature needs to be implemented
	 */
	function testProductVersionUpdate() {
		$this->cart->add($this->socks);
		$currentorder = $this->cart->current();
		$itembefore = $this->cart->get($this->socks);
		$this->assertEquals($itembefore->UnitPrice(),8,"unit price matches product price");
		//update product details, whilst items still incart
		$this->socks->BasePrice = 9;
		$this->socks->writeToStage('Stage');
		$this->socks->publish('Stage','Live');
		$itemafter = $this->cart->get($this->socks);
		$this->assertEquals($itemafter->UnitPrice(),9,"unit price matches updated product price");
	}

	/**
	* Tries to create an order item with a non-existent version.
	* @todo this generates a SilverStripe bug. It needs to be fixed in SS first.
	*/
	function testProductVersionDoesNotExist(){
		$brokenitem = new Product_OrderItem(array(
			"ProductID" => $this->socks->ID,
			"ProductVersion" => 99999 //non existent version
		));
		$this->assertNull($brokenitem->Product(),"version does not exist");
	}

	/**
	 * Check  the links are accurate
	 */
	function testLinks(){
		SecurityToken::disable();
		$product = $this->socks;
		$item = $product->Item();
		$this->assertEquals(
			"shoppingcart/add/Product/{$product->ID}",
			$item->addLink()
		);
		$this->assertEquals(
			"shoppingcart/remove/Product/{$product->ID}",
			$item->removeLink()
		);
		$this->assertEquals(
			"shoppingcart/removeall/Product/{$product->ID}",
			$item->removeallLink()
		);
		$this->assertEquals(
			"shoppingcart/setquantity/Product/{$product->ID}",
			$item->setquantityLink()
		);
	}

}