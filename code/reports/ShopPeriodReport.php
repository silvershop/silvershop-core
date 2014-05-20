<?php
/**
 * Base class for creating reports that can be filtered to a specific range.
 * Record grouping is also supported.
 */
class ShopPeriodReport extends SS_Report{

	protected $dataClass = 'Order';
	protected $periodfield = "\"Order\".\"Created\"";
	protected $grouping = false;
	protected $pagesize = 20;

	public function title(){
		return _t($this->class.".TITLE",$this->title);
	}

	public function description(){
		return _t($this->class.".DESCRIPTION",$this->description);
	}

	public function parameterFields() {
		$dateformat = Member::currentUser()->getDateFormat();
		$fields = new FieldList(
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

	public function canView($member = null){
		if(get_class($this) == "ShopPeriodReport"){
			return false;
		}
		return parent::canView($member);
	}

	public function getReportField(){
		$field = parent::getReportField();
		$field->getConfig()->removeComponentsByType('GridFieldPaginator');
		return $field;
	}

	public function sourceRecords($params){
		isset($params['Grouping']) || $params['Grouping'] = "Month";
		$output = new ArrayList();
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
				$record->FilterPeriod = (empty($result[$this->periodfield])) ? "uncategorised" : date($dformat, strtotime($result[$this->periodfield]));
			}
		}
		return $output;
	}

	public function query($params){
		$query = new ShopReport_Query();
		$filterperiod = $this->periodfield;
		$query->setFrom('"' . $this->dataClass . '"');
		$start = isset($params['StartPeriod']) && !empty($params['StartPeriod']) ? date('Y-m-d',strtotime($params["StartPeriod"])) : null;
		$end = isset($params['EndPeriod']) && !empty($params['EndPeriod']) ? date('Y-m-d',strtotime($params["EndPeriod"]) + 86400) : null; //end day is inclusive
		if($start && $end){
			$query->addHaving("$filterperiod BETWEEN '$start' AND '$end'");
		}elseif($start){
			$query->addHaving("$filterperiod > '$start'");
		}elseif($end){
			$query->addHaving("$filterperiod <= '$end'");
		}
		if($start || $end){
			$query->addHaving("$filterperiod IS NOT NULL"); //only include paid orders when we are doing specific period searching
		}
		if($this->grouping){
			switch($params['Grouping']){
				case "Year":
					$query->addGroupBy("YEAR($filterperiod)");
					break;
				case "Month":
				default:
					$query->addGroupBy("YEAR($filterperiod),MONTH($filterperiod)");
					break;
				case "Week":
					$query->addGroupBy("YEAR($filterperiod),WEEK($filterperiod)");
					break;
				case "Day":
					$query->setLimit("0,1000");
					$query->addGroupBy("YEAR($filterperiod),MONTH($filterperiod),DAY($filterperiod)");
					break;
			}
		}
		if(isset($params["ctf"]["ReportContent"]["sort"])){
			$dir = isset($params["ctf"]["ReportContent"]["dir"]) ? $params["ctf"]["ReportContent"]["dir"] : "DESC";
			$query->addOrderBy($params["ctf"]["ReportContent"]["sort"], $dir);
		}
		if(isset($params["ctf"]["ReportContent"]["start"])){
			$query->setLimit($params["ctf"]["ReportContent"]["start"].",".$this->pagesize);
		}else{
			$query->setLimit($this->pagesize);
		}
		return $query;
	}

}

class ShopReport_Query extends SQLQuery{

	public function canSortBy($fieldName) {
		return true;
	}
}
