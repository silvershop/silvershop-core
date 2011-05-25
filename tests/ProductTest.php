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
	
	static $orig = array();
	
	function setUp() {
		parent::setUp();
		
		
		/*
		$this->orig['Product_site_currency'] = Product::site_currency(); //fails here
		Product::set_site_currency('USD');
		
		$this->orig['Product_supported_currencies'] = Product::get_supported_currencies();
		Product::set_supported_currencies(array('EUR','USD','NZD'));
		*/
	}
	
	function tearDown() {
		parent::tearDown();
		
		/*
		Product::set_site_currency($this->orig['Product_site_currency']);
		Product::set_supported_currencies($this->orig['Product_supported_currencies']);
		*/
	}
	
	
	/* -----------OLD TESTS (to be removed) ------------------ */
	
	
	//Note: PriceByCurrency does not exist
	function old_testPriceByCurrency() {
		$p1a = $this->objFromFixture('Product', 'p1a');
		
		$this->assertType('ProductPrice', $p1a->PriceByCurrency('EUR'));
		$this->assertType('ProductPrice', $p1a->PriceByCurrency('USD'));
	}
	
	function old_testProductVersion() {
		$product = $this->objFromFixture('Product', 'p1a');
		$oldVersion = $product->Version;
		$product->write();
		$this->assertEquals($product->Version, $oldVersion);
		
		$product->Title = 'Changed';
		$product->write();
		$this->assertTrue($product->Version > $oldVersion);
	}
	
	function old_testAllowedCurrencies() {
		$origProduct_supported_currencies = Product::get_supported_currencies();
		Product::set_supported_currencies(array('EUR','NZD'));
		
		$product = $this->objFromFixture('Product', 'p1a');
		$this->assertEquals($product->Prices->Count(), 1);
		
		// Should exclude USD price from fixture
		$eurPrice = $product->Prices->First();
		$this->assertEquals($eurPrice->Money->Currency, 'EUR');
		
		Product::set_supported_currencies($origProduct_supported_currencies);
	}
	
	function old_testPrices() {
		$product = $this->objFromFixture('Product', 'p1a');
		$this->assertEquals($product->Prices()->Count(), 2);
		
		$usdPrice = $product->Prices()->First();
		$this->assertEquals($usdPrice->Money->Currency, 'USD');
		$this->assertEquals($usdPrice->Money->Amount, 500);
		
		$eurPrice = $product->Prices()->Last();
		$this->assertEquals($eurPrice->Money->Currency, 'EUR');
		$this->assertEquals($eurPrice->Money->Amount, 420);
	}
	
	function old_testPrice() {
		$product = $this->objFromFixture('Product', 'p1a');
		
		$product->setCurrentCurrency('USD');
		$this->assertType('ProductPrice', $product->Price);
		$this->assertEquals($product->Price->Money->Currency, 'USD');
		$this->assertEquals($product->Price->Money->Amount, 500);
		
		$product->setCurrentCurrency('EUR');
		$this->assertType('ProductPrice', $product->Price);
		$this->assertEquals($product->Price->Money->Currency, 'EUR');
		$this->assertEquals($product->Price->Money->Amount, 420);
		
		// NZD is not in fixtures
		$product->setCurrentCurrency('NZD');
		$this->assertNull($product->Price);
	}
	
	function old_testUrlSegmentIsUnique() {
		$product = $this->objFromFixture('Product', 'p1a');
		$newProduct = new Product();
		$newProduct->URLSegment = $product->URLSegment;
		$newProduct->write();
		$this->assertNotNull($newProduct->URLSegment);
		$this->assertNotEquals($newProduct->URLSegment, $product->URLSegment);
	}
	
	function old_testNewProductGetsUniqueUrlSegment() {
		// Create a new product without a title
		$newProduct = new Product();
		$newProduct->write();
		$this->assertNotNull($newProduct->URLSegment);
		$existingProduct = DataObject::get_one(
			'Product', 
			sprintf(
				'"Product"."URLSegment" = \'%s\' AND "Product"."ID" != %d', 
				$newProduct->URLSegment, 
				$newProduct->ID
			)
		);
		$this->assertFalse($existingProduct);
	}
	
	function old_testSetCurrentCurrency() {
		$product = $this->objFromFixture('Product', 'p1a');
		
		$this->assertType('ProductPrice', $product->Price);
		$this->assertEquals($product->Price->Money->Currency, 'USD',
			'Product::site_currency() is respected in Product->Price()'
		);
		
		$product->setCurrentCurrency('EUR');
		$this->assertEquals($product->Price->Money->Currency, 'EUR',
			'Product->setCurrentCurrency() is respected in Product->Price()'
		);
	}

	function old_testGroupChildrenCount() {
		$group1 = $this->objFromFixture('ProductGroup', 'g1');
		$this->assertEquals($group1->Products()->Count(), 2, 'The first group (g1) has 2 children Product pages.');
		
		$group2 = $this->objFromFixture('ProductGroup', 'g2');
		$this->assertEquals($group2->Products()->Count(), 2, 'The second group (g2) has 2 children Product pages.');
	}
	
	function old_testProductAttributes() {
		// Programmatically check the attributes
		$product = $this->objFromFixture('Product', 'p1a');
		$product->setCurrentCurrency('USD');
		$this->assertEquals($product->Price->Money->Amount, 500);
		$this->assertEquals($product->Price->Money->Currency, 'USD');
	}

	function old_testProgrammaticCanPurchase() {
		$product = $this->objFromFixture('Product', 'p1a');
		$product->setCurrentCurrency('USD');
		$this->assertFalse($product->canPurchase(), 'We set AllowPurchase to 0 in the yml file, so we can\'t purchase the product.');
		$product->AllowPurchase = 1;
		$this->assertTrue($product->canPurchase(), 'We can purchase it now, because we set the boolean to TRUE.');
	}

	/**
	 * Test that when a user attempts to add the product
	 * to their cart by visiting some-url/add that they
	 * CANNOT because AllowPurchase() returns FALSE.
	 */
	function old_testFunctionalDenyAdd() {
		$product = $this->objFromFixture('Product', 'p1a');
		$product->setCurrentCurrency('USD');
		$this->assertFalse($product->canPurchase(), 'The flag for allow purchase is set to FALSE.');
		$response = $this->get($product->addLink());
		$this->assertEquals($response->getStatusCode(), 403, 
			'Because we can\'t purchase the product, we get a blank page with no content.'
		);
	}

	/**
	 * Test that when a user attempts to add the product
	 * to their cart by visiting some-url/add that they can
	 * because AllowPurchase() returns TRUE.
	 */
	function old_testFunctionalAllowAdd() {
		$product = $this->objFromFixture('Product', 'p2a');
		$product->setCurrentCurrency('USD');
		$this->assertTrue($product->canPurchase(), 'The flag for allow purchase is set to TRUE.');
		$response = $this->get($product->addLink());
		$this->assertEquals($response->getStatusCode(), 200, 
			'We are allowed to purchase this product, we get redirected back.'
		);
	}
	
}
?>