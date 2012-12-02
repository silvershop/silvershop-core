<?php

class RestrictionRegionCountryDropdownField extends CountryDropdownField{
	
	function __construct($name, $title = null, $source = null, $value = "", $form = null) {
		
		$source = SiteConfig::current_site_config()->getCountriesList(true);
		
		parent::__construct($name, $title, $source, $value, $form);
		$this->setHasEmptyDefault(true);
		$this->setEmptyString("-- International --");
	}
	
}