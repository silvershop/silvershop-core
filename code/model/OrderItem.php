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

	static $disable_quantity_js = false;

	public static $db = array(
		'Quantity' => 'Int',
		'Amount' => 'Currency'
	);

	public static $casting = array(
		'UnitPrice' => 'EcommerceCurrency',
		'Total' => 'EcommerceCurrency'
	);

	######################
	## CMS CONFIG ##
	######################

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

	public static $singular_name = "Order Item";
	public static $plural_name = "Order Items";
	public static $default_sort = "\"Created\" DESC";

	public function __construct($object = null, $quantity = 1) {

		if(is_array($object))
			parent::__construct($object);
		else
			parent::__construct();
	}

	static function disable_quantity_js(){
		self::$disable_quantity_js = true;
	}

	function updateForAjax(array &$js) {
		$total = DBField::create('EcommerceCurrency', $this->Total())->Nice();
		$js[] = array('id' => $this->TableTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->CartTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->CartQuantityID(), 'parameter' => 'innerHTML', 'value' => $this->Quantity);
		$js[] = array('name' => $this->QuantityFieldName(), 'parameter' => 'value', 'value' => $this->Quantity);
	}

	/**
	 * Populate some OrderItem object attributes before
	 * writing them to the OrderItem DB record.
	 *
	 * PRECONDITION: The order item is not saved in the database yet.
	 */
	function onBeforeWrite() {
		parent::onBeforeWrite();
		//always keep quantity above 0
		if($this->Quantity < 1)
			$this->Quantity = 1;

		$this->CalculateTotal();
	}

	function hasSameContent($orderItem) {
		return $orderItem instanceof OrderItem;
	}

	public function debug() {
		$id = $this->ID;
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
		if((int)$this->Amount)
			return $this->Amount;
		return $this->CalculateTotal(); //revert to calculating total if stored value not available
	}

	/**
	 * Calculates the total for this item.
	 * Generally called by onBeforeWrite
	 */
	function CalculateTotal(){
		$total = $this->UnitPrice() * $this->Quantity;
		$this->extend('updateTotal',$total);
		$this->Amount = $total;
		return $total;
	}

	function TableTitle() {
		return 'Product';
	}

	function ProductTitle() {
		return $this->Product()->Title;
	}

	function CartQuantityID() {
		return $this->CartID() . '_Quantity';
	}

	function checkoutLink() {
		return CheckoutPage::find_link();
	}

}