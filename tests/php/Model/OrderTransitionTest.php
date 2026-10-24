<?php

declare(strict_types=1);

namespace SilverShop\Tests\Model;

use SilverShop\Model\Order;
use SilverShop\Model\OrderTransition;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;

/**
 * The structured, opt-out transition audit log written by Order (B).
 */
class OrderTransitionTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected function setUp(): void
    {
        parent::setUp();
        // Reaching 'Paid' would otherwise fire core's customer receipt email on a
        // fixtureless order with no address — unrelated to the audit log under test.
        Config::modify()->set(Order::class, 'send_receipt', false);
    }

    private function newCart(): Order
    {
        $order = Order::create();
        $order->write(); // Status defaults to 'Cart'; first write does not log (not yet in DB during onBeforeWrite)

        return $order;
    }

    public function testLogsTransitionWithNameByDefault(): void
    {
        $order = $this->newCart();

        $order->setStatus('Unpaid', 'placed_unpaid');
        $order->write();

        $transitions = $order->StatusTransitions();
        $this->assertCount(1, $transitions, 'exactly one transition recorded (creation itself is not logged)');

        $row = $transitions->first();
        $this->assertSame('Cart', $row->FromStatus);
        $this->assertSame('Unpaid', $row->ToStatus);
        $this->assertSame('placed_unpaid', $row->TransitionName, 'intent name carried from setStatus() to the audit row');
        $this->assertSame($order->ID, (int) $row->OrderID);
    }

    public function testBareAssignmentRecordsNullName(): void
    {
        $order = $this->newCart();

        // A CMS dropdown change is a plain magic assignment — no transition name.
        $order->Status = 'Processing';
        $order->write();

        $row = $order->StatusTransitions()->first();
        $this->assertNotNull($row, 'the transition is still recorded');
        $this->assertSame('Processing', $row->ToStatus);
        $this->assertNull($row->TransitionName, 'a nameless assignment records a null transition name');
    }

    public function testRecordsActingMember(): void
    {
        $memberId = $this->logInWithPermission('ADMIN');

        $order = $this->newCart();
        $order->setStatus('Unpaid', 'placed_unpaid');
        $order->write();

        $this->assertSame((int) $memberId, (int) $order->StatusTransitions()->first()->MemberID);
    }

    public function testOptOutDisablesLogging(): void
    {
        Config::modify()->set(Order::class, 'audit_status_transitions', false);

        $order = $this->newCart();
        $order->setStatus('Paid', 'placed_paid');
        $order->write();

        $this->assertCount(0, $order->StatusTransitions(), 'no audit rows when auditing is disabled');
        $this->assertSame('Paid', $order->Status, 'the status change itself still happens');
    }

    public function testMultipleTransitionsAccumulate(): void
    {
        $order = $this->newCart();

        $order->setStatus('Unpaid', 'placed_unpaid');
        $order->write();
        $order->setStatus('Paid', 'payment_received');
        $order->write();

        $this->assertCount(2, $order->StatusTransitions(), 'append-only: a row per transition');
    }
}
