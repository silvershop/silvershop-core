<?php

/**
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/


class OrderFilters_AroundDateFilter extends ExactMatchFilter {

	protected static $how_many_days_around = 31;
		static function set_how_many_days_around(integer $i){self::$how_many_days_around = $i;}
		static function get_how_many_days_around(){return self::$how_many_days_around;}

	/**
	 *
	 *@return SQLQuery
	 **/
	public function apply(SQLQuery $query) {
		$query = $this->applyRelation($query);
		$value = $this->getValue();
		$date = new Date();
		$date->setValue($value);
		$formattedDate = $date->format("Y-m-d");

		// changed for PostgreSQL compatability
		// NOTE - we may wish to add DATEDIFF function to PostgreSQL schema, it's just that this would be the FIRST function added for SilverStripe
		$db = DB::getConn();
		if( $db instanceof PostgreSQLDatabase ) {
			// don't know whether functions should be used, hence the following code using an interval cast to an integer
			$query->where("(\"Order\".\"Created\"::date - '$formattedDate'::date)::integer > -".self::get_how_many_days_around()." AND (\"Order\".\"Created\"::date - '$formattedDate'::date)::integer < ".self::get_how_many_days_around());
		}
		else {
			// default is MySQL DATEDIFF() function - broken for others, each database conn type supported must be checked for!
			$query->where("(DATEDIFF(\"Order\".\"Created\", '$formattedDate') > -".self::get_how_many_days_around()." AND DATEDIFF(\"Order\".\"Created\", '$formattedDate') < ".self::get_how_many_days_around().")");
		}
		return $query;

	}

	/**
	 *
	 *@return Boolean
	 **/
	public function isEmpty() {
		return $this->getValue() == null || $this->getValue() == '';
	}

}



class OrderFilters_MultiOptionsetFilter extends SearchFilter {

	/**
	 *
	 *@return SQLQuery
	 **/
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
			$query->where(implode(" OR ", $matches));
		}
		return $query;
	}

	/**
	 *
	 *@return Boolean
	 **/
	public function isEmpty() {
		if(is_array($this->getValue())) {
			return count($this->getValue()) == 0;
		}
		else {
			return $this->getValue() == null || $this->getValue() == '';
		}
	}
}

class OrderFilters_MultiOptionsetStatusIDFilter extends SearchFilter {

	/**
	 *
	 *@return SQLQuery
	 **/
	public function apply(SQLQuery $query) {
		$query = $this->applyRelation($query);
		$values = $this->getValue();
		if(count($values)) {
			foreach($values as $value) {
				$matches[] = "\"StatusID\" = ".intval($value);
			}
			$query->where(implode(" OR ", $matches));
		}
		return $query;
	}

	/**
	 *
	 *@return Boolean
	 **/
	public function isEmpty() {
		if(is_array($this->getValue())) {
			return count($this->getValue()) == 0;
		}
		else {
			return $this->getValue() == null || $this->getValue() == '';
		}
	}
}

class OrderFilters_HasBeenCancelled extends SearchFilter {

	/**
	 *
	 *@return SQLQuery
	 **/
	public function apply(SQLQuery $query) {
		$query = $this->applyRelation($query);
		$value = $this->getValue();
		if($value == 1) {
			$query->where("\"CancelledByID\" IS NOT NULL AND \"CancelledByID\" > 0");
		}
		return $query;
	}

	/**
	 *
	 *@return Boolean
	 **/
	public function isEmpty() {
		return $this->getValue() == null || $this->getValue() == '' || $this->getValue() == 0;
	}
}

class OrderFilters_MustHaveAtLeastOnePayment extends SearchFilter {

	/**
	 *
	 *@return SQLQuery
	 **/
	public function apply(SQLQuery $query) {
		$query = $this->applyRelation($query);
		$value = $this->getValue();
		if($value && in_array($value, array(0,1))) {
			$query->innerJoin(
				$table = "Payment", // framework already applies quotes to table names here!
				$onPredicate = "\"Payment\".\"OrderID\" = \"Order\".\"ID\"",
				$tableAlias=null
			);
		}
		return $query;
	}

	/**
	 *
	 *@return Boolean
	 **/
	public function isEmpty() {
		return $this->getValue() == null || $this->getValue() == '' || $this->getValue() == 0;
	}
}
