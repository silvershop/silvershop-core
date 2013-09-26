<?php

class ShopTools{
	
	static function price_for_display($price){
		$currency = Payment::site_currency();
		$field = new Money("Price");
		$field->setAmount($price);
		$field->setCurrency($currency);
		return $field;
	}
	
	
}