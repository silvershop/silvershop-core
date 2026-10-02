<?php

declare(strict_types=1);

namespace SilverShop\Tests\Model\Modifiers;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Modifiers\Tax\FlatTax;
use SilverShop\Model\Order;
use SilverShop\Model\TaxClass;
use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTestBootstrap;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;

/**
 * A product variation must be taxed at its product's TaxClass rate.
 *
 * FlatTax reads each item's rate from the buyable's getTaxRate(). For a variation product the buyable is the
 * {@see Variation}, so the Variation must expose getTaxRate() (delegating to its Product). Without it, FlatTax
 * can't see the variation's rate and falls back to the flat default — over/under-charging VAT on variation items.
 */
final class FlatTaxVariationRateTest extends FunctionalTest
{
    protected static $fixture_file = __DIR__ . '/../../Fixtures/variations.yml';

    protected static bool $disable_theme = true;

    protected Variation $redLarge;

    protected function setUp(): void
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        ShopTestBootstrap::setConfiguration();

        // Flat default is 21%; the product's tax class is the reduced 9%. They differ, so a fallback is visible.
        Config::modify()
            ->set(FlatTax::class, 'exclusive', true)
            ->set(FlatTax::class, 'rate', 0.21)
            ->set(Order::class, 'modifiers', [FlatTax::class]);

        $this->logInWithPermission('ADMIN');

        $reduced = TaxClass::create();
        $reduced->Title = 'Reduced (9%)';
        $reduced->Rate = 0.09;
        $reduced->write();

        // Assign the reduced rate to the variation product (the parent of the fixture's variations).
        $ball = $this->objFromFixture(Product::class, 'ball'); // BasePrice 22.00
        $ball->TaxClassID = $reduced->ID;
        $ball->write();
        $ball->publishSingle();

        $this->redLarge = $this->objFromFixture(Variation::class, 'redLarge'); // Price 22.00
    }

    public function testVariationIsTaxedAtItsProductRate(): void
    {
        ShoppingCart::singleton()->add($this->redLarge);
        $order = ShoppingCart::singleton()->current();
        $order->calculate();

        $tax = (float) $order->Modifiers()->filter('ClassName', FlatTax::class)->first()->Amount;

        // 9% of the €22 variation = €1.98. Without Variation::getTaxRate() FlatTax falls back to the flat 21%
        // (= €4.62), because the Variation buyable exposes no tax rate.
        $this->assertEqualsWithDelta(
            1.98,
            $tax,
            0.001,
            'A variation must be taxed at its product\'s TaxClass rate (9%), not the flat default (21%).'
        );
    }
}
