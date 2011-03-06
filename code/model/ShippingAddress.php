<?php

/**
 * @description:
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class ShippingAddress extends DataObject {

	static $db = array(
		'ShippingName' => 'Text',
		'ShippingAddress' => 'Text',
		'ShippingAddress2' => 'Text',
		'ShippingCity' => 'Text',
		'ShippingPostalCode' => 'Varchar(30)',
		'ShippingState' => 'Varchar(30)',
		'ShippingCountry' => 'Varchar(200)',
		'ShippingPhone' => 'Varchar(200)'
	);

	static $has_one = array(
		"Order" => "Order"
	);

	static $indexes = array(
		// "SearchFields" => "fulltext (ShippingAddress, ShippingAddress2, ShippingCity, ShippingPostalCode, ShippingState, ShippingPhone)"
		array( 'name' => 'SearchFields', 'type' => 'fulltext', 'value' => 'ShippingAddress, ShippingAddress2, ShippingCity, ShippingPostalCode, ShippingState, ShippingPhone' )
	);

	public static $casting = array(
		"ShippingFullCountryName" => "Varchar"
	);

	public static $searchable_fields = array(
		'OrderID' => array(
			'field' => 'NumericField',
			'title' => 'Order Number'
		),
		"ShippingName" => "PartialMatchFilter",
		"ShippingAddress" => "PartialMatchFilter",
		"ShippingCity" => "PartialMatchFilter",
		"ShippingCountry" => "PartialMatchFilter"
	);

	public static $field_labels = array(
		'ShippingName' => 'Name',
		'ShippingAddress' => 'Address',
		'ShippingAddress2' => 'Address ',
		'ShippingCity' => 'City',
		'ShippingPostalCode' => 'Postal code',
		'ShippingState' => 'State',
		'ShippingCountry' => 'Country',
		'ShippingPhone' => 'Phone'
	);

	public static $summary_fields = array(
		"ShippingName" => "Name",
		"Order.ID"
	); //note no => for relational fields

	public static $singular_name = "Shipping Address";

	public static $plural_name = "Shipping Addresses";

	public static function get_shipping_fields() {
		$array = ShippingAddress::$db;
		$newArray = array();
		foreach($array as $key => $value) {
			$newArray[$key] = $key;
		}
		return $newArray;
	}

	function ShippingFullCountryName() {
		return EcommerceRole::find_country_title($this->ShippingCountry);
	}

	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->replaceField("OrderID", $fields->dataFieldByName("OrderID")->performReadonlyTransformation());
		return $fields;
	}

	function scaffoldSearchFields(){
		$fields = parent::scaffoldSearchFields();
		$fields->replaceField("OrderID", new NumericField("OrderID", "Order Number"));
		return $fields;
	}

	function makeShippingAddressFromMember($member = null) {
		if(!$member) {
			$member = Member::currentUser();
		}
		if($member) {
			$this->ShippingName = $member->Title;
			$this->ShippingAddress = $member->Address;
			$this->ShippingAddress2 = $member->AddressLine2;
			$this->ShippingCity = $member->City;
			$this->ShippingPostalCode = $member->PostalCode;
			$this->ShippingState = $member->State;
			$this->ShippingCountry = $member->Country;
			$this->ShippingPhone = $member->Phone;
		}
		return $this;
	}

	function populateDefaults() {
		parent::populateDefaults();
		$this->ShippingCountry = ShoppingCart::get_country();
	}

}
