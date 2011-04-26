<?php

/**
 * @description: each order has a billing address.
 *
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: member
 *
 **/

class BillingAddress extends OrderAddress {

	static $db = array(
		'FirstName' => 'Text',
		'Surname' => 'Text',
		'Address' => 'Text',
		'Address2' => 'Text',
		'City' => 'Text',
		'PostalCode' => 'Varchar(30)',
		'State' => 'Varchar(30)',
		'Country' => 'Varchar(4)',
		'Phone' => 'Varchar(200)'
	);

	/**
	 * HAS_ONE =array(ORDER => ORDER);
	 * we place this relationship here
	 * (rather than in the parent class: OrderAddress)
	 * because that makes for a cleaner relationship
	 * (otherwise we ended up with a "has two" relationship in Order)
	 **/
	static $has_one = array(
		"Order" => "Order"
	);

	static $indexes = array(
		// "SearchFields" => "fulltext (Address, Address2, City, PostalCode, State, Phone)"
		array(
			'name' => 'SearchFields',
			'type' => 'fulltext',
			'value' => 'Address, Address2, City, PostalCode, State, Phone'
		)
	);

	public static $casting = array(
		"FullCountryName" => "Varchar(200)"
	);

	public static $searchable_fields = array(
		'OrderID' => array(
			'field' => 'NumericField',
			'title' => 'Order Number'
		),
		"FirstName" => "PartialMatchFilter",
		"Surname" => "PartialMatchFilter",
		"Address" => "PartialMatchFilter",
		"City" => "PartialMatchFilter",
		"Country" => "PartialMatchFilter"
	);

	public static $summary_fields = array(
		"Order.Title",
		"Surname",
		"City",
		"Country"
	);

	public static $singular_name = "Billing Address";
		function i18n_singular_name() { return _t("OrderAddress.BILLINGADDRESS", "Billing Address");}

	public static $plural_name = "Billing Addresses";
		function i18n_plural_name() { return _t("OrderAddress.BILLINGADDRESSES", "Billing Addresses");}

	/**
	 *
	 *@return String
	 **/
	function FullCountryName() {
		return EcommerceRole::find_country_title($this->Country);
	}


	/**
	 *@return Fieldset
	 **/
	public function getFields() {
		$fields = parent::getEcommerceFields();
		// *** BILLING ADDRESS
		//postal code
		$postalCodeField = new TextField('PostalCode', _t('OrderAddress.POSTALCODE','Postal Code'));
		if(OrderAddress::get_postal_code_url()){
			$postalCodeField->setRightTitle('<a href="'.OrderAddress::get_postal_code_url().'" id="BillingPostalCodeLink" class="postalCodeLink">'.OrderAddress::get_postal_code_label().'</a>');
		}
		if(OrderAddress::get_include_state()) {
			$stateField = new TextField('State', _t('OrderAddress.STATE','State'));
		}
		else {
			//adding statefield here as hidden field to make the code easier below...
			$stateField = new HiddenField('State', '', "Bliss");
		}
		// country
		$countriesForDropdown = EcommerceRole::list_of_allowed_countries_for_dropdown();
		$countryField = new DropdownField('Country',  _t('OrderAddress.COUNTRY','Country'), $countriesForDropdown, EcommerceCountry::get_country());
		$countryField->addExtraClass('ajaxCountryField');
		$setCountryLinkID = $countryField->id() . '_SetCountryLink';
		$countryAJAXLink = new HiddenField($setCountryLinkID, '', ShoppingCart::set_country_link());
		// compile fields
		$billingFields = new CompositeField(
			new HeaderField(_t('OrderAddress.BILLING DETAILS','Billing Details'), 3),
			new TextField('Address', _t('OrderAddress.ADDRESS','Address')),
			new TextField('Address2', _t('OrderAddress.ADDRESS2','&nbsp;')),
			new TextField('City', _t('OrderAddress.CITY','City')),
			$postalCodeField,
			$stateField,
			$countryField,
			$countryAJAXLink,
			new TextField('Phone', _t('OrderAddress.PHONE','Phone'))
		);
		$billingFields->SetID('BillingFields');
		$fields->push($billingFields);
		$this->extend('augmentGetEcommerceFields', $fields);
		return $fields;
	}

	/**
	 * Return which member fields should be required on {@link OrderForm}
	 * and {@link ShopAccountForm}.
	 *
	 * @return array
	 */
	function getRequiredFields() {
		$requiredFieldsArray = array(
			'Address',
			'City',
			'Country'
		);
		$this->extend('augmentGetEcommerceRequiredFields', $requiredFieldsArray);
		return $requiredFieldsArray;
	}



	function populateDefaults() {
		parent::populateDefaults();
		$this->Country = EcommerceCountry::get_country();
	}



}
