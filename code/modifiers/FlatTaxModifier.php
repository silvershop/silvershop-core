<?php
/**
 * Handles calculation of sales tax on Orders.
 *
 * If you would like to make your own tax calculator,
 * create a subclass of this and enable it by using
 * {@link Order::set_modifiers()} in your project
 * _config.php file.
 *
 * Sample configuration in your _config.php:
 *
 * <code>
 *	//rate , name, isexclusive
 * 	FlatTaxModifier::set_tax(0.15, 'GST', false);
 * </code>
 *
 * @package ecommerce
 */
class FlatTaxModifier extends OrderModifier {

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

	protected static $name = null;
	protected static $rate = null;
	protected static $exclusive = null;

	static $includedmessage = "%.1f%% %s (inclusive)";
	static $excludedmessage = "%.1f%% %s";

	static function set_tax($rate, $name = null, $exclusive = true) {
		self::$rate = $rate;
		self::$name = (string)$name;
		self::$exclusive = (bool)$exclusive;
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

	protected function LiveRate() {
		return self::$rate;
	}

	protected function LiveName() {
		return self::$name;
	}

	protected function LiveIsExclusive() {
		return self::$exclusive;
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
	 */
	function Charge() {
		if($this->IsExclusive())
			return $this->TaxableAmount() * $this->Rate();
		return $this->TaxableAmount() - ($this->TaxableAmount()/(1+$this->Rate())); //inclusive tax requires a different calculation
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

	function TableValue(){
		return $this->Charge();
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
		$message = ($this->IsExclusive()) ? self::$excludedmessage : self::$includedmessage;
		return sprintf($message,$this->Rate() * 100, $this->Name());
	}

	/**
	 * PRECONDITION: The order item is not saved in the database yet.
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();

		$this->Rate = $this->LiveRate();
		$this->Name = $this->LiveName();
		$this->TaxType = $this->LiveIsExclusive() ? 'Exclusive' : 'Inclusive';
	}
}