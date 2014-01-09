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

		Checkout::$member_creation_enabled = true;
		Checkout::$membership_required = false;
	}
	
	function testSetUpShippingAddress(){		
		$this->checkout->setShippingAddress($this->address1);
		$this->assertEquals($this->address1->ID, $this->cart->ShippingAddressID,"shipping address was successfully added");
	}
	
	function testSetUpBillingAddress(){
		$this->checkout->setBillingAddress($this->address2);
		$this->assertEquals($this->address2->ID, $this->cart->BillingAddressID,"billing address was successfully added");
	}

	function testSetShippingMethod(){
		//TODO: combine shipping framework with core, or remove reliance
		//$this->checkout->setShippingMethod(new ShippingMethod()); //see shippingframework submodule
	}
	
	function testSetPaymentMethod(){
		$this->assertTrue($this->checkout->setPaymentMethod("Dummy"),"Valid method set correctly");
		$this->assertEquals('Dummy', $this->checkout->getSelectedPaymentMethod(false));
	}
	
	/**
	 * Tests the default membership configuration.
	 * You can become a member, but it is not necessary
	 */
	function testCanBecomeMember(){
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
		
		$this->setExpectedException('ValidationException');

		$member = $this->checkout->createMembership(array(
			'FirstName' => 'Susan',
			'Surname' => 'Jackson',
			'Email' => 'susan@jmail.com',
			'Password' => 'jaleho3htgll'
		));
	}
	
	/**
	 * @expectedException ValidationException
	 * @expectedExceptionMessage Creating new memberships is not allowed
	 */
	function testMembersOnly(){
		Checkout::$member_creation_enabled = false;
		Checkout::$membership_required = true;
		$result = $this->checkout->createMembership(array(
			'FirstName' => 'Some',
			'Surname' => 'Body',
			'Email' => 'somebody@somedomain.com',
			'Password' => 'pass1234'
		));

		$this->fail("Exception was expected here");
	}

	/**
	 * @expectedException ValidationException
	 * @expectedExceptionMessage A password is required
	 */
	function testMemberWithoutPassword(){
		$result = $this->checkout->createMembership(array(
			'FirstName' => 'Jim',
			'Surname' => 'Smith',
			'Email' => 'jim@smith.com'
		));
		$this->fail("Exception was expected here");
	}

	/**
	 * @expectedException ValidationException
	 * @expectedExceptionMessage A member already exists with the Email jeremy@peremy.com
	 */
	function testMemberAlreadyExists(){
		$result = $this->checkout->createMembership(array(
			'FirstName' => 'Jeremy',
			'Surname' => 'Peremy',
			'Email' => 'jeremy@peremy.com',
			'Password' => 'jeremyperemy'
		));
		$this->fail("Exception was expected here");

	}

	/**
	 * @expectedException ValidationException
	 * @expectedExceptionMessage Required field not found: Email
	 */
	function testMemberMissingIdentifier(){
		$result = $this->checkout->createMembership(array(
			'FirstName' => 'John',
			'Surname' => 'Doe',
			'Password' => 'johndoe1234'
		));
		$this->fail("Exception was expected here");
	}
	
}