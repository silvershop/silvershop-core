<?php

namespace SilverShop\Tests\Product;


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
        'Product' => 'CustomProduct',
        'Recipient' => Member::class,
    );
    private static $buyable_relationship = "Product";
    private static $required_fields = array(
        'Color',
        'Size',
        'Premium',
        'Recipient',
    );

    public function UnitPrice()
    {
        if ($product = $this->Product()) {
            return $product->Price;
        }
        return 0;
    }
}
