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
class ShopSalesReport extends SS_Report{
	
	static $saletimefield = "Paid";
	
	protected $title = "Shop Sales";
	protected $description = "Monitor shop sales performance for a particular period. Group results by year, month, or day.";
	
	function title(){
		return _t("ShopSalesReport.TITLE",$this->title);
	}
	
	function description(){
		return _t("ShopSalesReport.DESCRIPTION",$description);
	}
	
	function parameterFields() {
		$dateformat = Member::currentUser()->getDateFormat();
		$fields = new FieldSet(
			$start = new DateField("StartPeriod","Start ($dateformat)"),
			$end = new DateField("EndPeriod","End ($dateformat)"),
			new DropdownField("Grouping","Group By",array(
				"Year" => "Year",
				"Month" => "Month",
				"Week" => "Week",
				"Day" => "Day"
			))
		);
		$start->setConfig("dateformat",$dateformat);
		$end->setConfig("dateformat",$dateformat);
		$start->setConfig("showcalendar", true); //Not working! (js does not run)
		$end->setConfig("showcalendar", true); //Not working!
		return $fields;
	}
	
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
			"Period" => "Period",
			"Count" => "Count",
			"Sales" => "Sales"
		);
	}
	
	function sourceRecords($params){
		isset($params['Grouping']) || $params['Grouping'] = "Month";
		$output = new DataObjectSet();
		$query = $this->query($params);
		$results = $query->execute();
		//TODO: push empty months and days to fill out gaps
		foreach($results as $result){
			$output->push($record = new DataObject($result));
			$dformats = array(
				"Year" => "Y",
				"Month" => "Y - F",
				"Week" => "o - W",
				"Day" =>	"d F Y"
			);
			$dformat = $dformats[$params['Grouping']];			
			$record->Period = (empty($result[self::$saletimefield])) ? "uncategorised" : date($dformat, strtotime($result[self::$saletimefield]));
		}
		return $output;
	}
	
	function query($params){
		$dunit = self::$saletimefield;
		$query = new SQLQuery();
		$query->select("\"$dunit\",SUM(\"Total\") AS Sales, COUNT(\"ID\") AS Count");
		$query->from("\"Order\"");
		$start = isset($params['StartPeriod']) && !empty($params['StartPeriod']) ? date('Y-m-d',strtotime($params["StartPeriod"])) : null;
		$end = isset($params['EndPeriod']) && !empty($params['EndPeriod']) ? date('Y-m-d',strtotime($params["EndPeriod"]) + 86400) : null; //end day is inclusive
		if($start && $end){
			$query->where("\"$dunit\" BETWEEN '$start' AND '$end'");
		}elseif($start){
			$query->where("\"$dunit\" > '$start'");
		}elseif($end){
			$query->where("\"$dunit\" <= '$end'");
		}
		$query->where("\"$dunit\" IS NOT NULL"); //only include paid orders
		switch($params['Grouping']){
			case "Year":
				$query->groupby("YEAR(\"$dunit\")");
				break;
			case "Month":
			default:
				$query->groupby("YEAR(\"$dunit\"),MONTH(\"$dunit\")");
				break;
			case "Week":
				$query->groupby("YEAR(\"$dunit\"),WEEK(\"$dunit\")");
				break;
			case "Day":
				$query->limit("0,1000");
				$query->groupby("YEAR(\"$dunit\"),MONTH(\"$dunit\"),DAY(\"$dunit\")");
				break;
		}
		return $query;
	}
	
}