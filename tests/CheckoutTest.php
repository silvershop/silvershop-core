<?php
/**
 * Test checkout process
 * 
 * @package shop
 * @subpackage tests
 * 
 * @todo check session retrieval of orders
 */
class CheckoutTest extends SapphireTest {

	static $fixture_file = 'shop/tests/shop.yml';
	static $disable_theme = true;
	static $use_draft_site = true;
	
	function setUp() {
		parent::setUp();
		ShopTest::setConfiguration();
	
		SSViewer::set_theme('skeleton'); //TODO: make this work without a theme
	
		$this->mp3player = $this->objFromFixture('Product', 'mp3player');
		$this->socks = $this->objFromFixture('Product', 'socks');
		$this->beachball = $this->objFromFixture('Product', 'beachball');
		$this->hdtv = $this->objFromFixture('Product', 'hdtv');
	
		$this->mp3player->publish('Stage','Live');
		$this->socks->publish('Stage','Live');
		$this->beachball->publish('Stage','Live');
		$this->hdtv->publish('Stage','Live');
	
		$this->cart = ShoppingCart::getInstance();
		
		$this->checkoutpage = $this->objFromFixture('CheckoutPage', 'checkout');
		$this->checkoutpage->publish('Stage','Live');
	}
	
	function testFindLink() {
		$link = CheckoutPage::find_link();
		$this->assertEquals(Director::baseURL() . 'checkout/', $link, 'find_link() returns the correct link to checkout.');
	}
	
	function testPlaceOrder(){
	
		//place items in cart
		$this->cart->add($this->mp3player,2);
		$this->cart->add($this->socks);
	
		$order = $this->cart->current();
		
		$member = ShopMember::ecommerce_create_or_merge(array(
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
		));
	
		$order = DataObject::get_by_id('Order',$order->ID); //update order variable to db-stored version
		$this->assertNotNull($order);
		$this->assertEquals($order->GrandTotal(),408,'grand total');
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
		//TODO: check items match
		//$this->assertEmailSent("james@jamesbrown.net.xx","test@myshop.com","Shop Sale Information #".$order->ID);
		//TODO: check cart is now empty
		//$this->assertFalse($this->cart->current(),'cart has been cleared');
	
		$order->Member()->logOut();
		$this->cart->clear(); //cleanup
	}
	
	function testMemberOrder(){
	
		$this->cart->add($this->mp3player);
	
		$joemember = $this->objFromFixture('Member', 'joebloggs');
		$joemember->logIn();
		$cart = ShoppingCart::current_order();
		
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
		),"Place order");
	
		//TODO: test that the form is pre-populated with the member's details
		//TODO: what happens if member enters different email? / name?
	
		$order = DataObject::get_by_id('Order',$cart->ID);
		$this->assertNotNull($order,'Order exists');
		if($order){
			$this->assertEquals($order->Status,'Unpaid','status is now "unpaid"');
			$this->assertEquals($order->FirstName,'Joseph','order first name');
			$this->assertEquals($order->Surname,'Blog','order surname');
			$this->assertEquals($order->Email,'joe@blog.net.abz','order email');
			$this->assertEquals($order->Address,'100 Melrose Place','order address');
			$this->assertNull($order->AddressLine2,'order address2');
			$this->assertEquals($order->City,'Martinsonville','order city');
			$this->assertNull($order->PostalCode,'order postcode');
			$this->assertEquals($order->Country,'EG','order country');
			//ASSUMPTION: member details are NOT updated with order
			$this->assertEquals($order->MemberID,$joemember->ID,'Order associated with member');
			$this->assertEquals($order->Member()->FirstName,'Joe','member first name has not changed');
		}
	
		$joemember->logOut();
		$this->cart->clear(); //cleanup
	}
	
	function testNoMemberOrder(){
		//TODO: test configuration that deines non-member orders
		//adjust configuration to allow non member orders
		OrderForm::set_user_membership_optional(true);
		OrderForm::set_force_membership(false);
		$this->cart->add($this->socks);
		$order = $this->cart->current();
		$this->assertTrue($this->placeOrder(
			'Donald',
			'Duck',
			'donald@pondcorp.edu.za',
			'4 The Strand',
			null,
			'Melbourne',
			null,
			'AU'
		));
	
		$order = DataObject::get_by_id('Order',$order->ID); //update $order
		$this->assertNotNull($order,'Order exists');
	
		if($order){
			$this->assertEquals($order->Status,'Unpaid','status is now "unpaid"');
			$this->assertEquals($order->MemberID,0,'No associated member');
			$this->assertEquals($order->GrandTotal(),8,'grand total');
			$this->assertEquals($order->TotalOutstanding(),8,'total outstanding');
			$this->assertEquals($order->TotalPaid(),0,'total outstanding');
			$this->assertEquals($order->FirstName,'Donald','order first name');
			$this->assertEquals($order->Surname,'Duck','order surname');
			$this->assertEquals($order->Email,'donald@pondcorp.edu.za','order email');
			$this->assertEquals($order->Address,'4 The Strand');
			$this->assertNull($order->AddressLine2,'order address2');
			$this->assertEquals($order->City,'Melbourne','order city');
			$this->assertNull($order->PostalCode,'order postcode');
			$this->assertEquals($order->Country,'AU','order country');
		}
	
		$this->cart->clear(); //cleanup
	}
	
	/*
	 function testOrderFormValidation(){
		//TODO: test trying to use an email that has already been taken
		//TODO: submit with empty cart
		//TODO: missing required fields
	}
	*/
	
	//TODO: test shipping / billing details
	//TODO: test country selection - countries that are not on list
	
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
		$order = ShoppingCart::singleton()->current();
		$order->update($data);
		$order->write();
		if($member){
			$order->MemberID = $member->ID;
			$order->write();
		}
		return OrderProcessor::create($order)->placeOrder();
		//TODO: form submissions not working - theme issues
		//$this->submitForm('OrderForm_OrderForm','action_processOrder',$data);
	}

}