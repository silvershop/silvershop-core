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
	protected $periodfield = "Order.Paid";
	protected $grouping = true;
		
	function getReportField(){
		$reportfield = parent::getReportField();
		$reportfield->setShowPagination(false);
		$reportfield->addSummary("Totals",array(
			"Sales" => array("sum","Currency->Nice"),
			//"Count" => array("sum","Currency->Nice") //Not working! (TableListField error, when enabled)
		));
		//TODO: add averages (not working, because you can't have more than one summary row)
		return $reportfield;
	}
	
	function columns(){
		return array(
			"FilterPeriod" => "Period",
			"Count" => "Count",
			"Sales" => "Sales"
		);
	}
	
	function query($params){
		$query = parent::query($params);
		$query->select(
			"$this->periodfield AS FilterPeriod",
			"Count(Order.ID) AS Count",
			"Sum(Order.Total) AS Sales"
		);
		$query->where("\"Order\".\"Paid\" IS NOT NULL");
		return $query;
	}
	
}