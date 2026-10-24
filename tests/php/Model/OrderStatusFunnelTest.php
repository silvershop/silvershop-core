<?php

declare(strict_types=1);

namespace SilverShop\Tests\Model;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\OrderProcessor;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\Order;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTestBootstrap;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Omnipay\Model\Payment;

/**
 * Proves the BC-warranted Order::setStatus() funnel:
 *  A. magic `$order->Status = X` routes through setStatus(), and the legacy
 *     onStatusChange hook still fires at write time (backwards compatible);
 *  B. an explicit setStatus($value, $name) carries the transition name (intent);
 *  C. a strict extension can veto a transition before it is written;
 *  D. core's own OrderProcessor payment write now funnels through setStatus()
 *     and carries a transition name (the thing setField('Status',…) could not do).
 */
final class OrderStatusFunnelTest extends SapphireTest
{
    protected static $fixture_file = __DIR__ . '/../Fixtures/shop.yml';

    protected static $disable_theme = true;

    protected static $use_draft_site = true;

    protected $usesTransactions = false;

    protected function setUp(): void
    {
        parent::setUp();
        OrderStatusFunnelTest_RecordingExtension::reset();
        OrderStatusFunnelTest_VetoExtension::$vetoTarget = null;
    }

    // A — universal funnel + BC: magic assignment hits setStatus(); onStatusChange still fires.
    public function testMagicAssignmentFunnelsAndOnStatusChangeStillFires(): void
    {
        Config::modify()->merge(Order::class, 'extensions', [OrderStatusFunnelTest_RecordingExtension::class]);

        $order = Order::create();
        $order->write(); // Status defaults to 'Cart'
        OrderStatusFunnelTest_RecordingExtension::reset();

        $order->Status = 'Processing'; // plain magic assignment

        $this->assertSame(
            [['from' => 'Cart', 'to' => 'Processing', 'name' => null]],
            OrderStatusFunnelTest_RecordingExtension::$pre,
            'magic $order->Status = X routes through setStatus() -> onBeforeStatusChange, at assignment time'
        );
        $this->assertSame(
            [],
            OrderStatusFunnelTest_RecordingExtension::$post,
            'onStatusChange has not fired yet (it is a write-time hook)'
        );

        $order->write();

        $this->assertSame('Processing', $order->Status);
        $this->assertSame(
            [['from' => 'Cart', 'to' => 'Processing']],
            OrderStatusFunnelTest_RecordingExtension::$post,
            'legacy onStatusChange still fires at write time (backwards compatible)'
        );
    }

    // B — intent: explicit setStatus() carries the transition name.
    public function testExplicitSetStatusCarriesTransitionName(): void
    {
        Config::modify()->merge(Order::class, 'extensions', [OrderStatusFunnelTest_RecordingExtension::class]);

        $order = Order::create();
        $order->write();
        OrderStatusFunnelTest_RecordingExtension::reset();

        $order->setStatus('Sent', 'ship_it');

        $this->assertSame(
            [['from' => 'Cart', 'to' => 'Sent', 'name' => 'ship_it']],
            OrderStatusFunnelTest_RecordingExtension::$pre,
            'explicit setStatus() carries the transition name'
        );
        $this->assertSame('Sent', $order->getField('Status'), 'the field is written');
    }

    // C — enforcement: returning false from onBeforeStatusChange vetoes the transition.
    public function testVetoBlocksTransitionBeforeWrite(): void
    {
        Config::modify()->merge(Order::class, 'extensions', [OrderStatusFunnelTest_VetoExtension::class]);
        OrderStatusFunnelTest_VetoExtension::$vetoTarget = 'AdminCancelled';

        $order = Order::create();
        $order->write(); // 'Cart'

        $order->setStatus('AdminCancelled', 'admin_cancel'); // vetoed
        $this->assertSame('Cart', $order->getField('Status'), 'vetoed transition leaves status unchanged');

        $order->setStatus('Processing', 'advance'); // allowed
        $this->assertSame('Processing', $order->getField('Status'), 'non-vetoed transition proceeds');
    }

    // D — core path: OrderProcessor's payment write funnels through setStatus() WITH a name.
    public function testProcessorPaymentFunnelsWithTransitionName(): void
    {
        ShoppingCart::singleton()->clear();
        ShopTestBootstrap::setConfiguration();
        Config::modify()->merge(Order::class, 'extensions', [OrderStatusFunnelTest_RecordingExtension::class]);
        Config::modify()->set(ShopConfigExtension::class, 'email_from', 'shopadmin@example.com');

        $socks = $this->objFromFixture(Product::class, 'socks');
        $socks->publishSingle();

        $cart = ShoppingCart::singleton();
        $cart->add($socks);
        $order = $cart->current();
        $order->Email = 'receipt@example.com';
        $order->calculate();

        $payment = Payment::create()->init('Dummy', 8, 'NZD');
        $payment->Status = 'Captured';
        $payment->OrderID = $order->ID;
        $payment->write();

        OrderStatusFunnelTest_RecordingExtension::reset();
        OrderProcessor::create($order)->completePayment();

        $this->assertSame('Paid', $order->Status, 'order ends Paid (behaviour preserved)');

        // completePayment() delegates to placeOrder(); with the payment already
        // captured the order is placed straight to Paid ('placed_paid'). The point
        // is that the core write funnelled through setStatus() carrying an intent
        // name at all — not which of the declared names it was.
        $paidNames = array_values(array_map(
            fn ($e) => $e['name'],
            array_filter(OrderStatusFunnelTest_RecordingExtension::$pre, fn ($e) => $e['to'] === 'Paid')
        ));
        $this->assertNotEmpty(
            $paidNames,
            "OrderProcessor's Paid write funnels through setStatus() -> onBeforeStatusChange"
        );
        $this->assertNotContains(
            null,
            $paidNames,
            'the Paid transition carried an intent name, not a nameless setField()'
        );
        foreach ($paidNames as $n) {
            $this->assertContains(
                $n,
                ['placed_paid', 'payment_received'],
                'the Paid transition name is one the processor declares'
            );
        }
    }
}
