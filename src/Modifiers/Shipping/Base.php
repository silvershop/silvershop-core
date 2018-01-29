<?php

namespace SilverShop\Core\Modifiers\Shipping;


use SilverShop\Core\Model\OrderModifier;

class Base extends OrderModifier
{
    private static $singular_name = 'Shipping';

    public function required()
    {
        return true; //TODO: make it optional
    }

    public function requiredBeforePlace()
    {
        return true;
    }
}
