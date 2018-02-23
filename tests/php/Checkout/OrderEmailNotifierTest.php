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

    public function setUp()
    {
        parent::setUp();
        Config::modify()->set(Email::class, 'admin_email', 'shop-admin@example.com');
        // clear any setting that might have been made via shop-config
        Config::modify()->remove(ShopConfigExtension::class, 'email_from');
        $this->order = $this->objFromFixture(Order::class, 'paid');
        $this->notifier = OrderEmailNotifier::create($this->order);
    }

    public function testAdminNotification()
    {
        $this->notifier->sendAdminNotification();
        $this->assertEmailSent('shop-admin@example.com', 'shop-admin@example.com');
    }

    public function testConfirmation()
    {
        $this->notifier->sendConfirmation();
        $this->assertEmailSent('test@example.com', 'shop-admin@example.com');
    }

    public function testReceipt()
    {
        $this->notifier->sendReceipt();
        $this->assertEmailSent('test@example.com', 'shop-admin@example.com');
    }

    public function testStatusUpdate()
    {
        $this->notifier->sendStatusChange('test subject');
        $this->assertEmailSent('test@example.com', 'shop-admin@example.com', 'Silvershop - test subject');
    }
}
