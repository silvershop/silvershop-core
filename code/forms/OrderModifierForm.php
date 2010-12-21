<?php

/**
 * @TODO What does this class do in relation to
 *
 * @see OrderModifier
 *
 * @package ecommerce
 */
class OrderModifierForm extends Form {
	/*
	protected $order;

	function __construct(Order $order, CheckoutPage $checkoutPage, $name, FieldSet $fields, FieldSet $actions, $validator = null) {
		$this->order = $order;

		parent::__construct($checkoutPage, $name, $fields, $actions, $validator);
	}
	*/

	function redirect($status = "success", $message = ""){
		if(Director::is_ajax()){
			return ShoppingCart::return_data($status, $message);
		}
		Director::redirect(CheckoutPage::find_link());

	}


}

