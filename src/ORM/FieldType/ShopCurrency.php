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
     *
     * @config
     * @var    string
     */
    private static $decimal_delimiter = '.';

    /**
     * The shop currency thousands delimiter
     *
     * @config
     * @var    string
     */
    private static $thousand_delimiter = ',';

    /**
     * Whether or not to append the currency symbol to
     *
     * @config
     * @var    string
     */
    private static $append_symbol = false;

    /**
     * Whether or not to use a textual 'free' instead of 0.00
     *
     * @var bool
     */
    private static $use_free_text = false;

    /**
     * HTML to use for negative numbers
     *
     * @config
     * @var    string
     */
    private static $negative_value_format = '<span class="negative">(%s)</span>';

    private static $casting = [
        'forTemplate' => 'HTMLFragment',
        'Nice' => 'HTMLFragment',
        'NiceOrEmpty' => 'HTMLFragment',
    ];

    public function Nice()
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

    public function forTemplate()
    {
        return $this->Nice();
    }

    /**
     * If no cents on the price, trim those off.
     *
     * @return string
     */
    public function TrimCents()
    {
        $val = $this->value;

        if (floor($val) == $val) {
            return floor($val);
        }

        return $val;
    }

    public function NiceOrEmpty()
    {
        if ($this->value != 0) {
            return $this->Nice();
        }
        return '';
    }
}
