<?php

namespace SilverShop\Model\Modifiers\Shipping;

class Free extends Base
{
    /**
     * Calculate whether the current order is eligable for free shipping
     */
    public function eligable()
    {
    }

    public function TableValue()
    {
        return _t(__CLASS__ . '.Free', 'FREE');
    }
}
