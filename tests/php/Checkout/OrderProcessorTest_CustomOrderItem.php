<?php

namespace SilverShop\Tests\Checkout;

use SilverShop\Model\Product\OrderItem;
use SilverStripe\Dev\TestOnly;

// Class that writes order-item data to the DB upon placement
class OrderProcessorTest_CustomOrderItem extends OrderItem implements TestOnly
{
    private static array $db = [
        'IsPlaced' => 'Boolean'
    ];

    private static string $table_name = 'SilverShop_Test_CustomOrderItem';

    public function onPlacement(): void
    {
        parent::onPlacement();
        $this->IsPlaced = true;
    }
}
