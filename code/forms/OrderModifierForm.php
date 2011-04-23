<?php


/**
 * @description: this class is the base class for forms in the checkout form... we could do with more stuff here....
 *
 * @see OrderModifier
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/
class OrderModifierForm extends Form {

	protected $order;
	
	
	function __construct($controller = null, $name,$fields,$actions,$validator){
		if(!$controller){
			$controller = new OrderModifier_Controller();
		}
		parent::__construct($controller, $name, $fields, $actions, $validator);
	}
	

	function redirect($status = "success", $message = ""){
		return ShoppingCart::return_message($status, $message);
	}


}

