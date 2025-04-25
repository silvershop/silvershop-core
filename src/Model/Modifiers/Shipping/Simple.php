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
     */
    private static int $default_charge = 10;

    /**
     * @config
     */
    private static array $charges_by_country = [];

    private static string $table_name = 'SilverShop_SimpleModifier';

    public function value($subtotal = null): int|float
    {
        $country = $this->Country();
        if ($country && isset(self::config()->charges_by_country[$country])) {
            return self::config()->charges_by_country[$country];
        }

        return self::config()->default_charge;
    }

    public function getTableTitle(): string
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
     * @return ?string
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
