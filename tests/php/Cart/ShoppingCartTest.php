<?php

namespace SilverShop\Tests\Cart;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Order;
use SilverShop\Model\Variation\OrderItem;
use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;

class ShoppingCartTest extends SapphireTest
{
    protected static $fixture_file  = [
        '../Fixtures/shop.yml',
        '../Fixtures/variations.yml'
    ];

    public static $disable_theme  = true;

    protected static $use_draft_site = false;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var ShoppingCart
     */
    protected $cart;

    public function setUp(): void
    {
        parent::setUp();
        ShopTest::setConfiguration(); //reset config
        Config::modify()->set(Order::class, 'extensions', [ShoppingCartTest_TestShoppingCartHooksExtension::class]);

        ShoppingCart::singleton()->clear();
        ShoppingCartTest_TestShoppingCartHooksExtension::reset();

        $this->cart = ShoppingCart::singleton();
        $this->product = $this->objFromFixture(Product::class, 'mp3player');
        $this->product->publishSingle();
    }

    public function testAddToCart(): void
    {
        $this->assertTrue((boolean)$this->cart->add($this->product), "add one item");
        $this->assertEquals(
            ['onStartOrder', 'beforeAdd', 'afterAdd'],
            ShoppingCartTest_TestShoppingCartHooksExtension::$stack
        );

        $this->assertTrue((boolean)$this->cart->add($this->product), "add another item");
        $this->assertEquals(
            ['onStartOrder', 'beforeAdd', 'afterAdd', 'beforeAdd', 'afterAdd'],
            ShoppingCartTest_TestShoppingCartHooksExtension::$stack
        );

        $item = $this->cart->get($this->product);
        $this->assertEquals($item->Quantity, 2, "quantity is 2");
    }

    public function testRemoveFromCart(): void
    {
        $this->assertTrue((boolean)$this->cart->add($this->product), "add item");
        $this->assertEquals(
            ['onStartOrder', 'beforeAdd', 'afterAdd'],
            ShoppingCartTest_TestShoppingCartHooksExtension::$stack
        );

        $this->assertTrue($this->cart->remove($this->product), "item was removed");
        $this->assertEquals(
            ['onStartOrder', 'beforeAdd', 'afterAdd', 'beforeRemove', 'afterRemove'],
            ShoppingCartTest_TestShoppingCartHooksExtension::$stack
        );
        $item = $this->cart->get($this->product);
        $this->assertFalse((bool)$item, "item not in cart");
        $this->assertFalse($this->cart->remove($this->product), "try remove non-existent item");
    }

    public function testSetQuantity(): void
    {
        $this->assertTrue((boolean)$this->cart->setQuantity($this->product, 25), "quantity set");

        $this->assertEquals(
            ['onStartOrder', 'beforeSetQuantity', 'afterSetQuantity'],
            ShoppingCartTest_TestShoppingCartHooksExtension::$stack
        );

        $item = $this->cart->get($this->product);
        $this->assertEquals($item->Quantity, 25, "quantity is 25");
    }

    public function testClear(): void
    {
        $this->assertTrue((boolean)$this->cart->add($this->product), "add one item");
        $this->assertTrue((boolean)$this->cart->add($this->product), "add another item");
        $this->assertInstanceOf(Order::class, $this->cart->current(), "there's a cart");
        $this->assertTrue($this->cart->clear(), "clear the cart");
        $this->assertFalse((bool)$this->cart->current(), "there is no cart");
    }

    public function testCartSingleton(): void
    {
        $this->assertTrue((boolean)$this->cart->add($this->product), "add one item");
        $order = $this->cart->current();

        $this->assertEquals($order->ID, ShoppingCart::curr()->ID, "if singleton order ids will match");
    }

    public function testErrorInCartHooks(): void
    {
        Config::modify()->set(Order::class, 'extensions', [ShoppingCartTest_TestShoppingCartErroringHooksExtension::class]);

        ShoppingCart::singleton()->clear();
        $shoppingCart = ShoppingCart::singleton();

        $this->assertTrue((boolean)$this->cart->add($this->product, 1), "add one item");
        $item = $shoppingCart->get($this->product);
        $this->assertFalse(
            (boolean)$this->cart->add($this->product, 1),
            "Cannot add more than one item, extension will error"
        );
        $this->assertEquals($item->Quantity, 1, "quantity is 1");

        $this->assertTrue((boolean)$shoppingCart->setQuantity($this->product, 10), "quantity set");
        $item = $shoppingCart->get($this->product);
        $this->assertEquals($item->Quantity, 10, "quantity is 10");

        $this->assertFalse((boolean)$shoppingCart->setQuantity($this->product, 11), "Cannot set quantity to more than 10 items");
        $item = $shoppingCart->get($this->product);
        $this->assertEquals($item->Quantity, 10, "quantity is 10");
    }

    public function testProductVariations(): void
    {
        /** @var Variation $variation */
        $variation = $this->objFromFixture(Variation::class, 'redLarge');

        /** @var Variation $ball2 */
        $ball2 = $this->objFromFixture(Variation::class, 'redSmall');

        $this->assertTrue((boolean)$this->cart->add($variation), "add one item");

        $this->assertEquals(
            ['onStartOrder', 'beforeAdd', 'afterAdd'],
            ShoppingCartTest_TestShoppingCartHooksExtension::$stack
        );

        $this->assertTrue((boolean)$this->cart->add($ball2), "add another item");
        $this->assertTrue($this->cart->remove($variation), "remove first item");

        $this->assertEquals(
            ['onStartOrder', 'beforeAdd', 'afterAdd', 'beforeAdd', 'afterAdd', 'beforeRemove', 'afterRemove'],
            ShoppingCartTest_TestShoppingCartHooksExtension::$stack
        );

        $this->assertFalse((bool)$this->cart->get($variation), "first item not in cart");
        $this->assertNotNull($this->cart->get($ball2), "second item is in cart");

        /** @var Product $dataObject */
        $dataObject = $this->objFromFixture(Product::class, 'ball');

        $redLarge = $this->objFromFixture(Variation::class, 'redLarge');
        $redLarge->Price = 0;
        $redLarge->write();

        $dataObject->BasePrice = 0;
        $dataObject->write();

        $item = $this->cart->add($dataObject);
        $this->assertNotNull($item, "Product with variations can be added to cart");
        $this->assertInstanceOf(OrderItem::class, $item, 'A variation should be added to cart.');
        $this->assertEquals(20, $item->Buyable()->Price, 'The buyable variation was added');
    }
}
