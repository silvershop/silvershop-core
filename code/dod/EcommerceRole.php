<?php
/**
 * @description EcommerceRole provides customisations to the {@link Member}
 ** class specifically for this ecommerce module.
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class EcommerceRole extends DataObjectDecorator {


	protected static $fixed_country_code = '';
		static function set_fixed_country_code($v) {self::$fixed_country_code = $v;}
		static function get_fixed_country_code() {return self::$fixed_country_code;}
	//f.e. http://www.nzpost.co.nz/Cultures/en-NZ/OnlineTools/PostCodeFinder

	static function get_postal_code_url() {$sc = DataObject::get_one('SiteConfig'); if($sc) {return $sc->PostalCodeURL;}  }

	static function get_postal_code_label() {$sc = DataObject::get_one('SiteConfig'); if($sc) {return $sc->PostalCodeLabel;}  }

	protected static $customer_group_code = 'shop_customers';
		static function set_customer_group_code($v) {self::$customer_group_code = $v;}
		static function get_customer_group_code(){return self::$customer_group_code;}

	protected static $customer_group_name = "shop customers";
		static function set_customer_group_name($v) {self::$customer_group_name = $v;}
		static function get_customer_group_name() {return self::$customer_group_name;}

	protected static $customer_permission_code = "SHOP_CUSTOMER";
		static function set_customer_permission_code($v) {self::$customer_permission_code = $v;}
		static function get_customer_permission_code() {return self::$customer_permission_code;}

	function extraStatics() {
		return array(
			'db' => array(
				'Address' => 'Varchar(255)',
				'AddressLine2' => 'Varchar(255)',
				'City' => 'Varchar(100)',
				'PostalCode' => 'Varchar(30)',
				'State' => 'Varchar(100)',
				'Country' => 'Varchar(200)',
				'Phone' => 'Varchar(100)',
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

	protected static function add_members_to_customer_group() {
		$gp = DataObject::get_one("Group", "\"Title\" = '".self::get_customer_group_name()."'");
		if($gp) {
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
				$sort = "",
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

	function updateCMSFields(&$fields) {
		$fields->replaceField('Country', new DropdownField('Country', 'Country', Geoip::getCountryDropDown()));
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
		if(self::get_postal_code_url() != ''){
			$postalCodeField->setRightTitle('<a href="'.self::get_postal_code_url().'" id="PostalCodeLink">'.self::get_postal_code_label().'</a>');
		}
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
			new TextField('Phone', _t('EcommerceRole.PHONE','Phone')),
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


/*******************************************************
   * SHOP ADMIN
*******************************************************/


	protected static $admin_group_code = "shop_administrators";
		static function set_admin_group_code($v) {self::$admin_group_code = $v;}
		static function get_admin_group_code() {return self::$admin_group_code;}

	protected static $admin_group_name = "shop administrators";
		static function set_admin_group_name($v) {self::$admin_group_name = $v;}
		static function get_admin_group_name() {return self::$admin_group_name;}

	protected static $admin_permission_code = "SHOP_ADMIN";
		static function set_admin_permission_code($v) {self::$admin_permission_code = $v;}
		static function get_admin_permission_code() {return self::$admin_permission_code;}


	public function requireDefaultRecords() {
		parent::requireDefaultRecords();
		if(!$admin_group = DataObject::get_one("Group", "\"Code\" = '".self::get_admin_group_code()."'")) {
			$admin_group = new Group();
			$admin_group->Code = self::get_admin_group_code();
			$admin_group->Title = self::get_admin_group_name();
			$admin_group->write();
			Permission::grant( $admin_group->ID, self::get_admin_permission_code());
			DB::alteration_message(self::get_admin_group_name().' Group created',"created");
		}
		elseif(DB::query("SELECT * FROM \"Permission\" WHERE \"GroupID\" = '".$admin_group->ID."' AND \"Code\" LIKE '".self::get_admin_permission_code()."'")->numRecords() == 0 ) {
			Permission::grant($admin_group->ID, self::get_admin_permission_code());
			DB::alteration_message(self::get_admin_group_name().' permissions granted',"created");
		}
		if(!$customer_group = DataObject::get_one("Group", "\"Code\" = '".self::get_customer_group_code()."'")) {
			$customer_group = new Group();
			$customer_group->Code = self::get_customer_group_code();
			$customer_group->Title = self::get_customer_group_name();
			$customer_group->write();
			Permission::grant( $customer_group->ID, self::get_customer_permission_code());
			DB::alteration_message(self::get_customer_group_name().' Group created',"created");
		}
		elseif(DB::query("SELECT * FROM \"Permission\" WHERE \"GroupID\" = '".$customer_group->ID."' AND \"Code\" LIKE '".self::get_customer_permission_code()."'")->numRecords() == 0 ) {
			Permission::grant($customer_group->ID, self::get_customer_permission_code());
			DB::alteration_message(self::get_customer_group_name().' permissions granted',"created");
		}
	}

	function IsShopAdmin() {
		if($this->owner->IsAdmin()) {
			return true;
		}
		else{
			return Permission::checkMember($this->owner, self::get_admin_permission_code());
		}
	}


}



