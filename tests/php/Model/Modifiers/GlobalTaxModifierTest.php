<?php

namespace SilverShop\Tests\Model\Modifiers;

use SilverShop\Model\Modifiers\Tax\GlobalTax;
use SilverShop\Model\Order;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;

class GlobalTaxModifierTest extends SapphireTest
{
    public function setUp()
    {
        parent::setUp();

        Config::modify()->set(
            Order::class,
            'modifiers',
            [
                GlobalTax::class
            ]
        )->set(
            GlobalTax::class,
            'country_rates',
            [
                'NZ' => array('rate' => 0.15, 'name' => 'GST', 'exclusive' => false),
                'UK' => array('rate' => 0.175, 'name' => 'VAT', 'exclusive' => true),
            ]
        );
    }

    public function testModification()
    {
        $modifier = GlobalTax::create();
        $this->assertEquals(15, $modifier->value(100)); //15% tax default
    }
}
