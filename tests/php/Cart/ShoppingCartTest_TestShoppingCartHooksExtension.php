<?php

namespace SilverShop\Tests\Cart;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

class ShoppingCartTest_TestShoppingCartHooksExtension extends Extension implements TestOnly
{
    public static $stack = [];

    public static function reset()
    {
        self::$stack = [];
    }

    public function onStartOrder()
    {
        self::$stack[] = 'onStartOrder';
    }

    public function beforeAdd($buyable, $quantity, $filter)
    {
        self::$stack[] = 'beforeAdd';
    }

    public function afterAdd($item, $buyable, $quantity, $filter)
    {
        self::$stack[] = 'afterAdd';
    }

    public function beforeRemove($buyable, $quantity, $filter)
    {
        self::$stack[] = 'beforeRemove';
    }

    public function afterRemove($buyable, $quantity, $filter)
    {
        self::$stack[] = 'afterRemove';
    }

    public function beforeSetQuantity($buyable, $quantity, $filter)
    {
        self::$stack[] = 'beforeSetQuantity';
    }

    public function afterSetQuantity($item, $buyable, $quantity, $filter)
    {
        self::$stack[] = 'afterSetQuantity';
    }
}
