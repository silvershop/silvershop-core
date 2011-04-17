<?php

/**
 *@author nicolaas [at] sunnysideup.co.nz
 *
 **/

class EcommerceCountry extends DataObject {

	protected static $auto_add_countries = true;
		static function set_auto_add_countries(boolean $b) {self::$auto_add_countries = $b;}
		static function get_auto_add_countries() {return self::$auto_add_countries;}

	static $db = array(
		"Code" => "Varchar(3)",
		"Name" => "Varchar(200)",
		"DoNotAllowSales" => "Boolean"
	);

	static $indexes = array(
		"Code" => true
	);

	static $default_sort = "Name";

	public static $singular_name = "Country";

	public static $plural_name = "Countries";

	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		if(self::get_auto_add_countries()) {
			if(!DataObject::get("EcommerceCountry") || isset($_REQUEST["resetecommercecountries"])) {
				$array = Geoip::getCountryDropDown();
				foreach($array as $key => $value) {
					if(!DataObject::get_one("EcommerceCountry", "\"Code\" = '".$key."'")) {
						$obj = new EcommerceCountry();
						$obj->Code = $key;
						$obj->Name = $value;
						$obj->write();
					}
				}
			}
		}
	}
}

