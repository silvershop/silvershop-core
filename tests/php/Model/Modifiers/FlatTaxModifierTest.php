<?php

declare(strict_types=1);

namespace SilverShop\Tests\Model\Modifiers;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Modifiers\OrderModifier;
use SilverShop\Model\Modifiers\Tax\FlatTax;
use SilverShop\Model\Order;
use SilverShop\Page\Product;
use SilverShop\Tests\Model\Product\CustomProduct_OrderItem;
use SilverShop\Tests\ShopTestBootstrap;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;

/**
 * @package    shop
 * @subpackage tests
 */
final class FlatTaxModifierTest extends FunctionalTest
{
    protected static $fixture_file = __DIR__ . '/../../Fixtures/shop.yml';

    protected static bool $disable_theme = true;

    protected Product $mp3player;

    protected Product $socks;

    protected ShoppingCart $cart;

    protected static $extra_dataobjects = [
        CustomProduct_OrderItem::class
    ];

    protected function setUp(): void
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        ShopTestBootstrap::setConfiguration();

        Config::modify()
            ->set(
                Order::class,
                'modifiers',
                [
                    FlatTax::class
                ]
            )
            ->set(FlatTax::class, 'name', 'GST')
            ->set(FlatTax::class, 'rate', 0.15);

        $this->logInWithPermission('ADMIN');
        $this->cart = ShoppingCart::singleton();
        $this->mp3player = $this->objFromFixture(Product::class, 'mp3player');
        $this->socks = $this->objFromFixture(Product::class, 'socks');
        $this->mp3player->publishSingle();
        $this->socks->publishSingle();
    }

    public function testInclusiveTax(): void
    {
        Config::modify()->set(FlatTax::class, 'exclusive', false);
        $this->cart->clear();
        $this->cart->add($this->mp3player);

        $order = $this->cart->current();
        $order->calculate();
        /**
         * @var FlatTax $modifier
         */
        $modifier = $order->Modifiers()->filter(['ClassName' => FlatTax::class])
            ->first();
        $this->assertEquals(26.09, $modifier->Amount); //remember that 15% tax inclusive is different to exclusive
        $this->assertEquals(200, $order->GrandTotal());
    }

    public function testExclusiveTax(): void
    {
        Config::modify()->set(FlatTax::class, 'exclusive', true);
        $this->cart->clear();
        $this->cart->add($this->mp3player);

        $order = $this->cart->current();
        $order->calculate();
        /**
         * @var OrderModifier $modifier
         */
        $modifier = $order->Modifiers()->filter(['ClassName' => FlatTax::class])
            ->first();
        $this->assertEquals(30, $modifier->Amount);
        $this->assertEquals(230, $order->GrandTotal());
    }

    public function testProductSpecificTaxRates(): void
    {
        Config::modify()->set(FlatTax::class, 'exclusive', true);
        $this->mp3player->TaxRate = 0.15;
        $this->mp3player->write();
        $this->mp3player->publishSingle();

        $this->socks->TaxRate = 0;
        $this->socks->write();
        $this->socks->publishSingle();

        $this->cart->clear();
        $this->cart->add($this->mp3player);
        $this->cart->add($this->socks);

        $order = $this->cart->current();
        $order->calculate();
        /**
         * @var OrderModifier $modifier
         */
        $modifier = $order->Modifiers()->filter(['ClassName' => FlatTax::class])
            ->first();

        $this->assertEquals(30, $modifier->Amount);
        $this->assertEquals(238, $order->GrandTotal());
    }
}
