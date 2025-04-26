<?php

namespace SilverShop\Tests\ORM\FieldType;

use SilverShop\ORM\FieldType\ShopCountry;
use SilverStripe\Dev\SapphireTest;

class ShopCountryTest extends SapphireTest
{
    public function testField(): void
    {
        $field = ShopCountry::create("Country");
        $field->setValue("ABC");
        $this->assertEquals("ABC", $field->forTemplate());
        $field->setValue("NZ");
        $this->assertEquals("New Zealand", $field->forTemplate());
    }
}
