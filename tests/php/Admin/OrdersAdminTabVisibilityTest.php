<?php

declare(strict_types=1);

namespace SilverShop\Tests\Admin;

use SilverShop\Admin\OrdersAdmin;
use SilverShop\Model\Order;
use SilverShop\Model\OrderStatusLog;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;

/**
 * The legacy OrderStatusLog tab is hidden only when it would be empty (logging
 * off AND no rows) — never when it holds data.
 */
class OrdersAdminTabVisibilityTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testLogTabHiddenWhenUnconfiguredAndEmpty(): void
    {
        Config::modify()->set(Order::class, 'log_status', []);

        $models = OrdersAdmin::create()->getManagedModels();

        $this->assertArrayHasKey(Order::class, $models, 'the Order tab is always present');
        $this->assertArrayNotHasKey(
            OrderStatusLog::class,
            $models,
            'the status-log tab is hidden out of the box (no logging configured, no rows)'
        );
    }

    public function testLogTabShownWhenLoggingConfigured(): void
    {
        Config::modify()->set(Order::class, 'log_status', ['Paid']);

        $models = OrdersAdmin::create()->getManagedModels();

        $this->assertArrayHasKey(OrderStatusLog::class, $models, 'configured logging keeps the tab');
    }

    public function testLogTabShownWhenRowsExist(): void
    {
        Config::modify()->set(Order::class, 'log_status', []);

        // e.g. a row written by a module (order-notifications) even with core logging off.
        $order = Order::create();
        $order->write();
        $log = OrderStatusLog::create();
        $log->Title = 'Example';
        $log->OrderID = $order->ID;
        $log->write();

        $models = OrdersAdmin::create()->getManagedModels();

        $this->assertArrayHasKey(
            OrderStatusLog::class,
            $models,
            'existing rows keep the tab visible, so no data is hidden'
        );
    }
}
