<?php
/**
 * Address model
 */
class Address extends DataObject{
	
	static $db = array(
		'Address' => 'Varchar(255)',		//Street address, P.O. box, company name, c/o
		'AddressLine2' => 'Varchar(255)',	//Apartment, suite, unit, building, floor, etc
		'City' => 'Varchar(100)',
		'PostalCode' => 'Varchar(30)',
		'State' => 'Varchar(100)',
		'Country' => 'Varchar',
		'FirstName' => 'Varchar(100)',
		'Surname' => 'Varchar(100)',
		'Phone' => 'Varchar(100)',
		'Latitude' => 'Float(10,6)',
		'Longitude' => 'Float(10,6)'
	);
	
	static $has_one = array(
		'Member' => 'Member'		
	);
	
	/**
	 * @todo: customise format and labels, based on passed locale
	 * @param unknown_type $nameprefix
	 */
	function getFormFields($nameprefix = ""){
		$countries = SiteConfig::current_site_config()->getCountriesList();
		$countryfield = (count($countries)) ? new DropdownField($nameprefix."Country",_t('Address.COUNTRY','Country'),$countries) : new ReadonlyField("Country",_t('Address.COUNTRY','Country'));
		$fields = new FieldSet(
			$countryfield,
			new TextField($nameprefix.'Address', _t('Address.ADDRESS','Address')),
			new TextField($nameprefix.'AddressLine2', _t('Address.ADDRESSLINE2','&nbsp;')),
			new TextField($nameprefix.'City', _t('Address.CITY','City')),
			new TextField($nameprefix.'State', _t('Address.STATE','State')),
			new TextField($nameprefix.'PostalCode', _t('Address.POSTALCODE','Postal Code')),
			new TextField($nameprefix.'Phone', _t('Address.PHONE','Phone Number'))
		);
		$this->extend('updateFormFields',$fields,$nameprefix);
		return $fields;
	}
	
	function getRequiredFields($nameprefix = ""){
		$fields = array(
			$nameprefix.'Address',
			$nameprefix.'City',
			$nameprefix.'State',
			$nameprefix.'Country'
		);
		$this->extend('updateRequiredFields',$fields,$nameprefix);
		return $fields;
	}
	
	/**
	 * Produces a map for loading/saving form fields.
	 */
	function getFieldMap($prefix = ''){
		$map = $this->getFormFields()->saveableFields();
		foreach($map as $key => $value){
			$map[$prefix.$key] = $key;
			unset($map[$key]);
		}
		return $map;
	}
	
	/**
	 * Convert address to a single string.
	 */
	function toString($separator = ", "){
		$fields = array(
			$this->FirstName,
			$this->Surname,
			$this->Address,
			$this->AddressLine2,
			$this->City,
			$this->PostalCode,
			$this->State,
			$this->Country,
			$this->Phone
		);
		$this->extend('updateToString',$fields);
		return implode($separator,array_filter($fields));
	}

	function forTemplate(){
		return $this->renderWith('Address');
	}
	
}