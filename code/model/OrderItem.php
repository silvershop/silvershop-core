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
	
	protected $_id;
	
	protected $_quantity;
	
	public static $db = array(
		'Quantity' => 'Int'
	);
	
	public static $casting = array(
		'UnitPrice' => 'Currency',
		'Total' => 'Currency'
	);
	
	public function __construct($object = null, $quantity = 1) {

		// Case 1: Constructed by getting OrderItem from DB
		if(is_array($object)) {
			$this->_quantity = $object['Quantity'];
		}
		
		// Case 2: Constructed in memory
		if(is_object($object)) {
			$this->_quantity = $quantity;
		}
		
		parent::__construct();
	}

	function updateForAjax(array &$js) {
		$total = DBField::create('Currency', $this->Total())->Nice();
		$js[] = array('id' => $this->TableTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->CartTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->CartQuantityID(), 'parameter' => 'innerHTML', 'value' => $this->getQuantity());
		$js[] = array('name' => $this->AjaxQuantityFieldName(), 'parameter' => 'value', 'value' => $this->getQuantity());
	}
	
	/**
	 * Populate some OrderItem object attributes before
	 * writing them to the OrderItem DB record.
	 * 
	 * PRECONDITION: The order item is not saved in the database yet.
	 */
	function onBeforeWrite() {
		parent::onBeforeWrite();
		
		$this->Quantity = $this->_quantity;
	}
	
	/**
	 * Get the quantity attribute from memory.
	 * @return int
	 */
	public function getQuantity() {
		return $this->_quantity;
	}
	
	/**
	 * Set the quantity attribute in memory.
	 * PRECONDITION: The order item is not saved in the database yet.
	 * 
	 * @param int $quantity The quantity to set
	 */
	public function setQuantityAttribute($quantity) {
		$this->_quantity = $quantity;
	}
	
	/**
	 * Increment the quantity attribute in memory by a given amount.
	 * PRECONDITION: The order item is not saved in the database yet.
	 * 
	 * @param int $quantity The amount to increment the quantity by.
	 */
	public function addQuantityAttribute($quantity) {
		$this->_quantity += $quantity;
	}
	
	function hasSameContent($orderItem) {
		return $orderItem instanceof OrderItem;
	}

	public function debug() {
		$id = $this->ID ? $this->ID : $this->_id;
		$quantity = $this->_quantity;
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
	
	protected function AjaxQuantityFieldName() {
		return $this->MainID() . '_Quantity';
	}
	
	function AjaxQuantityField() {
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript('ecommerce/javascript/ecommerce.js');
		
		$quantityName = $this->AjaxQuantityFieldName();
		$setQuantityLinkName = $quantityName . '_SetQuantityLink';
		$setQuantityLink = $this->setquantityLink();
		
		return <<<HTML
			<input name="$quantityName" class="ajaxQuantityField" type="text" value="$this->_quantity" size="3" maxlength="3" disabled="disabled"/>
			<input name="$setQuantityLinkName" type="hidden" value="$setQuantityLink"/>
HTML;
	}
	
	function Total() {
		return $this->UnitPrice() * $this->_quantity;
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
?>