<?php

/**
 *@author nicolaas [at] sunnysideup . co . nz
 *@description: adds all customers to a customer group
 *to action: use HourlyEcommerceGroupUpdate::add_members_to_customer_group OR cron job to http://www.mysite.com/silverstripe/HourlyTask/
 **/

class HourlyEcommerceGroupUpdate extends HourlyTask {

	protected static $group_name = "Shop Customers";
		static function set_group_name($v) {self::$group_name = $v;}
		static function get_group_name(){return self::$group_name;}

	static function add_members_to_customer_group() {
		$gp = DataObject::get_one("Group", "\"Title\" = '".self::get_group_name()."'");
		if(!$gp) {
			$gp = new Group();
			$gp->Title = self::get_group_name();
			$gp->Sort = 999998;
			$gp->write();
		}
		$allCombos = DB::query("Select \"ID\", \"MemberID\", \"GroupID\" FROM \"Group_Members\" WHERE \"Group_Members\".\"GroupID\" = ".$gp->ID.";");
		//make an array of all combos
		$alreadyAdded = array();
		$alreadyAdded[-1] = -1;
		if($allCombos) {
			foreach($allCombos as $combo) {
				$alreadyAdded[$combo["MemberID"]] = $combo["MemberID"];
			}
		}
		$extraWhere =
		$unlistedMembers = DataObject::get(
			"Member",
			$where = "\"Member\".\"ID\" NOT IN (".implode(",",$alreadyAdded).")",
			$sort = null,
			$join = "INNER JOIN \"Order\" ON \"Order\".\"MemberID\" = \"Member\".\"ID\""
		);

		//add combos
		if($unlistedMembers) {
			$existingMembers = $gp->Members();
			foreach($unlistedMembers as $member) {
				$existingMembers->add($member);
			}
		}

	}

	function process() {
		self::add_members_to_customer_group();
	}




}
