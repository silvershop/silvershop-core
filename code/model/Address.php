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

	private static $required_fields = array(
		'Country',
		'State',
		'City',
		'Address'
	);

	function getFrontEndFields($params = null){
		$fields = new FieldList(
			$this->getCountryField(),
			$addressfield = TextField::create('Address', _t('Address.ADDRESS','Address')),
			$address2field = TextField::create('AddressLine2', _t('Address.ADDRESSLINE2','&nbsp;')),
			$cityfield = TextField::create('City', _t('Address.CITY','City')),
			$statefield = TextField::create('State', _t('Address.STATE','State')),
			$postcodefield = TextField::create('PostalCode', _t('Address.POSTALCODE','Postal Code')),
			$phonefield = TextField::create('Phone', _t('Address.PHONE','Phone Number'))
		);
		if(isset($params['addfielddescriptions']) && !empty($params['addfielddescriptions'])){
			$addressfield->setDescription(_t("Address.ADDRESSHINT","street / thoroughfare number, name, and type or P.O. Box"));
			$address2field->setDescription(_t("Address.ADDRESS2HINT","premises, building, apartment, unit, floor"));
			$cityfield->setDescription(_t("Address.CITYHINT","or suburb, county, district"));
			$statefield->setDescription(_t("Address.STATEHINT","or province, territory, island"));
		}

		$this->extend('updateFormFields', $fields);
		return $fields;
	}

	function getCountryField(){
		$countries = SiteConfig::current_site_config()->getCountriesList();
		$countryfield = new ReadonlyField("Country",_t('Address.COUNTRY','Country'));
		if(count($countries) > 1){
			$countryfield = new DropdownField("Country",_t('Address.COUNTRY','Country'), $countries);
			$countryfield->setHasEmptyDefault(true);
		}
		return $countryfield;
	}
	
	/**
	 * Get an array of data fields that must be populated for model to be valid.
	 * Required fields can be customised via self::$required_fields
	 */
	function getRequiredFields(){
		$fields = $this->config()->required_fields;
		$this->extend('updateRequiredFields', $fields);
		return $fields;
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
		$fields = array(
			$this->Address,
			$this->AddressLine2,
			$this->City,
			$this->State,
			$this->PostalCode,
			$this->Country
		);
		$this->extend('updateToString',$fields);
		return implode($separator,array_filter($fields));
	}

	function getTitle(){
		return $this->toString();
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