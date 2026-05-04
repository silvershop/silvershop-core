<?php

declare(strict_types=1);

namespace SilverShop\Currency;

use SilverShop\Extension\ShopConfigExtension;
use SilverShop\ShopTools;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;

/**
 * Simple currency service that uses statically configured exchange rates.
 *
 * Exchange rates are defined relative to the shop's base currency.
 * For example, if the base currency is "NZD" and the rate for "USD" is 0.65,
 * then 1 NZD = 0.65 USD.
 *
 * Configuration example (YAML):
 * <code>
 * SilverShop\Currency\SimpleCurrencyService:
 *   exchange_rates:
 *     USD: 0.65
 *     EUR: 0.58
 *     GBP: 0.50
 * </code>
 */
class SimpleCurrencyService implements CurrencyService
{
    use Injectable;
    use Configurable;

    /**
     * Session key for storing the active currency
     */
    private static string $session_key = 'SilverShop.activeCurrency';

    /**
     * Exchange rates relative to the shop base currency.
     * Keys are currency codes, values are the exchange rate (base → foreign).
     *
     * @var array<string, float>
     */
    private static array $exchange_rates = [];

    public function convert(float $price, string $from, string $to): float
    {
        if ($from === $to) {
            return $price;
        }

        $rate = $this->getExchangeRate($from, $to);
        return $price * $rate;
    }

    public function getActiveCurrency(): string
    {
        $session = ShopTools::getSession();
        $currency = $session->get(self::config()->session_key);

        if ($currency) {
            return (string)$currency;
        }

        return ShopConfigExtension::get_site_currency();
    }

    public function setActiveCurrency(string $currency): void
    {
        $session = ShopTools::getSession();
        $session->set(self::config()->session_key, $currency);
    }

    public function getExchangeRate(string $from, string $to): float
    {
        if ($from === $to) {
            return 1.0;
        }

        $baseCurrency = ShopConfigExtension::get_site_currency();
        $rates = self::config()->exchange_rates;

        // Build rate from $from to $to using the base currency as pivot
        $fromRate = $this->getRateToBase($from, $baseCurrency, $rates);
        $toRate = $this->getRateFromBase($to, $baseCurrency, $rates);

        return $fromRate * $toRate;
    }

    /**
     * Get the rate to convert from $currency to the base currency.
     * (i.e. how many base-currency units equal 1 unit of $currency)
     *
     * @param  string $currency
     * @param  string $baseCurrency
     * @param  array<string, float>  $rates
     */
    private function getRateToBase(string $currency, string $baseCurrency, array $rates): float
    {
        if ($currency === $baseCurrency) {
            return 1.0;
        }

        if (isset($rates[$currency]) && $rates[$currency] > 0) {
            // rates[$currency] = base→foreign, so foreign→base = 1/$rates[$currency]
            return 1.0 / (float)$rates[$currency];
        }

        return 1.0;
    }

    /**
     * Get the rate to convert from the base currency to $currency.
     * (i.e. how many units of $currency equal 1 base-currency unit)
     *
     * @param  string $currency
     * @param  string $baseCurrency
     * @param  array<string, float>  $rates
     */
    private function getRateFromBase(string $currency, string $baseCurrency, array $rates): float
    {
        if ($currency === $baseCurrency) {
            return 1.0;
        }

        if (isset($rates[$currency])) {
            return (float)$rates[$currency];
        }

        return 1.0;
    }
}
