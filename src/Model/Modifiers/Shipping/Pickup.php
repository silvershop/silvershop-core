<?php

namespace SilverShop\Model\Modifiers\Shipping;

use SilverShop\ORM\FieldType\CanBeFreeCurrency;

/**
 * Pickup the order from the store.
 *
 * @package    shop
 * @subpackage shipping
 */
class Pickup extends Base
{
    private static $singular_name = 'Pick Up Shipping';

    private static $table_name = 'SilverShop_PickupModifier';

    private static $defaults = [
        'Type' => 'Ignored',
    ];

    private static $casting = [
        'TableValue' => CanBeFreeCurrency::class,
    ];
}
