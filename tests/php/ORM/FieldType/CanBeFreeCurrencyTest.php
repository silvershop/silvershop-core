<?php

namespace SilverShop\Tests\ORM\FieldType;

use SilverShop\ORM\FieldType\CanBeFreeCurrency;
use SilverStripe\Dev\SapphireTest;

class CanBeFreeCurrencyTest extends SapphireTest
{
    public function testField(): void
    {
        $canBeFreeCurrency = CanBeFreeCurrency::create("Test");
        $canBeFreeCurrency->setValue(20000);
        $this->assertEquals("$20,000.00", $canBeFreeCurrency->Nice());
        $canBeFreeCurrency->setValue(0);
        $this->assertEquals("<span class=\"free\">FREE</span>", $canBeFreeCurrency->Nice());
    }
}
