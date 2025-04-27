<?php

namespace SilverShop\Tests\Extension;

use PageController;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Order;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTest;
use SilverStripe\Dev\FunctionalTest;

class ViewableCartExtensionTest extends FunctionalTest
{
    public static $fixture_file  = __DIR__ . '/../Fixtures/shop.yml';
    public static bool $disable_theme = true;

    public function setUp(): void
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        ShopTest::setConfiguration();
        $this->logInWithPermission('ADMIN');
        $this->objFromFixture(Product::class, "socks")->publishSingle();
    }

    function testCart(): void
    {
        $order = $this->objFromFixture(Order::class, "cart");
        ShoppingCart::singleton()->setCurrent($order);
        $page = PageController::create();
        $this->assertEquals("$8.00", (string)$page->renderWith("CartTestTemplate"));
    }
}
