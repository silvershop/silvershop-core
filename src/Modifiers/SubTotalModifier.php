<?php

namespace SilverShop\Core;

use SilverShop\Core\Model\OrderModifier;


/**
 * SubTotal modifier provides a way to display subtotal within the list of modifiers.
 *
 * @package    shop
 * @subpackage modifiers
 */
class SubTotalModifier extends OrderModifier
{
    private static $defaults = [
        'Type' => 'Ignored',
    ];

    private static $singular_name = 'Sub Total';

    private static $plural_name = 'Sub Totals';

    public function value($incoming)
    {
        return $this->Amount = $incoming;
    }
}
