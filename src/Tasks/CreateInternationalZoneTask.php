<?php

namespace SilverShop\Core\Tasks;


use SilverShop\Core\Cms\ShopConfig;
use SilverShop\Core\Model\Zone;
use SilverShop\Core\Model\ZoneRegion;
use SilverStripe\Dev\BuildTask;


class CreateInternationalZoneTask extends BuildTask
{
    protected $title = 'Create International Zone';

    protected $description = 'Quickly creates an international zone, based on all available countries.';

    public function run($request)
    {
        $zone = Zone::create();
        $zone->Name = 'International';
        $zone->Description = 'All countries';
        $zone->write();

        $countries = ShopConfig::current()->getCountriesList();

        foreach ($countries as $code => $country) {
            ZoneRegion::create()->update([
                'ZoneID' => $zone->ID,
                'Country' => $code,
            ])->write();
            echo '.';
        }
    }
}
