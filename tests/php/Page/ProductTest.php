<?php

namespace SilverShop\Tests\Page;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Order;
use SilverShop\Model\Product\OrderItem;
use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\ORM\DataObject;

/**
 * Test {@link Product}
 *
 * @package shop
 */
class ProductTest extends FunctionalTest
{
    protected static $fixture_file = __DIR__ . '/../Fixtures/shop.yml';
    protected static $disable_theme = true;
    protected static $use_draft_site = true;

    /**
     * @var Product
     */
    protected $mp3player;

    /**
     * @var Product
     */
    protected $socks;

    /**
     * @var Product
     */
    protected $beachball;

    /**
     * @var Product
     */
    protected $tshirt;

    /**
     * @var Product
     */
    protected $pdfbrochure;

    function setUp()
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        $this->mp3player = $this->objFromFixture(Product::class, "mp3player");
        $this->socks = $this->objFromFixture(Product::class, 'socks');
        $this->beachball = $this->objFromFixture(Product::class, 'beachball');
        $this->tshirt = $this->objFromFixture(Product::class, 'tshirt');
        $this->pdfbrochure = $this->objFromFixture(Product::class, 'pdfbrochure');
    }

    public function testCMSFields()
    {
        $fields = $this->tshirt->getCMSFields();
        $this->markTestIncomplete('Test Product CMS fields');
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
        $this->assertEquals(OrderItem::class, get_class($item));
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
        Product::add_extension(ProductTest_FractionalDiscountExtension::class);
        DataObject::flush_and_destroy_cache();
        $tshirt = Product::get()->byID($this->tshirt->ID);
        Config::modify()->set(Order::class, 'rounding_precision', 2);
        $this->assertEquals(24.99, $tshirt->sellingPrice());
        Config::modify()->set(Order::class, 'rounding_precision', 3);
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
            $this->objFromFixture(ProductCategory::class, "products")->ID,
        );
        $this->assertEquals(
            array_combine($expectedids, $expectedids),
            $this->beachball->getCategoryIDs()
        );
        $expectedids = array(
            $this->objFromFixture(ProductCategory::class, "products")->ID,
            $this->objFromFixture(ProductCategory::class, "electronics")->ID,
            $this->objFromFixture(ProductCategory::class, "musicplayers")->ID,
            $this->objFromFixture(ProductCategory::class, "clearance")->ID,
        );
        $this->assertEquals(
            array_combine($expectedids, $expectedids),
            $this->mp3player->getCategoryIDs()
        );
    }
}
