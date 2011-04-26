<?php

/**
 * @description: This class helps you to manage countries within the context of e-commerce.
 * For example: To what countries can be sold.
 * /dev/build/?resetecommercecountries=1 will reset the list of countries...
 *
 * @author nicolaas [at] sunnysideup.co.nz
 *
 * @package: ecommerce
 * @sub-package: member
 *
 *
 **/

class EcommerceCountry extends DataObject {

	/**
	 * Should we automatically add all countries to EcommerceCountry dataObjects.
	 * The default value is YES, but in some cases you may want to do this yourself.
	 * In this case, use set_auto_add_countries and set it to NO.
	 *@var Boolean
	 **/
	protected static $auto_add_countries = true;
		static function set_auto_add_countries(boolean $b) {self::$auto_add_countries = $b;}
		static function get_auto_add_countries() {return self::$auto_add_countries;}

	/**
	 * In determing the country from which the order originated.
	 * For, for example, tax purposes - we use the Billing Address (@see Order::Country).
	 * However, we can also choose the Shipping Address
	 *@var Boolean
	 **/
	protected static $use_shipping_address_country_as_default_country = false;
		static function set_use_shipping_address_country_as_default_country(boolean $b) {self::$use_shipping_address_country_as_default_country = $b;}
		static function get_use_shipping_address_country_as_default_country() {return self::$use_shipping_address_country_as_default_country;}

	static $db = array(
		"Code" => "Varchar(3)",
		"Name" => "Varchar(200)",
		"DoNotAllowSales" => "Boolean"
	);

	static $indexes = array(
		"Code" => true
	);

	static $default_sort = "\"Name\" ASC";

	public static $singular_name = "Country";
		function i18n_singular_name() { return _t("EcommerceCountry.COUNTRY", "Country");}

	public static $plural_name = "Countries";
		function i18n_plural_name() { return _t("EcommerceCountry.COUNTRIES", "Countries");}

	/**
	 * by setting this, the shop can only ever sell to ONE country.
	 *@param $code = string, Country Code  (e.g NZ)
	 **/
	protected static $fixed_country_code = '';
		static function set_fixed_country_code($s) {self::$fixed_country_code = $s;}
		static function get_fixed_country_code() {return self::$fixed_country_code;}

	/**
	 * Set $allowed_country_codes to allow sales to a select number of countries
	 *@param $a : array("NZ" => "NZ", "UK => "UK", etc...)
	 *@param $s : string - country code, e.g. NZ
	 **/
	protected static $allowed_country_codes = array();
		static function set_allowed_country_codes(array $a) {self::$allowed_country_codes = $a;}
		static function get_allowed_country_codes() {return self::$allowed_country_codes;}
		static function add_allowed_country_code(string $s) {self::$allowed_country_codes[$s] = $s;}
		static function remove_allowed_country_code(string $s) {unset(self::$allowed_country_codes[$s]);}


	/**
	*these variables and methods allow to to "dynamically limit the countries available, based on, for example: ordermodifiers, item selection, etc....
	* for example, if a person chooses delivery within Australasia (with modifier) - then you can limit the countries available to "Australasian" countries
	* @param $a = array should be country codes.e.g array("NZ", "NP", "AU");
	**/
	protected static $for_current_order_only_show_countries = array();
		static function set_for_current_order_only_show_countries(array $a) {
			if(count(self::$for_current_order_only_show_countries)) {
				self::$for_current_order_only_show_countries = array_intersect($a, self::$for_current_order_only_show_countries);
			}
			else {
				self::$for_current_order_only_show_countries = $a;
			}
		}
		static function get_for_current_order_only_show_countries() {return self::$for_current_order_only_show_countries;}

	protected static $for_current_order_do_not_show_countries = array();
		static function set_for_current_order_do_not_show_countries(array $a) {
			self::$for_current_order_do_not_show_countries = array_merge($a, self::$for_current_order_do_not_show_countries);
		}
		static function get_for_current_order_do_not_show_countries() {return self::$for_current_order_do_not_show_countries;}


	/**
	 * This function works out the most likely country of the current member / visitor.
	 *@return String - Country Code - e.g. NZ
	 **/
	public static function get_country() {
		$countryCode = '';
		//1. fixed country is first
		$countryCode = EcommerceRole::get_fixed_country_code();
		if(!$countryCode) {
			//2. check shipping address
			if($o = ShoppingCart::current_order()) {
				$countryCode = $o->Country();
			}
			//3 check session - NOTE: session saves to member + shipping address
			if(!$countryCode) {
				$countryCode = Session::get(ShoppingCart::get_country_setting_index());
				//5. check GEOIP information
				if(!$countryCode) {
					$countryCode = Geoip::visitor_country();
					//6. check default country....
					if(!$countryCode) {
						$countryCode = Geoip::$default_country_code;
						//7. check default countries from ecommerce... - NOTE: fixed is checked first....
						if(!$countryCode) {
							$a = EcommerceRole::get_allowed_country_codes();
							if(is_array($a) && count($a)) {
								$countryCode = array_shift($a);
							}
						}
					}
				}
			}
		}
		return $countryCode;
	}



	/**
	 *checks if a country code is allowed
	 *@param String $code - e.g. NZ
	 *@return Boolean
	 **/
	public static function country_code_allowed($code) {
		if($code) {
			$c = self::get_fixed_country_code();
			if($c) {
				if($c == $code) {
					return true;
				}
			}
			else {
				$a = self::get_allowed_country_codes();
				if(is_array($a) && count($a)) {
					if(in_array($code, $a, false) || array_key_exists($code, $a)) {
						return true;
					}
				}
				else {
					$a = Geoip::getCountryDropDown();
					if(isset($a[$code])) {
						return true;
					}
				}
			}
		}
		return false;
	}


	/**
	 *@param $code String (Country Code)
	 *@return String (country name)
	 **/
	public static function find_country_title($code) {
		$countries = Geoip::getCountryDropDown();
		// check if code was provided, and is found in the country array
		if($code && isset($countries[$code])) {
			return $countries[$code];
		}
		else {
			return false;
		}
	}

	/**
	 *@return Array (Code, Title)
	 **/
	public static function list_of_allowed_countries_for_dropdown() {
		$keys = array();
		$allowedCountryCode = self::get_fixed_country_code();
		$allowedCountryCodeArray = self::get_allowed_country_codes();
		if($allowedCountryCode) {
			$keys[$allowedCountryCode] = $allowedCountryCode;
		}
		elseif($allowedCountryCodeArray && count($allowedCountryCodeArray)) {
			$keys = array_merge($keys, $allowedCountryCodeArray);
		}
		if(isset($keys) && count($keys)) {
			$newArray = array();
			foreach($keys as $key) {
				$codeTitleArray[$key] = self::find_country_title($key);
			}
		}
		else {
			$codeTitleArray = Geoip::getCountryDropDown();
		}
		$onlyShow = self::get_for_current_order_only_show_countries();
		$doNotShow = self::get_for_current_order_do_not_show_countries();
		if(is_array($onlyShow) && count($onlyShow)) {
			foreach($codeTitleArray as $key => $value) {
				if(!in_array($key, $onlyShow)) {
					unset($codeTitleArray[$key]);
				}
			}
		}
		if(is_array($doNotShow) && count($doNotShow)) {
			foreach($doNotShow as $countryCode) {
				unset($codeTitleArray[$countryCode]);
			}
		}
		return $codeTitleArray;
	}

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

