<?php

namespace SilverShop\Tests\Model\Product;

use SilverShop\Model\Product\OrderItem;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Security\Member;

class CustomProduct_OrderItem extends OrderItem implements TestOnly
{
    private static array $db = [
        'Color' => "Enum('Red,Green,Blue','Red')",
        'Size' => 'Int',
        'Premium' => 'Boolean',
    ];
    private static array $defaults = [
        'Color' => 'Red',
        'Premium' => false,
    ];
    private static array $has_one = [
        'CustomProduct' => CustomProduct::class,
        'Recipient' => Member::class,
    ];
    private static string $buyable_relationship = "CustomProduct";
    private static array $required_fields = [
        'Color',
        'Size',
        'Premium',
        'Recipient',
    ];
    private static string $table_name = 'SilverShop_Test_CustomProduct_OrderItem';

    public function UnitPrice()
    {
        if ($product = $this->CustomProduct()) {
            return $product->Price;
        }
        return 0;
    }
}
