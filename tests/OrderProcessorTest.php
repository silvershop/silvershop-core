<?php
/**
 * Test OrderProcessor
 * 
 * @package shop
 * @subpackage tests
 */
class OrderProcessorTest extends SapphireTest {

	static $fixture_file = 'shop/tests/fixtures/shop.yml';
	static $disable_theme = true;
	static $use_draft_site = true;
	
	protected $processor;
	
	function setUp() {
		parent::setUp();
		ShopTest::setConfiguration();

		$this->mp3player = $this->objFromFixture('Product', 'mp3player');
		$this->socks = $this->objFromFixture('Product', 'socks');
		$this->beachball = $this->objFromFixture('Product', 'beachball');
		$this->hdtv = $this->objFromFixture('Product', 'hdtv');
	
		$this->mp3player->publish('Stage','Live');
		$this->socks->publish('Stage','Live');
		$this->beachball->publish('Stage','Live');
		$this->hdtv->publish('Stage','Live');
	
		$this->shoppingcart = ShoppingCart::singleton();
	}
	
	function testCreatePayment(){
		$order = $this->objFromFixture("Order", "unpaid");
		$processor = OrderProcessor::create($order);
		$payment = $processor->createPayment('Dummy');
		$this->assertTrue((boolean)$payment);
	}
	
	function testPlaceOrder(){
		//place items in cart
		$this->shoppingcart->add($this->mp3player,2);
		$this->shoppingcart->add($this->socks);
	
		$order = $this->shoppingcart->current();
		
		$member = ShopMember::create_or_merge(array(
			'FirstName' => 'James',
			'Surname'	=> 'Brown',
			'Email'		=> 'james@jamesbrown.net.xx',
			'Password'	=> 'jbrown'
		));
		$this->assertTrue((bool)$member);
		$member->write();	
		
		//submit checkout page
		$this->assertTrue($this->placeOrder(
			'James',
			'Brown',
			'james@jamesbrown.net.xx',
			'23 Small Street',
			'North Beach',
			'Springfield',
			'1234567',
			'NZ',
			'jbrown',
			'jbrown',
			$member
		),"Order placed sucessfully");
	
		$order = DataObject::get_by_id('Order',$order->ID); //update order variable to db-stored version
		$this->assertNotNull($order);
		$this->assertEquals($order->GrandTotal(),408,'grand total');
		$this->assertEquals($order->TotalOutstanding(),408,'total outstanding');
		$this->assertEquals($order->TotalPaid(),0,'total outstanding');
	
		$this->assertEquals($order->Status,'Unpaid','status is "unpaid"');
	
		$this->assertEquals($order->IsSent(),false);
		$this->assertEquals($order->IsProcessing(),false);
		$this->assertEquals($order->IsPaid(),false);
		$this->assertEquals($order->IsCart(),false);
	
		$this->assertEquals($order->FirstName,'James','order first name');
		$this->assertEquals($order->Surname,'Brown','order surname');
		$this->assertEquals($order->Email,'james@jamesbrown.net.xx','order email');
		
		$shippingaddress = $order->ShippingAddress();
		
		$this->assertEquals($shippingaddress->Address,'23 Small Street','order address');
		$this->assertEquals($shippingaddress->AddressLine2,'North Beach','order address2');
		$this->assertEquals($shippingaddress->City,'Springfield','order city');
		$this->assertEquals($shippingaddress->PostalCode,'1234567','order postcode');
		$this->assertEquals($shippingaddress->Country,'NZ','order country');
	
		$billingaddress = $order->BillingAddress();
		
		$this->assertEquals($billingaddress->Address,'23 Small Street','order address');
		$this->assertEquals($billingaddress->AddressLine2,'North Beach','order address2');
		$this->assertEquals($billingaddress->City,'Springfield','order city');
		$this->assertEquals($billingaddress->PostalCode,'1234567','order postcode');
		$this->assertEquals($billingaddress->Country,'NZ','order country');
		
		$this->assertNotNull($order->MemberID,'member exists now');
		$this->assertEquals($order->Member()->FirstName,'James','member first name matches');
		$this->assertEquals($order->Member()->Surname,'Brown','surname matches');
		$this->assertEquals($order->Member()->Email,'james@jamesbrown.net.xx','email matches');
		//$this->assertEquals($order->Member()->Password, Security::encrypt_password('jbrown'),'password matches'); //not finished...need to find out how to encrypt the same
	}
	
	function testMemberOrder(){

		//log out the admin user
		Member::currentUser()->logOut();
	
		$this->shoppingcart->add($this->mp3player);
	
		$joemember = $this->objFromFixture('Member', 'joebloggs');
		$joemember->logIn();
		$cart = ShoppingCart::curr();
		
		$this->assertTrue($this->placeOrder(
			'Joseph',
			'Blog',
			'joe@blog.net.abz',
			'100 Melrose Place',
			null,
			'Martinsonville',
			null,
			'EG',
			'newpassword',
			'newpassword',
			$joemember
		),"Member order placed successfully");
	
		$order = DataObject::get_by_id('Order',$cart->ID);
		$this->assertTrue((boolean)$order,'Order exists');
		$this->assertEquals($order->Status,'Unpaid','status is now "unpaid"');
		$this->assertEquals($order->FirstName,'Joseph','order first name');
		$this->assertEquals($order->Surname,'Blog','order surname');
		$this->assertEquals($order->Email,'joe@blog.net.abz','order email');
		
		$shippingaddress = $order->ShippingAddress();
		
		$this->assertEquals($shippingaddress->Address,'100 Melrose Place','order address');
		$this->assertNull($shippingaddress->AddressLine2,'order address2');
		$this->assertEquals($shippingaddress->City,'Martinsonville','order city');
		$this->assertNull($shippingaddress->PostalCode,'order postcode');
		$this->assertEquals($shippingaddress->Country,'EG','order country');
		
		//ASSUMPTION: member details are NOT updated with order
		$this->assertEquals($order->MemberID,$joemember->ID,'Order associated with member');
		$this->assertEquals($order->Member()->FirstName,'Joe','member first name has not changed');
		$this->assertTrue($order->Member()->inGroup($this->objFromFixture("Group", "customers"),true),"Member has been added to ShopMembers group");
	}
	
	
	function testNoMemberOrder(){

		//log out the admin user
		Member::currentUser()->logOut();

		$this->shoppingcart->add($this->socks);
		$order = $this->shoppingcart->current();
		$success = $this->placeOrder(
			'Donald',
			'Duck',
			'donald@pondcorp.edu.za',
			'4 The Strand',
			null,
			'Melbourne',
			null,
			'AU'
		);
		$error =  $this->processor->getError();
		$this->assertTrue($success,"Non-member order placed successfully ...$error");
	
		$order = DataObject::get_by_id('Order',$order->ID); //update $order
		$this->assertTrue((boolean)$order,'Order exists');
		$this->assertEquals($order->Status,'Unpaid','status is now "unpaid"');
		$this->assertEquals($order->MemberID,0,'No associated member');
		$this->assertEquals($order->GrandTotal(),8,'grand total');
		$this->assertEquals($order->TotalOutstanding(),8,'total outstanding');
		$this->assertEquals($order->TotalPaid(),0,'total outstanding');
		$this->assertEquals($order->FirstName,'Donald','order first name');
		$this->assertEquals($order->Surname,'Duck','order surname');
		$this->assertEquals($order->Email,'donald@pondcorp.edu.za','order email');
		
		$shippingaddress = $order->ShippingAddress();
		
		$this->assertEquals($shippingaddress->Address,'4 The Strand');
		$this->assertNull($shippingaddress->AddressLine2,'order address2');
		$this->assertEquals($shippingaddress->City,'Melbourne','order city');
		$this->assertNull($shippingaddress->PostalCode,'order postcode');
		$this->assertEquals($shippingaddress->Country,'AU','order country');
	}
	
	/**
	 * Helper function that populates a form with data and submits it.
	 */
	protected function placeOrder($firstname,$surname,$email,$address1,$address2 = null,$city,$postcode = null,$country = null,$password = null,$confirmpassword = null, $member = null){
		$data = array(
			'FirstName' => $firstname,
			'Surname' => $surname,
			'Email' => $email,
			'Address' => $address1,
			'City' => $city
		);
		if($address2) $data['AddressLine2'] = $address2;
		if($postcode) $data['PostalCode'] = $postcode;
		if($country) $data['Country'] = $country;
		if($password) $data['Password[_Password]'] = $password;
		if($confirmpassword) $data['Password[_ConfirmPassword]'] = $confirmpassword;

		$order = $this->shoppingcart->current();
		$order->update($data);
		$address = new Address();
		$address->update($data);
		$address->write();
		$order->ShippingAddressID = $address->ID;
		$order->BillingAddressID = $address->ID; //same (for now)
		$order->write();
		$this->processor = OrderProcessor::create($order);
		return $this->processor->placeOrder($member);
	}

}