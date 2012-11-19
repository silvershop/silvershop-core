<?php

/**
 * Test member functionality added via ShopMember extension
 */
class ShopMemberTest extends SapphireTest{
	
	static $fixture_file = array(
		'shop/tests/fixtures/ShopMembers.yml'
	);
	
	function testGetByIdentifier(){
		Member::set_unique_identifier_field("Email");
		$member = ShopMember::get_by_identifier('joe@bloggs.com');
		$this->assertNotNull($member);
		$this->assertEquals($member->Email,'joe@bloggs.com');
		$this->assertEquals($member->FirstName,'Joe');
	}
	
}