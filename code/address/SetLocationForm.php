<?php

class SetLocationForm extends Form{

	public function __construct($controller, $name = "SetLocationForm") {
		$countries = SiteConfig::current_site_config()->getCountriesList();
		$fields = new FieldList(
			$countryfield = new DropdownField("Country", _t('SetLocationForm.Country', 'Country'), $countries)
		);
		$countryfield->setHasEmptyDefault(true);
		$countryfield->setEmptyString(_t('SetLocationForm.ChooseCountry', 'Choose country...'));
		$actions = new FieldList(
			new FormAction("setLocation", "set")
		);
		parent::__construct($controller, $name, $fields, $actions);
		//load currently set location
		if($location = ShopUserInfo::singleton()->getLocation()){
			$countryfield->setHasEmptyDefault(false);
			$this->loadDataFrom($location);
		}
	}

	public function setLocation($data, $form) {
		ShopUserInfo::singleton()->setLocation($data);
		$this->controller->redirectBack();
	}

}

class LocationFormPageDecorator extends Extension{

	public static $allowed_actions = array(
		"SetLocationForm"
	);

	public function SetLocationForm() {
		return new SetLocationForm($this->owner);
	}

}
