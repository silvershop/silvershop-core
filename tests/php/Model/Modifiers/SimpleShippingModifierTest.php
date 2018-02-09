<?php

namespace SilverShop\Tests\Model\Modifiers;

use SilverShop\Model\Modifiers\Shipping\Simple;
use SilverStripe\Core\Config\Config;
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
        Config::modify()
            ->set(Simple::class, 'default_charge', 10)
            ->set(
                Simple::class,
                'charges_for_countries',
                [
                    'NZ' => 5,
                    'UK' => 20,
                ]
            );
    }

    public function testShippingCalculation()
    {
        $modifier = new Simple();
        $this->assertEquals(10, $modifier->value(100));
        $this->assertEquals(110, $modifier->modify(100));
    }
}
