<?php

namespace SilverShop\Tests\Model;


use SilverShop\Model\Address;
use SilverShop\Model\RegionRestriction;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;


class RegionRestrictionTest extends SapphireTest
{
    public static $fixture_file = array(
        '../Fixtures/RegionRestriction.yml',
        '../Fixtures/Addresses.yml',
    );

    public function testMatchLocal()
    {
        $address = $this->objFromFixture(Address::class, "wnz6012");
        $rate = $this->getRate($address);
        $this->assertTrue((boolean)$rate);
        $this->assertEquals(2, $rate->Rate);
    }

    public function testMatchRegional()
    {
        $address = $this->objFromFixture(Address::class, "wnz6022");
        $rate = $this->getRate($address);
        $this->assertTrue((boolean)$rate);
        $this->assertEquals(10, $rate->Rate);
    }

    public function testMatchNational()
    {
        $address = $this->objFromFixture(Address::class, "anz1010");
        $rate = $this->getRate($address);
        $this->assertTrue((boolean)$rate);
        $this->assertEquals(50, $rate->Rate);
    }

    public function testMatchDefault()
    {
        //add default rate
        $default = new RegionRestriction_RateTest(
            array(
                'Rate' => 100,
            )
        );
        $default->write();
        $address = $this->objFromFixture(Address::class, "bukhp193eq");
        $rate = $this->getRate($address);
        $this->assertTrue((boolean)$rate);
        $this->assertEquals(100, $rate->Rate);
    }

    public function testNoMatch()
    {
        $address = $this->objFromFixture(Address::class, "bukhp193eq");
        $rate = $this->getRate($address);
        $this->assertFalse($rate);
    }

    public function testMatchSQLEscaping()
    {
        $address = Address::create()->update([
            "Country" => "IT",
            "State"   => "Valle d'Aosta",
        ]);

        $rate = $this->getRate($address);
        $this->assertFalse((boolean)$rate, "Can't find rate with unescaped data");

        $address = Address::create()->update([
            "Country" => "NZ",
            "State"   => "Hawke's Bay",
        ]);
        $rate = $this->getRate($address);
        $this->assertTrue((boolean)$rate, "Rate with unescaped data found");
    }

    public function getRate(Address $address)
    {
        $where = RegionRestriction::address_filter($address);
        return DataObject::get_one("RegionRestriction_RateTest", $where, true, "Rate ASC");
    }
}


