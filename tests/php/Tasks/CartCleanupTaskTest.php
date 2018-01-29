<?php

namespace SilverShop\Core\Tests\Tasks;


use SilverShop\Core\Model\Order;
use SilverShop\Core\Tasks\CartCleanupTask;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\DB;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;



/**
 * @package    shop
 * @subpackage tests
 */
class CartCleanupTaskTest extends SapphireTest
{
    public function setUp()
    {
        parent::setUp();

        Config::nest();
        Config::inst()->update(CartCleanupTask::class, 'delete_after_mins', 120);
    }

    public function tearDown()
    {
        parent::tearDown();

        Config::unnest();
    }

    public function testRun()
    {
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
        $response = $task->run(new HTTPRequest('GET', '/'));

        $this->assertInstanceOf(Order::class, Order::get()->byID($orderRunningRecentID));
        $this->assertNull(Order::get()->byID($orderRunningOldID));
        $this->assertInstanceOf(Order::class, Order::get()->byID($orderPaidOldID));

        $this->assertEquals('1 old carts removed.', $task->log[count($task->log) - 1]);

        DBDatetime::clear_mock_now();
    }
}
