<?php

/**
 * Test member functionality added via ShopMember extension
 */
class ShopMemberTest extends FunctionalTest{
	
	static $fixture_file = array(
		'shop/tests/fixtures/ShopMembers.yml'
	);
	
	function testGetByIdentifier(){
		Member::config()->unique_identifier_field = 'Email';
		$member = ShopMember::get_by_identifier('joe@bloggs.com');
		$this->assertNotNull($member);
		$this->assertEquals('joe@bloggs.com', $member->Email);
		$this->assertEquals('Joe', $member->FirstName);		
	}
	
	function testCreateOrMerge(){
		$this->session()->inst_set('loggedInAs', null); //log out		
		
		//bad data
		$member = ShopMember::create_or_merge(array());
		$this->assertFalse($member, "Bad data provided");
		
		//existing, but non-matching user
		$member = ShopMember::create_or_merge(array(
			'Email' => 'joe@bloggs.com',
			'FirstName' => 'Joe',
			'Surname' => 'Bloggs',
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
		$this->session()->inst_set('loggedInAs', $this->objFromFixture("Member", "joebloggs")->ID); //log in existing 'joe bloggs' user
		$member = ShopMember::create_or_merge(array(
			'Email' => 'joe@bloggs.com',
			'FirstName' => 'Joey'
		));
		$this->assertTrue((boolean)$member,"Member has been found");
		$this->assertEquals('Bloggs', $member->Surname,'Surname remains the same');
		$this->assertEquals('Joey', $member->FirstName,'Firstname updated');
		
		$this->session()->inst_set('loggedInAs', null); //log out
	}
	
	function testCMSFields(){
		singleton("Member")->getCMSFields();
		singleton("Member")->getMemberFormFields();
	}
	
}