<?php

namespace SilverShop\Tests\Checkout;

use SilverShop\Checkout\OrderEmailNotifier;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\Order;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;

/**
 * Test OrderEmailNotifier
 */
class OrderEmailNotifierTest extends SapphireTest
{
    protected static $fixture_file = __DIR__ . '/../Fixtures/shop.yml';

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var OrderEmailNotifier
     */
    protected $notifier;

    public function setUp(): void
    {
        parent::setUp();
        Config::modify()->set(Email::class, 'admin_email', 'shop-admin@example.com');
        // clear any setting that might have been made via shop-config
        Config::modify()->remove(ShopConfigExtension::class, 'email_from');
        $this->order = $this->objFromFixture(Order::class, 'paid');
        $this->notifier = OrderEmailNotifier::create($this->order);
    }

    public function testAdminNotification(): void
    {
        $this->notifier->sendAdminNotification();
        $this->assertEmailSent('shop-admin@example.com', 'shop-admin@example.com');
    }

    public function testConfirmation(): void
    {
        $this->notifier->sendConfirmation();
        $this->assertEmailSent('test@example.com', 'shop-admin@example.com');
    }

    public function testReceipt(): void
    {
        $this->notifier->sendReceipt();
        $this->assertEmailSent('test@example.com', 'shop-admin@example.com');
    }

    public function testReceiptNoEmailSent(): void
    {
        $this->clearEmails();
        Config::modify()->set(Order::class, 'send_receipt', false);
        $order = $this->objFromFixture(Order::class, 'unpaid');
        $order->setField('Status', 'Paid');
        $order->write();
        $this->assertNull(
            $this->findEmail('hi@there.net', 'shop-admin@example.com'),
            'An email is not sent when the Order class send_receipt is set to false'
        );
    }

    public function testStatusUpdate(): void
    {
        $this->notifier->sendStatusChange('test subject');
        $this->assertEmailSent('test@example.com', 'shop-admin@example.com', 'Silvershop - test subject');
    }
}
