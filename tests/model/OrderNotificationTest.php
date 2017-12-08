<?php

use SilverStripe\Core\Config\Config;
use SilverStripe\Control\Email\Email;
use SilverStripe\Dev\SapphireTest;

/**
 * @date       12.01.2015
 * @package    shop
 * @subpackage tests
 */
class OrderNotificationTest extends SapphireTest
{
    protected static $fixture_file = 'silvershop/tests/fixtures/shop.yml';
    /** @var Order */
    protected $order;
    /** @var OrderEmailNotifier */
    protected $notifier;

    public function setUp()
    {
        parent::setUp();
        Config::inst()->update(Email::class, 'admin_email', 'shop-admin@example.com');
        $this->order = $this->objFromFixture('Order', 'paid');
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
        $this->assertEmailSent('test@example.com', 'shop-admin@example.com', _t('ShopEmail.StatusChangeSubject') . 'test subject');
    }
}
