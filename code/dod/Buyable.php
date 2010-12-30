<?php

class Buyable extends DataObjectDecorator {

	private static $shop_closed = null;

	protected static $order_item_class_name_post_fix = "_OrderItem";
		static function get_order_item_class_name_post_fix() {return self::$order_item_class_name_post_fix;}
		static function set_order_item_class_name_post_fix($v) {self::$order_item_class_name_post_fix = $v;}

	public function getCart() {
		HTTP::set_cache_age(0);
		return ShoppingCart::current_order();
	}

	/**
	 * Return the currency being used on the site.
	 * @return string Currency code, e.g. "NZD" or "USD"
	 */
	function Currency() {
		if(class_exists('Payment')) {
			return Payment::site_currency();
		}
	}

	/**
	 * Return the global tax information of the site.
	 * @return TaxModifier
	 */
	function TaxInfo() {
		$currentOrder = ShoppingCart::current_order();
		return $currentOrder->TaxInfo();
	}

	/*
	 * @Depreciated - use canPurchase instead
	 */
	function AllowPurchase() {
		user_error("this method has been Depreciated - use canPurchase", E_USER_NOTICE);
		return $this->owner->canPurchase();
	}

	function ShopClosed() {
		//CACHING!
		if(self::$shop_closed === null) {
			$sc = DataObject::get_one("SiteConfig");
			if($sc) {
				self::$shop_closed = $sc->ShopClosed;
			}
		}
		return self::$shop_closed;
	}

	/**
	 * Returns if the product is already in the shopping cart.
	 * Note : This function is usable in the Product context because a
	 * Product_OrderItem only has a Product object in attribute
	 *
	 * @return boolean
	 */
	function IsInCart() {
		return ($this->owner->OrderItem() && $this->OrderItem()->Quantity > 0) ? true : false;
	}

	function Item() {
		user_error("This method has been replaced by OrderItem to create clarity between buyable and order item.", E_USER_NOTICE);
		return $this->OrderItem();
	}

	function OrderItem() {
		$filter = "";
		$className = $this->owner->ClassName;
		$orderItemClassName = $this->classNameForOrderItem();
		$this->owner->extend('updateItemFilter',$filter);
		$item = ShoppingCart::get_order_item_by_buyableid($this->owner->ID, $orderItemClassName, $filter);

		if(!$item) {
			$item = new $orderItemClassName();
			$item->addBuyableToOrderItem($this->owner,0);
		}
		$this->owner->extend('updateDummyItem',$item);
		return $item; //return dummy item so that we can still make use of Item
	}

	//passing on shopping cart links ...is this necessary?? ...why not just pass the cart?
	function AddLink() {
		return ShoppingCart::add_item_link($this->owner->ID, $this->classNameForOrderItem(), $this->linkParameters());
	}

	function RemoveLink() {
		return ShoppingCart::remove_item_link($this->owner->ID, $this->classNameForOrderItem(), $this->linkParameters());
	}

	function RemoveAllLink() {
		return ShoppingCart::remove_all_item_link($this->owner->ID, $this->classNameForOrderItem(), $this->linkParameters());
	}

	function SetQuantityItemLink() {
		return ShoppingCart::set_quantity_item_link($this->owner->ID, $this->classNameForOrderItem(), $this->linkParameters());
	}

	function SetSpecificQuantityItemLink($quantity) {
		return ShoppingCart::set_quantity_item_link($this->owner->ID, $this->classNameForOrderItem(), array_merge($this->linkParameters(), array("quantity" => $quantity)));
	}

	protected function linkParameters(){
		$array = array();
		$this->owner->extend('updateLinkParameters',$array);
		return $array;
	}


	public function classNameForOrderItem() {
		$A = $this->owner->ClassName;
		$B = Buyable::get_order_item_class_name_post_fix();
		return $A.$B;
	}



}
