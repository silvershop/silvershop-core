<?php

namespace SilverShop\Tests\Model\Product;

use SilverShop\Model\Buyable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class CustomProduct extends DataObject implements Buyable, TestOnly
{
    private static $db = [
        'Title' => 'Varchar',
        'Price' => 'Currency',
    ];

    private static $order_item = CustomProduct_OrderItem::class;

    private static $table_name = 'SilverShop_Test_CustomProduct';

    public function createItem($quantity = 1, $filter = [])
    {
        $itemClass = self::config()->get('order_item');

        if (!$itemClass) {
            $itemClass = CustomProduct_OrderItem::class;
        }

        $item = Injector::inst()->create($itemClass);
        $item->CustomProductID = $this->ID;

        if ($filter) {
            $item->update($filter);
        }

        return $item;
    }

    public function canPurchase($member = null, $quantity = 1)
    {
        return $this->Price > 0;
    }

    public function sellingPrice()
    {
        return $this->Price;
    }
}
