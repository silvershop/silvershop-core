<?php
/**
 * An order item is a product which has been added to an order,
 * ready for purchase. An order item is typically a product itself,
 * but also can include references to other information such as
 * product attributes like colour, size, or type.
 *
 * @package ecommerce
 */
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

	public static $casting = array(
		'UnitPrice' => 'Currency',
		'Total' => 'Currency'
	);

	######################
	## CMS CONFIG ##
	######################

	public static $searchable_fields = array(
		"OrderID",
		"Title" => "PartialMatchFilter",
		"TableTitle" => "PartialMatchFilter",
		"CartTitle" => "PartialMatchFilter",
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

	public static $plural_name = "Order Items";

	public function addBuyable($object, $quantity = 1) {
		parent::addBuyable($object);
		$this->Version = $object->Version;
		$this->BuyableID = $object->ID;
		$this->Quantity = $quantity;
	}

	function updateForAjax(array &$js) {
		$total = $this->Total()->Nice();
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

	function TableTitle() {
		return $this->ClassName;
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

	function ProductTitle() {
		return $this->BuyableTitle();
	}

	function CartQuantityID() {
		return $this->CartID() . '_Quantity';
	}

	function checkoutLink() {
		return CheckoutPage::find_link();
	}


	## Often Overloaded functions ##

	function addLink() {
		return ShoppingCart::add_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	function removeLink() {
		return ShoppingCart::remove_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	function removeAllLink() {
		return ShoppingCart::remove_all_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	function setQuantityLink() {
		return ShoppingCart::set_quantity_item_link($this->BuyableID, $this->ClassName,$this->linkParameters());
	}

	function linkParameters(){
		$array = array();
		$this->extend('updateLinkParameters',$array);
		return $array;
	}



	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		// we must check for individual database types here because each deals with schema in a none standard way
		//can we use Table::has_field ???
		$db = DB::getConn();
		if( $db instanceof PostgreSQLDatabase ){
      $exist = DB::query("SELECT column_name FROM information_schema.columns WHERE table_name ='OrderItem' AND column_name = 'ItemID'")->numRecords();
		}
		else{
			// default is MySQL - broken for others, each database conn type supported must be checked for!
      $exist = DB::query("SHOW COLUMNS FROM \"OrderItem\" LIKE 'ItemID'")->numRecords();
		}
 		if($exist > 0) {
			DB::query("UPDATE \"OrderItem\" SET \"OrderItem\".\"BuyableID\" = \"OrderItem\".\"ItemID\"");
 			DB::query("ALTER TABLE \"OrderItem\" CHANGE COLUMN \"ItemID\" \"_obsolete_ItemID\" Integer(11)");
 			DB::alteration_message('Moved ItemID to BuyableID in OrderItem', 'obsolete');
		}
	}

}
