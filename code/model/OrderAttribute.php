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

	private static $singular_name = "Attribute";
	private static $plural_name = "Attributes";

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

	public function isLive() {
		return (!$this->isInDB() || $this->Order()->IsCart());
	}

	/**
	* Produces a title for use in templates.
	* @return string
	*/
	public function TableTitle() {
		$title = $this->i18n_singular_name();
		$this->extend('updateTableTitle', $title);
		return $title;
	}

	public function CartTitle() {
		$title = $this->TableTitle();
		$this->extend('updateCartTitle', $title);
		return $title;
	}

	public function ShowInTable() {
		return true;
	}

}
