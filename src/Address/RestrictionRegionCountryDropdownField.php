<?php

namespace SilverShop\Core\Address;


use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Forms\DropdownField;



class RestrictionRegionCountryDropdownField extends DropdownField
{
    public static $defaultname = "-- International --";

    public function __construct($name, $title = null, $source = null, $value = "")
    {
        $source = SiteConfig::current_site_config()->getCountriesList(true);
        parent::__construct($name, $title, $source, $value);
        $this->setHasEmptyDefault(true);
        $this->setEmptyString(self::$defaultname);
    }
}
