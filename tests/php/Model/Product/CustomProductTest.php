<?php

namespace SilverShop\Tests\Model\Product;

use SilverShop\Cart\ShoppingCart;
use SilverStripe\Dev\FunctionalTest;

/**
 * @package    shop
 * @subpackage tests
 */
class CustomProductTest extends FunctionalTest
{
    protected static $use_draft_site = true;

    protected static $extra_dataobjects = [
        CustomProduct::class,
        CustomProduct_OrderItem::class,
    ];

    public function setUp(): void
    {
        parent::setUp();
        // clear session
        ShoppingCart::singleton()->clear();
    }

    public function testCustomProduct(): void
    {
        $customProduct = CustomProduct::create()->update(
            [
                "Title" => "Thing",
                "Price" => 30,
            ]
        );
        $customProduct->write();

        $shoppingCart = ShoppingCart::singleton();

        $options1 = ['Color' => 'Green', 'Size' => 5, 'Premium' => true];
        $this->assertTrue((bool)$shoppingCart->add($customProduct, 1, $options1), "add to customisation 1 to cart");
        $item = $shoppingCart->get($customProduct, $options1);

        $this->assertTrue((bool)$item, "item with customisation 1 exists");
        $this->assertEquals(1, $item->Quantity);

        $this->assertTrue((bool)$shoppingCart->add($customProduct, 2, $options1), "add another two customisation 1");
        $item = $shoppingCart->get($customProduct, $options1);
        $this->assertEquals(3, $item->Quantity, "quantity has updated correctly");
        $this->assertEquals("Green", $item->Color);
        $this->assertEquals(5, $item->Size);
        $this->assertEquals(1, $item->Premium); //should be true?

        $this->assertFalse((bool)$shoppingCart->get($customProduct), "try to get a non-customised product");

        $options2 = ['Color' => 'Blue', 'Size' => 6, 'Premium' => false];
        $this->assertTrue((bool)$shoppingCart->add($customProduct, 5, $options2), "add customisation 2 to cart");
        $item = $shoppingCart->get($customProduct, $options2);
        $this->assertTrue((bool)$item, "item with customisation 2 exists");
        $this->assertEquals(5, $item->Quantity);

        $options3 = ['Color' => 'Blue'];
        $this->assertTrue((bool)$shoppingCart->add($customProduct, 1, $options3), "add a sub-variant of customisation 2");
        $item = $shoppingCart->get($customProduct, $options3);

        $this->assertTrue((bool)$shoppingCart->add($customProduct), "add product with no customisation");
        $item = $shoppingCart->get($customProduct);

        $order = $shoppingCart->current();
        $hasManyList = $order->Items();
        $this->assertEquals(4, $hasManyList->Count(), "4 items in cart");

        //remove
        $shoppingCart->remove($customProduct, 2, $options2);
        $item = $shoppingCart->get($customProduct, $options2);
        $this->assertNotNull($item, 'item exists in cart');
        $this->assertEquals(3, $item->Quantity);

        $shoppingCart->clear();

        //set quantity
        $options4 = ['Size' => 12, 'Color' => 'Blue', 'Premium' => false];
        $shoppingCart->setQuantity($customProduct, 5, $options4);

        $item = $shoppingCart->get($customProduct, $options4);
        $this->assertTrue((bool)$item, 'item exists in cart');

        $this->assertEquals(5, $item->Quantity, "quantity is 5");

        $this->markTestIncomplete("what about default values that have been set");
        //test by using urls
        //add a partial match
    }
}
