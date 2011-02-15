<?php
/**
 * @description: base class for OrderItem (item in cart) and OrderModifier (extra - e.g. Tax)
 * @see OrderModifier
 * @see OrderItem
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class OrderAttribute extends DataObject {

	public static $db = array(
		'Sort' => 'Int'
	);

	public static $has_one = array(
		'Order' => 'Order',
		'OrderAttribute_Group' => 'OrderAttribute_Group'
	);

	public static $casting = array(
		'TableTitle' => 'HTMLText',
		'TableSubTitle' => 'HTMLText',
		'CartTitle' => 'HTMLText'
	);

	public static $create_table_options = array(
		'MySQLDatabase' => 'ENGINE=InnoDB'
	);

	/**
	* @note: we can add the \"OrderAttribute_Group\".\"Sort\" part because this table is always included (see extendedSQL).
	**/
	public static $default_sort = "\"OrderAttribute\".\"Sort\" ASC, \"OrderAttribute\".\"Created\" DESC";

	public static $indexes = array(
		"Sort" => true,
	);

	protected static $has_been_written = false;
		public static function set_has_been_written() {Session::set("OrderAttributeHasBeenWritten", true); self::$has_been_written = true;}
		public static function get_has_been_written() {return Session::get("OrderAttributeHasBeenWritten") || self::$has_been_written ? true : false;}
		public static function unset_has_been_written() {Session::set("OrderAttributeHasBeenWritten", false);self::$has_been_written = false;}

	protected $_canEdit = null;

	function init() {
		return true;
	}

	function canCreate($member = null) {
		return true;
	}

	function canEdit($member = null) {
		if($this->_canEdit === null) {
			$this->_canEdit = false;
			if($this->OrderID) {
				if($this->Order()->exists()) {
					if($this->Order()->canEdit($member)) {
						$this->_canEdit = true;
					}
				}
			}
		}
		return $this->_canEdit;
	}

	function canDelete($member = null) {
		return false;
	}

	public function addBuyableToOrderItem($object) {
		//more may be added here in the future
		return true;
	}
	/**
	 * @see DataObject::extendedSQL
	 * TO DO: make it work... because we call DataObject::get(....) it may not be called....
	public function extendedSQL($filter = "", $sort = "", $limit = "", $join = "", $having = ""){
		$join .= " LEFT JOIN \"OrderAttribute_Group\" ON \"OrderAttribute_Group\".\"ID\" = \"OrderAttribute\".\"OrderAttribute_GroupID\"";
		return parent::extendedSQL($filter, $sort, $limit, $join, $having);
	}
	*/
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
		return get_class($this) . '_' .'DB_' . $this->ID;
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

	function onAfterWrite() {
		parent::onAfterWrite();
		self::set_has_been_written();
	}

	function onAfterDelete() {
		parent::onAfterDelete();
		self::set_has_been_written();
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
