<?php

/**
 * Test member functionality added via ShopMember extension
 */
class ShopMemberTest extends FunctionalTest{
	
	static $fixture_file = array(
		'shop/tests/fixtures/ShopMembers.yml',
		'shop/tests/fixtures/shop.yml'
	);
	
	function testGetByIdentifier(){
		Member::config()->unique_identifier_field = 'Email';
		$member = ShopMember::get_by_identifier('jeremy@peremy.com');
		$this->assertNotNull($member);
		$this->assertEquals('jeremy@peremy.com', $member->Email);
		$this->assertEquals('Jeremy', $member->FirstName);		
	}
	
	function testCreateOrMerge(){
		$this->session()->inst_set('loggedInAs', null); //log out		
		
		//bad data
		$member = ShopMember::create_or_merge(array());
		$this->assertFalse($member, "Bad data provided");
		
		//existing, but non-matching user
		$member = ShopMember::create_or_merge(array(
			'Email' => 'jeremy@peremy.com',
			'FirstName' => 'Jeremy',
			'Surname' => 'Peremy',
			'Password' => 'pass2234'	
		));
		$this->assertFalse($member, "Found member is not same as currently logged in member");
		
		//non existing user
		$member = ShopMember::create_or_merge(array(
			'Email' => 'foo@barbabab.net',
			'FirstName' =>	'Foo',
			'Surname' => 'Bar',
			'Password' => 'foobar'
		));
		$this->assertFalse($member->isInDB(),"New member is not saved to db");
		
		//existing member
		$this->session()->inst_set('loggedInAs', $this->objFromFixture("Member", "jeremyperemy")->ID); //log in existing 'joe bloggs' user
		$member = ShopMember::create_or_merge(array(
			'Email' => 'jeremy@peremy.com',
			'FirstName' => 'Jerry'
		));
		$this->assertTrue((boolean)$member,"Member has been found");
		$this->assertEquals('Peremy', $member->Surname,'Surname remains the same');
		$this->assertEquals('Jerry', $member->FirstName,'Firstname updated');
		
		$this->session()->inst_set('loggedInAs', null); //log out
	}
	
	function testCMSFields(){
		singleton("Member")->getCMSFields();
		singleton("Member")->getMemberFormFields();
	}
	
	function testPastOrders(){
		$member = $this->objFromFixture("Member", "joebloggs");
		$pastorders = $member->getPastOrders();
		$this->assertEquals(1,$pastorders->count());
	}

	//TODO: test login joins cart

}