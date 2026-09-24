<?php

declare(strict_types=1);

namespace SilverShop\Tests\Model\Modifiers;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Modifiers\Tax\FlatTax;
use SilverShop\Model\Order;
use SilverShop\Model\TaxClass;
use SilverShop\Page\Product;
use SilverShop\Tests\Model\Product\CustomProduct_OrderItem;
use SilverShop\Tests\ShopTestBootstrap;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;

/**
 * Characterises FlatTax's per-item ("product-specific tax rate") path when other
 * modifiers are present.
 *
 * FlatTax::value() takes one of two paths:
 *  - flat path (no product has a TaxClass): taxes the running total $incoming, which
 *    already reflects earlier chargeable/deductible modifiers — correct.
 *  - per-item path (some product HAS a TaxClass): re-computes tax from each item's
 *    Total() and IGNORES $incoming entirely.
 *
 * That divergence causes two issues, of very different status:
 *  1. Order-level DISCOUNTS are ignored on the per-item path → VAT is over-charged.
 *     A discount reduces the taxable base in every jurisdiction, so this is a bug.
 *  2. Chargeable modifiers (SHIPPING) are not taxed on the per-item path. Whether
 *     shipping SHOULD be taxed is jurisdiction-dependent (EU taxes/apportions it;
 *     several US states do not), so that is a configuration concern, not a blanket fix.
 */
final class FlatTaxMixedRateModifierTest extends FunctionalTest
{
    protected static $fixture_file = __DIR__ . '/../../Fixtures/shop.yml';

    protected static bool $disable_theme = true;

    protected static $extra_dataobjects = [
        CustomProduct_OrderItem::class,
        FlatTaxTest_ChargeModifier::class,
        FlatTaxTest_DiscountModifier::class,
    ];

    protected Product $mp3player;

    protected function setUp(): void
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        ShopTestBootstrap::setConfiguration();

        Config::modify()
            ->set(FlatTax::class, 'exclusive', true)
            ->set(FlatTax::class, 'rate', 0.15);

        $this->logInWithPermission('ADMIN');

        // A 15% tax class on the only product triggers FlatTax's per-item path.
        $taxClass = TaxClass::create();
        $taxClass->Title = 'High (15%)';
        $taxClass->Rate = 0.15;
        $taxClass->write();

        $this->mp3player = $this->objFromFixture(Product::class, 'mp3player'); // BasePrice 200
        $this->mp3player->TaxClassID = $taxClass->ID;
        $this->mp3player->write();
        $this->mp3player->publishSingle();
    }

    private function calculatedTax(array $modifiers): float
    {
        Config::modify()->set(Order::class, 'modifiers', $modifiers);
        ShoppingCart::singleton()->clear();
        ShoppingCart::singleton()->add($this->mp3player);
        $order = ShoppingCart::singleton()->current();
        $order->calculate();
        return (float) $order->Modifiers()->filter('ClassName', FlatTax::class)->first()->Amount;
    }

    /** Baseline: item only. Per-item path == flat path: 15% of 200 = 30. */
    public function testItemOnly(): void
    {
        $this->assertEqualsWithDelta(30.0, $this->calculatedTax([FlatTax::class]), 0.001);
    }

    /**
     * BUG: an order-level discount (-40) must reduce the taxable base before taxing.
     * 15% of (200 - 40) = 24.00. Core currently returns 30.00 (discount ignored).
     */
    public function testOrderDiscountReducesTaxableBase(): void
    {
        $tax = $this->calculatedTax([
            FlatTaxTest_DiscountModifier::class,
            FlatTax::class,
        ]);
        $this->assertEqualsWithDelta(
            24.0,
            $tax,
            0.001,
            'The per-item tax path must apply order-level discounts before taxing (over-charges VAT otherwise).'
        );
    }

    /**
     * Same bug, with shipping also present. The discount must still be applied;
     * shipping stays untaxed on this path (see the jurisdiction note below).
     * 15% of (200 - 40) = 24.00. Core currently returns 30.00.
     */
    public function testOrderDiscountAppliedWithShippingPresent(): void
    {
        $tax = $this->calculatedTax([
            FlatTaxTest_ChargeModifier::class,
            FlatTaxTest_DiscountModifier::class,
            FlatTax::class,
        ]);
        $this->assertEqualsWithDelta(24.0, $tax, 0.001);
    }

    /**
     * CHARACTERISATION (not a bug per se): shipping is NOT taxed on the per-item path.
     * Whether it should be is jurisdiction-dependent (EU: yes/apportioned; some US
     * states: no), so this documents the current behaviour and flags it as a config
     * concern for a tax module rather than asserting a universal "correct" value.
     * 15% of 200 = 30 (shipping's +10 untaxed).
     */
    public function testShippingTaxationIsJurisdictionDependentAndCurrentlyUntaxed(): void
    {
        $tax = $this->calculatedTax([
            FlatTaxTest_ChargeModifier::class,
            FlatTax::class,
        ]);
        $this->assertEqualsWithDelta(30.0, $tax, 0.001);
    }
}
