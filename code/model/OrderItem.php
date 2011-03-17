<?php
/**
 * @description: An order item is a product which has been added to an order,
 * ready for purchase. It links to a buyable (e.g. a product)
 * @description: An order item is a product which has been added to an order,
 ** ready for purchase. An order item is typically a product itself,
 ** but also can include references to other information such as
 ** product attributes like colour, size, or type.
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/
class OrderItem extends OrderAttribute {

	protected static $disable_quantity_js = false;
		static function disable_quantity_js(){self::$disable_quantity_js = true;}
		static function get_quantity_js(){return self::$disable_quantity_js;}
		static function set_quantity_js($v){self::$disable_quantity_js = $v;}

	public static $db = array(
		'Quantity' => 'Double',
		'BuyableID' => 'Int', //TODO: surely one day this can become a has_one property
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

	protected function QuantityFieldName() {
		return $this->MainID() . '_Quantity';
	}

	/*
	 * @Depricated - use QuantityField
	 */
	function AjaxQuantityField(){
		return $this->QuantityField();
	}

	function QuantityField(){
		return new EcomQuantityField($this);
	}

	function Total() {
		$total = $this->UnitPrice() * $this->Quantity;
		$this->extend('updateTotal',$total);
		return $total;
	}

	function TotalAsCurrencyObject() {
		return DBField::create('Currency',$this->Total());
	}

	function TableTitle() {
		return $this->ClassName;
	}

	function TableSubTitle() {
		return "";
	}

	//TODO: Change "Item" to something that doesn't conflict with OrderItem
	function Buyable($current = false) {
		$className = $this->BuyableClassName();
		if($this->BuyableID && $this->Version && !$current) {
			return Versioned::get_version($className, $this->BuyableID, $this->Version);
		}
		else {
			return DataObject::get_by_id($className, $this->BuyableID);
		}
	}

	function BuyableClassName() {
		$className = str_replace(Buyable::get_order_item_class_name_post_fix(), "", $this->ClassName);
		if(class_exists($className) && ClassInfo::is_subclass_of($className, "DataObject")) {
			return $className;
		}
		else {
			user_error($this->ClassName." does not have an item class: $className", E_USER_WARNING);
		}
	}

	function BuyableTitle() {
		if($item = $this->Buyable()) {
			return $item->Title;
		}
		return "Title not found"; //TODO: ugly to fall back on
	}

	function Link() {
		if($item = $this->Buyable()) {
			return $item->Link();
		}
		return ""; //TODO: ugly to fall back on
	}

	function ProductTitle() {
		user_error("This function has been replaced by BuyableTitle", E_USER_NOTICE);
		return $this->BuyableTitle();
	}

	function CartQuantityID() {
		return $this->CartID() . '_Quantity';
	}

	function checkoutLink() {
		return CheckoutPage::find_link();
	}

	## Often Overloaded functions ##
	function AddLink() {
		return ShoppingCart::add_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	function IncrementLink() {
		return ShoppingCart::increment_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	function DecrementLink() {
		return ShoppingCart::decrement_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	function RemoveLink() {
		return ShoppingCart::remove_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	function RemoveAllLink() {
		return ShoppingCart::remove_all_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	function SetQuantityLink() {
		return ShoppingCart::set_quantity_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	function SetSpecificQuantityItemLink($quantity) {
		return ShoppingCart::set_quantity_item_link($this->BuyableID, $this->ClassName, array_merge($this->linkParameters(), array("quantity" => $quantity)));
	}

	protected function linkParameters(){
		$array = array();
		$this->extend('updateLinkParameters',$array);
		return $array;
	}

	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		// we must check for individual database types here because each deals with schema in a none standard way
		//can we use Table::has_field ???
		$db = DB::getConn();
		$fieldArray = $db->fieldList("OrderItem");
		$hasField =  isset($fieldArray["ItemID"]);
		if($hasField) {
			DB::query("UPDATE \"OrderItem\" SET \"OrderItem\".\"BuyableID\" = \"OrderItem\".\"ItemID\"");
 			DB::query("ALTER TABLE \"OrderItem\" CHANGE COLUMN \"ItemID\" \"_obsolete_ItemID\" Integer(11)");
 			DB::alteration_message('Moved ItemID to BuyableID in OrderItem', 'obsolete');
		}
	}

}
