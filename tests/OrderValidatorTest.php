<?php

/**
 * Unit Test for OrderValidator. Test if the order validator will be used during order processing.
 * Requires {$link OrderValidatorTest_MyValidator} as order validator classes.
 * 
 * @package ecommerce
 * @subpackage tests
 */
class OrderValidatorTest extends FunctionalTest {

	static $fixture_file = 'ecommerce/tests/ecommerce.yml';

	function setUp() {
		parent::setUp();
		
		/* Set the validator to test */
		Order::set_default_validator('OrderValidatorTest_MyValidator');
	}

	/**
	 * Test UI order validator for quantity test. This test checks if the 
	 * validation class will be executed.  
	 */
	function testOrderValidator_BeforeProcessing() {
		
		$member= $this->objFromFixture('Member','member');
		$order = $this->objFromFixture('Order','order_processor_test');

		// (1)
		// test should throw an exception -> no member set
		try {
			$order->validate_onBeforeProcessing();
			$this->assertFalse(1==1,"Exception expected (Member not set), but hasn't been thrown.");
		}
		catch(Exception $e) {
			
			$this->assertTrue($e->getMessage() == OrderValidatorTest_MyValidator::$err_NoMember,$e->getMessage());
		}
		
		// (2) set member (quantity is okay) 
		$order->MemberId = $member->ID;
		$order->write();

		// (3) set quantity to 0 and test should fail
		$orderItems = $order->Items();
		foreach ($orderItems as $item) {
			$item->Quantity = 0;
			$item->write();
		}
		
		//  (3) test with quanity == 0 shall fail!
		try {
			$order->validate_onBeforeProcessing();
			$this->assertFalse(1==1,"Exception expected (Quantity is 0), but Before-Processing didn't throw it.");
		}
		catch(Exception $e) {
			$this->assertTrue($e->getMessage() == OrderValidatorTest_MyValidator::$err_InsufficientAmount,$e->getMessage());
		}

		// (4) set quantity to 1 and test should pass
		$orderItems = $order->Items();
		foreach ($orderItems as $item) {
			$item->Quantity = 1;
			$item->write();
		}

		// (4) test should pass
		try {
			$order->validate_onBeforeProcessing();
		}
		catch(Exception $e) {
			$this->assertFalse(1==1,"Before-Processing throw an exception without a valid reason: ".$e->getMessage());
		}
		return;
	}

	/**
	 * Test UI order validator for quantity test. This test checks if the 
	 * validation class will be executed.  
	 */
	function testOrderValidator_AfterProcessing_Member() {
		return;
		$member= $this->objFromFixture('Member','member');
		$order = $this->objFromFixture('Order','order_processor_test');

		// (1)
		// test should throw an exception -> no member set
		try {
			$order->validate_onAfterProcessing();
			$this->assertFalse(1==1,"Exception expected (Member not set), but hasn't been thrown.");
		}
		catch(Exception $e) {
			
			$this->assertTrue($e->getMessage() == OrderValidatorTest_MyValidator::$err_NoMember,$e->getMessage());
		}
		
		// (2) set member (quantity is okay) 
		$order->MemberId = $member->ID;
		$order->write();

		// (3) set quantity to 0 and test should fail
		$orderItems = $order->Items();
		
		foreach ($orderItems as $item) {
			$item->Quantity = 0;
			$item->write();
		}
		
		//  (3) test with quaniity == 0 shall fail!
		try {
			$order->validate_onAfterProcessing();
			$this->assertFalse(1==1,"Exception expected (Quantity is 0), but Before-Processing didn't throw it.");
		}
		catch(Exception $e) {
			$this->assertTrue($e->getMessage() == OrderValidatorTest_MyValidator::$err_InsufficientAmount,$e->getMessage());
		}

		// (4) set quantity to 1 and test should pass
		$orderItems = $order->Items();
		
		foreach ($orderItems as $item) {
			$item->Quantity = 1;
			$item->write();
		}

		// (4) order status != 'Query' -> test should fail
		try {
			$order->validate_onAfterProcessing();
			$this->assertFalse(1==1,"Exception expected (Order Status is not Query), but Before-Processing didn't throw it.");
		}
		catch(Exception $e) {
			$this->assertTrue($e->getMessage() == OrderValidatorTest_MyValidator::$err_InvalidOrderStatus,$e->getMessage());
		}

		// set status and test should go through
		$order->Status = 'Query';
		$order->write();
		
		try {
			$order->validate_onAfterProcessing();
		}
		catch(Exception $e) {
			$this->assertFalse(1==1,"Before-Processing throw an exception without a valid reason.");
		}	
		return;			
	}
}

/**
 * Unit Test class for order validation. Perform following tests:
 * - Member ID set
 * - Quantity of order items > 0
 * - Order status set (in after processing)
 *
 * @author Rainer Spittel
 * @package ecommerce
 * @subpackage tests
 */
class OrderValidatorTest_MyValidator extends OrderValidator implements TestOnly {

	static $err_NoMember           =  'Unit Test: Member not set.';
	static $err_InsufficientAmount =  'Unit Test: Insufficient amount.';
	static $err_InvalidOrderStatus =  'Unit Test: Order Status not correct. Order Processor failed';

	/**
	 * Before Processing: Unit test example.
	 */
	public function onBeforeProcessing($order) {
		if ($order == null) {
			throw new Exception("Internal Error: no order object has been found.");
		}
		$this->checkOrderMember($order);	
		$this->checkOrderItem_Quantity($order);				
	}

	/**
	 * After Processing: Unit test example.
	 */
	public function onAfterProcessing($order) {
		if ($order == null) {
			throw new Exception("Internal Error: no order object has been found.");
		}

		$this->checkOrderMember($order);	
		$this->checkOrderItem_Quantity($order);	
		$this->checkOrderStatus($order);
	}
	
	/**
	 * Check if the order has a relation ship to member. 
	 */
	protected function checkOrderMember($order) {
		
		$memberID = $order->MemberId;
		if ($memberID == null or $memberID == 0) {
			throw new Exception(OrderValidatorTest_MyValidator::$err_NoMember);			
		}
	}

	/**
	 * Check if the quantity of all order items is equal or greater than 1.
	 */
	protected function checkOrderItem_Quantity($order) {
		
		$orderItems = $order->Items();
		foreach ($orderItems as $item) {
			$quantity = (int)$item->Quantity;
			if ($quantity <= 0) {
				throw new Exception(OrderValidatorTest_MyValidator::$err_InsufficientAmount);
			}
		}		
	}
	
	protected function checkOrderStatus($order) {
		if ($order->Status != 'Query') {
			throw new Exception(OrderValidatorTest_MyValidator::$err_InvalidOrderStatus);			
		}
		
	}
}
?>