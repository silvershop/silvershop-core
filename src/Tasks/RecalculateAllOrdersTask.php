<?php

namespace SilverShop\Tasks;

use SilverShop\Model\Order;
use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;

/**
 * Recalculate All Orders
 * Re-runs all calculation functions on all orders so that database is populated with pre-calculated values.
 *
 * @subpackage tasks
 */
class RecalculateAllOrdersTask extends BuildTask
{
    protected $title = 'Recalculate All Orders';

    protected $description = 'Runs all price calculation functions on all orders.';

    public function run($request)
    {
        $br = Director::is_cli() ? "\n" : '<br/>';

        //TODO: include order total calculation, once that gets written
        //TODO: figure out how to make this run faster
        //TODO: better memory managment...the destroy calls are not enough it appears.

        if ($orders = Order::get()) {
            echo $br . 'Writing all order items ';
            foreach ($orders as $order) {
                $order->calculate();
                $order->write();
            }
            echo $br . 'done.' . $br;
        }
    }
}
