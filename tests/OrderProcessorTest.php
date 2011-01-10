<?php

/**
 * Unit Test for OrderProcessor. Test if the order processor will be used and changes will be applied.
 * Requires {@link UTOrderValidator} and {@link UTOrderProcessor} as order validator and processor classes.
 * 
 * @package ecommerce
 */
class OrderProcessorTest extends FunctionalTest {

	static $fixture_file = 'ecommerce/tests/ecommerce.yml';

	function setUp() {
		parent::setUp();
		
		/* Set the validator to test */
		Order::set_default_validator('OrderValidatorTest_MyValidator');
		Order::set_default_order_processor('OrderProcessorTest_MyProcessor');
	}

	/**
	 * Test the order processor. Check if order processor get applied.
	 */
	function testOrderValidator_BeforeProcessing_Member() {
		
		// quantity check shall pass anytime 
		
		$member= $this->objFromFixture('Member','member');
		$order = $this->objFromFixture('Order','order_processor_test');

		// set member id
		$order->MemberId = $member->ID;

		// set quantity of order items = 1
		$orderItems = $order->Items();
		foreach ($orderItems as $item) {
			$item->Quantity = 1;
			$item->write();
		}

		try {
			$order->validate_onBeforeProcessing();
		}
		catch(Exception $e) {
			// order test should pass (try OrderValidatorTest to see what fails).
			$this->assertFalse(1==1,"Exception: " . $e->getMessage() );
		}

		$order->executeOrderProcessor();

		try {
			$order->validate_onAfterProcessing();			
		}
		catch(Exception $e) {
			// order test should pass (try OrderValidatorTest to see what fails).
			$this->assertFalse(1==1,"Exception: " . $e->getMessage() );			
		}
		
	}
}

/**
 * Unit Test class for an order processing.
 *
 * @author Rainer Spittel
 * @package ecommerce
 * @subpackage tests
 */
class OrderProcessorTest_MyProcessor extends OrderProcessor implements TestOnly {
	
	public function process($order) {
		if ($order == null) {
			throw new Exception("Internal Error: no order object has been found.");
		}
		$this->processOrder($order);
	}
	
	/** 
	 * Changes the status of the order (set to 'Query').
	 * The {@link UTOrderValidator} will check if this status flag has been set.
	 */
	protected function processOrder($order) {
		
		$order->Status = 'Query';
		$order->write();
	}	
}

?>