<?php

class I18nDatetimeTest extends SapphireTest{

	public function testField() {

		$field = new I18nDatetime();
		$field->setValue('2012-11-21 11:54:13');

		$field->Nice();
		$field->NiceDate();
		$field->Nice24();

		$this->markTestIncomplete('assertions!');
	}

}
