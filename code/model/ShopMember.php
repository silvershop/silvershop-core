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
		return $member && $member->Country ? $member->Country : null;
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

}