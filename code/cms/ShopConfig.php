<?php

class ShopConfig extends DataExtension{
	
	static $db = array(
		'AllowedCountries' => 'Text'
	);
	
	static $has_one = array(
		'TermsPage' => 'SiteTree',
		"CustomerGroup" => "Group"
	);
	
	static function current(){
		return SiteConfig::current_site_config();
	}
	
	function updateCMSFields(FieldList $fields) {
		$fields->insertBefore($shoptab = new Tab('Shop', 'Shop'), 'Access');
		$fields->addFieldsToTab("Root.Shop", new TabSet("ShopTabs",
			$maintab = new Tab("Main",
				new TreeDropdownField('TermsPageID', _t("ShopConfig.TERMSPAGE",'Terms and Conditions Page'), 'SiteTree'),
				new TreeDropdownField("CustomerGroupID", _t("ShopConfig.CUSTOMERGROUP","Group to add new customers to"), "Group")
			),
			$countriestab = new Tab("Countries",
				$allowed = new CheckboxSetField('AllowedCountries','Allowed Ordering and Shipping Countries',
					Config::inst()->get('ShopConfig','iso_3166_country_codes')
				)
			)
		));
		$fields->removeByName("CreateTopLevelGroups");
		$countriestab->setTitle("Allowed Countries");
	}

	static function get_base_currency(){
		return Config::inst()->get('ShopConfig','base_currency');
	}

	static function get_site_currency(){
		return self::get_base_currency();
	}
	
	/**
	 * Get list of allowed countries
	 * @param boolean $prefixisocode - prefix the country code
	 * @return array
	 */
	function getCountriesList($prefixisocode = false){
		$countries = Config::inst()->get('ShopConfig','iso_3166_country_codes');
		asort($countries);
		if($allowed = $this->owner->AllowedCountries){
			$allowed = explode(",",$allowed);
			if(count($allowed > 0))
				$countries = array_intersect_key($countries,array_flip($allowed));
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
	static function countryCode2name($code){
		$codes = Config::inst()->get('ShopConfig','iso_3166_country_codes');
		if(isset($codes[$code])){
			return $codes[$code];
		}
		return $code;
	}
	
}
