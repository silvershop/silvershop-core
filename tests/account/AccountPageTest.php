<?php

class AccountPageTest extends FunctionalTest{

	public static $fixture_file = "shop/tests/fixtures/shop.yml";
	public static $disable_theme = true;
	public static $use_draft_site = true;

	public function setUp() {
		parent::setUp();
		$this->accountpage = $this->objFromFixture("AccountPage", "accountpage");
		$this->controller = new AccountPage_Controller($this->accountpage);
		$this->controller->init();
	}

	public function testCanViewAccountPage() {
		$this->markTestIncomplete('Log in and view account page');
	}

	public function testGlobals() {
		$this->assertFalse($this->accountpage->canCreate(), "account page exists");
		$this->assertEquals(Director::baseURL()."account/", AccountPage::find_link());
		$this->assertEquals(Director::baseURL()."account/order/10", AccountPage::get_order_link(10));
	}

	public function testAddressBook() {

		$member = $this->objFromFixture("Member", "joebloggs");
		$this->logInAs($member);

		$this->controller->init(); //reinit to connect up member

		$address = $this->objFromFixture("Address", "foobar");
		$address->MemberID = $member->ID;
		$address->write();

		$forms = $this->controller->addressbook();
		$createform = $forms['CreateAddressForm'];
		$defaultform = $forms['DefaultAddressForm'];

		$this->assertTrue($member->AddressBook()->exists());

		$this->assertTrue((boolean)$createform, "Create form exists");
		$this->assertTrue((boolean)$defaultform, "Default form exists");


		//$this->controller->saveaddresses($data, $createform);
		//$this->controller->savedefaultaddresses($data, $defaultform);
		$this->markTestIncomplete("save address and save default");
	}

	public function testEditProfile() {
		$this->controller->editprofile();
		$this->controller->EditAccountForm();
		$this->controller->ChangePasswordForm();
		$this->markTestIncomplete("Add some assertions");
	}

}
