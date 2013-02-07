<?php
/**
 * An order item is a product which has been added to an order,
 * ready for purchase. An order item is typically a product itself,
 * but also can include references to other information such as
 * product attributes like colour, size, or type.
 *
 * @package shop
 */
class OrderItem extends OrderAttribute {

	public static $db = array(
		'Quantity' => 'Int',
		'UnitPrice' => 'Currency'
	);

	public static $casting = array(
		'UnitPrice' => 'Currency',
		'Total' => 'Currency'
	);

	public static $searchable_fields = array(
		'OrderID' => array(
			'title' => 'Order ID',
			'field' => 'TextField'
		),
		"Title" => "PartialMatchFilter",
		"TableTitle" => "PartialMatchFilter",
		"CartTitle" => "PartialMatchFilter",
		"UnitPrice",
		"Quantity",
		"Total"
	);

	public static $field_labels = array();

	public static $summary_fields = array(
		"Order.ID" => "Order ID",
		"TableTitle" => "Title",
		"UnitPrice" => "Unit Price" ,
		"Quantity" => "Quantity" ,
		"Total" => "Total Price" ,
	);
	
	static $required_fields = array();
	static $buyable_relationship = "Product";
	static $disable_quantity_js = false;
	
	public static $singular_name = "Order Item";
	function i18n_singular_name() { return _t("OrderItem.SINGULAR", self::$singular_name); }
	public static $plural_name = "Order Items";
	function i18n_plural_name() { return _t("OrderItem.PLURAL", self::$plural_name); }
	public static $default_sort = "\"Created\" DESC";

	static function disable_quantity_js(){
		self::$disable_quantity_js = true;
	}
	
	/**
	 * Get the buyable object related to this item.
	 */
	function Buyable(){
		return $this->{$this->stat('buyable_relationship')}();
	}
	
	/**
	 * Get unit price for this item.
	 * Fetches from db, or Buyable, based on order status.
	 */
	function UnitPrice() {
		if($this->Order()->IsCart()){
			$buyable = $this->Buyable();
			$unitprice = ($buyable) ? $buyable->sellingPrice() : $this->UnitPrice;
			$this->extend('updateUnitPrice',$unitprice);
			return $this->UnitPrice = $unitprice;
		}
		return $this->UnitPrice;
	}
	
	/**
	 * Prevent unit price ever being below 0
	 */
	function setUnitPrice($val){
		if($val < 0){
			$val = 0;
		}
		$this->setField("UnitPrice", $val);
	}

	/**
	 * Get calculated total, or stored total
	 * depending on whether the order is in cart
	 */
	function Total() {
		if($this->Order()->IsCart()){ //always calculate total if order is in cart
			return $this->calculatetotal();
		}
		return $this->CalculatedTotal; //otherwise get value from database
	}

	/**
	 * Calculates the total for this item.
	 * Generally called by onBeforeWrite
	 */
	protected function calculatetotal(){
		$total = $this->UnitPrice() * $this->Quantity;
		$this->extend('updateTotal',$total);
		$this->CalculatedTotal = $total;
		return $total;
	}
	
	/**
	 * Last time saving/processing, before item permanently stored in database.
	 * This should only be called when order is transformed from
	 * Cart to Order, aka being 'placed'.
	 */
	function place(){}
	
	/**
	 * Intersects this item's required_fields with the data record.
	 * This is used for uniquely adding items to the cart.
	 */
	function uniquedata(){
		$required = $this->stat('required_fields'); //TODO: also combine with all ancestors of this->class
		$data = $this->record;
		$unique = array();
		//reduce record to only required fields
		if($required){
			foreach($required as $field){
				if($this->has_one($field)){
					$field = $field."ID"; //add ID to hasones
				}
				$unique[$field] = $this->$field;
			}
		}
		return $unique;
	}
	
	/**
	 * Force quantity to be at least 1, and recalculate total
	 * before saving to database.
	 */
	function onBeforeWrite() {
		parent::onBeforeWrite();
		if($this->OrderID && $this->Order() && $this->Order()->isCart()){
			//always keep quantity above 0
			if($this->Quantity < 1){
				$this->Quantity = 1;
			}
			$this->calculatetotal();
		}
	}
	
	/*
	 * Event handler called when an order is fully paid for.
	 */
	function onPayment() {
		$this->extend('onPayment',null);
	}
	
	/**
	 * Get the buyable image.
	 * Also serves as a standardised placeholder for overriding in subclasses.
	 */
	function Image(){
		if(method_exists($this->Buyable(),'Image')){
			return $this->Buyable()->Image();
		}
		return null;
	}

	function TableTitle() {
		return $this->i18n_singular_name();
	}

	function QuantityField(){
		return new EcomQuantityField($this);
	}
	
	function addLink() {
		return ShoppingCart_Controller::add_item_link($this->Buyable(),$this->uniquedata());
	}
	
	function removeLink() {
		return ShoppingCart_Controller::remove_item_link($this->Buyable(),$this->uniquedata());
	}
	
	function removeallLink() {
		return ShoppingCart_Controller::remove_all_item_link($this->Buyable(),$this->uniquedata());
	}
	
	function setquantityLink() {
		return ShoppingCart_Controller::set_quantity_item_link($this->Buyable(),$this->uniquedata());
	}
	
	//Deprecated, to be removed or factored out

	/**
	* @deprecated 1.0 - use QuantityField instead
	*/
	function AjaxQuantityField(){
		return $this->QuantityField();
	}
	
	/**
	 * @deprecated
	 */
	function checkoutLink() {
		return CheckoutPage::find_link();
	}
	
	protected function QuantityFieldName() {
		return $this->MainID() . '_Quantity';
	}
	
	function CartQuantityID() {
		return $this->CartID() . '_Quantity';
	}
	
	function updateForAjax(array &$js) {
		$total = DBField::create('Currency', $this->Total())->Nice();
		$js[] = array('id' => $this->TableTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->CartTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->CartQuantityID(), 'parameter' => 'innerHTML', 'value' => $this->Quantity);
		$js[] = array('name' => $this->QuantityFieldName(), 'parameter' => 'value', 'value' => $this->Quantity);
	}
}