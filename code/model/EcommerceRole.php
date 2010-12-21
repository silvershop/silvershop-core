<?php
/**
 * EcommerceRole provides customisations to the {@link Member}
 * class specifically for this ecommerce module.
 *
 * @package ecommerce
 */
class EcommerceRole extends DataObjectDecorator {


	protected static $fixed_country_code = '';
		static function set_fixed_country_code($v) {self::$fixed_country_code = $v;}
		static function get_fixed_country_code() {return self::$fixed_country_code;}

	protected static $postal_code_url = "http://www.nzpost.co.nz/Cultures/en-NZ/OnlineTools/PostCodeFinder";
		static function get_postal_code_url() {$sc = DataObject::get_one("SiteConfig"); if($sc) {return $sc->PostalCodeURL;} else {return self::$postal_code_url;} }
		static function set_postal_code_url($v) {self::$postal_code_url = $v;}

	protected static $postal_code_label = "find postcode";
		static function get_postal_code_label() {$sc = DataObject::get_one("SiteConfig"); if($sc) {return $sc->PostalCodeLabel;} else {return self::$postal_code_label;} }
		static function set_postal_code_label($v) {self::$postal_code_label = $v;}

	protected static $group_name = "Shop Customers";
		static function set_group_name($v) {self::$group_name = $v;}
		static function get_group_name(){return self::$group_name;}

	function extraStatics() {
		return array(
			'db' => array(
				'Address' => 'Varchar(255)',
				'AddressLine2' => 'Varchar(255)',
				'City' => 'Varchar(100)',
				'PostalCode' => 'Varchar(30)',
				'State' => 'Varchar(100)',
				'Country' => 'Varchar',
				'HomePhone' => 'Varchar(100)',
				'MobilePhone' => 'Varchar(100)',
				'Notes' => 'HTMLText'
			)
		);
	}

	static function findCountryTitle($code) {
		user_error("depreciated, please use EcommerceRole::find_country_title", E_USER_NOTICE);
		return self::find_country_title($code);
	}

	static function find_country_title($code) {
		$countries = Geoip::getCountryDropDown();
		// check if code was provided, and is found in the country array
		if($code && $countries[$code]) {
			return $countries[$code];
		} else {
			return false;
		}
	}

	/**
	 * Find the member's country.
	 *
	 * If there is no member logged in, try to resolve
	 * their IP address to a country.
	 *
	 * @return string Found country of member
	 */
	static function findCountry() {
		user_error("depreciated, please use EcommerceRole::find_country", E_USER_NOTICE);
		return self::find_country();
	}
	static function find_country() {
		$country = '';
		$member = Member::currentUser();
		if($member && $member->Country) {
			$country = $member->Country;
		}
		else {
			if($country = ShoppingCart::get_country()) {
				//do nothing
			}
			elseif($country = self::get_fixed_country_code()) {
				//do nothing
			}
			else {
				// HACK Avoid CLI tests from breaking (GeoIP gets in the way of unbiased tests!)
				// @todo Introduce a better way of disabling GeoIP as needed (Geoip::disable() ?)
				if(Director::is_cli()) {
					$country = '';
				}
				else {
					$country = Geoip::visitor_country();
				}
			}
		}
		return $country;
	}

	static function add_members_to_customer_group() {
		$gp = DataObject::get_one("Group", "\"Title\" = '".self::get_group_name()."'");
		if(!$gp) {
			$gp = new Group();
			$gp->Title = self::get_group_name();
			$gp->Sort = 999998;
			$gp->write();
		}
		$allCombos = DB::query("
			SELECT \"Group_Members\".\"ID\", \"Group_Members\".\"MemberID\", \"Group_Members\".\"GroupID\"
			FROM \"Group_Members\"
			WHERE \"Group_Members\".\"GroupID\" = ".$gp->ID.";"
		);
		//make an array of all combos
		$alreadyAdded = array();
		$alreadyAdded[-1] = -1;
		if($allCombos) {
			foreach($allCombos as $combo) {
				$alreadyAdded[$combo["MemberID"]] = $combo["MemberID"];
			}
		}
		$unlistedMembers = DataObject::get(
			"Member",
			$where = "\"Member\".\"ID\" NOT IN (".implode(",",$alreadyAdded).")",
			$sort = null,
			$join = "INNER JOIN \"Order\" ON \"Order\".\"MemberID\" = \"Member\".\"ID\""
		);

		//add combos
		if($unlistedMembers) {
			$existingMembers = $gp->Members();
			foreach($unlistedMembers as $member) {
				$existingMembers->add($member);
			}
		}
	}

	/**
	 * Create a new member with given data for a new member,
	 * or merge the data into the logged in member.
	 *
	 * IMPORTANT: Before creating a new Member record, we first
	 * check that the request email address doesn't already exist.
	 *
	 * @param array $data Form request data to update the member with
	 * @return boolean|object Member object or boolean FALSE
	 */
	public static function createOrMerge($data) {
		user_error("depreciated, please use EcommerceRole::ecommerce_create_or_merge", E_USER_NOTICE);
		return self::ecommerce_create_or_merge($data);

	}
	public static function ecommerce_create_or_merge($data) {
		// Because we are using a ConfirmedPasswordField, the password will
		// be an array of two fields
		if(isset($data['Password']) && is_array($data['Password'])) {
			$data['Password'] = $data['Password']['_Password'];
		}
		// We need to ensure that the unique field is never overwritten
		$uniqueField = Member::get_unique_identifier_field();
		if(isset($data[$uniqueField])) {
			$SQL_unique = Convert::raw2xml($data[$uniqueField]);
			// TODO review - should $uniqueField be quoted by Member::get_unique_identifier_field() already? (this would be sapphire bug)
			$existingUniqueMember = DataObject::get_one('Member', "\"$uniqueField\" = '{$SQL_unique}'");
			if($existingUniqueMember && $existingUniqueMember->exists()) {
				if(Member::currentUserID() != $existingUniqueMember->ID) {
					return false;
				}
			}
		}
		if(!$member = Member::currentUser()) {
			$member = new Member();
		}
		$member->update($data);
		return $member;
	}

	function updateCMSFields($fields) {
		$fields->removeByName('Country');
		$fields->addFieldToTab('Root.Main', new DropdownField('Country', 'Country', Geoip::getCountryDropDown()));
	}

	/**
	 * Give the two letter code to resolve the title of the country.
	 *
	 * @param string $code Country code
	 * @return string|boolean String if country found, boolean FALSE if nothing found
	 */
	function getEcommerceFields() {
		//postal code
		$postalCodeField = new TextField('PostalCode', _t('EcommerceRole.POSTALCODE','Postal Code'));
		$postalCodeField->setRightTitle('<a href="'.self::$postal_code_url.'" id="PostalCodeLink">'.self::$postal_code_label.'</a>');
		// country
		$countryField = new DropdownField('Country', _t('EcommerceRole.COUNTRY','Country'), Geoip::getCountryDropDown(), self::find_country());
		$countryField->addExtraClass('ajaxCountryField');
		//link used to update the country via Ajax
		$setCountryLinkID = $countryField->id() . '_SetCountryLink';
		$countryAJAXLink = new HiddenField($setCountryLinkID, '', ShoppingCart::get_country_link());
		$fields = new FieldSet(
			new HeaderField(_t('EcommerceRole.PERSONALINFORMATION','Personal Information'), 3),
			new TextField('FirstName', _t('EcommerceRole.FIRSTNAME','First Name')),
			new TextField('Surname', _t('EcommerceRole.SURNAME','Surname')),
			new TextField('HomePhone', _t('EcommerceRole.HOMEPHONE','Phone')),
			new TextField('MobilePhone', _t('EcommerceRole.MOBILEPHONE','Mobile')),
			new EmailField('Email', _t('EcommerceRole.EMAIL','Email')),
			new TextField('Address', _t('EcommerceRole.ADDRESS','Address')),
			new TextField('AddressLine2', _t('EcommerceRole.ADDRESSLINE2','&nbsp;')),
			new TextField('City', _t('EcommerceRole.CITY','City')),
			$postalCodeField,
			$countryField,
			$countryAJAXLink
		);
		$this->owner->extend('augmentEcommerceFields', $fields);
		return $fields;
	}

	/**
	 * Return which member fields should be required on {@link OrderForm}
	 * and {@link ShopAccountForm}.
	 *
	 * @return array
	 */
	function getEcommerceRequiredFields() {
		$fields = array(
			'FirstName',
			'Surname',
			'Email',
			'Address',
			'City',
			'Country'
		);
		$this->owner->extend('augmentEcommerceRequiredFields', $fields);
		return $fields;
	}

	public function CountryTitle() {
		return self::find_country_title($this->owner->Country);
	}

	//this method needs to be tested!
	public function onAfterWrite() {
		parent::onAfterWrite();
		self::add_members_to_customer_group();
	}


}
