<?php

namespace SilverShop\Tests;

use SilverShop\ShopTools;
use SilverStripe\Dev\SapphireTest;

class ShopToolsTest extends SapphireTest
{
    public function setUp(): void
    {
        parent::setUp();
        ShopTest::setConfiguration();
    }

    public function testPriceForDisplay(): void
    {
        $dbMoney = ShopTools::price_for_display(12345.67);
        $dbMoney->setCurrency("NZD");
        $dbMoney->setLocale("en_NZ");
        $this->assertEquals($dbMoney->Nice(), "$12,345.67");
    }
}
