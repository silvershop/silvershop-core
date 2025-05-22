<?php

namespace SilverShop\Tests\ORM\FieldType;

use SilverShop\ORM\FieldType\ShopCountry;
use SilverStripe\Dev\SapphireTest;

class ShopCountryTest extends SapphireTest
{
    public function testField(): void
    {
        $shopCountry = ShopCountry::create("Country");
        $shopCountry->setValue("ABC");
        $this->assertEquals("ABC", $shopCountry->forTemplate());
        $shopCountry->setValue("NZ");
        $this->assertEquals("New Zealand", $shopCountry->forTemplate());
    }
}
