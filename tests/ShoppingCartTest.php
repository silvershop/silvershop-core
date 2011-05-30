<?php
/**
 * Test {@link ShoppingCart}
 * 
 * Make sure to test modifying the cart via:
 * - Direct API access	
 * - URL links
 * - Form submission
 * 
 */
class ShoppingCartTest extends FunctionalTest {

	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	static $disable_theme = true;
	static $use_draft_site = false;
	
	function setUp(){
		parent::setUp();
		
		//publish some products
		$this->objFromFixture('Product', 'mp3player')->publish('Stage','Live');
		$this->objFromFixture('Product', 'socks')->publish('Stage','Live');
		$this->objFromFixture('Product', 'beachball')->publish('Stage','Live');
		$this->objFromFixture('Product', 'hdtv')->publish('Stage','Live');
		//note that we don't publish 'tshirt'... we want it to remain in draft form.
	}
	
	function testAddToCart(){
		
		/* Retrieve the product to compare from fixture */
		$product = $this->objFromFixture('Product', 'mp3player');
		$differentproduct = $this->objFromFixture('Product', 'socks');
		//products that can't be purchased
		$noPurchaseProduct = $this->objFromFixture('Product', 'beachball');
		$draftProduct = $this->objFromFixture('Product','tshirt');
		$noPriceProduct = $this->objFromFixture('Product','hdtv');
		
		/* add 2 of the same items via url */
		$this->get(ShoppingCart::add_item_link($product->ID)); //add item via url
		$this->get(ShoppingCart::add_item_link($product->ID)); //add another
		$this->get(ShoppingCart::add_item_link($differentproduct->ID)); //add a different product
		$this->get(ShoppingCart::add_item_link($noPurchaseProduct->ID));  //add a product that you can't add
		$this->get(ShoppingCart::add_item_link($draftProduct->ID));  //add a product that is draft
		$this->get(ShoppingCart::add_item_link($noPriceProduct->ID));  //add a product that has no price
		
		/* See what's in the cart */
		$items = ShoppingCart::get_items();
		
		$this->assertNotNull($items);
		if($items){
			$this->assertEquals($items->Count(), 2, 'There are 2 items in the cart');
			$mp3playeritem = $items->find('ProductID',$product->ID);
			
			/* We have the product that we asserted in our fixture file, with a quantity of 2 in the cart */
			$this->assertEquals($mp3playeritem->ProductID, $product->ID, 'We have the correct Product ID in the cart.');
			$this->assertEquals($mp3playeritem->Quantity, 2, 'We have 2 of this product in the cart.');
			
			/* set item quantiy */
			$this->get(ShoppingCart::set_quantity_item_link($product->ID,$product->class)."?quantity=5"); //add item via url
			$items = ShoppingCart::get_items();
			$mp3playeritem = $items->find('ProductID',$product->ID);
			$this->assertEquals($mp3playeritem->Quantity, 5, 'We have 5 of this product in the cart.');
			
			/* non purchasable product checks */
			$this->assertEquals($noPurchaseProduct->canPurchase(),false,'non-purcahseable product is not purchaseable');
			$this->assertArrayNotHasKey($noPurchaseProduct->ID,$items->map('ProductID'),'non-purcahable product is not in cart');
			$this->assertEquals($draftProduct->canPurchase(),false,'draft product is not purchaseable');
			$this->assertArrayNotHasKey($draftProduct->ID,$items->map('ProductID'),'draft product is not in cart');
			$this->assertEquals($noPriceProduct->canPurchase(),false,'product without price is not purchaseable');
			$this->assertArrayNotHasKey($noPriceProduct->ID,$items->map('ProductID'),'product without price is not in cart');
		}
		//TODO: price checks
		//TODO: adding item directly via class API
		//TODO: adding via form submission
		
	}
	
	function testRemoveFromCart(){
		
		/* Retrieve the product to compare from fixture */		
		$product = $this->objFromFixture('Product', 'mp3player');
		$anotherproduct = $this->objFromFixture('Product','tshirt');
		
		/* add items via url */
		$this->get(ShoppingCart::set_quantity_item_link($product->ID,$product->class)."?quantity=5");
		$this->get(ShoppingCart::add_item_link($anotherproduct->ID,$product->class));
		
		/* remove items via url */
		$this->get(ShoppingCart::remove_item_link($product->ID,$product->class));
		$this->get(ShoppingCart::remove_item_link($anotherproduct->ID,$product->class));
		
		$items = ShoppingCart::get_items();
		
		$this->assertNotNull($items);
		if($items){
			$this->assertEquals($items->Count(), 1, 'There is 1 item in the cart');
			$this->assertEquals($items->First()->Quantity, 4, 'We have 4 mp3 players in the cart.');
		}
		
		ShoppingCart::clear(); //test clearing cart
		$this->assertEquals(ShoppingCart::get_items(), null, 'Cart is clear'); //items is a databoject set, and will therefore be null when cart is empty.
		
		//TODO: remove item not in cart - insanity check
	}
	
	//TODO
	function todo_testParameterisedProduct(){
		$this->assertTrue(false); //stub for unit test
	}

}
?>
