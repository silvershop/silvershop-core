<?php

namespace SilverShop\Core\Tests\Modifiers;


use SilverShop\Core\Model\Order;
use SilverShop\Core\Modifiers\Tax\GlobalTax;
use SilverStripe\Dev\SapphireTest;



class GlobalTaxModifierTest extends SapphireTest
{
    public function setUp()
    {
        parent::setUp();
        Order::config()->modifiers = array(
            'GlobalTaxModifier',
        );
        GlobalTax::config()->country_rates = array(
            'NZ' => array('rate' => 0.15, 'name' => 'GST', 'exclusive' => false),
            'UK' => array('rate' => 0.175, 'name' => 'VAT', 'exclusive' => true),
        );
    }

    public function testModification()
    {
        $modifier = new GlobalTax();
        $this->assertEquals(15, $modifier->value(100)); //15% tax default
    }
}
