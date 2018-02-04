<?php

namespace SilverShop\Tasks;


use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\Zone;
use SilverShop\Model\ZoneRegion;
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

        $countries = ShopConfigExtension::current()->getCountriesList();

        foreach ($countries as $code => $country) {
            ZoneRegion::create()->update(
                [
                    'ZoneID' => $zone->ID,
                    'Country' => $code,
                ]
            )->write();
            echo '.';
        }
    }
}
