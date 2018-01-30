<?php

namespace SilverShop\Tests\Modifiers;


use SilverShop\Modifiers\Shipping\Simple;
use SilverStripe\Dev\SapphireTest;



/**
 * @package    shop
 * @subpackage tests
 */
class SimpleShippingModifierTest extends SapphireTest
{
    public function setUp()
    {
        parent::setUp();
        Simple::config()->default_charge = 10;
        Simple::config()->charges_for_countries = array(
            'NZ' => 5,
            'UK' => 20,
        );
    }

    public function testShippingCalculation()
    {
        $modifier = new Simple();
        $this->assertEquals(10, $modifier->value(100));
        $this->assertEquals(110, $modifier->modify(100));
    }
}
