<?php

/**
 * Enforce a createItem function on all objects that should be added to cart.
 * 
 * @package shop
 */
interface Buyable{
	
	/**
	 * Create a new OrderItem to add to an order.
	 * 
	 * @param int $quantity
	 * @param boolean $write
	 */
	function createItem($quantity = 1, $write = true);
	
}