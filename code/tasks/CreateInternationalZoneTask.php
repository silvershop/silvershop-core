<?php

class CreateInternationalZoneTask extends BuildTask
{
    protected $title       = "Create International Zone";

    protected $description = 'Quickly creates an international zone, based on all available countries.';

    public function run($request)
    {
        $zone = Zone::create();
        $zone->Name = "International";
        $zone->Description = "All countries";
        $zone->write();

        $countries = ShopConfig::current()->getCountriesList();

        foreach ($countries as $code => $country) {
            $zoneregion = ZoneRegion::create(
                array(
                    'ZoneID'  => $zone->ID,
                    'Country' => $code,
                )
            );
            $zoneregion->write();
            echo ".";
        }
    }
}
