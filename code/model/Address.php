<?php
/**
 * Address model using a generic format for storing international addresses.
 * 
 * Typical Address Hierarcy:
 * 	Continent
 * 	Country
 * 	State / Province / Territory (Island?)
 * 	District / Suburb / County / City
 *		Code / Zip (may cross over the above)
 * 	Street / Road - name + type: eg Gandalf Cresent
 * 	(Premises/Building/Unit/Suite)
 * 		(Floor/Level/Side/Wing)
 * 	Number / Entrance / Room
 * 	Person(s), Company, Department
 *
 * Collection of international address formats:
 * @see http://bitboost.com/ref/international-address-formats.html
 * xAL address standard:
 * @see https://www.oasis-open.org/committees/ciq/ciq.html#6
 * Universal Postal Union addressing standards:
 * @see http://www.upu.int/nc/en/activities/addressing/standards.html
 */
class Address extends DataObject{

	static $db = array(
		'Country'		=> 'ShopCountry',  //level1: Country = ISO 2-character country code
		'State'			=> 'Varchar(100)', //level2: Locality, Administrative Area, State, Province, Region, Territory, Island
		'City'			=> 'Varchar(100)', //level3: Dependent Locality, City, Suburb, County, District
		'PostalCode' 	=> 'Varchar(20)',  //code: ZipCode, PostCode (could cross above levels within a country)
		
		'Address'		=> 'Varchar(255)', //Number + type of thoroughfare/street. P.O. box
		'AddressLine2'	=> 'Varchar(255)', //Premises, Apartment, Building. Suite, Unit, Floor, Level, Side, Wing.

		'Latitude'		=> 'Float(10,6)',  //GPS co-ordinates
		'Longitude'		=> 'Float(10,6)',
		
		'Company'		=> 'Varchar(100)', //Business, Organisation, Group, Institution. 
		
		'FirstName'		=> 'Varchar(100)', //Individual, Person, Contact, Attention
		'Surname'		=> 'Varchar(100)',
		'Phone'			=> 'Varchar(100)',
	);
	
	static $has_one = array(
		'Member' => 'Member'		
	);
	
	static $casting = array(
		'Country' => 'ShopCountry'	
	);
	
	static $required_fields = array(
		'Address',
		'City',
		'State',
		'Country'
	);
	
	/**
	 * Tub-titles for address fields that describe what they are for
	 * @var boolean
	 */
	static $show_form_hints = false;
	
	/**
	 * @todo: customise format and labels, based on passed locale
	 * @param unknown_type $nameprefix
	 */
	function getFormFields($nameprefix = ""){
		$fields = new FieldList(
			$this->getCountryField($nameprefix),
			$addressfield = TextField::create($nameprefix.'Address', _t('Address.ADDRESS','Address')),
			$address2field = TextField::create($nameprefix.'AddressLine2', _t('Address.ADDRESSLINE2','&nbsp;')),
			$cityfield = TextField::create($nameprefix.'City', _t('Address.CITY','City')),
			$statefield = TextField::create($nameprefix.'State', _t('Address.STATE','State')),
			$postcodefield = TextField::create($nameprefix.'PostalCode', _t('Address.POSTALCODE','Postal Code')),
			$phonefield = TextField::create($nameprefix.'Phone', _t('Address.PHONE','Phone Number'))
		);		
		if(self::$show_form_hints){
			$addressfield->setDescription(_t("Address.ADDRESSHINT","street / thoroughfare number, name, and type or P.O. Box"));
			$address2field->setDescription(_t("Address.ADDRESS2HINT","premises, building, apartment, unit, floor"));
			$cityfield->setDescription(_t("Address.CITYHINT","or suburb, county, district"));
			$statefield->setDescription(_t("Address.STATEHINT","or province, territory, island"));
		}
		$this->extend('updateFormFields',$fields,$nameprefix);
		return $fields;
	}

	function getCountryField($nameprefix = ""){
		$countries = SiteConfig::current_site_config()->getCountriesList();
		$countryfield = new ReadonlyField($nameprefix."Country",_t('Address.COUNTRY','Country'));
		if(count($countries) > 1){
			$countryfield = new DropdownField($nameprefix."Country",_t('Address.COUNTRY','Country'), $countries);
			$countryfield->setHasEmptyDefault(true);
		}
		return $countryfield;
	}
	
	/**
	 * Get an array of fields that must be populated in a form.
	 * Required fields can be customised via self::$required_fields
	 */
	function getRequiredFields($nameprefix = ""){
		$fields = array();
		foreach(self::$required_fields as $field){
			$fields[] = $nameprefix.$field;
		}
		$this->extend('updateRequiredFields',$fields,$nameprefix);
		return $fields;
	}
	
	/**
	 * Produces a map of prefixed field names to fields names.
	 * Sourced from the form field's saveable fields.
	 * @param string $prefix prefix each key fieldname with a string
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
	 * Get data for form out, with prefix, if desired.
	 * @param  string $prefix prefix each field key with a string.
	 * @return array         map of prefixed fieldnames to values
	 */
	function getMappedData($prefix = ''){
		$data = array();
		foreach($this->getFieldMap($prefix) as $prefixed => $field){
			if(isset($this->record[$field])){
				$data[$prefixed] = $this->record[$field];
			}
		}
		return $data;
	}

	/**
	 * Get full name associated with this Address
	 */
	function getName(){
		return implode('',array_filter(array(
			$this->FirstName,
			$this->Surname
		)));
	}
	
	/**
	 * Convert address to a single string.
	 */
	function toString($separator = ", "){
		$fields = $this->getMappedData();
		$this->extend('updateToString',$fields);
		return implode($separator,array_filter($fields));
	}
	
	function forTemplate(){
		return $this->renderWith('Address');
	}
	
	/**
	 * Add alias setters for fields which are synonymous
	 */
	function setProvince($val){$this->State = $val;}
	function setTerritory($val){$this->State = $val;}
	function setIsland($val){$this->State = $val;}
	function setPostCode($val){$this->PostalCode = $val;}
	function setZipCode($val){$this->PostalCode = $val;}
	function setStreet($val){$this->Address = $val;}
	function setStreet2($val){$this->AddressLine2 = $val;}
	function setAddress2($val){$this->AddressLine2 = $val;}
	function setInstitution($val){$this->Company = $val;}
	function setBusiness($val){$this->Company = $val;}
	function setOrganisation($val){$this->Company = $val;}
	function setOrganization($val){$this->Company = $val;}
	
}