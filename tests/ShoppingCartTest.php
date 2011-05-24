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
	static $use_draft_site = true; //TODO: explain why
	
	function testAddToCart(){
		
		/* Retrieve the product to compare from fixture */
		$product = $this->objFromFixture('Product', 'mp3player');
		$noPurchaseProduct = $this->objFromFixture('Product','beachball');
		
		/* add items via url */
		$this->get(ShoppingCart::add_item_link($product->ID,$product->class)); //add item via url
		$this->get(ShoppingCart::add_item_link($product->ID,$product->class)); //add another
		/* attempt to add product with AllowPurcahse = false */
		
		//TODO: this fails becuase of user error. User errors should only occur when things really break, and we need to halt execution.
		//$this->get(ShoppingCart::add_item_link($noPurchaseProduct->ID,$noPurchaseProduct->class));  //insanity check
		
		/* See what's in the cart */
		$items = ShoppingCart::get_items();
		
		$this->assertEquals($items->Count(), 1, 'There is 1 item in the cart');

		/* We have the product that we asserted in our fixture file, with a quantity of 2 in the cart */
		$this->assertEquals($items->First()->BuyableID, $product->ID, 'We have the correct Product ID in the cart.');
		$this->assertEquals($items->First()->Quantity, 2, 'We have 2 of this product in the cart.');
		
		/* set item quantiy */
		$this->get(ShoppingCart::set_quantity_item_link($product->ID,$product->class)."?quantity=5"); //add item via url
		$items = ShoppingCart::get_items();
		$this->assertEquals($items->First()->Quantity, 5, 'We have 5 of this product in the cart.');
		
		//TODO: test adding a different item
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
		
		$this->assertEquals($items->Count(), 1, 'There is 1 item in the cart');
		$this->assertEquals($items->First()->Quantity, 4, 'We have 4 mp3 players in the cart.');
		
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
