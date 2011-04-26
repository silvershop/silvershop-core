<?php


/**
 * @description: cleans up old (abandonned) carts...
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: cms
 *
 **/


class CartCleanupTask extends HourlyTask {


	static $allowed_actions = array(
		'*' => 'ADMIN',
		'*' => 'SHOP_ADMIN'
	);


	protected $title = 'Clear old carts';

	protected $description = "Deletes abandonned carts";


	static function run_on_demand() {
		$obj = new CartCleanupTask();
		$obj->run();
	}


/*******************************************************
	 * CLEARING OLD ORDERS
*******************************************************/

	protected static $clear_days = 90;
		function set_clear_days(integer $i){self::$clear_days = $i;}
		function get_clear_days(){return(integer)self::$clear_days;}

	protected static $never_delete_if_linked_to_member = false;
		function set_never_delete_if_linked_to_member(boolean $b){self::$never_delete_if_linked_to_member = $b;}
		function get_never_delete_if_linked_to_member(){return(boolean)self::$never_delete_if_linked_to_member;}


/*******************************************************
	 * DELETE OLD SHOPPING CARTS
*******************************************************/

	/**
	 *@return Integer - number of carts destroyed
	 **/
	public function run($request){
		$count = 0;
		$time = date('Y-m-d H:i:s', strtotime("-".self::$clear_days." days"));
		$generalWhere = "\"StatusID\" = ".OrderStep::get_status_id_from_code("CREATED")." AND \"LastEdited\" < '$time'";
		if(self::$never_delete_if_linked_to_member) {
			$oldcarts = DataObject::get('Order',$generalWhere." AND \"Member\".\"ID\" IS NULL", $sort = "", $join = "LEFT JOIN \"Member\" ON \"Member\".\"ID\" = \"Order\".\"MemberID\" ");
		}
		else {
			$oldcarts = DataObject::get('Order',$generalWhere);
		}
		if($oldcarts){
			foreach($oldcarts as $cart){
				$count++;
				$cart->delete();
				$cart->destroy();
			}
		}
		return $count;
	}

}
