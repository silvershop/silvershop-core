<?php
/**
 * @todo How does this class work in relation to Product?
 *
 * @package ecommerce
 */
class ProductVariation extends DataObject {

	static $db = array(
		'Title' => 'Text',
		'Price' => 'Currency'
	);

	static $has_one = array(
		'Product' => 'Product'
	);

	static $casting = array(
		'Title' => 'Text',
		'Price' => 'Currency'
	);

	static $versioning = array(
		'Stage'
	);

	static $extensions = array(
		"Versioned('Stage')"
	);
	
	static $summary_fields = array(
		'Title' => 'Title',
		'Price' => 'Price'
	);

	function getCMSFields() {
		$fields = array();
		$fields[] = new TextField('Title');
		$fields[] = new TextField('Price');
		
		$set = new FieldSet($fields);
		$this->extend('updateCMSFields', $set);
		return $set;
	}

	/*
	 * @Depreciated - use canPurchase instead
	 */
	function AllowPurchase() {
		return $this->canPurchase();
	}

	function canPurchase($member = null) {
		$allowpurchase = false;
		if($product = $this->Product())
			$allowpurchase = $this->Price && $product->AllowPurchase;
			
		$extended = $this->extendedCan('canPurchase', $member);
		if($extended !== null) return $extended;
		
		return $allowpurchase; 
	}

	//TODO: change this function to match Product->IsInCart

	/*
	 * Returns if the product variation is already in the shopping cart.
	 * Note : This function is usable in the Product Variation context because a
	 * ProductVariation_OrderItem only has a ProductVariation object in attribute
	 */
	function IsInCart() {
		return ($this->Item() && $this->Item()->Quantity > 0) ? true : false;
	}

	/*
	 * Returns the order item which contains the product variation
	 * Note : This function is usable in the ProductVariation context because a
	 * ProductVariation_OrderItem only has a ProductVariation object in attribute
	 */
	function Item() {
		if($item = ShoppingCart::get_item_by_id($this->ProductID,$this->ID))
			return $item;
		return new ProductVariation_OrderItem($this,0); //return dummy item so that we can still make use of Item
	}

	function addLink() {
		return $this->Item()->addLink($this->ProductID,$this->ID);
	}
}

class ProductVariation_OrderItem extends Product_OrderItem {

	protected $_productVariationID;

	protected $_productVariationVersion;

	static $db = array(
		'ProductVariationVersion' => 'Int'
	);

	static $has_one = array(
		'ProductVariation' => 'ProductVariation'
	);

	public function __construct($productVariation = null, $quantity = 1) {

		// Case 1 : Constructed by the static function get of DataObject

		if(is_array($productVariation)) {
			$this->ProductVariationID = $this->_productVariationID = $productVariation['ProductVariationID'];
			$this->ProductVariationVersion = $this->_productVariationVersion = $productVariation['ProductVariationVersion'];
			parent::__construct($productVariation, $quantity);
		}

		// Case 2 : Constructed in memory

		else if(is_object($productVariation)) {
			parent::__construct($productVariation->Product(), $quantity);
			$this->_productVariationID = $productVariation->ID;
 			$this->_productVariationVersion = $productVariation->Version;
		}

		else parent::__construct();
	}

	// ProductVariation Access Function

	public function ProductVariation($current = false) {
		if($current) return DataObject::get_by_id('ProductVariation', $this->_productVariationID);
		else return Versioned::get_version('ProductVariation', $this->_productVariationID, $this->_productVariationVersion);
	}

	## Overloaded functions ##

	function addLink() {
		return ShoppingCart::add_item_link($this->_productID,$this->_productVariationID);
	}

	function removeLink() {
		return ShoppingCart::remove_item_link($this->_productID,$this->_productVariationID);
	}

	function removeallLink() {
		return ShoppingCart::remove_all_item_link($this->_productID,$this->_productVariationID);
	}

	function setquantityLink() {
		return ShoppingCart::set_quantity_item_link($this->_productID,$this->_productVariationID);
	}

	function hasSameContent($orderItem) {
		$equals = parent::hasSameContent($orderItem);
		return $equals && $orderItem instanceof ProductVariation_OrderItem && $this->_productVariationID == $orderItem->_productVariationID && $this->_productVariationVersion == $orderItem->_productVariationVersion;
	}

	function UnitPrice() {
		return $this->ProductVariation()->Price;
	}

	function TableTitle() {
		return parent::TableTitle() . ' (' . $this->ProductVariation()->Title . ')';
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();

		$this->ProductVariationID = $this->_productVariationID;
		$this->ProductVariationVersion = $this->_productVariationVersion;
	}

	public function debug() {
		$title = $this->TableTitle();
		$productVariationID = $this->_productVariationID;
		$productVariationVersion = $this->_productVariationVersion;
		return parent::debug() .<<<HTML
			<h3>ProductVariation_OrderItem class details</h3>
			<p>
				<b>Title : </b>$title<br/>
				<b>ProductVariation ID : </b>$productVariationID<br/>
				<b>ProductVariation Version : </b>$productVariationVersion<br/>
			</p>
HTML;
	}
}
