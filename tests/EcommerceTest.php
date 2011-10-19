<?php
/**
 * High level tests of the whole ecommerce module.
 *
 * @package ecommerce
 * @subpackage tests
 */
class EcommerceTest extends FunctionalTest {

	static $fixture_file = 'ecommerce/tests/ecommerce.yml';

	static $disable_theme = true;

	static $use_draft_site = true;

	function setUp() {
		parent::setUp();
		/* Let's check that we have the Payment module installed properly */
		$this->assertTrue(class_exists('Payment'), 'Payment module is installed.');
	}

	function testExampleConfig(){
		require_once(BASE_PATH.DIRECTORY_SEPARATOR.ECOMMERCE_DIR.DIRECTORY_SEPARATOR.'example_config.php');

		//TODO: test each configuration
	}

	function testCanViewCheckoutPage() {
		$this->get('checkout/');
	}

	function testCanViewProductPage() {
		$p1a = $this->objFromFixture('Product', 'tshirt');
		$p2a = $this->objFromFixture('Product', 'socks');
		$this->get(Director::makeRelative($p1a->Link()));
		$this->get(Director::makeRelative($p2a->Link()));
	}

	function testCanViewProductGroupPage() {
		$g1 = $this->objFromFixture('ProductGroup', 'g1');
		$g2 = $this->objFromFixture('ProductGroup', 'g2');
		$this->get(Director::makeRelative($g1->Link()));
		$this->get(Director::makeRelative($g2->Link()));
	}

	function old_testCanViewAccountPage() {
		/* If we're not logged in we get directed to the log-in page */
		$this->get('account/');
		$this->assertPartialMatchBySelector('p.message', array(
			"You'll need to login before you can access the account page. If you are not registered, you won't be able to access it until you make your first order, otherwise please enter your details below.", ));

		/* But if we're logged on you can see */
		$this->session()->inst_set('loggedInAs', $this->idFromFixture('Member', 'member'));
		$this->get('account/');
		$this->assertPartialMatchBySelector('#PastOrders h3', array('Your Order History'));
	}

	static function setConfiguration(){
		$ds = DIRECTORY_SEPARATOR;
		include(BASE_PATH.$ds.ECOMMERCE_DIR.$ds.'tests'.$ds.'test_config.php');
	}

}