<?php

namespace SilverShop\Tests;

use SilverShop\ShopUserInfo;
use SilverStripe\Dev\SapphireTest;

class ShopUserInfoTest extends SapphireTest
{
    public function testSetLocation()
    {
        ShopUserInfo::singleton()->setLocation(
            [
                'Country' => 'NZ',
                'State' => 'Wellington',
                'City' => 'Newton',
            ]
        );

        $location = ShopUserInfo::singleton()->getAddress();

        $this->assertEquals($location->Country, 'NZ');
        $this->assertEquals($location->State, 'Wellington');
        $this->assertEquals($location->City, 'Newton');
    }
}
