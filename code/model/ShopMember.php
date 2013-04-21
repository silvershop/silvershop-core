<?php
/**
 * ShopMember provides customisations to {@link Member} for shop purposes
 *
 * @package shop
 */
class ShopMember extends DataExtension {

	static $login_joins_cart = false;
	static function associate_to_current_order($join = true){self::$login_joins_cart = $join;}
	static function get_associate_to_current_order(){ return self::$login_joins_cart; }
	
	static $has_many = array(
		'AddressBook' => 'Address'
	);
	
	static $has_one = array(
		'DefaultShippingAddress' => 'Address',
		'DefaultBillingAddress' => 'Address'
	);
	
	
	function updateCMSFields(FieldList $fields){
		$fields->removeByName('Country');
		$fields->removeByName("DefaultShippingAddressID");
		$fields->removeByName("DefaultBillingAddressID");
		$fields->addFieldToTab('Root.Main', new DropdownField('Country', 'Country', SiteConfig::current_site_config()->getCountriesList()));
	}
	
	function updateMemberFormFields($fields){
		$fields->removeByName('DefaultShippingAddressID');
		$fields->removeByName('DefaultBillingAddressID');
		if($gender=$fields->fieldByName('Gender')){
			$gender->setHasEmptyDefault(true);
		}
	}
	
	/**
	 * Get member by unique field.
	 * @return Member|null
	 */
	static function get_by_identifier($value){
		$uniqueField = Member::get_unique_identifier_field();
		return DataObject::get_one('Member', "\"$uniqueField\" = '{$value}'");
	}
	
	static function create_or_merge($data){
		if(!isset($data[Member::get_unique_identifier_field()]) || empty($data[Member::get_unique_identifier_field()])){
			return false;	
		}
		$existingmember = self::get_by_identifier($data[Member::get_unique_identifier_field()]);
		if($existingmember && $existingmember->exists()){
			if(Member::currentUserID() != $existingmember->ID) {
				return false;
			}
		}
		if(!$member = Member::currentUser()) {
			$member = new Member();
		}
		$member->update($data);
		return $member;
	}
		
	/**
	 * Get country title by iso country code.
	 */
	static function find_country_title($code) {
		$countries = SiteConfig::current_site_config()->getCountriesList();
		// check if code was provided, and is found in the country array
		if($code && $countries[$code]) {
			return $countries[$code];
		} else {
			return false;
		}
	}
	
	/**
	 * Find country that member is from.
	 */
	static function find_country() {
		$member = Member::currentUser();
		if($member && $member->Country) {
			$country = $member->Country;
		} else {
			if($country = ShoppingCart::get_country()){
				return $country;
			}
		}
		return $country;
	}

	/**
	 * Link the current order to the current member on login,
	 * if there is one, and if configuration is set to do so.
	 */
	function memberLoggedIn(){
		if(self::$login_joins_cart && $order = ShoppingCart::singleton()->current()){
			$order->MemberID = $this->owner->ID;
			$order->write();
		}
	}
	
	/**
	 * Clear the cart, and session variables on member logout
	 */
	function memberLoggedOut(){
		ShoppingCart::singleton()->clear();
		OrderManipulation::clear_session_order_ids();
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
	 * @deprected - use ShopConfig customer group instead
	 */
	static function set_group_name($v){}
	
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
		user_error("deprecated, please use ShopMember::create_or_merge", E_USER_NOTICE);
		return self::ecommerce_create_or_merge($data);
	}
	
	static function ecommerce_create_or_merge(){
		user_error("deprecated, please use ShopMember::create_or_merge", E_USER_NOTICE);
		return self::create_or_merge($data);
	}

}

/**
 * @deprecated use ShopMember
 */
class EcommerceRole extends ShopMember{}