<?php
/**
 * Base class for creating reports that can be filtered to a specific range.
 * Record grouping is also supported.
 */
class ShopPeriodReport extends SS_Report{

	private static $display_uncategorised_data = false;

	protected $dataClass = 'Order';
	protected $periodfield = "\"Order\".\"Created\"";
	protected $grouping = false;
	protected $pagesize = 30;

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
			), "Month"));
			if(self::config()->display_uncategorised_data){
				$fields->push(
					CheckboxField::create("IncludeUncategorised", "Include Uncategorised Data")
						->setDescription("Display data that doesn't have a date.")
				);
			}
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
		$conf = $field->getConfig();
		$conf->removeComponentsByType('GridFieldPaginator')
			->addComponent($pagi = new GridFieldLitePaginator(5));

		$pagi->setTotalItems(100);

		return $field;
	}

	public function sourceRecords($params){
		isset($params['Grouping']) || $params['Grouping'] = "Month";

		$output = new ArrayList();
		$query = $this->query($params);
		//TODO: this breaks with large data sets
		$results = $query->execute();
		//TODO: push empty months and days to fill out gaps?
		foreach($results as $result){
			$record = new $this->dataClass($result);
			if($this->grouping){
				$dformats = array(
					"Year" => "Y",
					"Month" => "Y - F",
					//"Week" => "o - W",
					"Day" =>	"d F Y"
				);
				$dformat = $dformats[$params['Grouping']];
				$pf = "FilterPeriod";
				if(empty($result[$pf])){
					$record->FilterPeriod = "uncategorised";
					if(!isset($params['IncludeUncategorised'])){
						continue;
					}
				}else{
					$record->FilterPeriod = date($dformat, strtotime($result[$pf]));
				}
			}
			$output->push($record);
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
		if($start || $end || !self::config()->display_uncategorised_data){
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
		$query->setLimit($this->pagesize, $this->getOffset());
	
		return $query;
	}

	public function getOffset(){
		$state_json = isset($_REQUEST['Report']['GridState']) ? $_REQUEST['Report']['GridState'] : null;
		$offset = 0;

		if(!$state_json){
			return $offset;
		}
		//hack up state object to use
		$state = GridState::create(new GridField("Report"));
		$state->setValue($state_json);

		$state = $state->getData();
		//var_dump($state_json);
		//var_dump($state->Value());
		//Debug::show($state->GridFieldPaginator->currentPage);
		//TODO: return offset, based on this->pagesize

		return $offset;
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
