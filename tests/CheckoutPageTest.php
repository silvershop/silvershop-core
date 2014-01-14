<?php

class CheckoutPageTest extends SapphireTest{
	
	function testPayment(){
		
		//make payment
		$data = array(
			'name' => 'Joe Bloggs',
			'number' => '4242424242424242',
			'expiryMonth' => '',
			'expiryYear' => '',
			'cvv' => '123'
		);

		//$form->loadDataFrom($data);

		//$form->submitPayment($data, $form);

		//$payment = $order->Payments()->first();
		//$this->assertEquals("Dummy",$payment->Gateway);
	}

}