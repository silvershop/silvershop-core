<?php

class ShopCountryTest extends SapphireTest{
	
	function testField(){
		$field = new ShopCountry("Country");
		$field->setValue("ABC");
		$this->assertEquals($field->forTemplate(),"ABC");
		$field->setValue("NZ");
		$this->assertEquals($field->forTemplate(),"New Zealand");
	}
	
}