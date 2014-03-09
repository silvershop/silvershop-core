<?php

/**
 * @package shop
 * @subpackage cms
 */

class ShopConfig extends DataExtension {

	private static $db = array(
		'AllowedCountries' => 'Text'
	);

	private static $has_one = array(
		'TermsPage' => 'SiteTree',
		"CustomerGroup" => "Group",
		'DefaultProductImage' => 'Image',
	);

	private static $email_from;

	public static function current() {
		return SiteConfig::current_site_config();
	}

	public function updateCMSFields(FieldList $fields) {
		$fields->insertBefore($shoptab = new Tab('Shop', 'Shop'), 'Access');
		$fields->addFieldsToTab("Root.Shop", new TabSet("ShopTabs",
			$maintab = new Tab("Main",
				TreeDropdownField::create('TermsPageID',
					_t("ShopConfig.TERMSPAGE", 'Terms and Conditions Page'),
				'SiteTree'),
				TreeDropdownField::create("CustomerGroupID",
					_t("ShopConfig.CUSTOMERGROUP", "Group to add new customers to"),
				"Group"),
				UploadField::create('DefaultProductImage', _t('ShopConfig.DEFAULTIMAGE', 'Default Product Image'))
			),
			$countriestab = new Tab("Countries",
				CheckboxSetField::create('AllowedCountries', 'Allowed Ordering and Shipping Countries',
					self::config()->iso_3166_country_codes
				)
			)
		));
		$fields->removeByName("CreateTopLevelGroups");
		$countriestab->setTitle("Allowed Countries");
	}

	public static function get_base_currency() {
		return self::config()->base_currency;
	}

	public static function get_site_currency() {
		return self::get_base_currency();
	}

	/**
	 * Get list of allowed countries
	 * @param boolean $prefixisocode - prefix the country code
	 * @return array
	 */
	public function getCountriesList($prefixisocode = false) {
		$countries = self::config()->iso_3166_country_codes;
		asort($countries);
		if($allowed = $this->owner->AllowedCountries){
			$allowed = explode(",", $allowed);
			if(count($allowed > 0)){
				$countries = array_intersect_key($countries, array_flip($allowed));
			}
		}
		if($prefixisocode){
			foreach($countries as $key => $value){
				$countries[$key] = "$key - $value";
			}
		}
		return $countries;
	}

	/*
	 * Convert iso country code to English country name
	 * @return string - name of country
	 */
	public static function countryCode2name($code) {
		$codes = self::config()->iso_3166_country_codes;
		if(isset($codes[$code])){
			return $codes[$code];
		}
		return $code;
	}

	/**
	 * Helper for getting static shop config.
	 * The 'config' static function isn't avaialbe on Extensions.
	 * @return Config_ForClass configuration object
	 */
	public static function config() {
		return new Config_ForClass("ShopConfig");
	}

}
