<?php
/**
 * Base class for creating reports that can be filtered to a specific range.
 * Record grouping is also supported.
 */
class ShopPeriodReport extends SS_Report{
	
	protected $dataClass = 'Order';
	protected $periodfield = "Created";
	protected $grouping = false;
	
	function parameterFields() {
		$dateformat = Member::currentUser()->getDateFormat();
		$fields = new FieldSet(
			$start = new DateField("StartPeriod","Start ($dateformat)"),
			$end = new DateField("EndPeriod","End ($dateformat)")
		);
		if($this->grouping){
			$fields->push(new DropdownField("Grouping","Group By",array(
				"Year" => "Year",
				"Month" => "Month",
				"Week" => "Week",
				"Day" => "Day"
			)));
		}
		$start->setConfig("dateformat",$dateformat);
		$end->setConfig("dateformat",$dateformat);
		$start->setConfig("showcalendar", true); //Not working! (js does not run)
		$end->setConfig("showcalendar", true); //Not working!
		return $fields;
	}
	
	function getReportField(){
		$field = parent::getReportField();
		$field->setShowPagination(false);
		return $field;
	}
	
	function sourceRecords($params){
		isset($params['Grouping']) || $params['Grouping'] = "Month";
		$output = new DataObjectSet();
		$query = $this->query($params);
		$results = $query->execute();
		//TODO: push empty months and days to fill out gaps
		foreach($results as $result){
			$output->push($record = new $this->dataClass($result));
			if($this->grouping){
				$dformats = array(
					"Year" => "Y",
					"Month" => "Y - F",
					"Week" => "o - W",
					"Day" =>	"d F Y"
				);
				$dformat = $dformats[$params['Grouping']];
				$record->Period = (empty($result["FilterPeriod"])) ? "uncategorised" : date($dformat, strtotime($result["FilterPeriod"]));
			}
		}
		return $output;
	}
	
	function query($params){
		$query = new SQLQuery();
		$query->select("$this->periodfield AS FilterPeriod");
		$query->from($this->dataClass);
		$start = isset($params['StartPeriod']) && !empty($params['StartPeriod']) ? date('Y-m-d',strtotime($params["StartPeriod"])) : null;
		$end = isset($params['EndPeriod']) && !empty($params['EndPeriod']) ? date('Y-m-d',strtotime($params["EndPeriod"]) + 86400) : null; //end day is inclusive
		if($start && $end){
			$query->having("FilterPeriod BETWEEN '$start' AND '$end'");
		}elseif($start){
			$query->having("FilterPeriod > '$start'");
		}elseif($end){
			$query->having("FilterPeriod <= '$end'");
		}
		if($start || $end){
			$query->having("FilterPeriod IS NOT NULL"); //only include paid orders when we are doing specific period searching
		}
		if($this->grouping){
			switch($params['Grouping']){
				case "Year":
					$query->groupby("YEAR(FilterPeriod)");
					break;
				case "Month":
				default:
					$query->groupby("YEAR(FilterPeriod),MONTH(FilterPeriod)");
					break;
				case "Week":
					$query->groupby("YEAR(FilterPeriod),WEEK(FilterPeriod)");
					break;
				case "Day":
					$query->limit("0,1000");
					$query->groupby("YEAR(FilterPeriod),MONTH(FilterPeriod),DAY(FilterPeriod)");
					break;
			}
		}
		return $query;
	}
	
}