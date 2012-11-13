<?php

class RegionRestriction extends DataObject{
	
	static $db = array(
		"Country" => "ShopCountry",
		"State" => "Varchar",
		"City" => "Varchar",
		"PostalCode" => "Varchar(10)"
	);
	
	static $defaults = array(
		"Country" => "*",
		"State" => "*",
		"City" => "*",
		"PostalCode" => "*"
	);
	
	static $default_sort = "\"Country\" ASC, \"State\" ASC, \"City\" ASC, \"PostalCode\" ASC";
	
	static $summary_fields = array(
		'Country',
		'State',
		'City',
		'PostalCode'
	);
	
	static $field_labels = array(
		'Country' => 'Country',
		'State' => 'State/Region',
		'City' => 'City/Sub-Region',
		'PostalCode' => 'Post/Zip Code'
	);
	
	/*
	 * Specifies form field types to use in TableFields
	 */
	static $table_field_types = array(
		'Country' => 'RestrictionRegionCountryDropdownField',
		'State' => 'TextField',
		'City' => 'TextField',
		'PostalCode' => 'TextField'
	);
	
	/**
	 * Produce a SQL filter to get matching RegionRestrictions to a given address
	 * @param Address $address
	 */
	static function address_filter(Address $address){	
		$restrictables = array(
			"Country",
			"State",
			"City",
			"PostalCode"
		);
		$where = array();
		$rr = "\"RegionRestriction\"";
		foreach($restrictables as $field){
			$where[] = "TRIM(LOWER($rr.\"$field\")) = TRIM(LOWER('".$address->$field."')) OR $rr.\"$field\" = '*' OR $rr.\"$field\" = ''";
		}
		return "(".implode(") AND (", $where).")";
	}
	
	static function get_table_field_types(){
		return self::$table_field_types;
	}
	
	function onBeforeWrite(){
		//prevent empty data - '*' must be used
		foreach(self::$defaults as $field => $value){
			if(empty($this->$field)){
				$this->$field = $value;
			}
		}
		//TODO: prevent non-heirarichal entries, eg country = '*', state = 'blah'		
		parent::onBeforeWrite();
	}
	
}