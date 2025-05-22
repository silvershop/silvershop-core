<?php

namespace SilverShop\Model\Modifiers;

/**
 * SubTotal modifier provides a way to display subtotal within the list of modifiers.
 *
 * @package    shop
 * @subpackage modifiers
 */
class SubTotal extends OrderModifier
{
    private static array $defaults = [
        'Type' => 'Ignored',
    ];

    private static string $singular_name = 'Sub Total';

    private static string $plural_name = 'Sub Totals';

    private static string $table_name = 'SilverShop_SubTotalModifier';

    public function value($incoming): int|float
    {
        return $this->Amount = $incoming;
    }
}
