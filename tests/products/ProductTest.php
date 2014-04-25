<?php
/**
 * Test {@link Product}
 * 
 * @package shop
 */
class ProductTest extends FunctionalTest {
	
	protected static $fixture_file = 'shop/tests/fixtures/shop.yml';
	protected static $disable_theme = true;
	protected static $use_draft_site = true;
	
	function setUp() {
		parent::setUp();
		
	}

	public function testCanViewProductPage() {
		$p1a = $this->objFromFixture('Product', 'tshirt');
		$p2a = $this->objFromFixture('Product', 'socks');
		$this->get(Director::makeRelative($p1a->Link()));
		$this->get(Director::makeRelative($p2a->Link()));
	}
	
	function testProductVersion() {
		$this->markTestIncomplete('rewrite old test');
		// $product = $this->objFromFixture('Product', 'p1a');
		// $oldVersion = $product->Version;
		// $product->write();
		// $this->assertEquals($product->Version, $oldVersion);
		
		// $product->Title = 'Changed';
		// $product->write();
		// $this->assertTrue($product->Version > $oldVersion);
	}
	
	function testAllowedCurrencies() {
		$this->markTestIncomplete('rewrite old test');
		// $origProduct_supported_currencies = Product::get_supported_currencies();
		// Product::set_supported_currencies(array('EUR','NZD'));
		
		// $product = $this->objFromFixture('Product', 'p1a');
		// $this->assertEquals($product->Prices->Count(), 1);
		
		// // Should exclude USD price from fixture
		// $eurPrice = $product->Prices->First();
		// $this->assertEquals($eurPrice->Money->Currency, 'EUR');
		
		// Product::set_supported_currencies($origProduct_supported_currencies);
	}
	
	function testPrices() {
		$this->markTestIncomplete('rewrite old test');
		// $product = $this->objFromFixture('Product', 'p1a');
		// $this->assertEquals($product->Prices()->Count(), 2);
		
		// $usdPrice = $product->Prices()->First();
		// $this->assertEquals($usdPrice->Money->Currency, 'USD');
		// $this->assertEquals($usdPrice->Money->Amount, 500);
		
		// $eurPrice = $product->Prices()->Last();
		// $this->assertEquals($eurPrice->Money->Currency, 'EUR');
		$this->assertEquals($eurPrice->Money->Amount, 420);
	}
	
	function testPrice() {
		$this->markTestIncomplete('rewrite old test');
		// $product = $this->objFromFixture('Product', 'p1a');
		
		// $product->setCurrentCurrency('USD');
		// $this->assertType('ProductPrice', $product->Price);
		// $this->assertEquals($product->Price->Money->Currency, 'USD');
		// $this->assertEquals($product->Price->Money->Amount, 500);
		
		// $product->setCurrentCurrency('EUR');
		// $this->assertType('ProductPrice', $product->Price);
		// $this->assertEquals($product->Price->Money->Currency, 'EUR');
		// $this->assertEquals($product->Price->Money->Amount, 420);
		
		// // NZD is not in fixtures
		// $product->setCurrentCurrency('NZD');
		// $this->assertNull($product->Price);
	}
	
	function testUrlSegmentIsUnique() {
		$this->markTestIncomplete('rewrite old test');
		// $product = $this->objFromFixture('Product', 'p1a');
		// $newProduct = new Product();
		// $newProduct->URLSegment = $product->URLSegment;
		// $newProduct->write();
		// $this->assertNotNull($newProduct->URLSegment);
		// $this->assertNotEquals($newProduct->URLSegment, $product->URLSegment);
	}
	
	function testNewProductGetsUniqueUrlSegment() {
		$this->markTestIncomplete('rewrite old test');
		// Create a new product without a title
		// $newProduct = new Product();
		// $newProduct->write();
		// $this->assertNotNull($newProduct->URLSegment);
		// $existingProduct = DataObject::get_one(
		// 	'Product', 
		// 	sprintf(
		// 		'"Product"."URLSegment" = \'%s\' AND "Product"."ID" != %d', 
		// 		$newProduct->URLSegment, 
		// 		$newProduct->ID
		// 	)
		// );
		// $this->assertFalse($existingProduct);
	}
	
	function testSetCurrentCurrency() {
		$this->markTestIncomplete('rewrite old test');
		// $product = $this->objFromFixture('Product', 'p1a');
		
		// $this->assertType('ProductPrice', $product->Price);
		// $this->assertEquals($product->Price->Money->Currency, 'USD',
		// 	'Product::site_currency() is respected in Product->Price()'
		// );
		
		// $product->setCurrentCurrency('EUR');
		// $this->assertEquals($product->Price->Money->Currency, 'EUR',
		// 	'Product->setCurrentCurrency() is respected in Product->Price()'
		// );
	}

	function testGroupChildrenCount() {
		$this->markTestIncomplete('rewrite old test');
		// $group1 = $this->objFromFixture('ProductGroup', 'g1');
		// $this->assertEquals($group1->Products()->Count(), 2, 'The first group (g1) has 2 children Product pages.');
		
		// $group2 = $this->objFromFixture('ProductGroup', 'g2');
		// $this->assertEquals($group2->Products()->Count(), 2, 'The second group (g2) has 2 children Product pages.');
	}
	
	function testProductAttributes() {
		$this->markTestIncomplete('rewrite old test');
		// Programmatically check the attributes
		// $product = $this->objFromFixture('Product', 'p1a');
		// $product->setCurrentCurrency('USD');
		// $this->assertEquals($product->Price->Money->Amount, 500);
		// $this->assertEquals($product->Price->Money->Currency, 'USD');
	}

	function testProgrammaticCanPurchase() {
		$this->markTestIncomplete('rewrite old test');
		// $product = $this->objFromFixture('Product', 'p1a');
		// $product->setCurrentCurrency('USD');
		// $this->assertFalse($product->canPurchase(), 'We set AllowPurchase to 0 in the yml file, so we can\'t purchase the product.');
		// $product->AllowPurchase = 1;
		// $this->assertTrue($product->canPurchase(), 'We can purchase it now, because we set the boolean to TRUE.');
	}

	/**
	 * Test that when a user attempts to add the product
	 * to their cart by visiting some-url/add that they
	 * CANNOT because AllowPurchase() returns FALSE.
	 */
	function testFunctionalDenyAdd() {
		$this->markTestIncomplete('rewrite old test');
		// $product = $this->objFromFixture('Product', 'p1a');
		// $product->setCurrentCurrency('USD');
		// $this->assertFalse($product->canPurchase(), 'The flag for allow purchase is set to FALSE.');
		// $response = $this->get($product->addLink());
		// $this->assertEquals($response->getStatusCode(), 403, 
		// 	'Because we can\'t purchase the product, we get a blank page with no content.'
		// );
	}

	/**
	 * Test that when a user attempts to add the product
	 * to their cart by visiting some-url/add that they can
	 * because AllowPurchase() returns TRUE.
	 */
	function testFunctionalAllowAdd() {
		$this->markTestIncomplete('rewrite old test');
		// $product = $this->objFromFixture('Product', 'p2a');
		// $product->setCurrentCurrency('USD');
		// $this->assertTrue($product->canPurchase(), 'The flag for allow purchase is set to TRUE.');
		// $response = $this->get($product->addLink());
		// $this->assertEquals($response->getStatusCode(), 200, 
		// 	'We are allowed to purchase this product, we get redirected back.'
		// );
	}
	
}
