<?php
/**
 * Test {@link Product}
 * 
 * @package ecommerce
 */
class ProductTest extends FunctionalTest {
	
	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	
	static $disable_theme = true;
	
	static $use_draft_site = true;

	function testGroupChildrenCount() {
		$group1 = $this->objFromFixture('ProductGroup', 'g1');
		$this->assertEquals($group1->Children()->Count(), 2, 'The first group (g1) has 2 children Product pages.');
		
		$group2 = $this->objFromFixture('ProductGroup', 'g2');
		$this->assertEquals($group2->Children()->Count(), 2, 'The second group (g2) has 2 children Product pages.');
	}
	
	function testProductAttributes() {
		// Programmatically check the attributes
		$product = $this->objFromFixture('Product', 'p1a');
		$this->assertEquals($product->Price, 500, 'The price of product-1a is 500.');
		$this->assertEquals($product->Parent()->URLSegment, 'group-1', 'group-1 is product-1a\'s parent');
		
		// Check the actual HTML of the elements on the Product page
		//$this->get($product->URLSegment);
	}

	function testProgrammaticAllowPurchase() {
		$product = $this->objFromFixture('Product', 'p1a');
		$this->assertFalse($product->AllowPurchase(), 'We set AllowPurchase to 0 in the yml file, so we can\'t purchase the product.');
		$product->AllowPurchase = 1;
		$this->assertTrue($product->AllowPurchase(), 'We can purchase it now, because we set the boolean to TRUE.');
	}

	/**
	 * Test that when a user attempts to add the product
	 * to their cart by visiting some-url/add that they
	 * CANNOT because AllowPurchase() returns FALSE.
	 */
	function testFunctionalDenyAdd() {
		$product = $this->objFromFixture('Product', 'p1a');
		$this->assertFalse($product->AllowPurchase(), 'The flag for allow purchase is set to FALSE.');
		$response = $this->get($product->URLSegment . '/add');
		$this->assertTrue($response->getBody() == '', 'Because we can\'t purchase the product, we get a blank page with no content.');
	}

	/**
	 * Test that when a user attempts to add the product
	 * to their cart by visiting some-url/add that they can
	 * because AllowPurchase() returns TRUE.
	 */
	function testFunctionalAllowAdd() {
		$product = $this->objFromFixture('Product', 'p2a');
		$this->assertTrue($product->AllowPurchase(), 'The flag for allow purchase is set to TRUE.');
		$response = $this->get($product->URLSegment . '/add');
		$this->assertTrue($response->getBody() != '', 'We are allowed to purchase this product, we get redirected back.');
	}
	
}
?>