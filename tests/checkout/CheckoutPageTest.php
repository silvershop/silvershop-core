<?php

class CheckoutPageTest extends FunctionalTest{

	public static $fixture_file = 'shop/tests/fixtures/shop.yml';
	public static $disable_theme = true;
	public static $use_draft_site = true;

	protected $controller;

	public function setUp() {
		parent::setUp();
		ShopTest::setConfiguration();
	}

	public function testActionsForm() {
		$order = $this->objFromFixture("Order", "unpaid");
		OrderManipulation::add_session_order($order);
		$this->get("/checkout/order/".$order->ID);

		//make payment action
		$this->post("/checkout/order/ActionsForm", array(
			'OrderID' => $order->ID,
			'PaymentMethod' => 'Dummy',
			'action_dopayment' => 'submit'
		));

		//cancel action
		$this->post("/checkout/order/ActionsForm", array(
			'OrderID' => $order->ID,
			'action_docancel' => 'submit'
		));

		$this->markTestIncomplete('Add some assertions');
	}

	public function testCanViewCheckoutPage() {
		$this->get('checkout');
		$this->markTestIncomplete("check order hasn't started");
	}

	public function testFindLink() {
		$this->checkoutpage = $this->objFromFixture('CheckoutPage', 'checkout');
		$this->checkoutpage->publish('Stage', 'Live');
		$link = CheckoutPage::find_link();
		$this->assertEquals(Director::baseURL() . 'checkout/', $link, 'find_link() returns the correct link to checkout.');
	}

}
