<?php

/**
 * Test {@link Product}
 *
 * @package shop
 */
class ProductTest extends FunctionalTest
{
    protected static $fixture_file   = 'silvershop/tests/fixtures/shop.yml';
    protected static $disable_theme  = true;
    protected static $use_draft_site = true;

    function setUp()
    {
        parent::setUp();
        $this->tshirt = $this->objFromFixture('Product', 'tshirt');
        $this->socks = $this->objFromFixture('Product', 'socks');
        $this->beachball = $this->objFromFixture('Product', 'beachball');
        $this->pdfbrochure = $this->objFromFixture('Product', 'pdfbrochure');
        $this->mp3player = $this->objFromFixture("Product", "mp3player");
    }

    public function testCMSFields()
    {
        $fields = $this->tshirt->getCMSFields();
    }

    public function testCanPurchase()
    {
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

    public function testSellingPrice()
    {
        $this->assertEquals(25, $this->tshirt->sellingPrice());
        $this->assertEquals(8, $this->socks->sellingPrice());
        $this->assertEquals(10, $this->beachball->sellingPrice());
        $this->assertEquals(0, $this->pdfbrochure->sellingPrice());

        $this->tshirt->BasePrice = -34;
        $this->assertEquals(0, $this->tshirt->sellingPrice());
    }

    public function testCreateItem()
    {
        $item = $this->tshirt->createItem(6);
        $this->assertEquals($this->tshirt->ID, $item->ProductID);
        $this->assertEquals(6, $item->Quantity);
        $this->assertEquals("Product_OrderItem", get_class($item));
    }

    public function testItem()
    {
        $this->assertFalse($this->tshirt->IsInCart(), "tshirt is not in cart");

        $item = $this->tshirt->Item();
        $this->assertEquals(1, $item->Quantity);
        $this->assertEquals(0, $item->ID);

        $sc = ShoppingCart::singleton();
        $sc->add($this->tshirt, 15);

        $this->assertTrue($this->tshirt->IsInCart(), "tshirt is in cart");
        $item = $this->tshirt->Item();
        $this->assertEquals(15, $item->Quantity);
    }

    public function testDiscountRoundingError()
    {
        // This extension adds a fractional discount, which could cause
        // the displayed unit price not to match the charged price at
        // large quantities.
        Product::add_extension('ProductTest_FractionalDiscountExtension');
        DataObject::flush_and_destroy_cache();
        $tshirt = Product::get()->byID($this->tshirt->ID);
        Config::inst()->update('Order', 'rounding_precision', 2);
        $this->assertEquals(24.99, $tshirt->sellingPrice());
        Config::inst()->update('Order', 'rounding_precision', 3);
        $this->assertEquals(24.985, $tshirt->sellingPrice());
        Product::remove_extension('ProductTest_FractionalDiscountExtension');
    }

    public function testCanViewProductPage()
    {
        $this->get(Director::makeRelative($this->tshirt->Link()));
        $this->get(Director::makeRelative($this->socks->Link()));
    }

    public function testCategories()
    {
        $expectedids = array(
            $this->objFromFixture("ProductCategory", "products")->ID,
        );
        $this->assertEquals(
            array_combine($expectedids, $expectedids),
            $this->beachball->getCategoryIDs()
        );
        $expectedids = array(
            $this->objFromFixture("ProductCategory", "products")->ID,
            $this->objFromFixture("ProductCategory", "electronics")->ID,
            $this->objFromFixture("ProductCategory", "musicplayers")->ID,
            $this->objFromFixture("ProductCategory", "clearance")->ID,
        );
        $this->assertEquals(
            array_combine($expectedids, $expectedids),
            $this->mp3player->getCategoryIDs()
        );
    }
}

class ProductTest_FractionalDiscountExtension extends DataExtension implements TestOnly
{
    public function updateSellingPrice(&$price)
    {
        $price -= 0.015;
    }
}
