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
		$this->tshirt = $this->objFromFixture('Product', 'tshirt');
		$this->socks = $this->objFromFixture('Product', 'socks');
		$this->beachball = $this->objFromFixture('Product', 'beachball');
		$this->pdfbrochure = $this->objFromFixture('Product', 'pdfbrochure');
	}

	public function testCMSFields() {
		$fields = $this->tshirt->getCMSFields();
	}
	
	public function testCanPurchase() {
		$this->assertTrue($this->tshirt->canPurchase());
		$this->assertTrue($this->socks->canPurchase());
		$this->assertFalse($this->beachball->canPurchase(), "beach ball has AllowPurchase flag to 0");
		$this->assertFalse($this->pdfbrochure->canPurchase(), "pdf brochure has 0 price");
		//allow 0 prices
		Product::config()->allow_zero_price = true;
		$this->assertTrue($this->pdfbrochure->canPurchase());
		//disable purchasing globally
		Product::config()->global_allow_purchase = false;
		$this->assertFalse($this->tshirt->canPurchase());

		Product::config()->allow_zero_price = false;
		Product::config()->global_allow_purchase = true;
	}
	
	public function testSellingPrice() {
		$this->assertEquals(25, $this->tshirt->sellingPrice());
		$this->assertEquals(8, $this->socks->sellingPrice());
		$this->assertEquals(10, $this->beachball->sellingPrice());
		$this->assertEquals(0, $this->pdfbrochure->sellingPrice());

		$this->tshirt->BasePrice = -34;
		$this->assertEquals(0, $this->tshirt->sellingPrice());
	}

	public function testCreateItem() {
		$item = $this->tshirt->createItem(6);
		$this->assertEquals($this->tshirt->ID, $item->ProductID);
		$this->assertEquals(6, $item->Quantity);
		$this->assertEquals("Product_OrderItem", get_class($item));
	}

	public function testItem() {
		$item = $this->tshirt->Item();
		$this->assertEquals(1, $item->Quantity);
		$this->assertEquals(0, $item->ID);

		$sc = ShoppingCart::singleton();
		$sc->add($this->tshirt, 15);

		$this->assertTrue($this->tshirt->IsInCart());
		$item = $this->tshirt->Item();
		$this->assertEquals(15, $item->Quantity);
	}

	public function testCanViewProductPage() {
		$this->get(Director::makeRelative($this->tshirt->Link()));
		$this->get(Director::makeRelative($this->socks->Link()));
	}
	
}
