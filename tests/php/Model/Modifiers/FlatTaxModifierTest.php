<?php

namespace SilverShop\Tests\Model\Modifiers;


use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Modifiers\Tax\FlatTax;
use SilverShop\Model\Order;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;


/**
 * @package    shop
 * @subpackage tests
 *
 */
class FlatTaxModifierTest extends FunctionalTest
{
    protected static $fixture_file  = '../Fixtures/shop.yml';
    protected static $disable_theme = true;

    public function setUp()
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        ShopTest::setConfiguration();

        Config::modify()
            ->set(Order::class, 'modifiers', [
                FlatTax::class
            ])
            ->set(FlatTax::class, 'name', 'GST')
            ->set(FlatTax::class, 'rate', 0.15);

        $this->cart = ShoppingCart::singleton();
        $this->mp3player = $this->objFromFixture(Product::class, 'mp3player');
        $this->mp3player->publishSingle();
    }

    public function testInclusiveTax()
    {
        Config::modify()->set(FlatTax::class, 'exclusive', false);
        $this->cart->clear();
        $this->cart->add($this->mp3player);
        $order = $this->cart->current();
        $order->calculate();
        $modifier = $order->Modifiers()
            ->filter('ClassName', FlatTax::class)
            ->first();
        $this->assertEquals(26.09, $modifier->Amount); //remember that 15% tax inclusive is different to exclusive
        $this->assertEquals(200, $order->GrandTotal());
    }

    public function testExclusiveTax()
    {
        Config::modify()->set(FlatTax::class, 'exclusive', true);
        $this->cart->clear();
        $this->cart->add($this->mp3player);
        $order = $this->cart->current();
        $order->calculate();
        $modifier = $order->Modifiers()
            ->filter('ClassName', FlatTax::class)
            ->first();
        $this->assertEquals(30, $modifier->Amount);
        $this->assertEquals(230, $order->GrandTotal());
    }
}
