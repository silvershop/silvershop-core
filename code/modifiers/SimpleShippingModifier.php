<?php
/**
 * SimpleShoppingModifier is the default shipping calculation
 * scheme. It lets you set fixed shipping* costs, or a fixed
 * cost for each country you're delivering to.
 *
 * If you require more advanced shipping control, we suggest
 * that you create your own subclass of {@link OrderModifier}
 *
 * @package ecommerce
 */
class SimpleShippingModifier extends OrderModifier {

	static $db = array(
		'Country' => 'Text',
		'ShippingChargeType' => "Enum('Default,ForCountry')"
	);

	static $default_charge = 0;

	static function set_default_charge($defaultCharge) {
		self::$default_charge = $defaultCharge;
	}

	static $charges_by_country = array();

	/**
	 * Set shipping charges on a country by country basis.
	 * For example, SimpleShippingModifier::set_charges_for_countries(array(
	 *   'US' => 10,
	 *   'NZ' => 5,
	 * ));
	 * @param countryMap A map of 2-letter country codes
	 */
	static function set_charges_for_countries($countryMap) {
		self::$charges_by_country = array_merge(self::$charges_by_country, $countryMap);
	}

	// Attributes Functions

	function Country() {
		return $this->ID ? $this->Country : $this->LiveCountry();
	}

	function IsDefaultCharge() {
		return $this->ID ? $this->ShippingChargeType == 'Default' : $this->LiveIsDefaultCharge();
	}

	protected function LiveCountry() {
		$order = ShoppingCart::current_order();
		return $order->findShippingCountry(true);
	}

	protected function LiveIsDefaultCharge() {
		return !$this->LiveCountry() || !array_key_exists($this->LiveCountry(), self::$charges_by_country);
	}

	/**
	 * Find the amount for the shipping on the shipping country for the order.
	 */
	function LiveAmount() {
		return $this->LiveIsDefaultCharge() ? self::$default_charge : self::$charges_by_country[$this->LiveCountry()];
	}

	// Display Functions

	function ShowInCart() {
		return $this->Total() > 0;
	}

	/**
	 * @TODO Add i18n entities to the text.
	 * @return string
	 */
	function TableTitle() {
		if($this->Country()) {
			$countryList = Geoip::getCountryDropDown();
			return "Shipping to {$countryList[$this->Country()]}";
		} else {
			return 'Shipping';
		}
	}

	/**
	 * @TODO Add i18n entities to the text.
	 * @return string
	 */
	function CartTitle() {
		return 'Shipping';
	}

	// Database Writing Function

	/*
	 * Precondition : The order item is not saved in the database yet
	 */
	function onBeforeWrite() {
		parent::onBeforeWrite();

		$this->Country = $this->LiveCountry();
		$this->ShippingChargeType = $this->LiveIsDefaultCharge() ? 'Default' : 'ForCountry';
	}
}
