<?php

declare(strict_types=1);

namespace SilverShop\Tests\Currency;

use SilverShop\Currency\CurrencyService;
use SilverShop\Currency\SimpleCurrencyService;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\Product\ProductCurrencyPrice;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTestBootstrap;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

/**
 * Tests for product multi-currency pricing.
 */
final class ProductCurrencyPriceTest extends SapphireTest
{
    protected static $fixture_file = __DIR__ . '/../Fixtures/shop.yml';

    /**
     * @var CurrencyService
     */
    private CurrencyService $currencyService;

    protected function setUp(): void
    {
        parent::setUp();
        ShopTestBootstrap::setConfiguration();

        Config::modify()->set(ShopConfigExtension::class, 'base_currency', 'NZD');
        Config::modify()->set(
            SimpleCurrencyService::class,
            'exchange_rates',
            [
                'USD' => 0.60,
                'EUR' => 0.55,
            ]
        );

        $this->currencyService = Injector::inst()->get(CurrencyService::class);
        // Reset currency to base
        $this->currencyService->setActiveCurrency('NZD');
    }

    protected function tearDown(): void
    {
        // Always reset active currency to base after each test
        $this->currencyService->setActiveCurrency('NZD');
        parent::tearDown();
    }

    public function testSellingPriceInBaseCurrency(): void
    {
        $product = $this->objFromFixture(Product::class, 'mp3player');
        $this->currencyService->setActiveCurrency('NZD');
        $this->assertEquals(200.0, $product->sellingPrice());
    }

    public function testSellingPriceWithConversionRate(): void
    {
        $product = $this->objFromFixture(Product::class, 'mp3player');
        $product->publishSingle();

        // With 0.60 NZD→USD rate, 200 NZD = 120 USD
        $this->currencyService->setActiveCurrency('USD');
        $this->assertEqualsWithDelta(120.0, $product->sellingPrice(), 0.01);
    }

    public function testSellingPriceWithExplicitCurrencyPrice(): void
    {
        $product = $this->objFromFixture(Product::class, 'mp3player');
        $product->publishSingle();

        // Add an explicit USD price
        $usdPrice = ProductCurrencyPrice::create();
        $usdPrice->Currency = 'USD';
        $usdPrice->Price = 99.99;
        $usdPrice->ProductID = $product->ID;
        $usdPrice->write();

        $this->currencyService->setActiveCurrency('USD');
        $this->assertEqualsWithDelta(99.99, $product->sellingPrice(), 0.01);
    }

    public function testSellingPriceExplicitPriceTakesPrecedenceOverConversion(): void
    {
        $product = $this->objFromFixture(Product::class, 'socks');
        $product->publishSingle();

        // The conversion rate would give: 8 * 0.60 = 4.80 USD
        // But we define an explicit 5.99 USD price
        $usdPrice = ProductCurrencyPrice::create();
        $usdPrice->Currency = 'USD';
        $usdPrice->Price = 5.99;
        $usdPrice->ProductID = $product->ID;
        $usdPrice->write();

        $this->currencyService->setActiveCurrency('USD');
        $this->assertEqualsWithDelta(5.99, $product->sellingPrice(), 0.01);
    }

    public function testProductCurrencyPriceRelationship(): void
    {
        $product = $this->objFromFixture(Product::class, 'tshirt');
        $product->publishSingle();

        $this->assertEquals(0, $product->Prices()->count());

        $usdPrice = ProductCurrencyPrice::create();
        $usdPrice->Currency = 'USD';
        $usdPrice->Price = 15.00;
        $usdPrice->ProductID = $product->ID;
        $usdPrice->write();

        $eurPrice = ProductCurrencyPrice::create();
        $eurPrice->Currency = 'EUR';
        $eurPrice->Price = 13.50;
        $eurPrice->ProductID = $product->ID;
        $eurPrice->write();

        $this->assertEquals(2, $product->Prices()->count());
        $this->assertNotNull($product->Prices()->filter('Currency', 'USD')->first());
        $this->assertNotNull($product->Prices()->filter('Currency', 'EUR')->first());
    }
}
