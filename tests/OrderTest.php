<?php
/**
 * @package ecommerce
 * @subpackage tests
 * 
 */
class OrderTest extends FunctionalTest {
	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	
	protected $orig = array();

	function setUp() {
		parent::setUp();
		$this->objFromFixture('Product', 'mp3player')->publish('Stage','Live');
		$this->objFromFixture('Product', 'socks')->publish('Stage','Live');
		$this->objFromFixture('Product', 'beachball')->publish('Stage','Live');
		$this->objFromFixture('Product', 'hdtv')->publish('Stage','Live');
		
		$this->objFromFixture('CheckoutPage', 'checkout')->publish('Stage','Live');
	}
	
	function tearDown() {
		parent::tearDown();
	}
	
	function testPlaceOrderWithForm(){
		
		/* Retrieve the product to compare from fixture */
		$mp3player = $this->objFromFixture('Product', 'mp3player');
		$socks = $this->objFromFixture('Product', 'socks');
		
		//place items in cart
		$this->get(ShoppingCart::add_item_link($mp3player->ID)); //add item via url
		$this->get(ShoppingCart::add_item_link($mp3player->ID)); //add another
		$this->get(ShoppingCart::add_item_link($socks->ID)); //add a different product
		
		$cart = ShoppingCart::current_order();
		
		//submit checkout page
		$this->placeOrder(
			'James',
			'Brown',
			'james@jamesbrown.net.xx',
			'23 Small Street',
			'North Beach',
			'Springfield',
			'1234567',
			'NZ',
			'jbrown',
			'jbrown'
		);

		$order = DataObject::get_by_id('Order',$cart->ID);
		$this->assertNotNull($order);
		$this->assertEquals($order->Total(),408,'grand total');
		$this->assertEquals($order->TotalOutstanding(),408,'total outstanding');
		$this->assertEquals($order->TotalPaid(),0,'total outstanding');

		/* check order details */
		$this->assertEquals($order->Status,'Unpaid','status is "unpaid"');
		//$this->assertEquals($order->SessionID,session_id(),'session id'); // this fails..why?
		
		/* is functions */
		$this->assertEquals($order->IsSent(),false);
		$this->assertEquals($order->IsProcessing(),false);
		$this->assertEquals($order->IsPaid(),false);
		$this->assertEquals($order->IsCart(),false);		
		
		$this->assertEquals($order->FirstName,'James','order first name');
		$this->assertEquals($order->Surname,'Brown','order surname');
		$this->assertEquals($order->Email,'james@jamesbrown.net.xx','order email');
		$this->assertEquals($order->Address,'23 Small Street','order address');
		$this->assertEquals($order->AddressLine2,'North Beach','order address2');
		$this->assertEquals($order->City,'Springfield','order city');
		$this->assertEquals($order->PostalCode,'1234567','order postcode');
		$this->assertEquals($order->Country,'NZ','order country');
		
		/* check membership details */
		$this->assertNotNull($order->MemberID,'member exists now');
		//TODO: check that the member is now logged in
		$this->assertEquals($order->Member()->FirstName,'James','member first name matches');
		$this->assertEquals($order->Member()->Surname,'Brown','surname matches');
		$this->assertEquals($order->Member()->Email,'james@jamesbrown.net.xx','email matches');
		//$this->assertEquals($order->Member()->Password, Security::encrypt_password('jbrown'),'password matches'); //not finished...need to find out how to encrypt the same
		
		//TODO: test redirected to the right place?
		//TODO: canEdit, canCancel, canCreate, canDelete
		//TODO: check email
		//TODO: check items match
		//TODO: check cart is now empty
	}
	
	
	function testExistingMemberOrder(){
		$joemember = $this->objFromFixture('Member', 'joebloggs');
		$joemember->logIn();
		
		//TODO: check if 
		
		$this->placeOrder(
			'Joseph',
			'Blog',
			'joe@blog.net.abz',
			'100 Melrose Place',
			null,
			'Martinsonville',
			null,
			'EG',
			'newpassword',
			'newpassword'
		);
		
		//TODO: test that the form is pre-populated with the member's details
		//TODO: what happens if member enters different email? / name?
		
	}
	
	function testNoMemberOrder(){
		
	
	}
	
	function testOrderFormValidation(){
		
		
		
	}
	
	
	/**
	 * Helper function that populates a form with data and submits it.
	 */
	protected function placeOrder($firstname,$surname,$email,$address1,$address2 = null,$city,$postcode = null,$country = null,$password = null,$confirmpassword = null,$paymentmethod = "ChequePayment"){
		$this->get('CheckoutPage_Controller');
		
		$data = array(
			'FirstName' => $firstname,
			'Surname' => $surname,
			'Email' => $email,
			'Address' => $address1,
			'City' => $city,
			'PaymentMethod' => $paymentmethod
		);
		
		if($address2) $data['AddressLine2'] = $address2;
		if($postcode) $data['PostalCode'] = $postcode;
		if($country) $data['Country'] = $country;
		if($password) $data['Password[_Password]'] = $password;
		if($confirmpassword) $data['Password[_ConfirmPassword]'] = $confirmpassword;
		
		$this->submitForm('OrderForm_OrderForm','action_processOrder',$data);
	}
	
	
	
	
	/* -------- OLD TESTS (to be removed or factored in) ------------------*/
	
	function old_testValidateProductCurrencies() {
		$productUSDOnly = $this->objFromFixture('Product', 'p1b');
		$orderInEUR = $this->objFromFixture('Order', 'open_order_eur');
	
		$invalidItem = new ProductOrderItem(null, null, $productUSDOnly, 1);
		$invalidItem->write();
		$orderInEUR->Items()->add($invalidItem);
		
		$validationResult = $orderInEUR->validate();
		$this->assertContains('No price found', $validationResult->message());
	}
	
	function old_testAllowedProducts() {
		$product1aNotAllowed = $this->objFromFixture('Product', 'p1a');
		$product2aUSD = $this->objFromFixture('Product', 'p2a');
		$product2bEURUSD = $this->objFromFixture('Product', 'p2b');
		
		$orderEUR = new Order();
		$orderEUR->Currency = 'EUR';
		$orderEUR->write();
		$this->assertNotContains($product2aUSD->ID, $orderEUR->AllowedProducts()->column('ID'));
		$this->assertNotContains($product1aNotAllowed->ID, $orderEUR->AllowedProducts()->column('ID'));
		$this->assertContains($product2bEURUSD->ID, $orderEUR->AllowedProducts()->column('ID'));
		
		$orderUSD = new Order();
		$orderUSD->Currency = 'USD';
		$orderUSD->write();
		$this->assertContains($product2aUSD->ID, $orderUSD->AllowedProducts()->column('ID'));
		$this->assertNotContains($product1aNotAllowed->ID, $orderUSD->AllowedProducts()->column('ID'));
		$this->assertContains($product2bEURUSD->ID, $orderUSD->AllowedProducts()->column('ID'));
	}
	
	function old_testSubtotalInDatabase() {
		$product1a = $this->objFromFixture('Product', 'p1a');
		$product1b = $this->objFromFixture('Product', 'p1b');
		
		// @todo Determine Order Currency automatically
		$order = new Order();
		$order->Currency = 'USD';
		$order->write();
		
		$item1a = new ProductOrderItem(null, null, $product1a, 2);
		$item1a->write();
		$order->Items()->add($item1a);
		$item1b = new ProductOrderItem(null, null, $product1b, 1);
		$item1b->write();
		$order->Items()->add($item1b);

		// 500 + 500 + 600
		$subtotal = $order->SubTotal;
		$this->assertEquals($subtotal->Amount, 1600);
		$this->assertEquals($subtotal->Currency, 'USD');
	}
	
	/**
	 * Test the lock status of an order.
	 */
	function old_testIsLocked() {
		$order = new Order();
		$order->write();
		
		$this->assertFalse($order->IsLocked());
		
		// order is still editable (it hasn't been checked out)
		$order->SystemStatus = Order::$statusTemporary;
		$order->write();
		$this->assertFalse($order->IsLocked());
		
		// order is still editable (it hasn't been checked out)
		$order->SystemStatus = Order::$statusDraft;
		$order->write();
		$this->assertFalse($order->IsLocked());
		
		// order is not editable (it has been checked out)
		$order->SystemStatus = Order::$statusAvailable;
		$order->write();
		$this->assertTrue($order->IsLocked());
		
		// order is not editable (it has been checked out)
		$order->SystemStatus = Order::$statusArchived;
		$order->write();
		$this->assertTrue($order->IsLocked());

		// order is not editable (it has been checked out)
		$order->SystemStatus = Order::$statusDeleted;
		$order->write();
		$this->assertTrue($order->IsLocked());
	}	
	
	function old_testProductOrderItems() {
		
		$product1a = $this->objFromFixture('Product', 'p1a');
		$product1b = $this->objFromFixture('Product', 'p1b');
		
		$order = new Order();
		$order->Currency = 'USD';
		$order->write();
		
		$item1a = new ProductOrderItem(null, null, $product1a, 2);
		$item1a->write();
		$order->Items()->add($item1a);
		$item1b = new ProductOrderItem(null, null, $product1b, 1);
		$item1b->write();
		$order->Items()->add($item1b);
		$item1c = new ProductOrderItem(null, null, $product1a, 1);
		$item1c->write();
		$order->Items()->add($item1c);

		$items = $order->ProductOrderItems();
		
		$testString = 'ProductList: ';
		
		foreach ($items as $item) {
			$testString .= $item->Product()->Title.";";
		}
		$this->assertEquals($testString, "ProductList: Product 1a;Product 1b;Product 1a;");
	}
}
?>