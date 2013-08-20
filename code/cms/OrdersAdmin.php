<?php
/**
 * Order administration interface, based on ModelAdmin
 * @package shop
 * @subpackage cms
 */
class OrdersAdmin extends ModelAdmin{

	static $url_segment = 'orders';
	static $menu_title = 'Orders';
	static $menu_priority = 1;

	public static $managed_models = array(
		'Order' => array(
			'title' => 'Orders'
		)
	);

	public function getList() {
		$context = $this->getSearchContext();
		$params = $this->request->requestVar('q');
		//TODO update params DateTo, to include the day, ie 23:59:59

		$list = $context->getResults($params)
			->exclude("Status",Order::$hidden_status); //exclude hidden statuses

		$this->extend('updateList', $list);

		return $list;
	}

}
