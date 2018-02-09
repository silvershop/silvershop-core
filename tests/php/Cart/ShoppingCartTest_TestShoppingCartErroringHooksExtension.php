<?php

namespace SilverShop\Tests\Cart;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

class ShoppingCartTest_TestShoppingCartErroringHooksExtension extends Extension implements TestOnly
{
    public function beforeSetQuantity($buyable, $quantity, $filter)
    {
        if ($quantity > 10) {
            throw new \Exception('Invalid quantity');
        }
    }

    public function afterAdd($item, $buyable, $quantity, $filter)
    {
        if ($item->Quantity > 1) {
            throw new \Exception('Invalid quantity');
        }
    }
}
