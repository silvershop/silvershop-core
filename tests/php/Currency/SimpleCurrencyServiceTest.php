<?php

declare(strict_types=1);

namespace SilverShop\Tests\Currency;

use SilverShop\Currency\CurrencyService;
use SilverShop\Currency\SimpleCurrencyService;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Tests\ShopTestBootstrap;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

/**
 * Tests for the SimpleCurrencyService.
 */
final class SimpleCurrencyServiceTest extends SapphireTest
{
    protected function setUp(): void
    {
        parent::setUp();
        ShopTestBootstrap::setConfiguration();

        // Set up exchange rates for testing (NZD as base)
        Config::modify()->set(ShopConfigExtension::class, 'base_currency', 'NZD');
        Config::modify()->set(
            SimpleCurrencyService::class,
            'exchange_rates',
            [
                'USD' => 0.60,
                'EUR' => 0.55,
                'GBP' => 0.48,
            ]
        );
    }

    public function testServiceIsRegisteredInInjector(): void
    {
        $service = Injector::inst()->get(CurrencyService::class);
        $this->assertInstanceOf(CurrencyService::class, $service);
        $this->assertInstanceOf(SimpleCurrencyService::class, $service);
    }

    public function testGetActiveCurrencyDefaultsToBaseCurrency(): void
    {
        $service = new SimpleCurrencyService();
        $this->assertEquals('NZD', $service->getActiveCurrency());
    }

    public function testSetAndGetActiveCurrency(): void
    {
        $service = new SimpleCurrencyService();
        $service->setActiveCurrency('USD');
        $this->assertEquals('USD', $service->getActiveCurrency());

        // Reset to base
        $service->setActiveCurrency('NZD');
        $this->assertEquals('NZD', $service->getActiveCurrency());
    }

    public function testConvertSameCurrency(): void
    {
        $service = new SimpleCurrencyService();
        $this->assertEquals(100.0, $service->convert(100.0, 'NZD', 'NZD'));
        $this->assertEquals(100.0, $service->convert(100.0, 'USD', 'USD'));
    }

    public function testConvertFromBaseCurrency(): void
    {
        $service = new SimpleCurrencyService();

        // 100 NZD should be 60 USD (rate 0.60)
        $this->assertEqualsWithDelta(60.0, $service->convert(100.0, 'NZD', 'USD'), 0.001);

        // 100 NZD should be 55 EUR (rate 0.55)
        $this->assertEqualsWithDelta(55.0, $service->convert(100.0, 'NZD', 'EUR'), 0.001);
    }

    public function testConvertToBaseCurrency(): void
    {
        $service = new SimpleCurrencyService();

        // 60 USD should be 100 NZD (1 / 0.60)
        $this->assertEqualsWithDelta(100.0, $service->convert(60.0, 'USD', 'NZD'), 0.001);
    }

    public function testConvertBetweenForeignCurrencies(): void
    {
        $service = new SimpleCurrencyService();

        // 60 USD → NZD (100) → EUR (55)
        // So 60 USD should be approx 55 EUR
        $this->assertEqualsWithDelta(55.0, $service->convert(60.0, 'USD', 'EUR'), 0.001);
    }

    public function testGetExchangeRateSameCurrency(): void
    {
        $service = new SimpleCurrencyService();
        $this->assertEquals(1.0, $service->getExchangeRate('NZD', 'NZD'));
        $this->assertEquals(1.0, $service->getExchangeRate('USD', 'USD'));
    }

    public function testGetExchangeRateFromBase(): void
    {
        $service = new SimpleCurrencyService();
        $this->assertEqualsWithDelta(0.60, $service->getExchangeRate('NZD', 'USD'), 0.001);
        $this->assertEqualsWithDelta(0.55, $service->getExchangeRate('NZD', 'EUR'), 0.001);
    }

    public function testGetExchangeRateToBase(): void
    {
        $service = new SimpleCurrencyService();
        $this->assertEqualsWithDelta(1.0 / 0.60, $service->getExchangeRate('USD', 'NZD'), 0.001);
    }

    public function testUnknownCurrencyFallsBackToOneToOne(): void
    {
        $service = new SimpleCurrencyService();
        // No rate configured for JPY — should return 1:1
        $this->assertEquals(1.0, $service->getExchangeRate('NZD', 'JPY'));
        $this->assertEquals(100.0, $service->convert(100.0, 'NZD', 'JPY'));
    }
}
