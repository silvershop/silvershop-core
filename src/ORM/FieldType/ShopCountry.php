<?php

namespace SilverShop\ORM\FieldType;

use SilverShop\Extension\ShopConfigExtension;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\FieldType\DBVarchar;

class ShopCountry extends DBVarchar
{
    public function __construct($name = null, $size = 3, $options = [])
    {
        parent::__construct($name, $size = 3, $options);
    }

    public function forTemplate()
    {
        return $this->Nice();
    }

    /**
     * Convert ISO abbreviation to full, translated country name
     */
    public function Nice()
    {
        $val = ShopConfigExtension::countryCode2name($this->value);
        if (!$val) {
            $val = $this->value;
        }
        return _t(__CLASS__ . '.' . $this->value, $val);
    }

    public function XML()
    {
        return Convert::raw2xml($this->Nice());
    }
}
