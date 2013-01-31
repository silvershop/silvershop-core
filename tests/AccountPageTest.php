<?php

class AccountPageTest extends SapphireTest{
	
	static $fixture_file = "shop/tests/fixtures/shop.yml";
	
	function setUp(){
		parent::setUp();
		$this->controller = new AccountPage_Controller($this->objFromFixture("AccountPage", "accountpage"));
		$this->controller->init();
	}
	
	function testAddressBook(){
		$this->controller->addressbook();
		
		//TODO: save address
		//TODO: save default
	}
	
	function testEditProfile(){
		$this->controller->editprofile();
		$this->controller->EditAccountForm();
		$this->controller->ChangePasswordForm();
	}
	
}