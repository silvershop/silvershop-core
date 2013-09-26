<?php

class CanBeFreeCurrencyTest extends SapphireTest{
	
	function testField(){
		$field = new CanBeFreeCurrency("Test");
		$field->setValue(20000);
		$this->assertEquals($field->Nice(),"$20,000.00");
		$field->setValue(0);
		$this->assertEquals($field->Nice(),"<span class=\"free\">FREE</span>");
	}
	
}