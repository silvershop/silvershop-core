<?php
/**
 * @package ecommerce
 * @subpackage tests
 */
class PaymentTest extends SapphireTest {
	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	
	function old_testValidateWrongCurrency() {
		$o = new Order();
		$o->Currency = 'USD';
		$o->write();
		
		$p = new Payment();
		$p->Money->setCurrency('EUR'); //fails here
		$p->Money->setAmount(1.23);
		$p->OrderID = $o->ID;
		
		$validationResult = $p->validate();
		$this->assertContains(
			'Currency of payment', $validationResult->message()
		);
	}
}
?>