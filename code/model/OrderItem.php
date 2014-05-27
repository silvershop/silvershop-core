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

	private static $db = array(
		'Quantity' => 'Int',
		'UnitPrice' => 'Currency'
	);

	private static $casting = array(
		'UnitPrice' => 'Currency',
		'Total' => 'Currency'
	);

	private static $searchable_fields = array(
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

	private static $summary_fields = array(
		"Order.ID" => "Order ID",
		"TableTitle" => "Title",
		"UnitPrice" => "Unit Price" ,
		"Quantity" => "Quantity" ,
		"Total" => "Total Price" ,
	);

	private static $required_fields = array();
	private static $buyable_relationship = "Product";

	private static $singular_name = "Item";
	private static $plural_name = "Items";
	private static $default_sort = "\"Created\" DESC";

	/**
	 * Get the buyable object related to this item.
	 */
	public function Buyable() {
		return $this->{self::config()->buyable_relationship}();
	}

	/**
	 * Get unit price for this item.
	 * Fetches from db, or Buyable, based on order status.
	 */
	public function UnitPrice() {
		if($this->Order()->IsCart()){
			$buyable = $this->Buyable();
			$unitprice = ($buyable) ? $buyable->sellingPrice() : $this->UnitPrice;
			$this->extend('updateUnitPrice', $unitprice);
			return $this->UnitPrice = $unitprice;
		}
		return $this->UnitPrice;
	}

	/**
	 * Prevent unit price ever being below 0
	 */
	public function setUnitPrice($val) {
		if($val < 0){
			$val = 0;
		}
		$this->setField("UnitPrice", $val);
	}

	/**
	 * Prevent quantity being below 1.
	 * 0 quantity means it should instead be deleted.
	 * @param int $val new quantity to set
	 */
	public function setQuantity($val) {
		$val = $val < 1 ? 1 : $val;
		$this->setField("Quantity", $val);
	}

	/**
	 * Get calculated total, or stored total
	 * depending on whether the order is in cart
	 */
	public function Total() {
		if($this->Order()->IsCart()){ //always calculate total if order is in cart
			return $this->calculatetotal();
		}
		return $this->CalculatedTotal; //otherwise get value from database
	}

	/**
	 * Calculates the total for this item.
	 * Generally called by onBeforeWrite
	 */
	protected function calculatetotal() {
		$total = $this->UnitPrice() * $this->Quantity;
		$this->extend('updateTotal', $total);
		$this->CalculatedTotal = $total;
		return $total;
	}

	/**
	 * Intersects this item's required_fields with the data record.
	 * This is used for uniquely adding items to the cart.
	 */
	public function uniquedata() {
		$required = self::config()->required_fields; //TODO: also combine with all ancestors of this->class
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
	 * Recalculate total before saving to database.
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if($this->OrderID && $this->Order() && $this->Order()->isCart()){
			$this->calculatetotal();
		}
	}

	/*
	 * Event handler called when an order is fully paid for.
	 */
	public function onPayment() {
		$this->extend('onPayment');
	}

	/**
	 * Event handlier called for last time saving/processing,
	 * before item permanently stored in database.
	 * This should only be called when order is transformed from
	 * Cart to Order, aka being 'placed'.
	 */
	public function onPlacement() {
		$this->extend('onPlacement');
	}

	/**
	 * Get the buyable image.
	 * Also serves as a standardised placeholder for overriding in subclasses.
	 */
	public function Image() {
		return $this->Buyable()->Image();
	}

	public function QuantityField() {
		return Injector::inst()->create('ShopQuantityField', $this);
	}

	public function addLink() {
		return ShoppingCart_Controller::add_item_link($this->Buyable(), $this->uniquedata());
	}

	public function removeLink() {
		return ShoppingCart_Controller::remove_item_link($this->Buyable(), $this->uniquedata());
	}

	public function removeallLink() {
		return ShoppingCart_Controller::remove_all_item_link($this->Buyable(), $this->uniquedata());
	}

	public function setquantityLink() {
		return ShoppingCart_Controller::set_quantity_item_link($this->Buyable(), $this->uniquedata());
	}

}
