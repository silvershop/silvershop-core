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
		$this->address1 = $this->objFromFixture("Address", "address1");
		$this->address2 = $this->objFromFixture("Address", "address2");
		$this->checkout = new Checkout($this->cart);
	}
	
	function testSetUpShippingAddress(){		
		$this->checkout->setShippingAddress($this->address1);
		$this->assertEquals($this->cart->ShippingAddressID,$this->address1->ID,"shipping address was successfully added");
	}
	
	function testSetUpBillingAddress(){
		$this->checkout->setBillingAddress($this->address2);
		$this->assertEquals($this->cart->BillingAddressID,$this->address2->ID,"billing address was successfully added");
	}

	function testSetShippingMethod(){
		//TODO: combine shipping framework with core, or remove reliance
		//$this->checkout->setShippingMethod(new ShippingMethod()); //see shippingframework submodule
	}
	
	function testSetPaymentMethod(){
		// ShopPayment::set_supported_methods(array(
		// 	'Cheque' => 'Cheque'
		// ));
		$this->assertTrue($this->checkout->setPaymentMethod("Cheque"),"Valid method set correctly");
		$this->assertEquals($this->checkout->getSelectedPaymentMethod(false),'Cheque');
	}
	
	/**
	 * Tests the default membership configuration.
	 * You can become a member, but it is not necessary
	 */
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
		$this->assertTrue(($result instanceof Member), $this->checkout->getMessage());
		$this->assertTrue($this->checkout->validateMember($result));
	}
	
	function testMustBecomeOrBeMember(){
		Checkout::$member_creation_enabled = true;
		Checkout::$membership_required = true;
		
		$member = $this->checkout->createMembership(array(
			'FirstName' => 'Susan',
			'Surname' => 'Jackson',
			'Email' => 'susan@jmail.com',
			'Password' => 'jaleho3htgll'	
		));
		
		$this->assertTrue($this->checkout->validateMember($member));
		//check can't proceed without being a member
		$this->assertFalse($this->checkout->validateMember(false));
	}
	
	function testNoMemberships(){
		Checkout::$member_creation_enabled = false;
		Checkout::$membership_required = false;
		
		$member = $this->checkout->createMembership(array(
			'FirstName' => 'Susan',
			'Surname' => 'Jackson',
			'Email' => 'susan@jmail.com',
			'Password' => 'jaleho3htgll'
		));
		
		//validate member returns true - any member or not is allowed
		$this->assertTrue($this->checkout->validateMember($member));
		//check can't proceed without being a member
		$this->assertTrue($this->checkout->validateMember(false));
		
		//TODO: check membership does not get tied to order
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