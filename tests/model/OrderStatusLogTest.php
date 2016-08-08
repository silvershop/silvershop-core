<?php
/**
 * @link OrderStatusLog
 * @package shop_statuschangeemail
 * @subpackage tests
 */
class OrderStatusLogTest extends SapphireTest
{
    protected static $fixture_file = array(
        'silvershop/tests/fixtures/Orders.yml',
        'silvershop/tests/fixtures/ShopMembers.yml',
        'silvershop/tests/fixtures/Pages.yml'
    );

    public function setUp()
    {
        parent::setUp();
        ShopTest::setConfiguration();
        Order::config()->log_status = array('Processing', 'Sent', 'AdminCancelled', 'MemberCancelled');

    }

    public function testOrderStatusLogItemsWithMember()
    {
        // start a new order
        $order = $this->objFromFixture("Order", "cart1");
        $member = $this->objFromFixture('Member', 'jeremyperemy');
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
            'jeremy@peremy.com',
            'test@myshop.com',
            _t('ShopEmail.StatusChangeSubject') . $log_order_status_processing->Title
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
            "jeremy@peremy.com",
            "test@myshop.com",
            _t('ShopEmail.StatusChangeSubject') . $log_order_status_sent->Title
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
            "jeremy@peremy.com",
            "test@myshop.com",
            _t('ShopEmail.StatusChangeSubject') . $log_order_status_admin_cancelled->Title
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
            'jeremy@peremy.com',
            'test@myshop.com',
            _t('ShopEmail.StatusChangeSubject') . $log_order_status_member_cancelled->Title
        );
    }

    public function testOrderPlacedByGuest()
    {
        // start a new order
        $order = $this->objFromFixture("Order", "cart1");
        $order->FirstName = "Edmund";
        $order->Surname = "Hillary";
        $order->Email = "ed@everest.net";
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
            "ed@everest.net",
            "test@myshop.com",
            _t('ShopEmail.StatusChangeSubject') . $log_order_status_processing->Title
        );
    }
}
