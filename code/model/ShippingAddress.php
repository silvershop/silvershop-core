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
		'ShippingCountry' => 'Varchar(4)',
		'ShippingPhone' => 'Varchar(200)'
	);

	static $has_one = array(
		"Order" => "Order"
	);

	static $indexes = array(
		// "SearchFields" => "fulltext (ShippingAddress, ShippingAddress2, ShippingCity, ShippingPostalCode, ShippingState, ShippingPhone)"
		array(
			'name' => 'SearchFields',
			'type' => 'fulltext',
			'value' => 'ShippingAddress, ShippingAddress2, ShippingCity, ShippingPostalCode, ShippingState, ShippingPhone'
		)
	);

	public static $casting = array(
		"ShippingFullCountryName" => "Varchar(200)"
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

	/**
	 *
	 *@return String
	 **/
	function getShippingFullCountryName() {
		return $this->ShippingFullCountryName();
	}

	/**
	 *
	 *@return String
	 **/
	function ShippingFullCountryName() {
		return EcommerceRole::find_country_title($this->ShippingCountry);
	}

	/**
	 *
	 *@return FieldSet
	 **/
	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->replaceField("OrderID", $fields->dataFieldByName("OrderID")->performReadonlyTransformation());
		return $fields;
	}

	/**
	 *
	 *@return FieldSet
	 **/
	function scaffoldSearchFields(){
		$fields = parent::scaffoldSearchFields();
		$fields->replaceField("OrderID", new NumericField("OrderID", "Order Number"));
		return $fields;
	}

	/**
	 *
	 *@return DataObject (ShippingAddress)
	 **/
	function makeShippingAddressFromMember($member = null, $forceOverRide = false) {
		if(!$member) {
			$member = Member::currentUser();
		}
		if($member) {
			if(!$this->ShippingName || $forceOverRide) $this->ShippingName = $member->Title;
			if(!$this->ShippingAddress || $forceOverRide) $this->ShippingAddress = $member->Address;
			if(!$this->ShippingAddress2 || $forceOverRide) $this->ShippingAddress2 = $member->AddressLine2;
			if(!$this->ShippingCity || $forceOverRide) $this->ShippingCity = $member->City;
			if(!$this->ShippingPostalCode || $forceOverRide) $this->ShippingPostalCode = $member->PostalCode;
			if(!$this->ShippingState || $forceOverRide) $this->ShippingState = $member->State;
			if(!$this->ShippingCountry || $forceOverRide) $this->ShippingCountry = $member->Country;
			//if(!$this->ShippingPhone || $forceOverRide) $this->ShippingPhone = $member->Phone;
		}
		return $this;
	}

	function populateDefaults() {
		parent::populateDefaults();
		$this->ShippingCountry = ShoppingCart::get_country();
	}

}
