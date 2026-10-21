<?php

declare(strict_types=1);

namespace SilverShop\Tests\Cart;

use SilverShop\Model\Product\OrderItem;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Security\Member;

/**
 * Order item with a has_one listed in required_fields, so that cart lines
 * differing only by that relation are kept separate.
 */
class ShoppingCartRequiredHasOneTest_OrderItem extends OrderItem implements TestOnly
{
    private static array $has_one = [
        'Selection' => Member::class,
    ];

    private static array $required_fields = [
        'Selection',
    ];

    private static string $table_name = 'SilverShop_Test_RequiredHasOneOrderItem';
}
