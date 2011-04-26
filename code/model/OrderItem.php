<?php
/**
 * @description: An order item is a product which has been added to an order,
 * ready for purchase. An order item is typically a product itself,
 * but also can include references to other information such as
 * product attributes like colour, size, or type.
 *
 *
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: model
 *
 **/
class OrderItem extends OrderAttribute {

	protected static $disable_quantity_js = false;
		static function set_disable_quantity_js(boolean $b){self::$disable_quantity_js = $b;}
		static function get_disable_quantity_js(){return self::$disable_quantity_js;}
		static function disable_quantity_js(){self::$disable_quantity_js = true;}

	public static $db = array(
		'Quantity' => 'Double',
		'BuyableID' => 'Int',
		'Version' => 'Int'
	);

	public static $indexes = array(
		"Quantity" => true
	);

	public static $casting = array(
		'UnitPrice' => 'Currency',
		'Total' => 'Currency'
	);

	######################
	## CMS CONFIG ##
	######################

	public static $searchable_fields = array(
		'OrderID' => array(
			'field' => 'NumericField',
			'title' => 'Order Number'
		),
		"TableTitle" => "PartialMatchFilter",
		"UnitPrice",
		"Quantity",
		"Total"
	);

	public static $field_labels = array(

	);

	public static $summary_fields = array(
		"Order.ID" => "Order ID",
		"TableTitle" => "Title",
		"UnitPrice" => "Unit Price" ,
		"Quantity" => "Quantity" ,
		"Total" => "Total Price" ,
	);


	public static $singular_name = "Order Item";
		function i18n_singular_name() { return _t("OrderItem.ORDERITEM", "Order Item");}

	public static $plural_name = "Order Items";
		function i18n_plural_name() { return _t("OrderItem.ORDERITEMS", "Order Items");}

	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName("Version");
		$fields->removeByName("Sort");
		$fields->removeByName("OrderAttribute_GroupID");
		$buyables = Buyable::get_array_of_buyables();
		$classNameArray = array();
		$buyablesArray = array();
		if($buyables && count($buyables)) {
			foreach($buyables as $buyable) {
				$classNameArray[$buyable.Buyable::get_order_item_class_name_post_fix()] = $buyable;
				$newObjects = DataObject::get($buyable);
				if($newObjects) {
					$buyablesArray = array_merge($buyablesArray, $newObjects->toDropDownMap());
				}
			}
		}
		if(count($classNameArray)) {
			$fields->addFieldToTab("Root.Main", new DropdownField("ClassName", _t("OrderItem.TYPE", "Type"), $classNameArray));
			$fields->replaceField("BuyableID", new DropdownField("BuyableID", _t("OrderItem.BOUGHT", "Bought"), $buyablesArray));
		}
		return $fields;
	}

	/**
	 *
	 * @return FieldSet
	  **/
	function scaffoldSearchFields(){
		$fields = parent::scaffoldSearchFields();
		$fields->replaceField("OrderID", new NumericField("OrderID", "Order Number"));
		return $fields;
	}

	public function addBuyableToOrderItem($buyable, $quantity = 1) {
		$this->Version = $buyable->Version;
		$this->BuyableID = $buyable->ID;
		$this->Quantity = $quantity;
		//should always come last!
		parent::addBuyableToOrderItem($buyable);
	}

	/**
	 *
	 * @return Array used to create JSON for AJAX
	  **/
	function updateForAjax(array &$js) {
		$total = $this->TotalAsCurrencyObject()->Nice();
		$js[] = array('id' => $this->TableTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->CartTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->CartQuantityID(), 'parameter' => 'innerHTML', 'value' => $this->Quantity);
		$js[] = array('name' => $this->QuantityFieldName(), 'parameter' => 'value', 'value' => $this->Quantity);
	}


	function onBeforeWrite() {
		parent::onBeforeWrite();
		//always keep quantity above 0
		if(floatval($this->Quantity) == 0) {
			$this->Quantity = 1;
		}
		//product ID and version ID need to be set in subclasses
	}

	/**
	 * Set the quantity attribute in memory.
	 * PRECONDITION: The order item is not saved in the database yet.
	 *
	 * @param int $quantity The quantity to set
	 */
	public function setQuantityAttribute($quantity) {
		$this->Quantity = $quantity;
	}

	/**
	 * Increment the quantity attribute in memory by a given amount.
	 * PRECONDITION: The order item is not saved in the database yet.
	 *
	 * @param int $quantity The amount to increment the quantity by.
	 */
	public function addQuantityAttribute($quantity) {
		$this->Quantity += $quantity;
	}

	/**
	 *
	 * @return Boolean
	  **/
	function hasSameContent($orderItem) {
		return $orderItem instanceof OrderItem && $this->BuyableID == $orderItem->BuyableID && $this->Version == $orderItem->Version;
	}

	public function debug() {
		$id = $this->ID ? $this->ID : $this->BuyableID;
		$quantity = $this->Quantity;
		$orderID = $this->ID ? $this->OrderID : 'The order has not been saved yet, so there is no ID';
		return <<<HTML
			<h2>$this->class</h2>
			<h3>OrderItem class details</h3>
			<p>
				<b>ID : </b>$id<br/>
				<b>Quantity : </b>$quantity<br/>
				<b>Order ID : </b>$orderID
			</p>
HTML;
	}


	######################
	## TEMPLATE METHODS ##
	######################

	function UnitPrice() {
		user_error("OrderItem::UnitPrice() called. Please implement UnitPrice() on $this->class", E_USER_ERROR);
	}

	/**
	 *
	 *@return integer
	  **/
	protected function QuantityFieldName() {
		return $this->MainID() . '_Quantity';
	}

	/*
	 * @Depricated - use QuantityField
	 */
	function AjaxQuantityField(){
		return $this->QuantityField();
	}

	/**
	 *
	 * @return Field (EcomQuantityField)
	  **/
	function QuantityField(){
		return new EcomQuantityField($this);
	}

	/**
	 *
	 * @return Float
	  **/
	function Total() {
		$total = $this->UnitPrice() * $this->Quantity;
		$this->extend('updateTotal',$total);
		return $total;
	}

	/**
	 *
	 * @return Currency (DB Object)
	  **/
	function TotalAsCurrencyObject() {
		return DBField::create('Currency',$this->Total());
	}

	/**
	 *
	 * @return DataObject (Any type of Data Object that is buyable)
	  **/
	function Buyable($current = false) {
		$className = $this->BuyableClassName();
		if($this->BuyableID && $this->Version && !$current) {
			return Versioned::get_version($className, $this->BuyableID, $this->Version);
		}
		else {
			return DataObject::get_by_id($className, $this->BuyableID);
		}
	}

	/**
	 *
	 * @return String
	  **/
	function BuyableClassName() {
		$className = str_replace(Buyable::get_order_item_class_name_post_fix(), "", $this->ClassName);
		if(class_exists($className) && ClassInfo::is_subclass_of($className, "DataObject")) {
			return $className;
		}
		user_error($this->ClassName." does not have an item class: $className", E_USER_WARNING);
	}

	/**
	 *
	 * @return String
	  **/
	function BuyableTitle() {
		if($item = $this->Buyable()) {
			if($title = $item->Title) {
				return $title;
			}
			//This should work in all cases, because ultimately, it will return #ID - see DataObject
			return $item->getTitle();
		}
		user_error("No Buyable could be found for OrderItem with ID: ".$this->ID, E_USER_WARNING);
	}

	/**
	 *
	 * @return String (URLSegment)
	  **/
	function Link() {
		if($item = $this->Buyable()) {
			return $item->Link();
		}
		user_error("No Buyable could be found for OrderItem with ID: ".$this->ID, E_USER_WARNING);
	}

	/**
	 *
	 * @return String
	  **/
	function ProductTitle() {
		user_error("This function has been replaced by BuyableTitle", E_USER_NOTICE);
		return $this->BuyableTitle();
	}

	/**
	 *
	 * @return String
	  **/
	function TableTitle() {
		return $this->ClassName;
	}

	/**
	 *
	 * @return String
	  **/
	function TableSubTitle() {
		return "";
	}

	/**
	 *
	 * @return String
	  **/
	function CartQuantityID() {
		return $this->CartID() . '_Quantity';
	}

	/**
	 *
	 * @return String (URLSegment)
	  **/
	function CheckoutLink() {
		return CheckoutPage::find_link();
	}

	## Often Overloaded functions ##

	/**
	 *
	 * @return String (URLSegment)
	  **/
	function AddLink() {
		return ShoppingCart::add_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	/**
	 *
	 * @return String (URLSegment)
	  **/
	function IncrementLink() {
		return ShoppingCart::increment_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	/**
	 *
	 * @return String (URLSegment)
	  **/
	function DecrementLink() {
		return ShoppingCart::decrement_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	/**
	 *
	 * @return String (URLSegment)
	  **/
	function RemoveLink() {
		return ShoppingCart::remove_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	/**
	 *
	 * @return String (URLSegment)
	  **/
	function RemoveAllLink() {
		return ShoppingCart::remove_all_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	/**
	 *
	 * @return String (URLSegment)
	  **/
	function SetQuantityLink() {
		return ShoppingCart::set_quantity_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	/**
	 *
	 * @return String (URLSegment)
	  **/
	function SetSpecificQuantityItemLink($quantity) {
		return ShoppingCart::set_quantity_item_link($this->BuyableID, $this->ClassName, array_merge($this->linkParameters(), array("quantity" => $quantity)));
	}

	/**
	 *
	 * @return array for use as get variables in link
	  **/
	protected function linkParameters(){
		$array = array();
		$this->extend('updateLinkParameters',$array);
		return $array;
	}

}
