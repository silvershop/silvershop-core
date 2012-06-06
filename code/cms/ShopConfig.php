<?php

class ShopConfig extends DataObjectDecorator{
	
	function extraStatics(){
		return array(
			'db' => array(
				'AllowedCountries' => 'Text'		
			)		
		);
	}
	
	function populateDefaults(){
		$this->owner->AllowedCountries = Geoip::visitor_country();
	}
	
	function updateCMSFields($fields){
		$fields->insertBefore($shoptab = new Tab('Shop', 'Shop'), 'Access');
		//$fields->findOrMakeTab("Root.Shop.Shipping","Shipping Countries"); //TODO: make Shipping tab
		$fields->addFieldsToTab("Root.Shop", array(
			$allowed = new CheckboxSetField('AllowedCountries','Allowed Ordering and Shipping Countries',Geoip::getCountryDropDown())
		));
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