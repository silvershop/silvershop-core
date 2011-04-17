<?php

/**
 *@description: adds a few functions to SiteTree to give each page some e-commerce related functionality.
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class EcommerceSiteTreeExtension extends DataObjectDecorator {

	/**
	 *@return Boolean
	 **/
	function ShopClosed() {
		$siteConfig = DataObject::get_one("SiteConfig");
		return $siteConfig->ShopClosed;
	}

	/**
	 *@return Order
	 **/
	function Cart() {
		return ShoppingCart::current_order();
	}

	/**
	 *@return Integer
	 **/
	public function NumItemsInCart() {
		$order = ShoppingCart::current_order();
		if($order) {
			return $order->TotalItems();
		}
		return 0;
	}

}

class EcommerceSiteTreeExtension_Controller extends Extension {

	function addAjaxCart() {
		ShoppingCart::add_requirements();
	}

	/**
	 *@return string
	 **/
	function SimpleCartLinkAjax() {
		return ShoppingCart::get_url_segment()."/showcart/";
	}

	/**
	 *@return Boolean
	 **/
	public function MoreThanOneItemInCart() {
		return $this->NumItemsInCart() > 1;
	}

	/**
	 *@return Float
	 **/
	public function SubTotalCartValue() {
		$order = ShoppingCart::current_order();
		return $order->SubTotal;
	}

	/**
	 *@return String (URLSegment)
	 **/
	public function AccountPageLink() {
		return AccountPage::find_link();
	}

	/**
	 *@return String (URLSegment)
	 **/
	public function CheckoutLink() {
		return CheckoutPage::find_link();
	}

}
