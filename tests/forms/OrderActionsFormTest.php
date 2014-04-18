<?php

class OrderActionsFormTest extends FunctionalTest{

	protected static $fixture_file = "shop/tests/fixtures/shop.yml";

	public function setUp() {
		parent::setUp();
		ShopTest::setConfiguration();
	}

	public function testForm() {
		$order = $this->objFromFixture("Order", "unpaid");
		OrderManipulation::add_session_order($order);
		$controller = new CheckoutPage_Controller(
			$this->objFromFixture("CheckoutPage", "checkout")
		);
		$form = new OrderActionsForm($controller, "ActionsForm", $order);
		$form->dopayment(array(
			'OrderID' => $order->ID,
			'PaymentMethod' => 'Dummy'
		), $form);
		$this->assertEquals(1, $order->Payments()->count());
	}

}
