<?php
class GlobalTaxModifierTest extends SapphireTest{
	
	function setUp() {
		parent::setUp();

		Order::set_modifiers(array(
			'GlobalTaxModifier'
		));
	
		// Set the tax configuration on a per-country basis to test
		GlobalTaxModifier::set_for_country('NZ', 0.125, 'GST', 'inclusive');
		GlobalTaxModifier::set_for_country('UK', 0.175, 'VAT', 'exclusive');
	}
	
	function testModification(){
		$modifier = new GlobalTaxModifier();
		$this->assertEquals(15,$modifier->value(100)); //15% tax default
	}
	
}