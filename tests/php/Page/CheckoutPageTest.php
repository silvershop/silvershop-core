<?php

namespace SilverShop\Tests\Page;

use SilverShop\Extension\OrderManipulationExtension;
use SilverShop\Model\Order;
use SilverShop\Page\CheckoutPage;
use SilverShop\Tests\ShopTest;
use SilverStripe\Control\Director;
use SilverStripe\Dev\FunctionalTest;

class CheckoutPageTest extends FunctionalTest
{
    protected static $fixture_file   = [
        __DIR__ . '/../Fixtures/Pages.yml',
        __DIR__ . '/../Fixtures/shop.yml',
    ];
    protected static $disable_theme  = true;
    protected static $use_draft_site = true;
    protected $controller;

    public function setUp(): void
    {
        parent::setUp();
        ShopTest::setConfiguration();
    }

    public function testActionsForm()
    {
        $order = $this->objFromFixture(Order::class, "unpaid");
        OrderManipulationExtension::add_session_order($order);
        $this->get("/checkout/order/" . $order->ID);

        //make payment action
        $this->post(
            "/checkout/order/ActionsForm",
            [
                'OrderID'          => $order->ID,
                'PaymentMethod'    => 'Dummy',
                'action_dopayment' => 'submit',
            ]
        );

        //cancel action
        $this->post(
            "/checkout/order/ActionsForm",
            [
                'OrderID'         => $order->ID,
                'action_docancel' => 'submit',
            ]
        );

        $order = Order::get()->byID($order->ID);
        $this->assertNull($order->PaymentStatus, 'Payment status should be null after cancellation');
        $this->assertEquals('Unpaid', $order->Status, 'Order status should be Unpaid');
    }

    public function testCanViewCheckoutPage()
    {
        $page = $this->get('checkout');
        $this->assertEquals(404, $page->getStatusCode(), 'Cannot access the Checkout Page without a current order');
    }

    public function testFindLink()
    {
        $checkoutpage = $this->objFromFixture(CheckoutPage::class, 'checkout');
        $checkoutpage->publishSingle();
        $link = CheckoutPage::find_link();
        $this->assertEquals(
            Director::baseURL() . 'checkout',
            $link,
            'find_link() returns the correct link to checkout.'
        );
    }
}
