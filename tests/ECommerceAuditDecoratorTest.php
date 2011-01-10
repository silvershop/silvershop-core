<?php

class ECommerceAuditDecoratorTest extends SapphireTest {

	static $fixture_file = 'ecommerce/tests/ECommerceAuditDecoratorTest.yml';

	function setUp() {
		parent::setUp();
		
		Object::add_extension('Payment', 'ECommerceAuditDecorator');
		Object::add_extension('Payment', "Versioned('Stage')");
	}
	
	/**
	 * Test single payment (immediate successful payment).
	 */
	function testAuditLoggin_simple1() {
		$order = $this->objFromFixture('Order', 'order1');
		
		$payment = new Payment();	
		$payment->Status          = 'Success'; 
		$payment->Money->Amount   = 420.0; 
		$payment->Money->Currency = 'EUR';
		$payment->OrderID = $order->ID;
		$payment->write();
		
		$this->assertEquals(1,$payment->Version);

		$priceVersionObj = Versioned::get_version('Payment', $payment->ID, $payment->Version);
		
		$this->assertEquals(420.00,$priceVersionObj->OrderOutstandingBefore->Amount);
		$this->assertEquals(  0.00,$priceVersionObj->OrderOutstandingAfter->Amount);
	}


	/**
	 * Test single payment (immediate successful payment).
	 * The amount of payment will not be automatically be refunded after
	 * a payment was successful.
	 */
	function testAuditLoggin_simple2() {
		$order = $this->objFromFixture('Order', 'order1');
		
		// create full payment for the entire order
		$payment = new Payment();	
		$payment->Status          = 'Success'; 
		$payment->Money->Amount   = 420.0; 
		$payment->Money->Currency = 'EUR';
		$payment->OrderID = $order->ID;
		$payment->write();
		$id = $payment->ID;

		// check first version of payment
		$payment_v1 = Versioned::get_version('Payment', $payment->ID, 1);
		
		$this->assertEquals(420.00,$payment_v1->OrderOutstandingBefore->Amount);
		$this->assertEquals(  0.00,$payment_v1->OrderOutstandingAfter->Amount);

		DataObject::flush_and_destroy_cache();

		$payment_new = DataObject::get_by_id('Payment', $id);

		// changes the status of the payment after if has been set to success.
		$payment_new->Status  = 'Failed'; 
		$payment_new->write();

		$this->assertEquals(2,$payment_new->Version);

		// get log entry (out of versioned table)
		$versionObj = Versioned::get_version(
			'Payment', 
			$payment_new->ID, 
			2
		);

		// total outstanding still '0.00'
		$this->assertEquals(0.00,(float) $versionObj->OrderOutstandingBefore->Amount,"OrderOutstandingBefore not correct");
		$this->assertEquals(0.00,(float) $versionObj->OrderOutstandingAfter->Amount,"OrderOutstandingAfter not correct");

		$order = $this->objFromFixture('Order', 'order1');

		// create new payment 
		$payment = new Payment();	
		$payment->Status          = 'Success'; 
		$payment->Money->Amount   = 420.0; 
		$payment->Money->Currency = 'EUR';
		$payment->OrderID = $order->ID;
		$payment->write();
	}
	
	/**
	 * Test single payment (incomplete and then successful payment).
	 */
	function testAuditLoggin_simple3() {
		$order = $this->objFromFixture('Order', 'order1');
		
		$payment = new Payment();	
		$payment->Status          = 'Incomplete'; 
		$payment->Money->Amount   = 420.0; 
		$payment->Money->Currency = 'EUR';
		$payment->OrderID = $order->ID;
		$payment->write();

		$this->assertEquals(1,$payment->Version);

		$priceVersionObj = Versioned::get_version(
			'Payment', 
			$payment->ID, 
			$payment->Version
		);
		
		$this->assertEquals(420.00,$priceVersionObj->OrderOutstandingBefore->Amount,"Test 1");
		$this->assertEquals(420.00,$priceVersionObj->OrderOutstandingAfter->Amount,"Test 2");

		$payment->Status = 'Success'; 
		$payment->write();

		$this->assertEquals(2,$payment->Version);

		$priceVersionObj = Versioned::get_version(
			'Payment', 
			$payment->ID, 
			2
		);

		$this->assertEquals(420.00,$priceVersionObj->OrderOutstandingBefore->Amount,"Test 3");
		$this->assertEquals(0.00,(float)$priceVersionObj->OrderOutstandingAfter->Amount,"Test 4");
	}

	/**
	 * Test single payment (immediate Failure payment).
	 */
	function testAuditLoggin_failure1() {
		$order = $this->objFromFixture('Order', 'order1');
		
		$payment = new Payment();	
		$payment->Status          = 'Failure'; 
		$payment->Money->Amount   = 420.0; 
		$payment->Money->Currency = 'EUR';
		$payment->OrderID = $order->ID;
		$payment->write();

		$priceVersionObj = Versioned::get_version(
			'Payment', 
			$payment->ID, 
			$payment->Version
		);
		$this->assertEquals(420.00,$priceVersionObj->OrderOutstandingBefore->Amount);
		$this->assertEquals(420.00,$priceVersionObj->OrderOutstandingAfter->Amount);
	}

	/**
	 * Test single payment (immediate Failure payment).
	 */
	function testAuditLoggin_failure2() {
		$order = $this->objFromFixture('Order', 'order1');
		
		$payment = new Payment();	
		$payment->Status          = 'Pending'; 
		$payment->Money->Amount   = 420.0; 
		$payment->Money->Currency = 'EUR';
		$payment->OrderID = $order->ID;
		$payment->write();

		$priceVersionObj = Versioned::get_version(
			'Payment', 
			$payment->ID, 
			$payment->Version
		);
		$this->assertEquals(420.00,$priceVersionObj->OrderOutstandingBefore->Amount);
		$this->assertEquals(420.00,$priceVersionObj->OrderOutstandingAfter->Amount);
	}
	
	/**
	 * Test single payment with invalid currency.
	 */
	function testAuditLoggin_failure3() {
		$order = $this->objFromFixture('Order', 'order1');
		
		$payment = new Payment();	
		$payment->Status          = 'Pending'; 
		$payment->Money->Amount   = 420.0; 
		$payment->Money->Currency = 'USD';
		$payment->OrderID = $order->ID;
		
		try {
			$payment->write();
		}
		catch (Exception $e) {
			return;
		}
		$this->assertEquals(1,0);
	}
	
	/**
	 * Test multiple payments for one order (immediate successful payment).
	 *
	 * @todo Interesting side effect: when adding a payment option, it does
	 * not go into the order collection directly and total-outstanding
	 * returns the wrong value. 
	 * Order object need to be reloaded (forced).
	 */
	function testAuditLoggin_complex1() {
		$order = $this->objFromFixture('Order', 'order1');
		
		$orderId = $order->ID;
		// perform first payment (100.00 of 420.00 in total)
		$payment = new Payment();	
		$payment->Status          = 'Success'; 
		$payment->Money->Amount   = 100.0; 
		$payment->Money->Currency = 'EUR';
		$payment->OrderID = $orderId;
		$payment->write();
		
		$this->assertEquals(1,$payment->Version);

		$obj = Versioned::get_version('Payment', $payment->ID, $payment->Version);		
		$this->assertEquals(420.00,$obj->OrderOutstandingBefore->Amount);
		$this->assertEquals(320.00,$obj->OrderOutstandingAfter->Amount);

		DataObject::flush_and_destroy_cache();
		$order = DataObject::get_by_id('Order', $orderId);
		
		// perform second payment (remaining 320.00)
		$payment = new Payment();	
		$payment->Status          = 'Success'; 
		$payment->Money->Amount   = 320.0; 
		$payment->Money->Currency = 'EUR';
		$payment->OrderID = $order->ID;
		$payment->write();

		$this->assertEquals(1,$payment->Version);

		$obj = Versioned::get_version('Payment', $payment->ID, $payment->Version);		
		$this->assertEquals(320.00,(float)$obj->OrderOutstandingBefore->Amount);
		$this->assertEquals(  0.00,(float)$obj->OrderOutstandingAfter->Amount);
	}	
}

?>