<?php

class NestedCheckoutTest extends SapphireTest
{
    public static $fixture_file = SHOP_DIR . '/tests/fixtures/pages/NestedCheckout.yml';

    public function setUp()
    {
        parent::setUp();
        $this->checkoutpage = $this->objFromFixture('CheckoutPage', 'checkout');
    }

    public function testNestedCheckoutForm()
    {

        $this->assertEquals(
            Director::baseURL() . SHOP_DIR . '/checkout/',
            CheckoutPage::find_link(),
            'Link is: ' . CheckoutPage::find_link()
        );
    }
}
