<?php

class CheckedCheckboxFieldTest extends SapphireTest{
	
	function testValidation(){
		$field = new CheckedCheckboxField("Name");
		$field->setValue("");
		$this->assertFalse($field->validate(new RequiredFields()));
		$field->setValue(0);
		$this->assertFalse($field->validate(new RequiredFields()));
		$field->setValue(1);
		$this->assertTrue($field->validate(new RequiredFields()));
	}
	
} 