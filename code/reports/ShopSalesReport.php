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

	public function getReportField(){
		$reportfield = parent::getReportField();
		$reportfield->getConfig()->removeComponentsByType('GridFieldPaginator');
		// TODO: Add this back in - I'm not sure how this works in Gridfield
//		$reportfield->addSummary("Totals",array(
//			"Sales" => array("sum","Currency->Nice"),
//			//"Count" => array("sum","Currency->Nice") //Not working! (TableListField error, when enabled)
//		));
		//TODO: add averages (not working, because you can't have more than one summary row)
		return $reportfield;
	}

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
			->selectField("Sum(\"Order\".\"Total\")", "Sales")
			->setWhere("\"Order\".\"Paid\" IS NOT NULL");
	}

}
