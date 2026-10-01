<?php

declare(strict_types=1);

namespace SilverShop\Tests\Extension;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * Exercises the updateSiteCurrency / updateSupportedCurrencies hooks on ShopConfigExtension. Each hook only
 * overrides when the matching static is set, so a single registered extension can prove both the passive
 * (default) and the overriding behaviour.
 *
 * @extends Extension<static>
 */
class ShopConfigCurrencyTest_CurrencyExtension extends Extension implements TestOnly
{
    public static ?string $currency = null;

    /**
     * @var string[]|null
     */
    public static ?array $supported = null;

    public function updateSiteCurrency(string &$currency): void
    {
        if (self::$currency !== null) {
            $currency = self::$currency;
        }
    }

    /**
     * @param string[] $currencies
     */
    public function updateSupportedCurrencies(array &$currencies): void
    {
        if (self::$supported !== null) {
            $currencies = self::$supported;
        }
    }
}
