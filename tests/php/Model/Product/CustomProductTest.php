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

    public function setUp()
    {
        parent::setUp();
        // clear session
        ShoppingCart::singleton()->clear();
    }

    public function testCustomProduct()
    {
        $thing = CustomProduct::create()->update(
            [
                "Title" => "Thing",
                "Price" => 30,
            ]
        );
        $thing->write();

        $cart = ShoppingCart::singleton();

        $options1 = array('Color' => 'Green', 'Size' => 5, 'Premium' => true);
        $this->assertTrue((bool)$cart->add($thing, 1, $options1), "add to customisation 1 to cart");
        $item = $cart->get($thing, $options1);

        $this->assertTrue((bool)$item, "item with customisation 1 exists");
        $this->assertEquals(1, $item->Quantity);

        $this->assertTrue((bool)$cart->add($thing, 2, $options1), "add another two customisation 1");
        $item = $cart->get($thing, $options1);
        $this->assertEquals(3, $item->Quantity, "quantity has updated correctly");
        $this->assertEquals("Green", $item->Color);
        $this->assertEquals(5, $item->Size);
        $this->assertEquals(1, $item->Premium); //should be true?

        $this->assertFalse((bool)$cart->get($thing), "try to get a non-customised product");

        $options2 = array('Color' => 'Blue', 'Size' => 6, 'Premium' => false);
        $this->assertTrue((bool)$cart->add($thing, 5, $options2), "add customisation 2 to cart");
        $item = $cart->get($thing, $options2);
        $this->assertTrue((bool)$item, "item with customisation 2 exists");
        $this->assertEquals(5, $item->Quantity);

        $options3 = array('Color' => 'Blue');
        $this->assertTrue((bool)$cart->add($thing, 1, $options3), "add a sub-variant of customisation 2");
        $item = $cart->get($thing, $options3);

        $this->assertTrue((bool)$cart->add($thing), "add product with no customisation");
        $item = $cart->get($thing);

        $order = $cart->current();
        $items = $order->Items();
        $this->assertEquals(4, $items->Count(), "4 items in cart");

        //remove
        $cart->remove($thing, 2, $options2);
        $item = $cart->get($thing, $options2);
        $this->assertNotNull($item, 'item exists in cart');
        $this->assertEquals(3, $item->Quantity);

        $cart->clear();

        //set quantity
        $options4 = array('Size' => 12, 'Color' => 'Blue', 'Premium' => false);
        $resp = $cart->setQuantity($thing, 5, $options4);

        $item = $cart->get($thing, $options4);
        $this->assertTrue((bool)$item, 'item exists in cart');

        $this->assertEquals(5, $item->Quantity, "quantity is 5");

        $this->markTestIncomplete("what about default values that have been set");
        //test by using urls
        //add a partial match
    }
}
