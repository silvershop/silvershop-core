<?php

namespace SilverShop\Tests\Cart;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

class ShoppingCartTest_TestShoppingCartHooksExtension extends Extension implements TestOnly
{
    public static $stack = [];

    public static function reset(): void
    {
        self::$stack = [];
    }

    public function onStartOrder(): void
    {
        self::$stack[] = 'onStartOrder';
    }

    public function beforeAdd($buyable, $quantity, $filter): void
    {
        self::$stack[] = 'beforeAdd';
    }

    public function afterAdd($item, $buyable, $quantity, $filter): void
    {
        self::$stack[] = 'afterAdd';
    }

    public function beforeRemove($buyable, $quantity, $filter): void
    {
        self::$stack[] = 'beforeRemove';
    }

    public function afterRemove($buyable, $quantity, $filter): void
    {
        self::$stack[] = 'afterRemove';
    }

    public function beforeSetQuantity($buyable, $quantity, $filter): void
    {
        self::$stack[] = 'beforeSetQuantity';
    }

    public function afterSetQuantity($item, $buyable, $quantity, $filter): void
    {
        self::$stack[] = 'afterSetQuantity';
    }
}
