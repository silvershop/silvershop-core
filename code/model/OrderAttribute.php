<?php
/**
 * A single line in an order. This could be an item, or a subtotal line.
 * 
 * @see OrderItem
 * @see OrderModifier
 *
 * @package shop
 */
class OrderAttribute extends DataObject {

	private static $db = array(
		'CalculatedTotal' => 'Currency'
	);

	private static $has_one = array(
		'Order' => 'Order'
	);

	private static $casting = array(
		'TableTitle' => 'Text',
		'CartTitle' => 'Text'
	);

	public function canCreate($member = null) {
		return false;
	}

	public function canDelete($member = null) {
		return false;
	}

	function isLive(){
		return (!$this->isInDB() || $this->Order()->IsCart());
	}

	/**
	 * Return a name of what this attribute is
	 * called e.g. "Modifier", or "Product".
	 *
	 * @return string
	 */
	function TableTitle() {
		return 'Attribute';
	}
	
	function CartTitle() {
		return $this->TableTitle();
	}
	
	function ShowInTable() {
		return true;
	}

}