<?php

declare(strict_types=1);

namespace SilverShop\Tests\Checkout;

use ReflectionMethod;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\OrderProcessor;
use SilverShop\Model\Address;
use SilverShop\Model\Order;
use SilverShop\Page\CheckoutPage;
use SilverShop\Page\CheckoutPageController;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTestBootstrap;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Omnipay\Model\Message\PaymentMessage;
use SilverStripe\Omnipay\Model\Payment;

/**
 * Offsite-payment fixes: the failed-payment message, the order reference seen by onPlaceOrder, reference
 * uniqueness for the same order, and the payment description sent to the gateway.
 */
final class PaymentFixesTest extends SapphireTest
{
    protected static $fixture_file = [
        __DIR__ . '/../Fixtures/Pages.yml',
        __DIR__ . '/../Fixtures/shop.yml',
    ];

    protected static $disable_theme = true;

    protected static $use_draft_site = true;

    protected $usesTransactions = false;

    protected static $required_extensions = [
        Order::class => [OrderProcessorTest_ReferenceRecorder::class],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        ShopTestBootstrap::setConfiguration();
        OrderProcessorTest_ReferenceRecorder::$seen = null;
    }

    private function cartWithItem(): Order
    {
        $socks = $this->objFromFixture(Product::class, 'socks');
        $socks->publishSingle();
        ShoppingCart::singleton()->add($socks);

        return ShoppingCart::singleton()->current();
    }

    private function addMessage(Payment $payment, string $type, string $text): void
    {
        $message = PaymentMessage::create();
        $message->Type = $type;
        $message->Message = $text;
        $message->PaymentID = $payment->ID;
        $message->write();
    }

    public function testFailedPaymentMessageIsShown(): void
    {
        $order = $this->cartWithItem();
        $payment = Payment::create()->init('Dummy', 8, 'NZD');
        $payment->OrderID = $order->ID;
        $payment->write();

        $controller = CheckoutPageController::create($this->objFromFixture(CheckoutPage::class, 'checkout'));
        $this->assertFalse($controller->PaymentErrorMessage(), 'no messages, no error');

        $this->addMessage($payment, 'PurchaseRequest', 'Redirecting to the gateway');
        $this->assertFalse($controller->PaymentErrorMessage(), 'a request message is not an error');

        $this->addMessage($payment, 'PurchaseError', 'The card was declined');
        $this->assertSame('The card was declined', $controller->PaymentErrorMessage());
    }

    public function testOnPlaceOrderSeesTheFinalReference(): void
    {
        $order = $this->cartWithItem();
        $address = Address::create([
            'Address' => '1 Main Street',
            'City' => 'Amsterdam',
            'State' => 'Noord-Holland',
            'Country' => 'NL',
        ]);
        $address->write();
        $order->update([
            'FirstName' => 'Jane',
            'Surname' => 'Doe',
            'Email' => 'jane@example.com',
            'ShippingAddressID' => $address->ID,
            'BillingAddressID' => $address->ID,
        ]);
        $order->write();

        $this->assertTrue(OrderProcessor::create($order)->placeOrder());

        $this->assertNotSame('', (string) OrderProcessorTest_ReferenceRecorder::$seen, 'reference set before the hook');
        $this->assertSame($order->Reference, OrderProcessorTest_ReferenceRecorder::$seen);
        $this->assertSame(
            str_pad((string) $order->ID, Order::config()->get('reference_id_padding'), '0', STR_PAD_LEFT),
            $order->Reference
        );
    }

    public function testRegeneratingTheReferenceOfTheSameOrderKeepsIt(): void
    {
        $order = $this->objFromFixture(Order::class, 'unpaid');
        $order->Reference = null;
        $order->generateReference();
        $order->write();
        $reference = $order->Reference;

        // a second, stale instance of the same order regenerates its reference
        $stale = Order::get()->byID($order->ID);
        $stale->generateReference();
        $this->assertSame($reference, $stale->Reference, 'own row does not count as a duplicate');

        // another order that would produce the same reference still gets a suffix
        $other = $this->objFromFixture(Order::class, 'paid');
        $other->Reference = $reference;
        $other->write();
        $stale->generateReference();
        $this->assertSame($reference . '1', $stale->Reference);
    }

    public function testGatewayDataHasADescriptionThatCustomDataCanOverride(): void
    {
        $order = $this->objFromFixture(Order::class, 'unpaid');
        $order->Reference = '00042';
        $processor = OrderProcessor::create($order);
        $getGatewayData = new ReflectionMethod(OrderProcessor::class, 'getGatewayData');

        $this->assertSame('Order 00042', $getGatewayData->invoke($processor, [])['description']);
        $this->assertSame(
            'Shop order 00042',
            $getGatewayData->invoke($processor, ['description' => 'Shop order 00042'])['description']
        );
    }
}
