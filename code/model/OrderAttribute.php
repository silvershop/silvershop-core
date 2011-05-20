<?php
/**
 * @see OrderModifier
 * @see OrderItem
 * @see OrderModifier
 *
 * @package ecommerce
 */
class OrderAttribute extends DataObject {

	protected $_id;

	public static $has_one = array(
		'Order' => 'Order'
	);

	public static $casting = array(
		'TableTitle' => 'Text',
		'CartTitle' => 'Text',
	);


	public function getIdAttribute() {
		return $this->_id;
	}

	public function setIdAttribute($id) {
		$this->_id = $id;
	}

	public function canCreate($member = null) {
		return false;
	}

	public function canDelete($member = null) {
		return false;
	}

	/**
	 * @TODO Where is this method used?
	 * @return Order
	 */
	/*function Order() {
		if($this->ID) return DataObject::get_by_id('Order', $this->OrderID);
		else return ShoppingCart::current_order();
	}*/


	######################
	## TEMPLATE METHODS ##
	######################

	/**
	 * Return a string of class names, in order
	 * of heirarchy from OrderAttribute for the
	 * current attribute.
	 *
	 * e.g.: "product_orderitem orderitem
	 * orderattribute".
	 *
	 * Used by the templates.
	 *
	 * @return string
	 */
	function Classes() {
		$class = get_class($this);
		$classes = array();
		$classes[] = strtolower($class);

		while(get_parent_class($class) != 'DataObject' && $class = get_parent_class($class)) {
			$classes[] = strtolower($class);
		}

		return implode(' ', $classes);
	}

	function MainID() {
		return get_class($this) . '_' . ($this->ID ? 'DB_' . $this->ID : $this->_id);
	}

	function TableID() {
		return 'Table_' . $this->MainID();
	}

	function CartID() {
		return 'Cart_' . $this->MainID();
	}

	function ShowInTable() {
		return true;
	}

	function ShowInCart() {
		return $this->ShowInTable();
	}

	function TableTitleID() {
		return $this->TableID() . '_Title';
	}

	function CartTitleID() {
		return $this->CartID() . '_Title';
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

	function TableTotalID() {
		return $this->TableID() . '_Total';
	}

	function CartTotalID() {
		return $this->CartID() . '_Total';
	}

}
