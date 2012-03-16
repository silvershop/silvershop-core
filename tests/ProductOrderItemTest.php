<?php
/**
 * @package shop
 * @subpackage tests
 */
class ProductOrderItemTest extends FunctionalTest {

	static $fixture_file = 'shop/tests/ecommerce.yml';

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
		
		$this->cart = ShoppingCart::getInstance();
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
		//update product details
		$this->socks->Price = 9;
		$this->socks->write();
		$itemafter = $this->cart->get($this->socks);
		$this->assertEquals($itemafter->UnitPrice(),9,"unit price matches updated product price"); //total is updated whilst item is still in cart
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
		$this->assertEquals($brokenitem->UnitPrice(),0,"version does not exist");
	}

}