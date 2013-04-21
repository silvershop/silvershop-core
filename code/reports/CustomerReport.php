<?php
/**
 * List top customers, especially those who spend alot, and those who buy alot.
 * @todo customer making the most purchases
 * @todo customer who has spent the most money
 * @todo new registrations graph
 * @todo demographics
 */
class CustomerReport extends ShopPeriodReport{
	
	protected $title = "Customers";
	protected $dataClass = "Member";
	protected $periodfield = "Order.Paid";
	
	function columns(){
		return array(
			"FirstName" => "First Name",
			"Surname" => "Surname",
			"Email" => "Email",
			"Created" => "Joined",
			"Spent" => "Spent",
			"Orders" => "Orders",
			"NumVisit" => "Visits",
			"edit"=>	array(
				"title" => "Edit",
				"formatting" => '<a href=\"admin/security/EditForm/field/Members/item/$ID/edit\" target=\"_new\">edit</a>'
			),
			
		);
	}
	
	function getReportField(){
		$field = parent::getReportField();
		return $field;
	}
	
	function query($params){
		$query = parent::query($params);
		$query->select(
			"$this->periodfield AS FilterPeriod",
			"Member.ID",
			"Member.FirstName","Member.Surname","Member.Email",
			"NumVisit","Member.Created",
			"Count(Order.ID) AS Orders",
			"Sum(Order.Total) AS Spent"
		);
		$query->innerJoin("Order", "Member.ID = Order.MemberID");
		$query->groupby("Member.ID");
		if(!$query->orderby){
			$query->orderby("Spent DESC,Orders DESC");
		}
		$query->limit("50");
		return $query;
	}

}