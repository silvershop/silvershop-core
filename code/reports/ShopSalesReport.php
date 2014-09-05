<?php
/**
 * Order sales for the entire shop.
 *
 * @todo: exclude some records: cancelled, refunded, etc
 * @todo: include a graph
 * @todo: count products sold
 * @todo: show geographical map of sales
 * @todo: add profits
*/
class ShopSalesReport extends ShopPeriodReport{

	protected $title = "Shop Sales";
	protected $description = "Monitor shop sales performance for a particular period. Group results by year, month, or day.";
	protected $dataClass = "Order";
	protected $periodfield = "\"Order\".\"Paid\"";
	protected $grouping = true;
	protected $pagesize = 365;

	public function columns(){
		return array(
			"FilterPeriod" => "Period",
			"Count" => "Count",
			"Sales" => "Sales"
		);
	}

	public function query($params){
		return parent::query($params)
			->selectField("Count(\"Order\".\"ID\")", "Count")
			->selectField("Sum(\"Order\".\"Total\")", "Sales");
	}

}
