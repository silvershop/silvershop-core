<?php
/**
 * @package ecommerce
 * @subpackage tests
 */
class ProductPriceTest extends SapphireTest {

	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	
	protected $orig = array();

	function setUp() {
		parent::setUp();
		
		$this->orig['Product_site_currency'] = Product::site_currency();
		Product::set_site_currency('USD');
		
		$this->orig['Product_supported_currencies'] = Product::get_supported_currencies();
		Product::set_supported_currencies(array('EUR','USD','NZD'));
	}
	
	function tearDown() {
		parent::tearDown();
		
		Product::set_site_currency($this->orig['Product_site_currency']);
		Product::set_supported_currencies($this->orig['Product_supported_currencies']);
	}
	
	function testProductVersionAutomaticallySet() {
		$p1a = $this->objFromFixture('Product', 'p1a');
		$price = new ProductPrice();
		$price->ProductID = $p1a->ID;
		$price->write();
		$this->assertEquals($price->ProductVersion, $p1a->Version);
	}

	function testCreateProductPriceForUnexistentProduct() {
		$this->setExpectedException('Exception');
		
		$p = new ProductPrice();
		$p->ProductID = 12345;
	}

	function testProductVersionIncreasedWhenPriceChanged() {
		$p1a = $this->objFromFixture('Product', 'p1a');
		$p1aOldVersion = $p1a->Version;
		$pr1aUSD = $this->objFromFixture('ProductPrice', 'pr1aUSD');

		// Write price without changes
		$pr1aUSD->write();
		$p1a = $pr1aUSD->Product();
		$this->assertEquals(
			$p1a->Version,
			$p1aOldVersion,
			'ProductPrice doesn\'t write new product version when not changed'
		);
		
		// Write price with changes
		$pr1aUSD->Money->setAmount(99);
		$pr1aUSD->write();

		// Check that product version has incremented
		$p1a = $pr1aUSD->Product();
		$this->assertTrue(
			$p1a->Version > $p1aOldVersion,
			'ProductPrice increases product version if price is changed'
		);

		// Check that product version matches
		$this->assertEquals(
			$pr1aUSD->ProductVersion, $p1a->Version,
			'ProductPrice points to new product version on the changed record'
		);
		
		// Check that both prices have the right version
		$p1a->flushCache();
		$pr1aEUR = $p1a->PriceByCurrency('EUR');
		$this->assertEquals(
			$pr1aEUR->ProductVersion, $p1a->Version,
			'ProductPrice points to new product version on all other prices on this product after a change to one price'
		);
		$pr1aUSD = $p1a->PriceByCurrency('USD');
		$this->assertEquals(
			$pr1aUSD->ProductVersion, $p1a->Version,
			'ProductPrice points to new product version on all other prices on this product after a change to one price'
		);
	}
	
	function testPriceVersionMatchesProductVersionWhenProductIsChanged() {
		$p1a = $this->objFromFixture('Product', 'p1a');
		$p1aOrigVersion = $p1a->Version;
		$pr1aUSD = $this->objFromFixture('ProductPrice', 'pr1aUSD');
		$pr1aEUR = $this->objFromFixture('ProductPrice', 'pr1aEUR');
		
		$p1a->Title = 'Altered';
		$p1a->write();
		
		$this->assertTrue($p1a->Version > $p1aOrigVersion);
		
		$pr1aUSD = $p1a->PriceByCurrency('USD');
		$this->assertEquals($pr1aUSD->ProductVersion, $p1a->Version);
		
		$pr1aEUR = $p1a->PriceByCurrency('EUR');
		$this->assertEquals($pr1aEUR->ProductVersion, $p1a->Version);
	}

	function testAllowPriceOncePerCurrencyThroughForeignKey() {
		$product = new Product();
		$product->write();
		
		// Successful add
		$priceUSD1 = new ProductPrice();
		$priceUSD1->Money->Currency = 'USD';
		$priceUSD1->ProductID = $product->ID;
		$priceUSD1->write();
		
		// Failed add
		$priceUSD2 = new ProductPrice();
		$priceUSD2->Money->Currency = 'USD';
		$priceUSD2->ProductID = $product->ID;
		
		$this->setExpectedException('ValidationException');
		$priceUSD2->write();
		
		// We have to reload the product, as it was changed
		// by the price onBeforeWrite() calls
		$product = DataObject::get_by_id('Product', $product->ID);
		
		// Only the successful add should be written to the product
		$this->assertEquals($product->Prices()->Count(), 1);
	}
	
	function testAllowPriceOncePerCurrencyThroughComponentSet() {
		$product = new Product();
		$product->write();
		
		// Successful add
		$priceUSD1 = new ProductPrice();
		$priceUSD1->Money->Currency = 'USD';
		$priceUSD1->ProductID = $product->ID;
		$priceUSD1->write();
		
		// Failed add
		$priceUSD3 = new ProductPrice();
		$priceUSD3->Money->Currency = 'USD';
		$priceUSD3->write();
		
		$this->setExpectedException('ValidationException');
		$product->Prices()->add($priceUSD3);
	}
	
	function testCantSavePriceInUnsupportedCurrency() {
		$this->setExpectedException('ValidationException');
		
		$price = new ProductPrice();
		$price->Money->Currency = 'CAD';
		$price->write();
	}

}
?>