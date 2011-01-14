<?php

/**
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class CartPage extends Page{

	public static $db = array();

	public static $has_one = array(
		'CheckoutPage' => 'CheckoutPage',
		'ContinuePage' => 'SiteTree'
	);

	public static $icon = 'ecommerce/images/icons/cart';

	function getCMSFields(){
		$fields = parent::getCMSFields();
		if($checkouts = DataObject::get('CheckoutPage')) {
			$fields->addFieldToTab('Root.Content.Links',new DropdownField('CheckoutPageID','Checkout Page',$checkouts->toDropdownMap()));
		}
		$fields->addFieldToTab('Root.Content.Links',new TreeDropdownField('ContinuePageID','Continue Page',"SiteTree"));
		return $fields;
	}

	function MenuTitle() {
		$count = 0;
		$cart = ShoppingCart::current_order();
		if($cart) {
			if($cart = $this->Cart()) {
				if($items = $cart->Items()) {
					$count = $items->count();
				}
			}
		}
		$v = $this->MenuTitle;
		if($count) {
			$v .= " (".$count.")";
		}
		return $v;
	}

}

class CartPage_Controller extends Page_Controller{

	public function init() {
		parent::init();
		ShoppingCart::add_requirements();
		Requirements::themedCSS('CheckoutPage');
	}

	function Order() {
		if($orderID = intval(Director::urlParam('Action'))) {
			return DataObject::get_by_id('Order', $orderID);
		}
		else {
			return ShoppingCart::current_order();
		}
	}

}



