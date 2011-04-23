<?php
/**
 * @description EcommerceRole provides customisations to the {@link Member}
 ** class specifically for this ecommerce module.
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class EcommerceRole extends DataObjectDecorator {

	function extraStatics() {
		return array(
			'db' => array(
				'Address' => 'Varchar(255)',
				'AddressLine2' => 'Varchar(255)',
				'City' => 'Varchar(100)',
				'PostalCode' => 'Varchar(30)',
				'State' => 'Varchar(100)',
				'Country' => 'Varchar(4)',
				'Phone' => 'Varchar(100)'
			),
			'casting' => array(
				"FullCountryName" => "Varchar"
			),
			'has_one' => array(
				'ShippingAddress' => 'ShippingAddress',
			)
		);
	}


	/**
	*@param $code = string
	**/
	protected static $fixed_country_code = '';
		static function set_fixed_country_code($s) {self::$fixed_country_code = $s;}
		static function get_fixed_country_code() {return self::$fixed_country_code;}

	/**
	*@param $a : array("NZ" => "NZ", "UK => "UK", etc...)
	**/
	protected static $allowed_country_codes = array();
		static function set_allowed_country_codes($a) {self::$allowed_country_codes = $a;}
		static function get_allowed_country_codes() {return self::$allowed_country_codes;}
		static function add_allowed_country_code($code) {self::$allowed_country_codes[$code] = $code;}
		static function remove_allowed_country_code($code) {unset(self::$allowed_country_codes[$code]);}


	/**
	*these variables and methods allow to to "dynamically limit the countries available, based on, for example: ordermodifiers, item selection, etc....
	* for example, if a person chooses delivery within Australasia (with modifier) - then you can limit the countries available to "Australasian" countries
	* @param $a = array should be country codes.e.g array("NZ", "NP", "AU");
	**/
	protected static $for_current_order_only_show_countries = array();
		static function set_for_current_order_only_show_countries($a) {
			if(count(self::$for_current_order_only_show_countries)) {
				self::$for_current_order_only_show_countries = array_intersect($a, self::$for_current_order_only_show_countries);
			}
			else {
				self::$for_current_order_only_show_countries = $a;
			}
		}
		static function get_for_current_order_only_show_countries() {return self::$for_current_order_only_show_countries;}

	protected static $for_current_order_do_not_show_countries = array();
		static function set_for_current_order_do_not_show_countries($a) {
			self::$for_current_order_do_not_show_countries = array_merge($a, self::$for_current_order_do_not_show_countries);
		}
		static function get_for_current_order_do_not_show_countries() {return self::$for_current_order_do_not_show_countries;}


	//e.g. http://www.nzpost.co.nz/Cultures/en-NZ/OnlineTools/PostCodeFinder
	static function get_postal_code_url() {$sc = DataObject::get_one('SiteConfig'); if($sc) {return $sc->PostalCodeURL;}  }

	static function get_postal_code_label() {$sc = DataObject::get_one('SiteConfig'); if($sc) {return $sc->PostalCodeLabel;}  }

	protected static $customer_group_code = 'shop_customers';
		static function set_customer_group_code($v) {self::$customer_group_code = $v;}
		static function get_customer_group_code() {return self::$customer_group_code;}

	protected static $customer_group_name = "shop customers";
		static function set_customer_group_name($v) {self::$customer_group_name = $v;}
		static function get_customer_group_name() {return self::$customer_group_name;}

	protected static $customer_permission_code = "SHOP_CUSTOMER";
		static function set_customer_permission_code($v) {self::$customer_permission_code = $v;}
		static function get_customer_permission_code() {return self::$customer_permission_code;}

	public function get_customer_group() {
		return DataObject::get_one("Group", "\"Code\" = '".self::get_customer_group_code()."' OR \"Title\" = '".self::get_customer_group_name()."'");
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

	static function findCountryTitle($code) {
		user_error("depreciated, please use ShoppingCart::get_country", E_USER_NOTICE);
		return self::find_country_title($code);
	}

	public static function country_code_allowed($code) {
		if($code) {
			$c = self::get_fixed_country_code();
			if($c) {
				if($c == $code) {
					return true;
				}
			}
			else {
				$a = self::get_allowed_country_codes();
				if(is_array($a) && count($a)) {
					if(in_array($code, $a, false) || array_key_exists($code, $a)) {
						return true;
					}
				}
				else {
					$a = Geoip::getCountryDropDown();
					if(isset($a[$code])) {
						return true;
					}
				}
			}
		}
		return false;
	}


	public static function find_country_title($code) {
		$countries = Geoip::getCountryDropDown();
		// check if code was provided, and is found in the country array
		if($code && isset($countries[$code])) {
			return $countries[$code];
		}
		else {
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
		user_error("depreciated, please use ShoppingCart::get_country", E_USER_NOTICE);
		return ShoppingCart::get_country();
	}

	//this function will be depreciated soon....
	public static function find_country() {
		user_error("depreciated, please use ShoppingCart::get_country", E_USER_NOTICE);
		return ShoppingCart::get_country();
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

	public function FullCountryName() {
		return self::find_country_title($this->owner->Country);
	}

	function updateCMSFields(&$fields) {
		$fields->replaceField('Country', new DropdownField('Country', 'Country', Geoip::getCountryDropDown()));
	}



	public static function list_of_allowed_countries_for_dropdown() {
		$keys = array();
		$allowedCountryCode = self::get_fixed_country_code();
		$allowedCountryCodeArray = self::get_allowed_country_codes();
		if($allowedCountryCode) {
			$keys[$allowedCountryCode] = $allowedCountryCode;
		}
		elseif($allowedCountryCodeArray && count($allowedCountryCodeArray)) {
			$keys = array_merge($keys, $allowedCountryCodeArray);
		}
		if(isset($keys) && count($keys)) {
			$newArray = array();
			foreach($keys as $key) {
				$codeTitleArray[$key] = self::find_country_title($key);
			}
		}
		else {
			$codeTitleArray = Geoip::getCountryDropDown();
		}
		$onlyShow = self::get_for_current_order_only_show_countries();
		$doNotShow = self::get_for_current_order_do_not_show_countries();
		if(is_array($onlyShow) && count($onlyShow)) {
			foreach($codeTitleArray as $key => $value) {
				if(!in_array($key, $onlyShow)) {
					unset($codeTitleArray[$key]);
				}
			}
		}
		if(is_array($doNotShow) && count($doNotShow)) {
			foreach($doNotShow as $countryCode) {
				unset($codeTitleArray[$countryCode]);
			}
		}
		return $codeTitleArray;
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
		if(self::get_postal_code_url()){
			$postalCodeField->setRightTitle('<a href="'.self::get_postal_code_url().'" id="PostalCodeLink">'.self::get_postal_code_label().'</a>');
		}
		// country
		$countriesForDropdown = EcommerceRole::list_of_allowed_countries_for_dropdown();
		$countryField = new DropdownField('Country',  _t('Order.COUNTRY','Country'), $countriesForDropdown, ShoppingCart::get_country());
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

	function populateDefaults() {
		parent::populateDefaults();
		$this->Country = ShoppingCart::get_country();
	}

}



