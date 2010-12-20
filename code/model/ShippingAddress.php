<?php


class ShippingAddress extends DataObject {

	static $db = array(
		'ShippingName' => 'Text',
		'ShippingAddress' => 'Text',
		'ShippingAddress2' => 'Text',
		'ShippingCity' => 'Text',
		'ShippingPostalCode' => 'Varchar(30)',
		'ShippingState' => 'Varchar(30)',
		'ShippingCountry' => 'Text',
		'ShippingPhone' => 'Varchar(30)'
	);

	static $has_one = array(
		"Order" => "Order"
	);

	static $indexes = array(
		"SearchFields" => "fulltext (ShippingAddress, ShippingAddress2, ShippingCity, ShippingPostalCode, ShippingState, ShippingPhone)"
	);

	public static $casting = array(); //adds computed fields that can also have a type (e.g.

	public static $searchable_fields = array(
		"ShippingName" => "PartialMatchFilter",
		"SearchFields" => "PartialMatchFilter"
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

}
