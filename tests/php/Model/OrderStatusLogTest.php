<?php

namespace SilverShop\Tests\Model;

use SilverShop\Checkout\OrderProcessor;
use SilverShop\Model\Order;
use SilverShop\Model\OrderStatusLog;
use SilverShop\Tests\ShopTest;
use SilverStripe\Control\Email\Mailer;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\TestMailer;
use SilverStripe\Security\Member;
use SilverStripe\Dev\SapphireTest;

/**
 * @link OrderStatusLog
 * @package shop_statuschangeemail
 * @subpackage tests
 */
class OrderStatusLogTest extends SapphireTest
{
    protected static $fixture_file = array(
        __DIR__ . '/../Fixtures/Orders.yml',
        __DIR__ . '/../Fixtures/ShopMembers.yml',
        __DIR__ . '/../Fixtures/Pages.yml'
    );

    public function setUp()
    {
        parent::setUp();
        ShopTest::setConfiguration();
        Config::modify()->set(Order::class, 'log_status', array('Processing', 'Sent', 'AdminCancelled', 'MemberCancelled'));
    }

    public function testOrderStatusLogItemsWithMember()
    {
        // start a new order
        $order = $this->objFromFixture(Order::class, "cart1");
        $member = $this->objFromFixture(Member::class, 'jeremyperemy');
        $order->MemberID = $member->ID;

        $no_log_generated_with_order_status_cart = OrderStatusLog::get()->sort('ID')->last();
        $this->assertNull(
            $no_log_generated_with_order_status_cart,
            "no log generated with Status of 'Cart'"
        );

        $order->Status = "Unpaid";
        $order->write();

        $no_log_generated_with_order_status_unpaid = OrderStatusLog::get()->sort('ID')->last();
        $this->assertNull(
            $no_log_generated_with_order_status_unpaid,
            "no log generated with Status of 'Unpaid'"
        );

        $processor = OrderProcessor::create($order);
        $response = $processor->makePayment("Manual", array());
        $order->Status = "Paid";
        $order->write();

        $log_order_status_paid = OrderStatusLog::get()->sort('ID')->last();
        $this->assertNull(
            $log_order_status_paid,
            "no log generated with Status of 'Unpaid'"
        );

        $order->Status = "Processing";
        $order->write();

        $log_order_status_processing = OrderStatusLog::get()->sort('ID')->last();
        $this->assertEquals(OrderStatusLog::get()->count(), '1', "One items in the OrderStatusLog");
        $this->assertNotNull(
            $log_order_status_processing,
            "a log when changing to 'Processing' status (and PaymentMethod is 'Manual')"
        );
        $this->assertSame(
            $log_order_status_processing->Order()->ID,
            $order->ID,
            "Log conatins an Order"
        );

        $this->assertContains(
            "Processing",
            $log_order_status_processing->Note,
            "Processing note is recorded"
        );
        $this->assertContains(
            'changed to "Processing"',
            $log_order_status_processing->Title,
            'Processing title is recorded'
        );
        $this->assertEmailSent(
            'jeremy@example.com',
            'shopadmin@example.com',
            'Silvershop - ' . $log_order_status_processing->Title
        );
        $order->Status = "Sent";
        $order->write();

        $log_order_status_sent = OrderStatusLog::get()->sort('ID')->last();
        $this->assertEquals(
            OrderStatusLog::get()->count(),
            '2',
            "Three items in the OrderStatusLog"
        );
        $this->assertNotNull(
            $log_order_status_sent,
            "an log should be recorded when an order's status is changed to 'Sent' (and PaymentMethod is 'Manual')"
        );
        $this->assertSame(
            $log_order_status_sent->Order()->ID,
            $order->ID,
            "Log conatins an Order"
        );
        $this->assertContains(
            "sent",
            $log_order_status_sent->Note,
            "Sent note is recorded"
        );
        $this->assertContains(
            'changed to "Sent"',
            $log_order_status_sent->Title,
            "Sent title is recorded"
        );

        $this->assertEmailSent(
            "jeremy@example.com",
            "shopadmin@example.com",
            'Silvershop - ' . $log_order_status_sent->Title
        );

        $order->Status = "Complete";
        $order->write();
        $this->assertEquals(
            OrderStatusLog::get()->count(),
            '2',
            "Additional item in the OrderStatusLog has not been created"
        );

        $order->Status = "AdminCancelled";
        $order->write();

        $log_order_status_admin_cancelled = OrderStatusLog::get()->sort('ID')->last();
        $this->assertEquals(
            OrderStatusLog::get()->count(),
            '3',
            "Three items in the OrderStatusLog"
        );
        $this->assertNotNull(
            $log_order_status_admin_cancelled,
            "a log should be recorded with change to 'Admin Cancelled' status (and PaymentMethod is 'Manual')"
        );
        $this->assertSame(
            $log_order_status_admin_cancelled->Order()->ID,
            $order->ID,
            "Log conatins an Order"
        );
        $this->assertContains(
            "cancelled",
            $log_order_status_admin_cancelled->Note,
            "Admin Cancelled note is recorded"
        );
        $this->assertContains(
            'changed to "Cancelled by admin"',
            $log_order_status_admin_cancelled->Title,
            "Admin Cancelled title is recorded"
        );
        $this->assertEmailSent(
            "jeremy@example.com",
            "shopadmin@example.com",
            'Silvershop - ' . $log_order_status_admin_cancelled->Title
        );

        $order->Status = "MemberCancelled";
        $order->write();
        $log_order_status_member_cancelled = OrderStatusLog::get()->sort('ID')->last();
        $this->assertEquals(
            OrderStatusLog::get()->count(),
            '4',
            "Four items in the OrderStatusLog"
        );
        $this->assertNotNull(
            $log_order_status_member_cancelled,
            "a log should be recorded for change to 'Member Cancelled' status (and PaymentMethod is 'Manual')"
        );
        $this->assertSame(
            $log_order_status_member_cancelled->Order()->ID,
            $order->ID,
            "Log conatins an Order"
        );
        $this->assertSame(
            "Your cancellation of the order has been noted.  Please contact us if you have any questions.",
            $log_order_status_member_cancelled->Note,
            "Member Cancelled note is recorded"
        );
        $this->assertContains(
            ' changed to "Cancelled by member"',
            $log_order_status_member_cancelled->Title,
            "Member Cancelled title is recorded"
        );
        $this->assertEmailSent(
            'jeremy@example.com',
            'shopadmin@example.com',
            'Silvershop - ' . $log_order_status_member_cancelled->Title
        );
    }

    public function testEmailSentOnce()
    {
        $order = $this->objFromFixture(Order::class, "cart1");
        $member = $this->objFromFixture(Member::class, 'jeremyperemy');
        $order->MemberID = $member->ID;

        $order->Status = 'Processing';
        $order->write();

        $logEntry = OrderStatusLog::get()->sort('ID')->last();

        $this->assertEquals(
            OrderStatusLog::get()->count(),
            1,
            "An item has been added to the status-log"
        );

        $this->assertEmailSent(
            'jeremy@example.com',
            'shopadmin@example.com',
            'Silvershop - ' . $logEntry->Title
        );

        // clear sent emails
        /**
         * @var TestMailer $mailer
         */
        $mailer = Injector::inst()->get(Mailer::class);
        $mailer->clearEmails();

        // force another write on the order
        $order->Notes = 'Random Test Notes';
        $order->write();

        // Status hasn't changed, so there should be just one log entry still
        $this->assertEquals(
            OrderStatusLog::get()->count(),
            1,
            "An item has been added to the status-log"
        );

        $this->assertFalse(
            (bool)$this->findEmail('jeremy@example.com', 'shopadmin@example.com'),
            'No additional email should be sent'
        );

        // Try re-writing the Log entry
        $logEntry->Note = 'Some random notes';
        $logEntry->write();

        $this->assertFalse(
            (bool)$this->findEmail('jeremy@example.com', 'shopadmin@example.com'),
            'No additional email should be sent'
        );
    }

    public function testOrderPlacedByGuest()
    {
        // start a new order
        $order = $this->objFromFixture(Order::class, "cart1");
        $order->FirstName = "Edmund";
        $order->Surname = "Hillary";
        $order->Email = "ed@example.com";
        $order->Status = "Unpaid";
        $order->write();

        $no_log_generated_with_order_status_unpaid = OrderStatusLog::get()->sort('ID')->last();
        $this->assertNull(
            $no_log_generated_with_order_status_unpaid,
            "no log generated with Status of 'Unpaid'"
        );

        $processor_guest = OrderProcessor::create($order);
        $response = $processor_guest->makePayment("Manual", array());
        $order->Status = "Paid";
        $order->write();

        $log_order_status_paid = OrderStatusLog::get()->sort('ID')->last();
        $this->assertNull(
            $log_order_status_paid,
            "no log generated with Status of 'Unpaid'"
        );

        $order->Status = "Processing";
        $order->write();

        $log_order_status_processing = OrderStatusLog::get()->sort('ID')->last();
        $this->assertEquals(OrderStatusLog::get()->count(), '1', "One items in the OrderStatusLog");
        $this->assertNotNull(
            $log_order_status_processing,
            "a log when changing to 'Processing' status (and PaymentMethod is 'Manual')"
        );
        $this->assertSame(
            $log_order_status_processing->Order()->ID,
            $order->ID,
            "Log conatins an Order"
        );

        $this->assertContains(
            "Processing",
            $log_order_status_processing->Note,
            "Processing note is recorded"
        );
        $this->assertContains(
            ' changed to "Processing"',
            $log_order_status_processing->Title,
            "Processing title is recorded"
        );
        $this->assertEmailSent(
            "ed@example.com",
            "shopadmin@example.com",
            'Silvershop - ' . $log_order_status_processing->Title
        );
    }

    public function testOrderIsRequired()
    {
        $log = new OrderStatusLog([
            'Title' => 'Test',
            'OrderID' => 1
        ]);
        $log->write();
        $this->assertTrue($log->exists());

        // Now we make sure we don't need to set an OrderID
        Config::modify()->set(OrderStatusLog::class, 'order_is_required', false);

        $log = new OrderStatusLog([
            'Title' => 'Test'
        ]);

        $log->write();
        $this->assertTrue($log->exists());
    }
}
