<?php
/**
 * @link ShoppingCart_Controller
 * 
 * Test manipulating via urls.
 */
class ShoppingCartControllerTest extends FunctionalTest {

	static $fixture_file = 'shop/tests/ecommerce.yml';
	static $disable_theme = true;
	static $use_draft_site = false;

	function setUp(){
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
		
		$this->cart = ShoppingCart::getInstance();
	}

	function testAddToCart(){

		// add 2 of the same items via url
		$this->get(ShoppingCart_Controller::add_item_link($this->mp3player->ID)); //add item via url
		$this->get(ShoppingCart_Controller::add_item_link($this->mp3player->ID)); //add another
		$this->get(ShoppingCart_Controller::add_item_link($this->socks->ID)); //add a different product
		$this->get(ShoppingCart_Controller::add_item_link($this->noPurchaseProduct->ID));  //add a product that you can't add
		$this->get(ShoppingCart_Controller::add_item_link($this->draftProduct->ID));  //add a product that is draft
		$this->get(ShoppingCart_Controller::add_item_link($this->noPriceProduct->ID));  //add a product that has no price

		// See what's in the cart
		$items = ShoppingCart::get_items();

		$this->assertNotNull($items);
		if($items){
			$this->assertEquals($items->Count(), 2, 'There are 2 items in the cart');
			$mp3playeritem = $items->find('ProductID',$this->mp3player->ID);

			/* We have the product that we asserted in our fixture file, with a quantity of 2 in the cart */
			$this->assertEquals($mp3playeritem->ProductID, $this->mp3player->ID, 'We have the correct Product ID in the cart.');
			$this->assertEquals($mp3playeritem->Quantity, 2, 'We have 2 of this product in the cart.');

			/* set item quantiy */
			$this->get(ShoppingCart_Controller::set_quantity_item_link($this->mp3player->ID)."?quantity=5"); //add item via url
			$items = ShoppingCart::get_items();
			$mp3playeritem = $items->find('ProductID',$this->mp3player->ID);
			$this->assertEquals($mp3playeritem->Quantity, 5, 'We have 5 of this product in the cart.');

			/* non purchasable product checks */
			$this->assertEquals($this->noPurchaseProduct->canPurchase(),false,'non-purcahseable product is not purchaseable');
			$this->assertArrayNotHasKey($this->noPurchaseProduct->ID,$items->map('ProductID'),'non-purcahable product is not in cart');
			$this->assertEquals($this->draftProduct->canPurchase(),false,'draft product is not purchaseable');
			$this->assertArrayNotHasKey($this->draftProduct->ID,$items->map('ProductID'),'draft product is not in cart');
			$this->assertEquals($this->noPriceProduct->canPurchase(),false,'product without price is not purchaseable');
			$this->assertArrayNotHasKey($this->noPriceProduct->ID,$items->map('ProductID'),'product without price is not in cart');
		}
		
		$this->cart->clear();
	}

	function testRemoveFromCart(){

		/* add items via url */
		$this->get(ShoppingCart_Controller::set_quantity_item_link($this->mp3player->ID)."?quantity=5");
		$this->assertTrue($this->cart->get($this->mp3player) !== false,"mp3player item now exists in cart");
		$this->get(ShoppingCart_Controller::add_item_link($this->socks->ID));
		$this->assertTrue($this->cart->get($this->socks) !== false,"socks item now exists in cart");

		/* remove items via url */
		$this->get(ShoppingCart_Controller::remove_item_link($this->socks->ID)); //remove one different = remove completely
		$this->assertFalse($this->cart->get($this->socks));
		
		$this->get(ShoppingCart_Controller::remove_item_link($this->mp3player->ID)); //remove one product = 4 left

		$mp3playeritem = $this->cart->get($this->mp3player);
		$this->assertTrue($mp3playeritem !== false,"product still exists");
		$this->assertEquals($mp3playeritem->Quantity,4,"only 4 of item left");
		
		$items = ShoppingCart::get_items();
		$this->assertNotNull($items,"Cart is not empty");

		$this->cart->clear(); //test clearing cart
		$this->assertEquals(ShoppingCart::get_items(), null, 'Cart is clear'); //items is a databoject set, and will therefore be null when cart is empty.
	}

}