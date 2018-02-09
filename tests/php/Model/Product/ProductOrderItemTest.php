<?php

namespace SilverShop\Tests\Model\Product;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Product\OrderItem;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTest;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Security\SecurityToken;

/**
 * @package    shop
 * @subpackage tests
 */
class ProductOrderItemTest extends FunctionalTest
{
    public static $fixture_file = __DIR__ . '/../../Fixtures/shop.yml';
    public static $disable_theme = true;
    public static $orig = array();

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
    protected $hdtv;

    /**
     * Create and publish some products.
     */
    public function setUp()
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        ShopTest::setConfiguration();

        $this->logInWithPermission('ADMIN');
        $this->mp3player = $this->objFromFixture(Product::class, 'mp3player');
        $this->socks = $this->objFromFixture(Product::class, 'socks');
        $this->beachball = $this->objFromFixture(Product::class, 'beachball');
        $this->hdtv = $this->objFromFixture(Product::class, 'hdtv');

        $this->mp3player->publishSingle();
        $this->socks->publishSingle();
        $this->beachball->publishSingle();
        $this->hdtv->publishSingle();

        $this->cart = ShoppingCart::singleton();
    }

    public function testEmptyItem()
    {
        $emptyitem = $this->mp3player->Item();
        $this->assertEquals(1, $emptyitem->Quantity, "Items always have a quantity of at least 1.");
    }

    /**
     * Test product updates. These may be caused by an admin, causing everyone's cart to update.
     */
    public function testProductVersionUpdate()
    {
        $this->cart->add($this->socks);
        $currentorder = $this->cart->current();
        $itembefore = $this->cart->get($this->socks);
        $this->assertEquals($itembefore->UnitPrice(), 8, "unit price matches product price");
        //update product details, whilst items still incart
        $this->socks->BasePrice = 9;
        $this->socks->writeToStage('Stage');
        $this->socks->publishSingle();
        $itemafter = $this->cart->get($this->socks);
        $this->assertEquals($itemafter->UnitPrice(), 9, "unit price matches updated product price");
    }

    /**
     * Tries to create an order item with a non-existent version.
     */
    public function testProductVersionDoesNotExist()
    {
        $brokenitem = OrderItem::create()->update(
            [
                "ProductID" => $this->socks->ID,
                "ProductVersion" => 99999 //non existent version
            ]
        );
        $this->assertNull($brokenitem->Product(), "version does not exist");
    }

    /**
     * Check  the links are accurate
     */
    public function testLinks()
    {
        SecurityToken::disable();
        $product = $this->socks;
        $item = $product->Item();
        $this->assertEquals(
            "shoppingcart/add/SilverShop-Page-Product/{$product->ID}",
            $item->addLink()
        );
        $this->assertEquals(
            "shoppingcart/remove/SilverShop-Page-Product/{$product->ID}",
            $item->removeLink()
        );
        $this->assertEquals(
            "shoppingcart/removeall/SilverShop-Page-Product/{$product->ID}",
            $item->removeallLink()
        );
        $this->assertEquals(
            "shoppingcart/setquantity/SilverShop-Page-Product/{$product->ID}",
            $item->setquantityLink()
        );
    }

    /**
     * Coverage for a bug where there's an error generating the link when ProductID = 0
     */
    public function testCorruptedOrderItemLinks()
    {
        SecurityToken::disable();
        $product = $this->socks;
        $item = $product->Item();
        $item->ProductID = 0;
        $this->assertEquals('', $item->removeLink());
    }
}
