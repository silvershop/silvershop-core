<?php
/**
 * SimpleShoppingModifier is the default shipping calculation
 * scheme. It lets you set fixed shipping* costs, or a fixed
 * cost for each country you're delivering to.
 *
 * If you require more advanced shipping control, we suggest
 * that you create your own subclass of {@link OrderModifier}
 *
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: modifiers
 *

 **/
class SimpleShippingModifier extends OrderModifier {


// ######################################## *** model defining static variables (e.g. $db, $has_one)

	static $db = array(
		'Country' => 'Varchar(3)',
		'ShippingChargeType' => "Enum('Default,ForCountry')"
	);

// ######################################## *** cms variables + functions (e.g. getCMSFields, $searchableFields)


// ######################################## *** other (non) static variables (e.g. protected static $special_name_for_something, protected $order)


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

// ######################################## *** CRUD functions (e.g. canEdit)
// ######################################## *** init and update functions

	public function runUpdate() {
		$this->checkField("Country");
		$this->checkField("ShippingChargeType");
		parent::runUpdate();
	}


// ######################################## *** form functions (e. g. showform and getform)
// ######################################## *** template functions (e.g. ShowInTable, TableTitle, etc...) ...  USES DB VALUES

	/**
	 * @return boolean
	 */

	public function ShowInCart() {
		return $this->CalculationTotal() > 0;
	}

	/**
	 * @return string
	 */
	public function TableTitle() {
		if($this->Country) {
			$countryList = Geoip::getCountryDropDown();
			return _t("SimpleShippingModifier.SHIPPINGTO", "Shipping to")." ".$countryList[$this->Country];
		}
		else {
			return _t("SimpleShippingModifier.SHIPPING", "Shipping");
		}
	}

	/**
	 * @return string
	 */
	public function CartTitle() {
		return _t("SimpleShippingModifier.SHIPPING", "Shipping");
	}


// ######################################## ***  inner calculations....  USES CALCULATED VALUES

	protected function IsDefaultCharge() {
		return !$this->LiveCountry() || !array_key_exists($this->LiveCountry(), self::$charges_by_country);
	}

// ######################################## *** calculate database fields: protected function Live[field name] ...  USES CALCULATED VALUES

	protected function LiveCountry() {
		EcommerceCountry::get_country();
	}

	/**
	 * Find the amount for the shipping on the shipping country for the order.
	 */
	protected function LiveCalculationValue() {
		return $this->IsDefaultCharge() ? self::$default_charge : self::$charges_by_country[$this->LiveCountry()];
	}

	protected function LiveShippingChargeType() {
		$this->IsDefaultCharge() ? 'Default' : 'ForCountry';
	}

// ######################################## *** Type Functions (IsChargeable, IsDeductable, IsNoChange, IsRemoved)
// ######################################## *** standard database related functions (e.g. onBeforeWrite, onAfterWrite, etc...)
// ######################################## *** AJAX related functions
// ######################################## *** debug functions

}
