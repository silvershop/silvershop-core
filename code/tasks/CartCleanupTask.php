<?php

class CartCleanupTask extends DailyTask{

	protected static $clear_days = 90;
		function set_clear_days($days = 90){self::$clear_days = $days;}
		function get_clear_days(){return self::$clear_days;}

	protected static $dont_delete_if_linked_to_member = false;
		function set_dont_delete_if_linked_to_member($b){self::$dont_delete_if_linked_to_member = $b;}
		function get_dont_delete_if_linked_to_member(){return self::$dont_delete_if_linked_to_member;}

	//Find and remove carts older than X days
	function process(){

		$time = date('Y-m-d H:i:s', strtotime("-".self::$clear_days." days"));
		$generalWhere = "\"Status\" = 'Cart' AND \"LastEdited\" < '$time'";
		if(self::$dont_delete_if_linked_to_member) {
			$oldcarts = DataObject::get('Order',$generalWhere." AND \"Member\".\"ID\" IS NULL", $sort = "", $join = "LEFT JOIN \"Member\" ON \"Member\".\"ID\" = \"Order\".\"MemberID\" ");
		}
		else {
			$oldcarts = DataObject::get('Order',$generalWhere);
		}
		if($oldcarts){
			foreach($oldcarts as $cart){
				echo "deleting ".$cart->Title."  </br>\n";
				$cart->delete();
				$cart->destroy();
			}
		}

	}

}
