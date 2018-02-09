<?php

namespace SilverShop\Tests\Model\Product;

use SilverShop\Model\Product\OrderItem;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Security\Member;

class CustomProduct_OrderItem extends OrderItem implements TestOnly
{
    private static $db = array(
        'Color' => "Enum('Red,Green,Blue','Red')",
        'Size' => 'Int',
        'Premium' => 'Boolean',
    );
    private static $defaults = array(
        'Color' => 'Red',
        'Premium' => false,
    );
    private static $has_one = array(
        'CustomProduct' => CustomProduct::class,
        'Recipient' => Member::class,
    );
    private static $buyable_relationship = "CustomProduct";
    private static $required_fields = array(
        'Color',
        'Size',
        'Premium',
        'Recipient',
    );
    private static $table_name = 'SilverShop_Test_CustomProduct_OrderItem';

    public function UnitPrice()
    {
        if ($product = $this->CustomProduct()) {
            return $product->Price;
        }
        return 0;
    }
}
