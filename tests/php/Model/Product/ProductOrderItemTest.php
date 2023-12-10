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
    public static $orig = [];

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
    protected $beachBall;

    /**
     * @var Product
     */
    protected $hdtv;

    /**
     * @var ShoppingCart
     */
    protected $cart;

    /**
     * Create and publish some products.
     */
    public function setUp(): void
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        ShopTest::setConfiguration();

        $this->logInWithPermission('ADMIN');
        $this->mp3player = $this->objFromFixture(Product::class, 'mp3player');
        $this->socks = $this->objFromFixture(Product::class, 'socks');
        $this->beachBall = $this->objFromFixture(Product::class, 'beachBall');
        $this->hdtv = $this->objFromFixture(Product::class, 'hdtv');

        $this->mp3player->publishSingle();
        $this->socks->publishSingle();
        $this->beachBall->publishSingle();
        $this->hdtv->publishSingle();

        $this->cart = ShoppingCart::singleton();
    }

    public function testEmptyItem()
    {
        $emptyItem = $this->mp3player->Item();
        $this->assertEquals(1, $emptyItem->Quantity, "Items always have a quantity of at least 1.");
    }

    /**
     * Test product updates. These may be caused by an admin, causing everyone's cart to update.
     */
    public function testProductVersionUpdate()
    {
        $this->cart->add($this->socks);

        $itemBefore = $this->cart->get($this->socks);
        $this->assertEquals($itemBefore->UnitPrice(), 8, "unit price matches product price");

        // update product details, whilst items still incart
        $this->socks->BasePrice = 9;
        $this->socks->writeToStage('Stage');
        $this->socks->publishSingle();

        $itemAfter = $this->cart->get($this->socks);
        $this->assertEquals($itemAfter->UnitPrice(), 9, "unit price matches updated product price");
    }

    /**
     * Tries to create an order item with a non-existent version.
     */
    public function testProductVersionDoesNotExist()
    {
        $brokenItem = OrderItem::create()->update(
            [
                "ProductID" => $this->socks->ID,
                "ProductVersion" => 99999 //non existent version
            ]
        );
        $this->assertNull($brokenItem->Product(), "version does not exist");
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
            $item->removeAllLink()
        );
        $this->assertEquals(
            "shoppingcart/setquantity/SilverShop-Page-Product/{$product->ID}",
            $item->setQuantityLink()
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
