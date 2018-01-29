<?php

namespace SilverShop\Core;
use SilverShop\Core\Model\FieldType\CanBeFreeCurrency;


/**
 * Pickup the order from the store.
 *
 * @package    shop
 * @subpackage shipping
 */
class PickupShippingModifier extends ShippingModifier
{
    private static $defaults = [
        'Type' => 'Ignored',
    ];

    private static $casting = [
        'TableValue' => CanBeFreeCurrency::class,
    ];

    private static $singular_name = 'Pick Up Shipping';
}
