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
 * @package ecommerce
 */
class TaxModifier extends OrderModifier {

	public static $db = array(
		'Country' => 'Text',
		'Rate' => 'Double',
		'Name' => 'Text',
		'TaxType' => "Enum('Exclusive,Inclusive')"
	);

	public static $has_one = array();

	public static $has_many = array();

	public static $many_many = array();

	public static $belongs_many_many = array();

	public static $defaults = array();

	public static $casting = array();

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

	function Country() {
		return $this->ID ? $this->Country : $this->LiveCountry();
	}

	function Rate() {
		return $this->ID ? $this->Rate : $this->LiveRate();
	}

	function Name() {
		return $this->ID ? $this->Name : $this->LiveName();
	}

	function IsExclusive() {
		return $this->ID ? $this->TaxType == 'Exclusive' : $this->LiveIsExclusive();
	}

	protected function LiveCountry() {
		return EcommerceRole::find_country();
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
	}

	protected function LiveIsExclusive() {
		if($this->LiveCountry() && isset(self::$excl_by_country[$this->LiveCountry()])) {
			return self::$excl_by_country[$this->LiveCountry()];
		}
	}

	function Amount() {
		return $this->AddedCharge();
	}

	/**
	 * Get the tax amount that needs to be added to the given order.
	 * If tax is setup to be inclusive, then this will be 0.
	 */
	function AddedCharge() {
		return $this->IsExclusive() ? $this->Charge() : 0;
	}

	/**
	 * Get the tax amount to charge on the order.
	 *
	 * Exclusive is easy, however, inclusive is harder.
	 * For example, with GST the tax amount is 1/9 of the
	 * inclusive price not 1/8.
	 */
	function Charge() {
		$rate = ($this->IsExclusive() ? $this->Rate() : (1 - (1 / (1 + $this->Rate()))));
		return $this->TaxableAmount() * $rate;
	}

	/**
	 * The total amount from the {@link Order} that
	 * is taxable.
	 */
	function TaxableAmount() {
		$order = $this->Order();
		return $order->SubTotal() + $order->ModifiersSubTotal($this->class);
	}

	function ShowInTable() {
		return $this->Rate();
	}

	/**
	 * The title of what appears on the OrderInformation
	 * template table on the checkout page.
	 *
	 * PRECONDITION: There is a rate set.
	 *
	 * @return string
	 */
	function TableTitle() {
		return number_format($this->Rate() * 100, 1) . '% ' . $this->Name() . ($this->IsExclusive() ? '' : ' (included in the above price)');
	}

	/**
	 * PRECONDITION: The order item is not saved in the database yet.
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();

		$this->Country = $this->LiveCountry();
		$this->Rate = $this->LiveRate();
		$this->Name = $this->LiveName();
		$this->TaxType = $this->LiveIsExclusive() ? 'Exclusive' : 'Inclusive';
	}
}
