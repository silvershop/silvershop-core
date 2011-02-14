<?php

/**
 *@description: adds a few functions to SiteTree to give each page some e-commerce related functionality.
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class EcommerceSiteTreeExtension extends DataObjectDecorator {

	function ShopClosed() {
		$siteConfig = DataObject::get_one("SiteConfig");
		return $siteConfig->ShopClosed;
	}

	function Cart() {
		return ShoppingCart::current_order();
	}

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

	function SimpleCartLinkAjax() {
		return ShoppingCart::get_url_segment()."/showcart/";
	}

	public function MoreThanOneItemInCart() {
		return $this->NumItemsInCart() > 1;
	}

	public function SubTotalCartValue() {
		$order = ShoppingCart::current_order();
		return $order->SubTotal;
	}

	public function AccountPageLink() {
		return AccountPage::find_link();
	}

	public function CheckoutLink() {
		return CheckoutPage::find_link();
	}

}
