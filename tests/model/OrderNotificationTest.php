<?php

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
        Config::inst()->update('Email', 'admin_email', 'admin@ss-shop.org');
        $this->order = $this->objFromFixture('Order', 'paid');
        $this->notifier = OrderEmailNotifier::create($this->order);
    }

    public function testAdminNotification()
    {
        $this->notifier->sendAdminNotification();
        $this->assertEmailSent('admin@ss-shop.org', 'admin@ss-shop.org');
    }

    public function testConfirmation()
    {
        $this->notifier->sendConfirmation();
        $this->assertEmailSent('test@example.com', 'admin@ss-shop.org');
    }

    public function testReceipt()
    {
        $this->notifier->sendReceipt();
        $this->assertEmailSent('test@example.com', 'admin@ss-shop.org');
    }

    public function testStatusUpdate()
    {
        $this->notifier->sendStatusChange('test subject');
        $this->assertEmailSent('test@example.com', 'admin@ss-shop.org', 'test subject');
    }
}
