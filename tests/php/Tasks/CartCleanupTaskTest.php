<?php

namespace SilverShop\Tests\Tasks;

use SilverShop\Model\Order;
use SilverShop\Tasks\CartCleanupTask;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBDatetime;

/**
 * @package    shop
 * @subpackage tests
 */
class CartCleanupTaskTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testRun()
    {
        Config::modify()->set(CartCleanupTask::class, 'delete_after_mins', 120);
        DBDatetime::set_mock_now('2014-01-31 13:00:00');

        // less than two hours old
        $orderRunningRecent = Order::create()->update(['Status' => 'Cart']);
        $orderRunningRecentID = $orderRunningRecent->write();
        DB::query('UPDATE "SilverShop_Order" SET "LastEdited" = \'2014-01-31 12:30:00\' WHERE "ID" = ' . $orderRunningRecentID);

        // three hours old
        $orderRunningOld = Order::create()->update(['Status' => 'Cart']);
        $orderRunningOldID = $orderRunningOld->write();
        DB::query('UPDATE "SilverShop_Order" SET "LastEdited" = \'2014-01-31 10:00:00\' WHERE "ID" = ' . $orderRunningOldID);

        // three hours old
        $orderPaidOld = Order::create()->update(['Status' => 'Paid']);
        $orderPaidOldID = $orderPaidOld->write();
        DB::query('UPDATE "SilverShop_Order" SET "LastEdited" = \'2014-01-31 10:00:00\' WHERE "ID" = ' . $orderPaidOldID);

        $task = new FakeCartCleanupTask();
        $response = $task->run(null);

        $this->assertInstanceOf(Order::class, Order::get()->byID($orderRunningRecentID));
        $this->assertNull(Order::get()->byID($orderRunningOldID));
        $this->assertInstanceOf(Order::class, Order::get()->byID($orderPaidOldID));

        $this->assertEquals('1 old carts removed.', $task->log[count($task->log) - 1]);

        DBDatetime::clear_mock_now();
    }
}
