<?php
/**
* @package shop
* @subpackage tests
*/
class SimpleShippingModifierTest extends SapphireTest{
	
	function setUp(){
		parent::setUp();
		// Set up the simple shipping calculator to test
		SimpleShippingModifier::set_default_charge(10);
		SimpleShippingModifier::set_charges_for_countries(array(
			'NZ' => 5,
			'UK' => 20
		));
	}
	
	function testShippingCalculation() {
		$modifier = new SimpleShippingModifier();
		$this->assertEquals(10,$modifier->value(100));
		$this->assertEquals(110,$modifier->modify(100));
		
	}
	
}