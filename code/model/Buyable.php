<?php

/**
 * A buyable class implies it as an associated order item that can be added
 * to an order.
 * 
 * Enforce a createItem function on all objects that should be added to cart.
 * eg: Product class.
 * 
 * @package shop
 */
interface Buyable{
	
	/**
	 * Create a new OrderItem to add to an order.
	 * 
	 * @param int $quantity
	 * @param boolean $write
	 * @return OrderItem new OrderItem object
	 */
	function createItem($quantity = 1, $filter = array());
	
	/**
	 * Checks if the buyable can be purchased.
	 * 
	 * @return boolean
	 */
	function canPurchase();
	
	/**
	 * The price the customer gets this buyable for, with any additional additions or subtractions.
	 */
	function sellingPrice();
	
}