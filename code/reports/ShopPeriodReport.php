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
				//"Week" => "Week",
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
					//"Week" => "o - W",
					"Day" =>	"d F Y"
				);
				$dformat = $dformats[$params['Grouping']];
				$pf = "FilterPeriod";
				$record->FilterPeriod = empty($result[$pf]) ? "uncategorised" : date($dformat, strtotime($result[$pf]));
			}
		}
		return $output;
	}

	public function query($params){
		$filterperiod = $this->periodfield;
		$query = new ShopReport_Query();
		$query->setSelect(array("FilterPeriod" => "MIN($filterperiod)"));

		$query->setFrom('"' . $this->dataClass . '"');
		$start = isset($params['StartPeriod']) && !empty($params['StartPeriod']) ? date('Y-m-d',strtotime($params["StartPeriod"])) : null;
		$end = isset($params['EndPeriod']) && !empty($params['EndPeriod']) ? date('Y-m-d',strtotime($params["EndPeriod"]) + 86400) : null; //end day is inclusive
		if($start && $end){
			$query->addWhere("$filterperiod BETWEEN '$start' AND '$end'");
		}elseif($start){
			$query->addWhere("$filterperiod > '$start'");
		}elseif($end){
			$query->addWhere("$filterperiod <= '$end'");
		}
		if($start || $end){
			$query->addWhere("$filterperiod IS NOT NULL");
		}
		if($this->grouping){
			switch($params['Grouping']){
				case "Year":
					$query->addGroupBy($this->fd($filterperiod, '%Y'));
					break;
				case "Month":
				default:
					$query->addGroupBy($this->fd($filterperiod, '%Y').",".$this->fd($filterperiod, '%m'));
					break;
				// case "Week":
				// 	$query->addGroupBy($this->fd($filterperiod, '%Y').",CAST(".$this->fd($filterperiod, '%d')."/365 * 52, INTEGER)");
				// 	break;
				case "Day":
					$query->setLimit("0,1000");
					$query->addGroupBy($this->fd($filterperiod, '%Y').",".$this->fd($filterperiod, '%m').",".$this->fd($filterperiod, '%d'));
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

	protected function fd($date, $format){
		return DB::getConn()->formattedDatetimeClause($date, $format);
	}

}

class ShopReport_Query extends SQLQuery{

	public function canSortBy($fieldName) {
		return true;
	}
}
