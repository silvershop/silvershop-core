<?php
/**
 * Handles calculation of sales tax on Orders on
 * a per-country basis.
 *
 * If you would like to make your own tax calculator,
 * create a subclass of this and enable it by using
 * {@link Order::set_modifiers()} in your project
 * _config.php file.
 *
 * Sample configuration in your _config.php:
 *
 * <code>
 * 	TaxModifier::set_for_country('NZ', 0.125, 'GST', 'inclusive');
 * 	TaxModifier::set_for_country('UK', 0.175, 'VAT', 'exclusive');
 * </code>
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: modifiers
 *

 **/

class TaxModifier extends OrderModifier {


// ######################################## *** model defining static variables (e.g. $db, $has_one)
	public static $db = array(
		'Country' => 'Varchar(3)',
		'Rate' => 'Double',
		'TaxType' => "Enum('Exclusive,Inclusive')",
		'Charge' => "Currency",
		'TaxableAmount' => "Currency"
	);

// ######################################## *** cms variables + functions (e.g. getCMSFields, $searchableFields)
// ######################################## *** other (non) static variables (e.g. protected static $special_name_for_something, protected $order)

	protected static $names_by_country;

	protected static $rates_by_country;

	protected static $excl_by_country;

	/**
	 * Set the tax information for a particular country.
	 * By default, no tax is charged.
	 *
	 * @param $country string The two-letter country code
	 * @param $rate float The tax rate, eg, 0.125 = 12.5%
	 * @param $name string The name to give to the tax, eg, "GST"
	 * @param $inclexcl string "inclusive" if the prices are tax-inclusive.
	 * 						"exclusive" if tax should be added to the order total.
	 */
	static function set_for_country($country, $rate, $name, $inclexcl) {
		self::$names_by_country[$country] = $name;
		self::$rates_by_country[$country] = $rate;
		switch($inclexcl) {
			case 'inclusive' : self::$excl_by_country[$country] = false; break;
			case 'exclusive' : self::$excl_by_country[$country] = true; break;
			default: user_error("TaxModifier::set_for_country - bad argument '$inclexcl' for \$inclexl.  Must be 'inclusive' or 'exclusive'.", E_USER_ERROR);
		}
	}
// ######################################## *** CRUD functions (e.g. canEdit)
// ######################################## *** init and update functions


	public function runUpdate() {
		$this->checkField("Country");
		$this->checkField("Rate");
		$this->checkField("TaxType");
		$this->checkField("Charge");
		$this->checkField("TaxableAmount");
		parent::runUpdate();
	}

// ######################################## *** form functions (e. g. showform and getform)
// ######################################## *** template functions (e.g. ShowInTable, TableTitle, etc...) ... USED DB VALUES

	function ShowInTable() {
		return $this->Rate;
	}

	/**
	 * @return string
	 */
	function TableValue() {
		return $this->Charge;
	}

	/**
	 * @return string
	 */
	function TableTitle() {
		return number_format($this->Rate * 100, 1) . '% ' . $this->Name . ($this->TaxType == "Exclusive" ? '' : _t("TaxModifier.INCLUDEDINTHEPRICE", ' (included in the above price)'));
	}


	public function IsExclusive() {
		return $this->TaxType == "Exclusive";
	}


// ######################################## ***  inner calculations.... ... USED CALCULATED VALUES



// ######################################## *** calculate database fields: protected function Live[field name]



	/**
	 * Get the tax amount to charge on the order.
	 *
	 * Exclusive is easy, however, inclusive is harder.
	 * For example, with GST the tax amount is 1/9 of the
	 * inclusive price not 1/8.
	 */
	protected function LiveCharge() {
		$rate = ($this->LiveIsExclusive() ? $this->LiveRate() : (1 - (1 / (1 + $this->LiveRate()))));
		return $this->LiveTaxableAmount() * $rate;
	}

	/**
	 * The total amount from the {@link Order} that
	 * is taxable.
	 */
	protected function LiveTaxableAmount() {
		$order = $this->Order();
		return $order->SubTotal() + $order->ModifiersSubTotal($this->class);
	}

	protected function LiveCountry() {
		return EcommerceCountry::get_country();
	}

	protected function LiveRate() {
		if($this->LiveCountry() && isset(self::$rates_by_country[$this->LiveCountry()])) {
			return self::$rates_by_country[$this->LiveCountry()];
		}
	}

	protected function LiveName() {
		if($this->LiveCountry() && isset(self::$names_by_country[$this->LiveCountry()])) {
			return self::$names_by_country[$this->LiveCountry()];
		}
		return _t("TaxModifier.TAX", "tax");
	}

	protected function LiveIsExclusive() {
		$exclusive = false;
		if($this->LiveCountry() && isset(self::$excl_by_country[$this->LiveCountry()])) {
			if( self::$excl_by_country[$this->LiveCountry()]) {
				$exclusive = true;
			}
		}
		return $exclusive;
	}

	protected function LiveTaxType() {
		if($this->LiveIsExclusive()) {
			return "Exclusive";
		}
		return "Inclusive";
	}

	protected function LiveCalculationValue() {
		return $this->LiveIsExclusive() ? $this->LiveCharge() : 0;
	}

// ######################################## *** Type Functions (IsChargeable, IsDeductable, IsNoChange, IsRemoved)

	protected function IsChargeable(){
		if($this->IsExclusive()) {
			return true;
		}
		return false;
	}

	protected function IsNoChange() {
		if($this->IsChargeable()) {
			return false;
		}
		else {
			return true;
		}
	}

// ######################################## *** standard database related functions (e.g. onBeforeWrite, onAfterWrite, etc...)
// ######################################## *** AJAX related functions
// ######################################## *** debug functions

}
