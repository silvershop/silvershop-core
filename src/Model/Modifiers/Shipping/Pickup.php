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
    private static string $singular_name = 'Pick Up Shipping';

    private static string $table_name = 'SilverShop_PickupModifier';

    private static array $defaults = [
        'Type' => 'Ignored',
    ];

    private static array $casting = [
        'TableValue' => CanBeFreeCurrency::class,
    ];
}
