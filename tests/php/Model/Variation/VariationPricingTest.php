<?php

declare(strict_types=1);

namespace SilverShop\Tests\Model\Variation;

use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;

/**
 * Covers variation price resolution: #1 fallback to the product BasePrice and
 * #2 the per-product "price all variations from base" flag.
 */
final class VariationPricingTest extends SapphireTest
{
    protected static $fixture_file = __DIR__ . '/../../Fixtures/variations.yml';

    protected static bool $use_draft_site = true;

    private function ballWith(float $variationPrice): array
    {
        $product = $this->objFromFixture(Product::class, 'ball'); // BasePrice 22.00
        $variation = $this->objFromFixture(Variation::class, 'redLarge');
        $variation->Price = $variationPrice;
        $variation->write();

        return [$product, $variation];
    }

    public function testFallbackToBasePriceWhenVariationHasNoPrice(): void
    {
        [, $variation] = $this->ballWith(0);
        // price_fallback defaults true → an unpriced variation inherits BasePrice.
        $this->assertSame(22.0, $variation->sellingPrice());
    }

    public function testVariationOwnPriceWins(): void
    {
        [, $variation] = $this->ballWith(18.5);
        $this->assertSame(18.5, $variation->sellingPrice());
    }

    public function testNoFallbackWhenDisabled(): void
    {
        Config::modify()->set(Variation::class, 'price_fallback', false);
        [, $variation] = $this->ballWith(0);
        $this->assertSame(0.0, $variation->sellingPrice());
    }

    public function testNoFallbackWhenZeroPriceAllowed(): void
    {
        Config::modify()->set(Product::class, 'allow_zero_price', true);
        [, $variation] = $this->ballWith(0);
        $this->assertSame(0.0, $variation->sellingPrice(), 'a deliberate 0 price is respected');
    }

    public function testFlatPricingUsesBasePriceRegardlessOfVariationPrice(): void
    {
        [$product, $variation] = $this->ballWith(18.5);
        $product->PriceVariationsFromBase = true;
        $product->write();

        $this->assertSame(22.0, $variation->sellingPrice(), 'flat pricing ignores the variation price');
    }
}
