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
		
		// changed for PostgreSQL compatability
		// NOTE - we may wish to add DATEDIFF function to PostgreSQL schema, it's just that this would be the FIRST function added for SilverStripe
		$db = DB::getConn();
		if( $db instanceof PostgreSQLDatabase )
		{
			// don't know whether functions should be used, hence the following code using an interval cast to an integer 
			return $query->where("(\"Order\".\"Created\"::date - '$formattedDate'::date)::integer > -".self::get_how_many_days_around()." AND (\"Order\".\"Created\"::date - '$formattedDate'::date)::integer < ".self::get_how_many_days_around());
	}
		else
		{
			// default is MySQL DATEDIFF() function - broken for others, each database conn type supported must be checked for!
			return $query->where("(DATEDIFF(\"Order\".\"Created\", '$formattedDate') > -".self::get_how_many_days_around()." AND DATEDIFF(\"Order\".\"Created\", '$formattedDate') < ".self::get_how_many_days_around().")");
		}

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
				$table = "Payment", // framework already applies quotes to table names here!
				$onPredicate = "\"Payment\".\"OrderID\" = \"Order\".\"ID\"",
				$tableAlias=null
			);
		}
	}

	public function isEmpty() {
		return $this->getValue() == null || $this->getValue() == '' || $this->getValue() == 0;
	}
}
