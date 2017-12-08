<?php

use SilverStripe\ORM\FieldType\DBCurrency;

/**
 * Improvements to Currency for presenting in templates.
 *
 * @package shop
 */
class ShopCurrency extends DBCurrency
{
    private static $decimal_delimiter     = '.';

    private static $thousand_delimiter    = ',';

    private static $append_symbol         = false;

    private static $negative_value_format = "<span class=\"negative\">(%s)</span>";

    public function Nice()
    {
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
        return "";
    }
}
