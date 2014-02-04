<?php

/**
 * Globally useful tools
 */
class ShopTools{

	public static function price_for_display($price){
		$currency = ShopConfig::get_site_currency();
		$field = new Money("Price");
		$field->setAmount($price);
		$field->setCurrency($currency);
		return $field;
	}

}
