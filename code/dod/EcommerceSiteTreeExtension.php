<?php

/**
 *@description: adds a few functions to SiteTree to give each page some e-commerce related functionality.
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class EcommerceSiteTreeExtension extends DataObjectDecorator {

	function MenuTitleEcommerce() {
		$v = $this->owner->MenuTitle;
		if($this->owner instanceOf CheckoutPage) {
			$count = 0;
			$cart = ShoppingCart::current_order();
			if($cart) {
				if($cart = $this->Cart()) {
					if($items = $cart->Items()) {
						$count = $items->count();
					}
				}
			}
			if($count) {
				$v .= " (".$count.")";
			}
		}
		return $v;
	}

	function ShopClosed() {
		$siteConfig = DataObject::get_one("SiteConfig");
		return $siteConfig->ShopClosed;
	}

}

class EcommerceSiteTreeExtension_Controller extends Extension {

	function addAjaxCart() {
		ShoppingCart::add_requirements();
	}

	function SimpleCartLinkAjax() {
		return ShoppingCart::get_url_segment()."/showcart/";
	}

	function Cart() {
		return ShoppingCart::current_order();

	}

	public function NumItemsInCart() {
		$cart = $this->Cart();
		if($cart) {
			if($cart = $this->Cart()) {
				if($items = $cart->Items()) {
					return $items->count();
				}
			}
		}
		return 0;
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
