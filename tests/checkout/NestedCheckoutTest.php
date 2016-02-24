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
        // NOTE: the "myshop" here comes from the fixtures
        $this->assertEquals(
            Director::baseURL() . 'myshop/checkout/',
            CheckoutPage::find_link(),
            'Link is: ' . CheckoutPage::find_link()
        );
    }
}
