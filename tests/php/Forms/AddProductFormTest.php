<?php

namespace SilverShop\Tests\Forms;

use SilverShop\Forms\AddProductForm;
use SilverShop\Page\Product;
use SilverShop\Page\ProductController;
use SilverStripe\Dev\FunctionalTest;

class AddProductFormTest extends FunctionalTest
{
    public static $fixture_file = "../Fixtures/shop.yml";

    public function testForm()
    {

        $controller = ProductController::create($this->objFromFixture(Product::class, "socks"));
        $form = AddProductForm::create($controller);
        $form->setMaximumQuantity(10);

        $this->markTestIncomplete("test can't go over max quantity");

        $data = array(
            'Quantity' => 4,
        );
        $form->addtocart($data, $form);

        $this->markTestIncomplete('check quantity');
    }
}
