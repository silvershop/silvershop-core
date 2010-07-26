<?php

class OrderFilters_EqualOrGreaterDateFilter extends ExactMatchFilter {

	public function apply(SQLQuery $query) {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$query = $this->applyRelation($query);
		$value = $this->getValue();
		$date = new Date();
		$date->setValue($value);
		$formattedDate = $date->format("Y-m-d");
		return $query->where("{$bt}Order{$bt}.{$bt}Created{$bt} >= '$formattedDate'");
	}
}

class OrderFilters_EqualOrSmallerDateFilter extends ExactMatchFilter {

	public function apply(SQLQuery $query) {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$query = $this->applyRelation($query);
		$value = $this->getValue();
		$date = new Date();
		$date->setValue($value);
		$formattedDate = $date->format("Y-m-d");
		return $query->where("{$bt}Order{$bt}.{$bt}Created{$bt} <= '$formattedDate'");
	}
}



