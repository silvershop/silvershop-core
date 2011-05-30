<?php
/**
 * @package ecommerce
 * @subpackage tests
 */
class OrderItemTest extends SapphireTest {
	
	/* OLD (to be removed) */	
	
	function old_testConstructorSetsQuantity() {
		$o = new OrderItem(null, null, null, 2);
		$o->write();
		$this->assertEquals($o->Quantity, 2);
	}
	
	function old_testConstructorDefaultsToQuantityOfOne() {
		$o = new OrderItem();
		$o->write();
		$this->assertEquals($o->Quantity, 1);
	}
}
?>