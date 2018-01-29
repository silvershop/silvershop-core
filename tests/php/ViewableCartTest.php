<?php

namespace SilverShop\Core\Tests;


//use PageController;
use SilverShop\Core\Cart\ShoppingCart;
use SilverStripe\Dev\SapphireTest;
use PageController;


class ViewableCartTest extends SapphireTest
{
    public static $fixture_file  = 'silvershop/tests/fixtures/shop.yml';
    public static $disable_theme = true;

    public function setUpOnce()
    {
        parent::setUpOnce();
        // clear session
        ShoppingCart::singleton()->clear();
    }

    function setUp()
    {
        parent::setUp();
        ShopTest::setConfiguration();
        $this->objFromFixture("Product", "socks")->publishSingle();
    }

    function testCart()
    {
        $cart = $this->objFromFixture("Order", "cart");
        ShoppingCart::singleton()->setCurrent($cart);
        $page = new PageController();
        $this->assertEquals("$8.00", (string)$page->renderWith("CartTestTemplate"));
    }
}
