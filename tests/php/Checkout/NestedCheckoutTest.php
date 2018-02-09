<?php

namespace SilverShop\Tests\Checkout;

use SilverShop\Page\CheckoutPage;
use SilverStripe\Control\Director;
use SilverStripe\Dev\SapphireTest;

class NestedCheckoutTest extends SapphireTest
{
    public static $fixture_file = __DIR__ . '/../Fixtures/pages/NestedCheckout.yml';

    public function setUp()
    {
        parent::setUp();
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
