<?php

namespace SilverShop\Model\Modifiers\Shipping;

use SilverShop\Model\Modifiers\OrderModifier;

class Base extends OrderModifier
{
    private static string $singular_name = 'Shipping';

    private static string $table_name = 'SilverShop_BaseModifier';

    public function required(): bool
    {
        return true; //TODO: make it optional
    }

    public function requiredBeforePlace(): bool
    {
        return true;
    }
}
