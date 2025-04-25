<?php

namespace SilverShop\Tests\Forms;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Forms\AddProductForm;
use SilverShop\Page\Product;
use SilverShop\Page\ProductController;
use SilverStripe\Dev\FunctionalTest;

class AddProductFormTest extends FunctionalTest
{
    public static $fixture_file = "../Fixtures/shop.yml";

    public function testForm(): void
    {
        $controller = ProductController::create($this->objFromFixture(Product::class, "socks"));
        $form = AddProductForm::create($controller);
        $form->setMaximumQuantity(10);

        $form->addtocart(
            [
               'Quantity' => 11,
            ],
            $form
        );
        $order = ShoppingCart::curr();
        $this->assertEquals(
            10,
            $order->Items()->First()->Quantity,
            'Quantity set to maximum of 10 when over the maximum quantity'
        );

        ShoppingCart::singleton()->clear();
        $form->addtocart(
            [
                'Quantity' => 4,
            ],
            $form
        );
        $order = ShoppingCart::curr();
        $this->assertEquals(
            4,
            $order->Items()->First()->Quantity,
            'Quantity should be 4'
        );
    }
}
