<?php
/**
 * @link ShoppingCart_Controller
 *
 * Test manipulating via urls.
 */
class ShoppingCartControllerTest extends FunctionalTest {

	public static $fixture_file = 'shop/tests/fixtures/shop.yml';
	public static $disable_theme = true;
	public static $use_draft_site = false;

	public function setUp(){
		parent::setUp();

		ShopTest::setConfiguration(); //reset config

		$this->mp3player = $this->objFromFixture('Product', 'mp3player');
		$this->socks = $this->objFromFixture('Product', 'socks');
		//products that can't be purchased
		$this->noPurchaseProduct = $this->objFromFixture('Product', 'beachball');
		$this->draftProduct = $this->objFromFixture('Product','tshirt');
		$this->noPriceProduct = $this->objFromFixture('Product','hdtv');

		//publish some products
		$this->mp3player->publish('Stage','Live');
		$this->socks->publish('Stage','Live');
		$this->noPurchaseProduct->publish('Stage','Live');
		$this->noPriceProduct->publish('Stage','Live');
		//note that we don't publish 'tshirt'... we want it to remain in draft form.

		$this->cart = ShoppingCart::singleton();
	}

	public function testAddToCart(){
		// add 2 of the same items via url
		$this->get(ShoppingCart_Controller::add_item_link($this->mp3player)); //add item via url
		$this->get(ShoppingCart_Controller::add_item_link($this->mp3player)); //add another
		$this->get(ShoppingCart_Controller::add_item_link($this->socks)); //add a different product
		$this->get(ShoppingCart_Controller::add_item_link($this->noPurchaseProduct));  //add a product that you can't add
		$this->get(ShoppingCart_Controller::add_item_link($this->draftProduct));  //add a product that is draft
		$this->get(ShoppingCart_Controller::add_item_link($this->noPriceProduct));  //add a product that has no price

		// See what's in the cart
		$items = ShoppingCart::curr()->Items();
		$this->assertNotNull($items);

		$this->assertEquals($items->Count(), 2, 'There are 2 items in the cart');
		//join needed to provide ProductID
		$mp3playeritem = $items->innerJoin("Product_OrderItem","\"OrderItem\".\"ID\" = \"Product_OrderItem\".\"ID\"")->find('ProductID',$this->mp3player->ID);	//join needed to provide ProductID
		$this->assertNotNull($mp3playeritem, "Mp3 player is in cart");

		// We have the product that we asserted in our fixture file, with a quantity of 2 in the cart
		$this->assertEquals($mp3playeritem->ProductID, $this->mp3player->ID, 'We have the correct Product ID in the cart.');
		$this->assertEquals($mp3playeritem->Quantity, 2, 'We have 2 of this product in the cart.');

		// set item quantiy
		$this->get(ShoppingCart_Controller::set_quantity_item_link($this->mp3player,array('quantity' => 5))); //add item via url
		$items = ShoppingCart::curr()->Items();
		$mp3playeritem = $items->innerJoin("Product_OrderItem","\"OrderItem\".\"ID\" = \"Product_OrderItem\".\"ID\"")->find('ProductID',$this->mp3player->ID); //join needed to provide ProductID
		$this->assertEquals($mp3playeritem->Quantity, 5, 'We have 5 of this product in the cart.');

		// non purchasable product checks
		$this->assertEquals($this->noPurchaseProduct->canPurchase(),false,'non-purcahseable product is not purchaseable');
		$this->assertArrayNotHasKey($this->noPurchaseProduct->ID,$items->map('ProductID')->toArray(),'non-purcahable product is not in cart');
		$this->assertEquals($this->draftProduct->canPurchase(), true, 'draft products can be purchased');
		$this->assertArrayNotHasKey($this->draftProduct->ID,$items->map('ProductID')->toArray(),'draft product is not in cart');
		$this->assertEquals($this->noPriceProduct->canPurchase(),false,'product without price is not purchaseable');
		$this->assertArrayNotHasKey($this->noPriceProduct->ID,$items->map('ProductID')->toArray(),'product without price is not in cart');

		$this->cart->clear();
	}

	public function testRemoveFromCart(){

		// add items via url
		$this->get(ShoppingCart_Controller::set_quantity_item_link($this->mp3player,array('quantity' => 5)));
		$this->assertTrue($this->cart->get($this->mp3player) !== false,"mp3player item now exists in cart");
		$this->get(ShoppingCart_Controller::add_item_link($this->socks));
		$this->assertTrue($this->cart->get($this->socks) !== false,"socks item now exists in cart");

		// remove items via url
		$this->get(ShoppingCart_Controller::remove_item_link($this->socks)); //remove one different = remove completely
		$this->assertFalse($this->cart->get($this->socks));

		$this->get(ShoppingCart_Controller::remove_item_link($this->mp3player)); //remove one product = 4 left

		$mp3playeritem = $this->cart->get($this->mp3player);
		$this->assertTrue($mp3playeritem !== false,"product still exists");
		$this->assertEquals($mp3playeritem->Quantity,4,"only 4 of item left");

		$items = ShoppingCart::curr()->Items();
		$this->assertNotNull($items,"Cart is not empty");

		$this->cart->clear(); //test clearing cart
		$this->assertEquals(ShoppingCart::curr(), null, 'Cart is clear'); //items is a databoject set, and will therefore be null when cart is empty.
	}

	public function testVariations(){
		$this->loadFixture('shop/tests/fixtures/variations.yml');
		$ballRoot = $this->objFromFixture('Product', 'ball');
		$ballRoot->publish('Stage','Live');
		$ball1 = $this->objFromFixture('ProductVariation', 'redlarge');
		$ball2 = $this->objFromFixture('ProductVariation', 'redsmall');

		// Add the two variation items
		$this->get(ShoppingCart_Controller::add_item_link($ball1));
		$this->get(ShoppingCart_Controller::add_item_link($ball2));
		$items = ShoppingCart::curr()->Items();
		$this->assertNotNull($items);
		$this->assertEquals($items->Count(), 2,          'There are 2 items in the cart');

		// Remove one and see what happens
		$this->get(ShoppingCart_Controller::remove_all_item_link($ball1));
		$this->assertEquals($items->Count(), 1,          'There is 1 item in the cart');
		$this->assertFalse($this->cart->get($ball1),     "first item not in cart");
		$this->assertNotNull($this->cart->get($ball1),   "second item is in cart");
	}

}
