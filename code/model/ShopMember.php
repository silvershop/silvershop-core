<?php
/**
 * ShopMember provides customisations to {@link Member} for shop purposes
 *
 * @package shop
 */
class ShopMember extends DataExtension {

	private static $login_joins_cart = true;

	private static $has_many = array(
		'AddressBook' => 'Address'
	);

	private static $has_one = array(
		'DefaultShippingAddress' => 'Address',
		'DefaultBillingAddress' => 'Address'
	);

	/**
	 * Get member by unique field.
	 * @return Member|null
	 */
	public static function get_by_identifier($idvalue) {
		return Member::get()->filter(Member::get_unique_identifier_field(), $idvalue)->first();
	}

	/**
	 * Create new member with data, or merge data with existing.
	 * @param  array $data data to create or merge with
	 * @return Member|false the newly created, or existing member
	 */
	public static function create_or_merge($data) {
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
	public static function find_country_title($code) {
		$countries = SiteConfig::current_site_config()->getCountriesList();
		return ($code && $countries[$code]) ?  $countries[$code] : false;
	}

	public function updateCMSFields(FieldList $fields) {
		$fields->removeByName('Country');
		$fields->removeByName("DefaultShippingAddressID");
		$fields->removeByName("DefaultBillingAddressID");
		$fields->addFieldToTab('Root.Main',
			new DropdownField('Country', 'Country',
				SiteConfig::current_site_config()->getCountriesList()
			)
		);
	}

	public function updateMemberFormFields($fields) {
		$fields->removeByName('DefaultShippingAddressID');
		$fields->removeByName('DefaultBillingAddressID');
		if($gender=$fields->fieldByName('Gender')){
			$gender->setHasEmptyDefault(true);
		}
	}

	/**
	 * Link the current order to the current member on login,
	 * if there is one, and if configuration is set to do so.
	 */
	public function memberLoggedIn() {
		if(Member::config()->login_joins_cart && $order = ShoppingCart::singleton()->current()){
			$order->MemberID = $this->owner->ID;
			$order->write();
		}
	}

	/**
	 * Clear the cart, and session variables on member logout
	 */
	public function memberLoggedOut() {
		ShoppingCart::singleton()->clear();
		OrderManipulation::clear_session_order_ids();
	}

	public function getPastOrders() {
		return Order::get()
				->filter("MemberID", $this->owner->ID)
				->filter("Status:not", Order::config()->hidden_status);
	}

}
