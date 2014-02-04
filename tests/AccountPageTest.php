<?php

class AccountPageTest extends SapphireTest{

	public static $fixture_file = "shop/tests/fixtures/shop.yml";

	public function setUp(){
		parent::setUp();
		$this->controller = new AccountPage_Controller($this->objFromFixture("AccountPage", "accountpage"));
		$this->controller->init();
	}

	public function testAddressBook(){
		$this->controller->addressbook();

		//TODO: save address
		//TODO: save default
	}

	public function testEditProfile(){
		$this->controller->editprofile();
		$this->controller->EditAccountForm();
		$this->controller->ChangePasswordForm();
	}

}
