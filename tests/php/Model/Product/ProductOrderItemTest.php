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
    public static bool $disable_theme = true;
    public static array $orig = [];

    protected Product $mp3player;
    protected Product $socks;
    protected Product $beachball;
    protected Product $hdtv;
    protected ShoppingCart $cart;

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
        $this->beachball = $this->objFromFixture(Product::class, 'beachball');
        $this->hdtv = $this->objFromFixture(Product::class, 'hdtv');

        $this->mp3player->publishSingle();
        $this->socks->publishSingle();
        $this->beachball->publishSingle();
        $this->hdtv->publishSingle();

        $this->cart = ShoppingCart::singleton();
    }

    public function testEmptyItem(): void
    {
        $orderItem = $this->mp3player->Item();
        $this->assertEquals(1, $orderItem->Quantity, "Items always have a quantity of at least 1.");
    }

    /**
     * Test product updates. These may be caused by an admin, causing everyone's cart to update.
     */
    public function testProductVersionUpdate(): void
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
    public function testProductVersionDoesNotExist(): void
    {
        $orderItem = OrderItem::create()->update(
            [
                "ProductID" => $this->socks->ID,
                "ProductVersion" => 99999 //non existent version
            ]
        );
        $this->assertNull($orderItem->Product(), "version does not exist");
    }

    /**
     * Check  the links are accurate
     */
    public function testLinks(): void
    {
        SecurityToken::disable();
        $product = $this->socks;
        $orderItem = $product->Item();
        $this->assertEquals(
            "shoppingcart/add/SilverShop-Page-Product/{$product->ID}",
            $orderItem->addLink()
        );
        $this->assertEquals(
            "shoppingcart/remove/SilverShop-Page-Product/{$product->ID}",
            $orderItem->removeLink()
        );
        $this->assertEquals(
            "shoppingcart/removeall/SilverShop-Page-Product/{$product->ID}",
            $orderItem->removeAllLink()
        );
        $this->assertEquals(
            "shoppingcart/setquantity/SilverShop-Page-Product/{$product->ID}",
            $orderItem->setQuantityLink()
        );
    }

    /**
     * Coverage for a bug where there's an error generating the link when ProductID = 0
     */
    public function testCorruptedOrderItemLinks(): void
    {
        SecurityToken::disable();
        $product = $this->socks;
        $orderItem = $product->Item();
        $orderItem->ProductID = 0;
        $this->assertEquals('', $orderItem->removeLink());
    }
}
