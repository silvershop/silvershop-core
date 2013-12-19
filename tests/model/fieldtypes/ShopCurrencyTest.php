<?php

class ShopCurrencyTest extends SapphireTest{
	
	function testField(){
		ShopCurrency::setCurrencySymbol("X");
		ShopCurrency::setDecimalDelimiter("|");
		ShopCurrency::setThousandDelimiter("-");
		$field = new ShopCurrency("Price");
		$field->setValue(-12345.56);
		$this->assertEquals("<span class=\"negative\">(X12-345|56)</span>", $field->Nice());
	}
	
}