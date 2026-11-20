<?php

declare(strict_types=1);

namespace SilverShop\Tests\Admin;

use SilverShop\Admin\OrdersAdmin;
use SilverShop\Model\Order;
use SilverShop\Model\OrderTransition;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;

/**
 * The OrderTransition audit tab in OrdersAdmin: shown by default (auditing
 * is on), hidden only when auditing is off AND there are no rows.
 */
class OrdersAdminTransitionTabTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testTransitionTabShownByDefault(): void
    {
        $models = OrdersAdmin::create()->getManagedModels();

        $this->assertArrayHasKey(
            OrderTransition::class,
            $models,
            'auditing is on by default, so the transition tab shows'
        );
    }

    public function testTransitionTabHiddenWhenDisabledAndEmpty(): void
    {
        Config::modify()->set(Order::class, 'audit_status_transitions', false);

        $models = OrdersAdmin::create()->getManagedModels();

        $this->assertArrayNotHasKey(
            OrderTransition::class,
            $models,
            'auditing off and no rows -> tab hidden'
        );
    }

    public function testTransitionTabShownWhenRowsExist(): void
    {
        Config::modify()->set(Order::class, 'audit_status_transitions', false);

        $order = Order::create();
        $order->write();
        $row = OrderTransition::create();
        $row->FromStatus = 'Cart';
        $row->ToStatus = 'Unpaid';
        $row->OrderID = $order->ID;
        $row->write();

        $models = OrdersAdmin::create()->getManagedModels();

        $this->assertArrayHasKey(
            OrderTransition::class,
            $models,
            'existing rows keep the tab visible even with auditing off'
        );
    }
}
