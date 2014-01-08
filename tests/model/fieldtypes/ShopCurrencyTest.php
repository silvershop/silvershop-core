<?php

class ShopCurrencyTest extends SapphireTest{
	
	function testField(){
		$cfg = Config::inst();
		$cfg->update('ShopCurrency','currency_symbol',"X");
		$cfg->update('ShopCurrency','decimal_delimiter',"|");
		$cfg->update('ShopCurrency','thousand_delimiter',"-");
		$cfg->update('ShopCurrency','negative_value_format',"- %s");

		$field = new ShopCurrency("Price");
		$field->setValue(-12345.56);
		$this->assertEquals("- X12-345|56", $field->Nice());
	}
	
}