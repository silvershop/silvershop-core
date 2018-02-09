<?php

namespace SilverShop\Tests\Extension;

use PageController;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Order;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTest;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Dev\SapphireTest;

class ViewableCartExtensionTest extends FunctionalTest
{
    public static $fixture_file  = __DIR__ . '/../Fixtures/shop.yml';
    public static $disable_theme = true;

    function setUp()
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        ShopTest::setConfiguration();
        $this->logInWithPermission('ADMIN');
        $this->objFromFixture(Product::class, "socks")->publishSingle();
    }

    function testCart()
    {
        $cart = $this->objFromFixture(Order::class, "cart");
        ShoppingCart::singleton()->setCurrent($cart);
        $page = new PageController();
        $this->assertEquals("$8.00", (string)$page->renderWith("CartTestTemplate"));
    }
}
