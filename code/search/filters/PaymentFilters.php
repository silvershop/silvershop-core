<?php

/**
 * @description: provides a bunch of filters for search in ModelAdmin (CMS)
 *
 * @authors: Nicolaas
 *
 * @package: ecommerce
 * @sub-package: cms
 *
 **/


class PaymentFilter_AroundDateFilter extends ExactMatchFilter {

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
			$query->where("(\"Payment\".\"Created\"::date - '$formattedDate'::date)::integer > -".self::get_how_many_days_around()." AND (\"Payment\".\"Created\"::date - '$formattedDate'::date)::integer < ".self::get_how_many_days_around());
		}
		else {
			// default is MySQL DATEDIFF() function - broken for others, each database conn type supported must be checked for!
			$query->where("(DATEDIFF(\"Payment\".\"Created\", '$formattedDate') > -".self::get_how_many_days_around()." AND DATEDIFF(\"Payment\".\"Created\", '$formattedDate') < ".self::get_how_many_days_around().")");
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
