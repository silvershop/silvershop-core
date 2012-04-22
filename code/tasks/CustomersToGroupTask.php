<?php

class CustomersToGroupTask extends BuildTask{
	
	protected $title = "Customers to Group";
	protected $description = "Adds all customers to an assigned group.";
	
	function run($request){
		
		$gp = DataObject::get_one("Group", "\"Title\" = '".self::get_group_name()."'");
		if(!$gp) {
			$gp = new Group();
			$gp->Title = Customer::get_group_name();
			$gp->write();
		}
		$allCombos = DB::query("
				SELECT \"Group_Members\".\"ID\", \"Group_Members\".\"MemberID\", \"Group_Members\".\"GroupID\"
				FROM \"Group_Members\"
				WHERE \"Group_Members\".\"GroupID\" = ".$gp->ID.";"
		);
		//make an array of all combos
		$alreadyAdded = array();
		$alreadyAdded[-1] = -1;
		if($allCombos) {
			foreach($allCombos as $combo) {
				$alreadyAdded[$combo["MemberID"]] = $combo["MemberID"];
			}
		}
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
	
	
}