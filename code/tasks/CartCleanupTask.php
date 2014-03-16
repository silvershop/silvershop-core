<?php
/**
 * Cart Cleanup Task.
 *
 * Removes all orders (carts) that are older than a specific number of days.
 *
 * @package shop
 * @subpackage tasks
 */
class CartCleanupTask extends BuildTask {

	/**
	 * @config
	 *
	 * @var string
	 */
	private static $delete_after = "-2 HOURS";

	/**
	 * @var string
	 */
	protected $title = "Delete abandoned carts";

	/**
	 * @var string
	 */
	protected $description = "Deletes abandoned carts.";


	public function run($request) {
		$start = 0;
		$count = 0;
		$time = date('Y-m-d H:i:s', $this->config()->get('delete_after'));

		$orders = Order::get()->filter(array(
			'Status' => 'Cart',
			'LastEdited:LessThan' => $time
		));

		foreach($orders as $order) {
			echo ". ";

			$cart->delete();
			$cart->destroy();
		}

		echo "$count old carts removed.\n<br/>";
	}
}
