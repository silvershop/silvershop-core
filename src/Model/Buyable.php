<?php

namespace SilverShop\Model;

use SilverShop\Model\OrderItem;
use SilverStripe\Security\Member;

/**
 * A buyable class implies it as an associated order item that can be added
 * to an order.
 *
 * Enforce a createItem function on all objects that should be added to cart.
 * eg: Product class.
 *
 * @package shop
 */
interface Buyable
{
    /**
     * Create a new OrderItem to add to an order.
     *
     * @return OrderItem new OrderItem object
     */
    public function createItem(int $quantity = 1, array $filter = []): OrderItem;

    /**
     * Checks if the buyable can be purchased. If a buyable cannot be purchased
     * then the method should return false
     *
     * @param  Member|null $member   the Member that wants to purchase the buyable. Defaults to null
     * @param  int         $quantity the quantity to purchase. Defaults to 1
     * @return boolean true if the buyable can be purchased
     */
    public function canPurchase(?Member $member = null, int $quantity = 1): bool;

    /**
     * The price the customer gets this buyable for, with any additional
     * additions or subtractions.
     */
    public function sellingPrice(): float;
}
