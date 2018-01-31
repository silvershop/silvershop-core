<?php

namespace SilverShop\Tests\Model;


use SilverShop\Model\Address;
use SilverShop\Model\Zone;
use SilverStripe\Dev\SapphireTest;


class ZoneTest extends SapphireTest
{
    public static $fixture_file = array(
        __DIR__ . '/../Fixtures/Zones.yml',
        __DIR__ . '/../Fixtures/Addresses.yml',
    );

    public function testMatchingZones()
    {
        $this->assertZoneMatch($this->objFromFixture(Address::class, "wnz6012"), "TransTasman");
        $this->assertZoneMatch($this->objFromFixture(Address::class, "wnz6012"), "Local");
        $this->assertZoneMatch($this->objFromFixture(Address::class, "sau5024"), "TransTasman");
        $this->assertZoneMatch($this->objFromFixture(Address::class, "sau5024"), "Special");
        $this->assertZoneMatch($this->objFromFixture(Address::class, "scn266033"), "Asia");
        $this->assertNoZoneMatch($this->objFromFixture(Address::class, "zch1234"));

        $this->markTestIncomplete(
            'test match specificity, ie state matches should come before country matches, but not postcode matches'
        );
    }

    public function assertZoneMatch($address, $zonename)
    {
        $zones = Zone::get_zones_for_address($address);
        $this->assertNotNull($zones);
        $this->assertListContains(
            array(
                array('Name' => $zonename),
            ),
            $zones
        );
    }

    public function assertNoZoneMatch($address)
    {
        $zones = Zone::get_zones_for_address($address);
        $this->assertFalse($zones->exists(), "No zones exist");
    }
}
