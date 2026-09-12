<?php

declare(strict_types=1);

namespace SilverShop\Tests\Forms;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Forms\CartEditField;
use SilverStripe\Dev\SapphireTest;

/**
 * Regression coverage for {@see CartEditField} rendering carts that contain
 * custom buyables which are not variation-capable.
 */
final class CartEditFieldTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static $extra_dataobjects = [
        BuyableWithRelation::class,
        BuyableWithRelation_Note::class,
        BuyableWithRelation_OrderItem::class,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
    }

    /**
     * A buyable with an unrelated has_many (but no Variations relation) must not
     * cause CartEditField to call a non-existent Variations() method.
     *
     * Previously the field guarded variation rendering with
     * `$buyable->hasMany('Variations')`; because DataObject::hasMany() treats its
     * argument as a `$classOnly` flag rather than a component name, any non-empty
     * has_many returned truthy and led to a BadMethodCallException.
     */
    public function testFieldRendersForBuyableWithoutVariations(): void
    {
        $buyable = BuyableWithRelation::create();
        $buyable->Title = 'Charge';
        $buyable->Price = 30;
        $buyable->write();

        $cart = ShoppingCart::singleton();
        $this->assertTrue((bool) $cart->add($buyable), 'buyable added to cart');

        $order = $cart->current();

        $field = CartEditField::create('Cart', 'Cart', $order);

        // Before the fix this threw:
        // BadMethodCallException: the method 'Variations' does not exist
        $html = (string) $field->Field();

        $this->assertNotSame('', $html, 'CartEditField renders non-empty output');
    }
}
