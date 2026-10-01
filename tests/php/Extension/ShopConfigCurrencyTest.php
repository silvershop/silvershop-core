<?php

declare(strict_types=1);

namespace SilverShop\Tests\Extension;

use SilverShop\Extension\ShopConfigExtension;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Covers the extendable base-/supported-currency resolution: the defaults (hook passive), and the
 * updateSiteCurrency / updateSupportedCurrencies hooks overriding them.
 */
class ShopConfigCurrencyTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static $required_extensions = [
        SiteConfig::class => [ShopConfigCurrencyTest_CurrencyExtension::class],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        ShopConfigCurrencyTest_CurrencyExtension::$currency = null;
        ShopConfigCurrencyTest_CurrencyExtension::$supported = null;

        Config::modify()->set(ShopConfigExtension::class, 'base_currency', 'NZD');
        Config::modify()->set(ShopConfigExtension::class, 'supported_currencies', []);
    }

    public function testDefaultsToConfigWhenHookIsPassive(): void
    {
        $this->assertSame('NZD', ShopConfigExtension::get_site_currency());
        $this->assertSame(['NZD'], ShopConfigExtension::get_supported_currencies());
    }

    public function testUpdateSiteCurrencyHookOverridesBaseCurrency(): void
    {
        ShopConfigCurrencyTest_CurrencyExtension::$currency = 'EUR';

        $this->assertSame('EUR', ShopConfigExtension::get_site_currency());
        // With supported_currencies unset, the list falls back to the (now overridden) base currency.
        $this->assertSame(['EUR'], ShopConfigExtension::get_supported_currencies());
    }

    public function testUpdateSupportedCurrenciesHookOverridesList(): void
    {
        ShopConfigCurrencyTest_CurrencyExtension::$supported = ['EUR', 'USD', 'GBP'];

        $this->assertSame(['EUR', 'USD', 'GBP'], ShopConfigExtension::get_supported_currencies());
    }
}
