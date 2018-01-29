<?php

namespace SilverShop\Core\Tests\Model\FieldType;


use SilverShop\Core\Model\FieldType\CanBeFreeCurrency;
use SilverStripe\Dev\SapphireTest;


class CanBeFreeCurrencyTest extends SapphireTest
{
    public function testField()
    {
        $field = new CanBeFreeCurrency("Test");
        $field->setValue(20000);
        $this->assertEquals("$20,000.00", $field->Nice());
        $field->setValue(0);
        $this->assertEquals("<span class=\"free\">FREE</span>", $field->Nice());
    }
}
