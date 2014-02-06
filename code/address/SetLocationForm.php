<?php

class SetLocationForm extends Form{

	public function __construct($controller, $name = "SetLocationForm"){
		$countries = SiteConfig::current_site_config()->getCountriesList();
		$fields = new FieldList(
			$countryfield = new DropdownField("Country",_t('SetLocationForm.COUNTRY','Country'),$countries)
		);
		$countryfield->setHasEmptyDefault(true);
		$countryfield->setEmptyString(_t('SetLocationForm.CHOOSECOUNTRY','Choose country...'));
		$actions = new FieldList(
			new FormAction("setLocation","set")
		);
		parent::__construct($controller, $name, $fields, $actions);
		//load currently set location
		if($address = ShopUserInfo::get_location()){
			$countryfield->setHasEmptyDefault(false);
			$this->loadDataFrom($address);
		}
	}

	public function setLocation($data,$form){
		ShopUserInfo::set_location($data);
		$this->controller->redirectBack();
	}

}

class LocationFormPageDecorator extends Extension{

	public static $allowed_actions = array(
		"SetLocationForm"
	);

	public function SetLocationForm(){
		return new SetLocationForm($this->owner);
	}

}
