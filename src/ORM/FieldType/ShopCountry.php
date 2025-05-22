<?php

namespace SilverShop\ORM\FieldType;

use SilverStripe\Core\Convert;
use SilverStripe\i18n\Data\Intl\IntlLocales;
use SilverStripe\ORM\FieldType\DBVarchar;

class ShopCountry extends DBVarchar
{
    public function __construct($name = null, $size = 3, $options = [])
    {
        parent::__construct($name, $size = 3, $options);
    }

    public function forTemplate(): string
    {
        return $this->Nice();
    }

    /**
     * Convert ISO abbreviation to full, translated country name
     */
    public function Nice(): string
    {
        return IntlLocales::singleton()->countryName($this->value);
    }

    public function XML(): string
    {
        return Convert::raw2xml($this->Nice());
    }
}
