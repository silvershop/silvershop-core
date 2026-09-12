<?php

declare(strict_types=1);

namespace SilverShop\Tests\Forms;

use SilverShop\Model\OrderItem;
use SilverStripe\Dev\TestOnly;

class BuyableWithRelation_OrderItem extends OrderItem implements TestOnly
{
    private static array $has_one = [
        'BuyableWithRelation' => BuyableWithRelation::class,
    ];

    private static string $buyable_relationship = 'BuyableWithRelation';

    private static string $table_name = 'SilverShop_Test_BuyableWithRelation_OrderItem';

    public function UnitPrice()
    {
        if ($this->BuyableWithRelation()->exists()) {
            return $this->BuyableWithRelation()->Price;
        }

        return 0;
    }
}
