<?php

namespace SilverShop\Tests\Cart;

use Exception;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * @extends Extension<static>
 */
class ShoppingCartTest_TestShoppingCartErroringHooksExtension extends Extension implements TestOnly
{
    public function beforeSetQuantity($buyable, $quantity, $filter): void
    {
        if ($quantity > 10) {
            throw new Exception('Invalid quantity');
        }
    }

    public function afterAdd($item, $buyable, $quantity, $filter): void
    {
        if ($item->Quantity > 1) {
            throw new Exception('Invalid quantity');
        }
    }
}
