<?php

class CheckoutTest extends SapphireTest{
	
	static $fixture_file = array(
		'shop/tests/fixtures/Cart.yml',
		'shop/tests/fixtures/Addresses.yml',
		'shop/tests/fixtures/ShopMembers.yml'
	);
	
	function setUp(){
		parent::setUp();
		ShopTest::setConfiguration();
		
		$this->cart = $this->objFromFixture("Order", "cart1");
		$this->shippingaddress = $this->objFromFixture("Address", "wnz6012");
		$this->checkout = new Checkout($this->cart);
	}
	
	function testSetUpShippingAddress(){		
		$this->checkout->setShippingAddress($this->shippingaddress);
		//address was successfully added
		//don't allow adding bad addressses
	}
	
	function testSetShippingMethod(){
		$this->checkout->setShippingMethod(new ShippingMethod()); //see shippingframework submodule
	}
	
	function testSetPaymentMethod(){
		$this->checkout->setPaymentMethod("Cheque");
	}
	
	function testCanBecomeMember(){
		Checkout::$member_creation_enabled = true;
		Checkout::$membership_required = false;
		
		//check can proceeed with/without order
		//check member exists
		$result = $this->checkout->createMembership(array(
			'FirstName' => 'Jane',
			'Surname' => 'Smith',
			'Email' => 'jane@smith.net',
			'Password' => 'janesmith2012'	
		));
		$this->assertType("Member", $result, $this->checkout->getMessage());
	}
	
	function testMustBecomeOrBeMember(){
		Checkout::$member_creation_enabled = true;
		Checkout::$membership_required = true;
		//check can't proceed without being a member
		//$this->checkout->validateMember($member);
	}
	
	function testNoMemberships(){
		Checkout::$member_creation_enabled = false;
		Checkout::$membership_required = false;
		//validate member returns true - any member or not is allowed
		//check membership is not tied to order
	}
	
	function testMembersOnly(){
		Checkout::$member_creation_enabled = false;
		Checkout::$membership_required = true;
		//check non-members can't do anything
		$result = $this->checkout->createMembership(array(
			'FirstName' => 'Some',
			'Surname' => 'Body',
			'Email' => 'somebody@somedomain.com',
			'Password' => 'pass1234'
		));
		$this->assertFalse($result, "Can't create membership at all");
	}
	
	function testBadCreateMember(){
		Checkout::$member_creation_enabled = true;
		Checkout::$membership_required = false;
		//no password
		$result = $this->checkout->createMembership(array(
			'FirstName' => 'Jim',
			'Surname' => 'Smith',
			'Email' => 'jim@smith.com'
		));
		$this->assertFalse($result, "Can't create membership without password");
		
		//member exists
		$result = $this->checkout->createMembership(array(
			'FirstName' => 'Joe',
			'Surname' => 'Bloggs',
			'Email' => 'joe@bloggs.com',
			'Password' => 'joeblogga'
		));
		$this->assertFalse($result, "Can't overwrite existing member");
		
		//identifier missing
		$result = $this->checkout->createMembership(array(
			'FirstName' => 'John',
			'Surname' => 'Doe',
			'Password' => 'johndoe1234'
		));
		$this->assertFalse($result, "Must provide unique identifier field");
		
		//non-validating identifier
		//TODO: currently assumed to be valid because is handled by form validation
		/*
		$result = $this->checkout->createMembership(array(
			'FirstName' => 'Foo',
			'Surname' => 'Bar',
			'Email' => 'badlyformedemail',
			'Password' => 'foobar'
		));
		$this->assertFalse($result, "Unique identifier must be valid");
		*/
		//TODO: password validation - length etc (see PasswordValidator.php)
		
		//TODO: allow devs to define what member data is required, perhaps add $shopmember->
	}
	
}