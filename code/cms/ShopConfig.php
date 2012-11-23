<?php

class ShopConfig extends DataExtension{
	
	static $db = array(
		'AllowedCountries' => 'Text'
	);
	
	static $has_one = array(
		'TermsPage' => 'SiteTree'
	);
	
	function populateDefaults(){
		$this->owner->AllowedCountries = Geoip::visitor_country();
	}

	function updateCMSFields(FieldList $fields) {
		$fields->insertBefore($shoptab = new Tab('Shop', 'Shop'), 'Access');
				
		$fields->addFieldsToTab("Root.Shop", new TabSet("ShopTabs",
			$maintab = new Tab("Main",
				new TreeDropdownField('TermsPageID', _t("ShopConfig.TERMSPAGE",'Terms and Conditions Page'), 'SiteTree')
			),
			$countriestab = new Tab("Countries",
				$allowed = new CheckboxSetField('AllowedCountries','Allowed Ordering and Shipping Countries',Geoip::getCountryDropDown())
			)
		));
		$fields->removeByName("CreateTopLevelGroups");
		$countriestab->setTitle("Allowed Countries");
	}
	
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
	
}