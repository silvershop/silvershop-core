<?php

namespace SilverShop\Tasks;

use LogicException;
use SilverShop\Model\Order;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\FieldType\DBDatetime;

/**
 * Cart Cleanup Task.
 *
 * Removes all orders (carts) that are older than a specific time offset.
 *
 * @package    shop
 * @subpackage tasks
 */
class CartCleanupTask extends BuildTask
{
    /**
     * @config
     *
     * @var string
     */
    private static $delete_after_mins = 120;

    /**
     * @var string
     */
    protected $title = 'Delete abandoned carts';

    /**
     * @var string
     */
    protected $description = 'Deletes abandoned carts.';

    public function run($request)
    {
        if (!$this->config()->get('delete_after_mins')) {
            throw new LogicException('No valid time specified in "delete_after_mins"');
        }

        $count = 0;
        $time = date('Y-m-d H:i:s', DBDatetime::now()->getTimestamp() - $this->config()->get('delete_after_mins') * 60);

        $this->log('Deleting all orders since ' . $time);

        $orders = Order::get()->filter(
            [
                'Status' => 'Cart',
                'LastEdited:LessThan' => $time,
            ]
        );
        foreach ($orders as $order) {
            $this->log(sprintf('Deleting order #%s (Reference: %s)', $order->ID, $order->Reference));
            $order->delete();
            $order->destroy();
            $count++;
        }

        $this->log("$count old carts removed.");
    }

    protected function log($msg)
    {
        echo $msg . "\n";
    }
}
