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
    protected static bool $disable_theme  = true;
    protected static bool $use_draft_site = true;

    public function setUp(): void
    {
        parent::setUp();
        ShopTest::setConfiguration();
    }

    public function testActionsForm(): void
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

    public function testCanViewCheckoutPage(): void
    {
        $httpResponse = $this->get('checkout');
        $this->assertEquals(404, $httpResponse->getStatusCode(), 'Cannot access the Checkout Page without a current order');
    }

    public function testFindLink(): void
    {
        $dataObject = $this->objFromFixture(CheckoutPage::class, 'checkout');
        $dataObject->publishSingle();
        $link = CheckoutPage::find_link();
        $this->assertEquals(
            Director::baseURL() . 'checkout',
            $link,
            'find_link() returns the correct link to checkout.'
        );
    }
}
