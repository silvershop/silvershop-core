<?php

declare(strict_types=1);

namespace SilverShop\Tests\ORM\FieldType;

use SilverShop\ORM\FieldType\ShopCurrency;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\NumericField;

final class ShopCurrencyTest extends SapphireTest
{
    public function testField(): void
    {
        ShopCurrency::config()->currency_symbol = "X";
        ShopCurrency::config()->decimal_delimiter = "|";
        ShopCurrency::config()->thousand_delimiter = "-";
        ShopCurrency::config()->negative_value_format = "- %s";

        $shopCurrency = ShopCurrency::create("Price");
        $shopCurrency->setValue(-12345.56);
        $this->assertEquals("- X12-345|56", $shopCurrency->Nice());
    }

    public function testNiceUsesDecimalsConfig(): void
    {
        ShopCurrency::config()->currency_symbol = "$";
        ShopCurrency::config()->decimal_delimiter = ".";
        ShopCurrency::config()->thousand_delimiter = ",";
        ShopCurrency::config()->decimals = 4;

        $shopCurrency = ShopCurrency::create("Price");
        $shopCurrency->setValue(12345.5678);
        $this->assertEquals("$12,345.5678", $shopCurrency->Nice());
    }

    public function testScaffoldFormFieldReturnsNumericField(): void
    {
        ShopCurrency::config()->decimals = 2;

        $shopCurrency = ShopCurrency::create("Price");
        $field = $shopCurrency->scaffoldFormField("Price");

        $this->assertInstanceOf(NumericField::class, $field);
        $this->assertEquals(2, $field->getScale());
    }

    public function testScaffoldFormFieldRespectsDecimalsConfig(): void
    {
        ShopCurrency::config()->decimals = 4;

        $shopCurrency = ShopCurrency::create("Price");
        $field = $shopCurrency->scaffoldFormField("Price");

        $this->assertInstanceOf(NumericField::class, $field);
        $this->assertEquals(4, $field->getScale());
    }
}
