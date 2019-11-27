<?php

namespace SilverShop\Model\Modifiers\Shipping;

use SilverStripe\SiteConfig\SiteConfig;

/**
 * Flat shipping to specific countries.
 *
 * @package    shop
 * @subpackage modifiers
 */
class Simple extends Base
{
    /**
     * @config
     * @var float
     */
    private static $default_charge = 10;

    /**
     * @config
     * @var array
     */
    private static $charges_by_country = array();

    public function value($subtotal = null)
    {
        $country = $this->Country();
        if ($country && isset(self::config()->charges_by_country[$country])) {
            return self::config()->charges_by_country[$country];
        }

        return self::config()->default_charge;
    }

    public function getTableTitle()
    {
        if ($country = $this->Country()) {
            $countryList = SiteConfig::current_site_config()->getCountriesList();

            return _t(
                __CLASS__ . '.ShipToCountry',
                'Ship to {Country}',
                '',
                ['Country' => $countryList[$country]]
            );
        } else {
            return parent::getTableTitle();
        }
    }

    /**
     * @return string | null
     */
    public function Country()
    {
        if ($order = $this->Order()) {
            if ($order->getShippingAddress()->exists() && $order->getShippingAddress()->Country) {
                return $order->getShippingAddress()->Country;
            }
        }

        return null;
    }
}
