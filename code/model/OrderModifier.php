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

	private static $db = array(
		'Amount' => 'Currency',
		'Type' => "Enum('Chargable,Deductable,Ignored','Chargable')",
		'Sort' => 'Int'
	);

	private static $defaults = array(
		'Type' => 'Chargable'
	);

	private static $casting = array(
		'TableValue' => 'Currency'
	);

	private static $searchable_fields = array(
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

	private static $field_labels = array();
	private static $summary_fields = array(
		"Order.ID" => "Order ID",
		"TableTitle" => "Table Title",
		"ClassName" => "Type",
		"Amount" => "Amount" ,
		"Type" => "Type"
	);

	private static $singular_name = "Modifier";
	private static $plural_name = "Modifiers";

	private static $default_sort = "\"OrderModifier\".\"Sort\" ASC, \"Created\" ASC";

	private static $extensions = array(
		"OrderModifierLazyLoadFix"
	);

	/**
	* Specifies whether this modifier is always required in an order.
	*/
	public function required() {
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
	public function modify($subtotal, $forcecalculation = false) {
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
		$value = round($value, Order::config()->rounding_precision);
		$this->Amount = $value;
		return $subtotal;
	}

	/**
	 * Calculates value to store, based on incoming running total.
	 * @param float $incoming the incoming running total.
	 */
	public function value($incoming) {
		return 0;
	}

	/**
	 * Check if the modifier should be in the cart.
	 */
	public function valid() {
		$order = $this->Order();
		if(!$order){
			return false;
		}
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
	public function Amount() {
		return $this->Amount;
	}

	/**
	 * Monetary to use in templates.
	 */
	public function TableValue() {
		return $this->Total();
	}

	/**
	* Provides a modifier total that is positive or negative, depending on whether the modifier is chargable or not.
	*
	* @return boolean
	*/
	public function Total() {
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
	public function IsChargable() {
		return $this->Type == "Chargable";
	}

	/**
	 * Checks if the modifier can be removed.
	 * @return boolean
	 */
	public function canRemove() {
		return false;
	}

}

/**
 * Hack to fix issue with lazy loding
 * @see https://github.com/silverstripe/silverstripe-framework/issues/1682
 */
class OrderModifierLazyLoadFix extends DataExtension{

	public function augmentSQL(SQLQuery &$query) {
		$query->addLeftJoin("OrderModifier", "\"OrderModifier\".\"ID\" = \"OrderAttribute\".\"ID\"");
	}

}