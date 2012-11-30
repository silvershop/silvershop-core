<?php

class SetLocationForm extends Form{
	
	function __construct($controller, $name = "SetLocationForm"){
		$countries = SiteConfig::current_site_config()->getCountriesList();
		$fields = new FieldSet(
			$countryfield = new DropdownField("Country",_t('SetLocationForm.COUNTRY','Country'),$countries)
		);
		$countryfield->setHasEmptyDefault(true);
		$countryfield->setEmptyString(_t('SetLocationForm.CHOOSECOUNTRY','Choose country...'));
		$actions = new FieldSet(
			new FormAction("setLocation","set")	
		);
		parent::__construct($controller, $name, $fields, $actions);
		//load currently set location
		if($address = ShopUserInfo::get_location()){
			$countryfield->setHasEmptyDefault(false);
			$this->loadDataFrom($address);
		}
	}
	
	function setLocation($data,$form){
		ShopUserInfo::set_location($data);
		Controller::curr()->redirectBack();
	}
	
}

class LocationFormPageDecorator extends Extension{
	
	static $allowed_actions = array(
		"SetLocationForm"
	);
	
	function SetLocationForm(){
		return new SetLocationForm($this->owner);
	}
	
}