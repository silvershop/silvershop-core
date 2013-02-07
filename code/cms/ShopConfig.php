<?php

class ShopConfig extends DataObjectDecorator{
		
	function extraStatics(){
		return array(
			'db' => array(
				'AllowedCountries' => 'Text'		
			),
			'has_one' => array(
				'TermsPage' => 'SiteTree',
				"CustomerGroup" => "Group"
			)
		);
	}
	
	function populateDefaults(){
		$this->owner->AllowedCountries = Geoip::visitor_country();
	}
	
	function updateCMSFields($fields){
		$fields->insertBefore($shoptab = new Tab('Shop', 'Shop'), 'Access');
				
		$fields->addFieldsToTab("Root.Shop", new TabSet("ShopTabs",
			$maintab = new Tab("Main",
				new TreeDropdownField('TermsPageID', _t("ShopConfig.TERMSPAGE",'Terms and Conditions Page'), 'SiteTree'),
				new TreeDropdownField("CustomerGroupID", _t("ShopConfig.CUSTOMERGROUP","Group to add new customers to"), "Group")
			),
			$countriestab = new Tab("Countries",
				$allowed = new CheckboxSetField('AllowedCountries','Allowed Ordering and Shipping Countries',Geoip::getCountryDropDown())
			)
		));
		$fields->removeByName("CreateTopLevelGroups");
		$countriestab->setTitle("Allowed Countries");
	}
	
	/**
	 * Get list of allowed countries
	 * @param boolean $prefixisocode - prefix the country code
	 * @return array
	 */
	function getCountriesList($prefixisocode = false){
		$countries = Geoip::getCountryDropDown();
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
	
	/**
	 * Alias function for getting SiteConfig
	 * @return SiteConfig
	 */
	static function current($locale = null){
		return SiteConfig::current_site_config($locale);
	}
	
	
}