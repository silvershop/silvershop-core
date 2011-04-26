<?php

/**
 * @description: each order has a shipping address.
 *
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: member
 *
 **/

class ShippingAddress extends OrderAddress {

	static $db = array(
		'ShippingFirstName' => 'Text',
		'ShippingSurname' => 'Text',
		'ShippingAddress' => 'Text',
		'ShippingAddress2' => 'Text',
		'ShippingCity' => 'Text',
		'ShippingPostalCode' => 'Varchar(30)',
		'ShippingState' => 'Varchar(30)',
		'ShippingCountry' => 'Varchar(4)',
		'ShippingPhone' => 'Varchar(200)'
	);


	/**
	 * HAS_ONE =array(ORDER => ORDER);
	 * we place this relationship here
	 * (rather than in the parent class: OrderAddress)
	 * because that makes for a cleaner relationship
	 * (otherwise we ended up with a "has two" relationship in Order)
	 **/
	public static $has_one = array(
		"Order" => "Order"
	);

	static $indexes = array(
		// "SearchFields" => "fulltext (Address, Address2, City, PostalCode, State, Phone)"
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
		"ShippingSurname" => "PartialMatchFilter",
		"ShippingAddress" => "PartialMatchFilter",
		"ShippingCity" => "PartialMatchFilter",
		"ShippingCountry" => "PartialMatchFilter"
	);

	public static $summary_fields = array(
		"Order.Title",
		"ShippingSurname",
		"ShippingCity",
		"ShippingCountry",
	);

	public static $singular_name = "Shipping Address";
		function i18n_singular_name() { return _t("OrderAddress.SHIPPINGADDRESS", "Shipping Address");}

	public static $plural_name = "Shipping Addresses";
		function i18n_plural_name() { return _t("OrderAddress.SHIPPINGADDRESSES", "Shipping Addresses");}


	/**
	 *
	 *@return String
	 **/
	function ShippingFullCountryName() {
		return EcommerceRole::find_country_title($this->ShippingCountry);
	}


	/**
	 *@return Fieldset
	 **/
	 public function getEcommerceFields() {
		if(OrderAddress::get_use_separate_shipping_address()) {
			$fields = parent::getEcommerceFields();
			// *** BILLING ADDRESS
			//postal code
			$shippingPostalCodeField = new TextField('ShippingPostalCode', _t('OrderAddress.POSTALCODE','Postal Code'));
			if(self::get_postal_code_url()){
				$shippingPostalCodeField->setRightTitle('<a href="'.self::get_postal_code_url().'" id="ShippingPostalCodeLink" class="postalCodeLink">'.self::get_postal_code_label().'</a>');
			}
			//state
			if(OrderAddress::get_include_state()) {
				$shippingStateField = new TextField('ShippingState', _t('OrderAddress.STATE','State'));
			}
			else {
				//adding statefield here as hidden field to make the code easier below...
				$shippingStateField = new HiddenField('ShippingState', '', "Bliss");
			}
			// country
			$countriesForDropdown = EcommerceRole::list_of_allowed_countries_for_dropdown();
			$shippingCountryField = new DropdownField('ShippingCountry',  _t('OrderAddress.COUNTRY','Country'), $countriesForDropdown, EcommerceCountry::get_country());
			$shippingCountryField->addExtraClass('ajaxCountryField');
			if(count($countriesForDropdown) == 1) {
				$countryField = $shippingCountryField->performReadonlyTransformation();
			}
			$shippingFields = new CompositeField(
				new HeaderField(_t('OrderAddress.SENDGOODSTODIFFERENTADDRESS','Send goods to different address'), 3),
				new LiteralField('ShippingNote', '<p class="message warning">'._t('OrderAddress.SHIPPINGNOTE','Your goods will be sent to the address below.').'</p>'),
				new LiteralField('Help', '<p>'._t('OrderAddress.SHIPPINGHELP','You can use this for gift giving. No billing information will be disclosed to this address.').'</p>'),
				new TextField('ShippingName', _t('OrderAddress.NAME','Name')),
				new TextField('ShippingAddress', _t('OrderAddress.ADDRESS','Address')),
				new TextField('ShippingAddress2', _t('OrderAddress.ADDRESS2','')),
				new TextField('ShippingCity', _t('OrderAddress.CITY','City')),
				$shippingPostalCodeField,
				$shippingStateField,
				$shippingCountryField
			);
			$shippingFields->SetID('ShippingFields');
			$fields->push($shippingFields);
			$this->extend('augmentGetEcommerceFields', $fields);
		}
		else {
			$fields = new FieldSet();
		}
		return $fields;
	}

	/**
	 * Return which member fields should be required on {@link OrderForm}
	 * and {@link ShopAccountForm}.
	 *
	 * @return array
	 */
	function getEcommerceRequiredFields() {
		$requiredFieldsArray = array();
		$this->owner->extend('augmentEcommerceRequiredFields', $requiredFieldsArray);
		return $requiredFieldsArray;
	}

	/**
	 *
	 *@return DataObject (OrderAddress)
	 **/
	function makeAddressFromMember($member = null, $forceOverRide = false) {
		if(!$member) {
			$member = Member::currentUser();
		}
		if($member) {
			if(!$this->Name || $forceOverRide) $this->Name = $member->getTitle();
			if(!$this->Email || $forceOverRide) $this->Email = $member->Email;
		}
		return $this;
	}


	function populateDefaults() {
		parent::populateDefaults();
		$this->ShippingCountry = EcommerceCountry::get_country();
	}

}
