<?php
/**
 * @package ecommerce
 * @subpackage tests
 */
class OrderItemTest extends SapphireTest {
	function testConstructorSetsQuantity() {
		$o = new OrderItem(null, null, null, 2);
		$o->write();
		$this->assertEquals($o->Quantity, 2);
	}
	
	function testConstructorDefaultsToQuantityOfOne() {
		$o = new OrderItem();
		$o->write();
		$this->assertEquals($o->Quantity, 1);
	}
}
?>