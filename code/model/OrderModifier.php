<?php
/**
 * The OrderModifier class is a databound object for
 * handling the additional charges or deductions of
 * an order.
 *
 * @package shop
 * @subpackage modifiers
 */
class OrderModifier extends OrderAttribute {

	public static $db = array(
		'Amount' => 'Currency',
		'Type' => "Enum('Chargable,Deductable,Ignored','Chargable')",
		'Sort' => 'Int'
	);
	
	public static $defaults = array(
		'Type' => 'Chargable'
	);

	public static $casting = array(
		'TableValue' => 'Currency'
	);

	public static $searchable_fields = array(
		'OrderID' => array(
			'title' => 'Order ID',
			'field' => 'TextField'
		),
		"Title" => "PartialMatchFilter",
		"TableTitle" => "PartialMatchFilter",
		"CartTitle" => "PartialMatchFilter",
		"Amount",
		"Type"
	);

	public static $field_labels = array();
	public static $summary_fields = array(
		"Order.ID" => "Order ID",
		"TableTitle" => "Table Title",
		"ClassName" => "Type",
		"Amount" => "Amount" ,
		"Type" => "Type"
	);

	public static $singular_name = "Modifier";
	function i18n_singular_name() {	return _t("OrderModifier.SINGULAR", self::$singular_name); }
	public static $plural_name = "Modifiers";
	function i18n_plural_name() { return _t("OrderModifier.PLURAL", self::$plural_name); }

	public static $default_sort = "\"Sort\" ASC, \"Created\" ASC";
	
	/**
	* Specifies whether this modifier is always required in an order.
	*/
	public function required(){
		return true;
	}
	
	/**
	 * Modifies the incoming value by adding, 
	 * subtracting or ignoring the value this modifier calculates.
	 * 
	 * Sets $this->Amount to the calculated value;
	 * @param $subtotal - running total to be modified
	 * @param $forcecalculation - force calculating the value, if order isn't in cart
	 * 
	 * @return $subtotal - updated subtotal
	 */
	public function modify($subtotal,$forcecalculation = false){
		$order = $this->Order();
		$value = ($order->IsCart() || $forcecalculation) ? $this->value($subtotal) : $this->Amount;
		switch($this->Type){			
			case "Chargable":
				$subtotal += $value;
				break;
			case "Deductable":
				$subtotal -= $value;
				break;
			case "Ignored":
				break;
		}
		$value = round($value,Order::$rounding_precision);
		$this->Amount = $value;
		return $subtotal;
	}
	
	/**
	 * Calculates value to store, based on incoming running total.
	 * @param float $incoming the incoming running total.
	 */
	public function value($incoming){
		return 0;
	}
	
	/**
	 * Check if the modifier should be in the cart.
	 */
	public function valid(){
		return true;
	}

	/**
	 * This function is always called to determine the
	 * amount this modifier needs to charge or deduct.
	 *
	 * If the modifier exists in the DB, in which case it
	 * already exists for a given order, we just return
	 * the Amount data field from the DB. This is for
	 * existing orders.
	 *
	 * If this is a new order, and the modifier doesn't
	 * exist in the DB ($this->ID is 0), so we return
	 * the amount from $this->LiveAmount() which is a
	 * calculation based on the order and it's items.
	 */
	function Amount() {
		return $this->Amount;
	}
	
	/**
	 * Monetary to use in templates.
	 */
	function TableValue() {
		return $this->Total();
	}
	
	/**
	* Produces a title for use in templates.
	* @return string
	*/
	function TableTitle(){
		return $this->i18n_singular_name();
	}

	/**
	* Provides a modifier total that is positive or negative, depending on whether the modifier is chargable or not.
	*
	* @return boolean
	*/
	function Total() {
		if($this->Type == "Deductable"){
			return $this->Amount * -1;
		}
		return $this->Amount;
	}
	
	/**
	 * Checks if this modifier has type = Chargable
	 * 
	 * @return boolean
	 */
	function IsChargable() {
		return $this->Type == "Chargable";
	}
	
	/**
	 * Checks if the modifier can be removed.
	 * @return boolean
	 */
	function canRemove() {
		return false;
	}

	function removeLink() {
		return CheckoutPage_Controller::remove_modifier_link($this->ID);
	}
	
}