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
interface Buyable {

	/**
	 * Create a new OrderItem to add to an order.
	 *
	 * @param int $quantity
	 * @param boolean $write
	 * @return OrderItem new OrderItem object
	 */
	public function createItem($quantity = 1, $filter = array());

	/**
	 * Checks if the buyable can be purchased. If a buyable cannot be purchased
	 * then the method should return a {@link ShopBuyableException} containing
	 * the messaging.
	 *
	 * @throws ShopBuyableException
	 *
	 * @return boolean
	 */
	public function canPurchase($member = null, $quantity = 1);

	/**
	 * The price the customer gets this buyable for, with any additional 
	 * additions or subtractions.
	 *
	 * @return ShopCurrency
	 */
	public function sellingPrice();

}
