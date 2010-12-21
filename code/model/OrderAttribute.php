<?php
/**
 * @see OrderModifier
 * @see OrderItem
 * @see OrderModifier
 *
 * @package ecommerce
 */
class OrderAttribute extends DataObject {

	public static $db = array(
		'Sort' => 'Int'
	);

	public static $has_one = array(
		'Order' => 'Order',
		'OrderAttribute_Group' => 'OrderAttribute_Group'
	);

	public static $casting = array(
		'TableTitle' => 'Text',
		'CartTitle' => 'Text'
	);

	public static $default_sort = "\"OrderAttribute\".\"Sort\" ASC, \"OrderAttribute\".\"Created\" DESC";

	public static $indexes = array(
		"Sort" => true,
	);

	public function canCreate($member = null) {
		return false;
	}

	public function canDelete($member = null) {
		return false;
	}

	function isLive(){
		return (!$this->ID || $this->Order()->IsCart());
	}
	/*
	public function __construct($object = null) {
		if(is_array($object)) {
			parent::__construct($object);
		}
		elseif($object) {
			$this->ItemID = $object->ID;
			parent::__construct();
		}
	}
	*/

	public function addItem($object) {
		//more may be added here in the future
		return true;
	}

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
	 * Used by the templates and for ajax updating functionality.
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
		return get_class($this) . '_' . ($this->ID ? 'DB_' . $this->ID : $this->ItemID);
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


class OrderAttribute_Group extends DataObject {

	public static $db = array(
		"Title" => "Varchar(100)",
		'Sort' => 'Int'
	);

	public static $default_sort = "\"Sort\" ASC";

	public static $indexes = array(
		"Sort" => true,
	);


}
