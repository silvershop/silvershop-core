<?php

namespace SilverShop\Tests\Product;

use SilverShop\Model\Buyable;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;


/**
 * @package    shop
 * @subpackage tests
 */
class CustomProduct extends DataObject implements Buyable, TestOnly
{
    private static $db = array(
        'Title' => 'Varchar',
        'Price' => 'Currency',
    );
    private static $order_item = 'CustomProduct_OrderItem';

    public function createItem($quantity = 1, $filter = array())
    {
        $itemclass = self::config()->order_item;
        $item = new $itemclass();
        $item->ProductID = $this->ID;
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
