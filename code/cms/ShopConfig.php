<?php

class ShopConfig extends DataObjectDecorator{
	
	function extraStatics(){
		return array(
			'db' => array(
				'AllowedCountries' => 'Text'		
			),
			'has_one' => array(
				'TermsPage' => 'SiteTree'
			)
		);
	}
	
	function populateDefaults(){
		$this->owner->AllowedCountries = Geoip::visitor_country();
	}
	
	function updateCMSFields($fields){
		$fields->insertBefore($shoptab = new Tab('Shop', 'Shop'), 'Access');
		$fields->addFieldsToTab("Root.Shop", new TabSet("ShopTabs",$countriestab = new Tab("Countries",
			$allowed = new CheckboxSetField('AllowedCountries','Allowed Ordering and Shipping Countries',Geoip::getCountryDropDown())
		)));
		$countriestab->setTitle("Allowed Countries");
	}
	
	function getCountriesList(){
		$countries = Geoip::getCountryDropDown();
		if($allowed = $this->owner->AllowedCountries){
			$allowed = explode(",",$allowed);
			if(count($allowed > 0))
				$countries = array_intersect_key($countries,array_flip($allowed));
		}
		return $countries;
	}
	
}