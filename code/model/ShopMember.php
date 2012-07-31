<?php
/**
 * ShopMember provides customisations to {@link Member} for shop purposes
 *
 * @package shop
 */
class ShopMember extends DataObjectDecorator {

	protected static $group_name = "Customers";
		static function set_group_name($v) {self::$group_name = $v;}
		static function get_group_name(){return self::$group_name;}

	protected static $login_joins_cart = false;
	static function associate_to_current_order($join = true){self::$login_joins_cart = $join;}
	static function get_associate_to_current_order(){ return self::$login_joins_cart; }
	
	function extraStatics() {
		return array(
			'has_many' => array(
				'AddressBook' => 'Address'
			),
			'has_one' => array(
				'DefaultShippingAddress' => 'Address',
				'DefaultBillingAddress' => 'Address'		
			)
		);
	}

	/**
	 * Link the current order to the current member, if there is one.
	 */
	function memberLoggedIn(){
		if(self::$login_joins_cart && $order = ShoppingCart::singleton()->current()){
			$order->MemberID = $this->owner->ID;
			$order->write();
		}
	}

	/**
	 * Clear the cart, and session variables.
	 */
	function memberLoggedOut(){
		ShoppingCart::singleton()->clear();
		OrderManipulation::clear_session_order_ids();
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

	static function find_country() {
		$member = Member::currentUser();
		if($member && $member->Country) {
			$country = $member->Country;
		} else {
			if($country = ShoppingCart::get_country())
				return $country;
			// HACK Avoid CLI tests from breaking (GeoIP gets in the way of unbiased tests!)
			// @todo Introduce a better way of disabling GeoIP as needed (Geoip::disable() ?)
			if(Director::is_cli()) {
				$country = null;
			} else {
				$country = Geoip::visitor_country();
			}
		}
		return $country;
	}
	
	public static function ecommerce_create_or_merge($data) {
		// Because we are using a ConfirmedPasswordField, the password will be an array of two fields
		if(isset($data['Password']) && is_array($data['Password'])) {
			$data['Password'] = $data['Password']['_Password'];
		}
		// We need to ensure that the unique field is never overwritten
		$uniqueField = Member::get_unique_identifier_field();
		if(isset($data[$uniqueField])) {
			$SQL_unique = Convert::raw2xml($data[$uniqueField]);
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
	
	function updateMemberFormFields($fields){
		$fields->removeByName('DefaultShippingAddressID');
		$fields->removeByName('DefaultBillingAddressID');
		if($gender=$fields->fieldByName('Gender')){
			$gender->setHasEmptyDefault(true);
		}
		
	}
	
	function getPastOrders($extrafilter = null){
		$filter = "\"MemberID\" = ".(int)$this->owner->ID;
		$statusFilter = " AND \"Order\".\"Status\" NOT IN('". implode("','", Order::$hidden_status) ."')";
		$statusFilter .= ($extrafilter) ? " AND $extrafilter" : "";
		return DataObject::get('Order',$filter.$statusFilter);
	}

	function CountryTitle() {
		return self::find_country_title($this->owner->Country);
	}	
	
	//deprecated functions
	
	/**
	 * @deprecated - use CustomersToGroupTask
	 */
	static function add_members_to_customer_group() {}
	
	/**
	 * @deprecated
	 * @param unknown_type $code
	 */
	static function findCountryTitle($code) {
		user_error("deprecated, please use ShopMember::find_country_title", E_USER_NOTICE);
		return self::find_country_title($code);
	}
	/**
	 * Find the member's country.
	 *
	 * If there is no member logged in, try to resolve
	 * their IP address to a country.
	 * @deprecated
	 * @return string Found country of member
	 */
	static function findCountry() {
		user_error("deprecated, please use ShopMember::find_country", E_USER_NOTICE);
		return self::find_country();
	}
	
	/**
	 * Create a new member with given data for a new member,
	 * or merge the data into the logged in member.
	 *
	 * IMPORTANT: Before creating a new Member record, we first
	 * check that the request email address doesn't already exist.
	 *
	 * @deprecated
	 * @param array $data Form request data to update the member with
	 * @return boolean|object Member object or boolean FALSE
	 */
	public static function createOrMerge($data) {
		user_error("deprecated, please use ShopMember::ecommerce_create_or_merge", E_USER_NOTICE);
		return self::ecommerce_create_or_merge($data);
	}

}

/**
 * @deprecated use ShopMember
 */
class EcommerceRole extends ShopMember{}