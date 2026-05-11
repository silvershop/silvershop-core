<?php

declare(strict_types=1);

namespace SilverShop\Tests\Page;

use SilverShop\Extension\OrderManipulationExtension;
use SilverShop\Model\Order;
use SilverShop\Page\CartPageController;
use SilverShop\Page\CheckoutPage;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTestBootstrap;
use SilverStripe\Control\Director;
use SilverStripe\Dev\FunctionalTest;

final class CheckoutPageTest extends FunctionalTest
{
    protected static $fixture_file   = [
        __DIR__ . '/../Fixtures/Pages.yml',
        __DIR__ . '/../Fixtures/shop.yml',
    ];

    protected static bool $disable_theme  = true;

    protected static bool $use_draft_site = true;

    protected function setUp(): void
    {
        parent::setUp();
        ShopTestBootstrap::setConfiguration();
    }

    public function testActionsForm(): void
    {
        $checkout = $this->objFromFixture(CheckoutPage::class, 'checkout');
        $checkout->publishSingle();

        $order = $this->objFromFixture(Order::class, "unpaid");
        OrderManipulationExtension::add_session_order($order);

        $orderUrl = $checkout->Link('order/' . $order->ID);
        $actionsFormUrl = $checkout->Link('ActionsForm');
        $this->get($orderUrl);

        //make payment action
        $response = $this->post(
            $actionsFormUrl,
            [
                'OrderID'          => $order->ID,
                'PaymentMethod'    => 'Dummy',
                'action_dopayment' => 'submit',
            ]
        );

        $order = Order::get()->byID($order->ID);
        $this->assertEquals('Paid', $order->Status, 'Order status should be Paid');

        //cancel action — Paid orders cannot be cancelled (cancel_before_processing=false)
        $this->post(
            $actionsFormUrl,
            [
                'OrderID'         => $order->ID,
                'action_docancel' => 'submit',
            ]
        );

        $order = Order::get()->byID($order->ID);
        $this->assertNull($order->PaymentStatus, 'Payment status should be null after cancellation');
        $this->assertEquals('Paid', $order->Status, 'Order status should remain Paid (cancel not permitted after payment)');
    }

    public function testCanViewCheckoutPage(): void
    {
        $httpResponse = $this->get('checkout');
        $this->assertEquals(404, $httpResponse->getStatusCode(), 'Cannot access the Checkout Page without a current order');
    }

    public function testCheckoutIncludesSessionKeepAliveScript(): void
    {
        $checkout = $this->objFromFixture(CheckoutPage::class, 'checkout');
        $checkout->publishSingle();

        // add_session_order() only records order history; it does not set an active cart.
        // Publish a product and add it to the cart via HTTP so ShoppingCart::curr() returns
        // an order with items, which causes OrderForm (and the keepalive script) to render.
        $socks = $this->objFromFixture(Product::class, 'socks');
        $socks->publishSingle();
        $this->get(CartPageController::add_item_link($socks));

        $httpResponse = $this->get('checkout');
        $this->assertEquals(200, $httpResponse->getStatusCode(), 'Checkout page should be available with a current order');
        $this->assertStringContainsString(
            'client/dist/javascript/checkout-session-keep-alive.js',
            (string)$httpResponse->getBody(),
            'Checkout keepalive script should be included on checkout pages'
        );
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
