<?php

declare(strict_types=1);

namespace SilverShop\Tests;

use SilverShop\ShopTools;
use SilverStripe\Dev\SapphireTest;

final class ShopToolsTest extends SapphireTest
{
    protected function setUp(): void
    {
        parent::setUp();
        ShopTestBootstrap::setConfiguration();
    }

    public function testPriceForDisplay(): void
    {
        $dbMoney = ShopTools::price_for_display(12345.67);
        $dbMoney->setCurrency("NZD");
        $dbMoney->setLocale("en_NZ");
        $this->assertEquals($dbMoney->Nice(), "$12,345.67");
    }
}
