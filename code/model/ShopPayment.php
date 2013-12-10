<?php
/**
 * Customisations to {@link Payment} specifically for the shop module.
 *
 * @package shop
 */
class ShopPayment extends DataExtension {
	
	static $has_one = array(
		'Order' => 'Order'
	);

}
