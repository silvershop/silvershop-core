<?php

namespace SilverShop\Tests\ORM\FieldType;

use SilverShop\ORM\FieldType\ShopCurrency;
use SilverStripe\Dev\SapphireTest;

class ShopCurrencyTest extends SapphireTest
{
    public function testField()
    {
        ShopCurrency::config()->currency_symbol = "X";
        ShopCurrency::config()->decimal_delimiter = "|";
        ShopCurrency::config()->thousand_delimiter = "-";
        ShopCurrency::config()->negative_value_format = "- %s";

        $field = new ShopCurrency("Price");
        $field->setValue(-12345.56);
        $this->assertEquals("- X12-345|56", $field->Nice());
    }
}
