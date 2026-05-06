<?php

declare(strict_types=1);

namespace SilverShop\Currency;

/**
 * Interface for currency conversion services.
 *
 * Implementations of this interface handle the conversion of prices between
 * currencies and track the currently active currency for the shop.
 */
interface CurrencyService
{
    /**
     * Convert a price from one currency to another.
     *
     * @param float  $price    the price to convert
     * @param string $from     the source currency code (e.g. "USD")
     * @param string $to       the target currency code (e.g. "EUR")
     * @return float the converted price
     */
    public function convert(float $price, string $from, string $to): float;

    /**
     * Get the currently active shop currency code.
     *
     * @return string currency code (e.g. "NZD")
     */
    public function getActiveCurrency(): string;

    /**
     * Set the active currency for the current session.
     *
     * @param string $currency currency code (e.g. "EUR")
     */
    public function setActiveCurrency(string $currency): void;

    /**
     * Get the exchange rate from one currency to another.
     *
     * @param string $from the source currency code
     * @param string $to   the target currency code
     * @return float the exchange rate (multiply $from price by this to get $to price)
     */
    public function getExchangeRate(string $from, string $to): float;
}
