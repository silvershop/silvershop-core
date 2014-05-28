<?php
/**
 * @package shop
 * @subpackage tests
 */

class CartCleanupTaskTest extends SapphireTest {

	public function setUp() {
		parent::setUp();

		Config::nest();
		Config::inst()->update('CartCleanupTask', 'delete_after_mins', 120);
	}

	public function tearDown() {
		parent::tearDown();

		Config::unnest();
	}

	public function testRun() {
		SS_Datetime::set_mock_now('2014-01-31 13:00:00');

		// less than two hours old
		$orderRunningRecent = new Order(array('Status' => 'Cart'));
		$orderRunningRecentID = $orderRunningRecent->write();
		DB::query('UPDATE "Order" SET "LastEdited" = \'2014-01-31 12:30:00\' WHERE "ID" = ' . $orderRunningRecentID);

		// three hours old
		$orderRunningOld = new Order(array('Status' => 'Cart'));
		$orderRunningOldID = $orderRunningOld->write();
		DB::query('UPDATE "Order" SET "LastEdited" = \'2014-01-31 10:00:00\' WHERE "ID" = ' . $orderRunningOldID);

		// three hours old
		$orderPaidOld = new Order(array('Status' => 'Paid'));
		$orderPaidOldID = $orderPaidOld->write();
		DB::query('UPDATE "Order" SET "LastEdited" = \'2014-01-31 10:00:00\' WHERE "ID" = ' . $orderPaidOldID);

		$task = new CartCleanupTaskTest_CartCleanupTaskFake();
		$response = $task->run(new SS_HTTPRequest('GET', '/'));

		$this->assertInstanceOf('Order', Order::get()->byID($orderRunningRecentID));
		$this->assertNull(Order::get()->byID($orderRunningOldID));
		$this->assertInstanceOf('Order', Order::get()->byID($orderPaidOldID));

		$this->assertEquals('1 old carts removed.', $task->log[count($task->log)-1]);

		SS_Datetime::clear_mock_now();
	}

}

class CartCleanupTaskTest_CartCleanupTaskFake extends CartCleanupTask {

	public $log = array();

	public function log($msg) {
		$this->log[] = $msg;
	}

}