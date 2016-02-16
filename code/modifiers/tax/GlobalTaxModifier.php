<?php

/**
 * Handles calculation of sales tax on Orders on
 * a per-country basis.
 *
 * @package    shop
 * @subpackage modifiers
 */
class GlobalTaxModifier extends TaxModifier
{
    private static $db            = array(
        'Country' => 'Varchar',
    );

    private static $country_rates = array();

    public function value($incoming)
    {
        $rate = $this->Type == "Chargable"
            ?
            $this->Rate()
            :
            round(1 - (1 / (1 + $this->Rate())), Order::config()->rounding_precision);
        return $incoming * $rate;
    }

    public function Rate()
    {
        $rates = self::config()->country_rates;
        if (isset($rates[$this->Country])) {
            return $this->Rate = $rates[$this->Country]['rate'];
        }
        $defaults = self::config()->defaults;
        return $this->Rate = $defaults['Rate'];
    }

    public function TableTitle()
    {
        $country = $this->Country ? " for " . $this->Country . " " : "";
        return parent::TableTitle() . $country .
        ($this->Type == "Chargable" ? '' : _t("GlobalTaxModifier.INCLUDED", ' (included in the above price)'));
    }
}
