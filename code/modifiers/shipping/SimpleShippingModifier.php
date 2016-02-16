<?php

/**
 * Flat shipping to specific countries.
 *
 * @package    shop
 * @subpackage modifiers
 */
class SimpleShippingModifier extends ShippingModifier
{
    private static $db                 = array(
        'Country' => 'Text',
    );

    private static $default_charge     = 10;

    private static $charges_by_country = array();

    public function value($subtotal = null)
    {
        $country = $this->Country();
        if ($country && isset(self::config()->charges_by_country[$country])) {
            return self::config()->charges_by_country[$country];
        }
        return self::config()->default_charge;
    }

    public function TableTitle()
    {
        if ($country = $this->Country()) {
            $countryList = SiteConfig::current_site_config()->getCountriesList();
            return sprintf(_t("SimpleShippingModifier.SHIPTO", "Ship to %s"), $countryList[$country]);
        } else {
            return parent::TableTitle();
        }
    }

    public function Country()
    {
        if ($order = $this->Order()) {
            return ($order->UseShippingAddress && $order->ShippingCountry)
                ?
                $order->ShippingCountry
                :
                $order->Country;
        }
        return null;
    }
}
