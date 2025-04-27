<?php

namespace SilverShop\ORM\FieldType;

use SilverStripe\ORM\FieldType\DBCurrency;

/**
 * Improvements to Currency for presenting in templates.
 *
 * @package shop
 */
class ShopCurrency extends DBCurrency
{
    /**
     * The shop currency decimal delimiter
     */
    private static string $decimal_delimiter = '.';

    /**
     * The shop currency thousands delimiter
     */
    private static string $thousand_delimiter = ',';

    /**
     * Whether or not to append the currency symbol to
     */
    private static bool $append_symbol = false;

    /**
     * Whether or not to use a textual 'free' instead of 0.00
     */
    private static bool $use_free_text = false;

    /**
     * HTML to use for negative numbers
     */
    private static string $negative_value_format = '<span class="negative">(%s)</span>';

    private static array $casting = [
        'forTemplate' => 'HTMLFragment',
        'Nice' => 'HTMLFragment',
        'NiceOrEmpty' => 'HTMLFragment',
    ];

    public function Nice(): string|float
    {
        if (self::config()->get('use_free_text') && $this->value == 0) {
            return _t(__CLASS__ . '.Free', 'Free');
        }

        $symbol = $this->config()->currency_symbol;
        $val = number_format(
            abs($this->value),
            2,
            self::config()->decimal_delimiter,
            self::config()->thousand_delimiter
        );
        if ($this->config()->append_symbol) {
            $val = $val . ' ' . $symbol;
        } else {
            $val = $symbol . $val;
        }
        if ($this->value < 0) {
            return sprintf(self::config()->negative_value_format, $val);
        }

        return $val;
    }

    public function forTemplate(): string
    {
        return $this->Nice();
    }

    /**
     * If no cents on the price, trim those off.
     *
     * @return mixed
     */
    public function TrimCents()
    {
        $val = $this->value;

        if (floor($val) == $val) {
            return floor($val);
        }

        return $val;
    }

    public function NiceOrEmpty(): string|float
    {
        if ($this->value != 0) {
            return $this->Nice();
        }
        return '';
    }
}
