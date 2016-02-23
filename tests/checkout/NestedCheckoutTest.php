<?php

class NestedCheckoutTest extends SapphireTest
{
    public static $fixture_file = 'silvershop/tests/fixtures/pages/NestedCheckout.yml';

    public function setUp()
    {
        parent::setUp();
        $this->checkoutpage = $this->objFromFixture('CheckoutPage', 'checkout');
    }

    public function testNestedCheckoutForm()
    {

        $this->assertEquals(
            Director::baseURL() . 'silvershop/checkout/',
            CheckoutPage::find_link(),
            'Link is: ' . CheckoutPage::find_link()
        );
    }
}
