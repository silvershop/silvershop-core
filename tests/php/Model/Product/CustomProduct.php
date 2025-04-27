<?php

namespace SilverShop\Tests\Model\Product;

use SilverShop\Model\Buyable;
use SilverShop\Model\OrderItem;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class CustomProduct extends DataObject implements Buyable, TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
        'Price' => 'Currency',
    ];

    private static string $order_item = CustomProduct_OrderItem::class;

    private static string $table_name = 'SilverShop_Test_CustomProduct';

    public function createItem(int $quantity = 1, array $filter = []): OrderItem
    {
        $itemClass = self::config()->get('order_item');

        if (!$itemClass) {
            $itemClass = CustomProduct_OrderItem::class;
        }

        $item = Injector::inst()->create($itemClass);
        $item->CustomProductID = $this->ID;

        if ($filter !== []) {
            $item->update($filter);
        }

        return $item;
    }

    public function canPurchase(?Member $member = null, int $quantity = 1): bool
    {
        return $this->Price > 0;
    }

    public function sellingPrice(): float
    {
        return $this->Price;
    }
}
