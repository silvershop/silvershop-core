<?php


class OrderFilters_AroundDateFilter extends ExactMatchFilter {

	protected static $how_many_days_around = 31;
		static function set_how_many_days_around($v){self::$how_many_days_around = $v;}
		static function get_how_many_days_around(){return self::$how_many_days_around;}

	public function apply(SQLQuery $query) {
		$query = $this->applyRelation($query);
		$value = $this->getValue();
		$date = new Date();
		$date->setValue($value);
		$formattedDate = $date->format("Y-m-d");
		return $query->where("(DATEDIFF(\"Order\".\"Created\", '$formattedDate') > -".self::get_how_many_days_around()." AND DATEDIFF(\"Order\".\"Created\", '$formattedDate') < ".self::get_how_many_days_around().")");
	}

	public function isEmpty() {
		return $this->getValue() == null || $this->getValue() == '';
	}

}



class OrderFilters_MultiOptionsetFilter extends SearchFilter {

	public function apply(SQLQuery $query) {
		$query = $this->applyRelation($query);
		$values = $this->getValue();
		if(count($values)) {
			foreach($values as $value) {
				$matches[] = sprintf("%s LIKE '%s%%'",
					$this->getDbName(),
					Convert::raw2sql(str_replace("'", '', $value))
				);
			}

			return $query->where(implode(" OR ", $matches));
		}
		return $query;
	}

	public function isEmpty() {
		if(is_array($this->getValue())) {
			return count($this->getValue()) == 0;
		}
		else {
			return $this->getValue() == null || $this->getValue() == '';
		}
	}
}
class OrderFilters_MustHaveAtLeastOnePayment extends SearchFilter {

	public function apply(SQLQuery $query) {
		$query = $this->applyRelation($query);
		$value = $this->getValue();
		if($value) {
			return $query->innerJoin(
				$table = "Payment",
				$onPredicate = "Payment.OrderID = Order.ID",
				$tableAlias=null
			);
		}
	}

	public function isEmpty() {
		return $this->getValue() == null || $this->getValue() == '' || $this->getValue() == 0;
	}
}
