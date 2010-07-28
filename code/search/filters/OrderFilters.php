<?php


class OrderFilters_AroundDateFilter extends ExactMatchFilter {

	protected static $how_many_days_around = 31;
		static function set_how_many_days_around($v){self::$how_many_days_around = $v;}
		static function get_how_many_days_around(){return self::$how_many_days_around;}

	public function apply(SQLQuery $query) {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$query = $this->applyRelation($query);
		$value = $this->getValue();
		$date = new Date();
		$date->setValue($value);
		$formattedDate = $date->format("Y-m-d");
		return $query->where("(DATEDIFF({$bt}Order{$bt}.{$bt}Created{$bt}, '$formattedDate') > -".self::get_how_many_days_around()." AND DATEDIFF({$bt}Order{$bt}.{$bt}Created{$bt}, '$formattedDate') < ".self::get_how_many_days_around().")");
	}
}



class Order_FiltersMultiOptionsetFilter extends SearchFilter {

	public function apply(SQLQuery $query) {
		$query = $this->applyRelation($query);
		$values = $this->getValue();
		foreach($values as $value) {
			$matches[] = sprintf("%s LIKE '%s%%'",
				$this->getDbName(),
				Convert::raw2sql(str_replace("'", '', $value))
			);
		}

		return $query->where(implode(" OR ", $matches));
	}

	public function isEmpty() {
		return $this->getValue() == null || $this->getValue() == '';
	}
}
