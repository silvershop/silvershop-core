<?php

class AccountPageTest extends FunctionalTest
{
    protected static $fixture_file   = array(
        'silvershop/tests/fixtures/Pages.yml',
        'silvershop/tests/fixtures/shop.yml',
    );
    protected static $disable_theme  = true;
    protected static $use_draft_site = true;

    public function setUp()
    {
        parent::setUp();
        Controller::add_extension('ShopTestControllerExtension');
        $this->accountpage = $this->objFromFixture("AccountPage", "accountpage");
        $this->controller = new AccountPage_Controller($this->accountpage);
        $this->controller->init();
    }

    public function testCanViewAccountPage()
    {
        $page = $this->get("account/");  // attempt to access the Account Page
        $this->assertEquals(200, $page->getStatusCode(), "a page should load");
        $this->assertTrue(
            $page->getHeader('X-TestPageClass') == 'Security' && $page->getHeader('X-TestPageAction') == 'login',
            'Need to login before accessing the account page'
        );

        // login using form
        $this->submitForm(
            "MemberLoginForm_LoginForm",
            "action_dologin",
            array(
                'Email'    => 'test@example.com',
                'Password' => '23u90oijlJKsa',
            )
        );

        $page = $this->get("account/");  // try accessing the account page again
        $this->assertEquals(200, $page->getStatusCode(), "a page should load");

        $this->assertEquals('AccountPage', $page->getHeader('X-TestPageClass'), "Account Page should open");
    }

    public function testGlobals()
    {
        $this->assertFalse($this->accountpage->canCreate(), "account page exists");
        $this->assertEquals(Director::baseURL() . "account/", AccountPage::find_link());
        $this->assertEquals(Director::baseURL() . "account/order/10", AccountPage::get_order_link(10));
    }

    public function testAddressBook()
    {
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

    public function testAddressBookWithDropdownFieldToSelectCountry()
    {
        $member = $this->objFromFixture("Member", "joebloggs");
        $this->logInAs($member);
        $this->controller->init(); //re-init to connect up member

        // Open Address Book page
        $page = $this->get("account/addressbook/"); // goto address book page
        $this->assertEquals(200, $page->getStatusCode(), "a page should load");
        $this->assertEquals('AccountPage', $page->getHeader('X-TestPageClass'), "Account page should open");
        $this->assertEquals('addressbook', $page->getHeader('X-TestPageAction'), "Account addressbook should open");

        // Create an address
        $data = array(
            "Country"      => "AU",
            "Address"      => "Sydney Opera House",
            "AddressLine2" => "Bennelong Point",
            "City"         => "Sydney",
            "State"        => "NSW",
            "PostalCode"   => "2000",
            "Phone"        => "1234 5678",
        );
        $this->submitForm("Form_CreateAddressForm", "action_saveaddress", $data);
        $this->assertEquals(200, $page->getStatusCode(), "a page should load");

        $au_address = Address::get()->filter('PostalCode', '2000')->sort('ID')->last();
        $this->assertEquals(
            "AU",
            $au_address->Country,
            "New address successfully saved, using dropdown to select the country"
        );
        $this->assertEquals(
            "Sydney Opera House",
            $au_address->Address,
            "Ensure that the Address is the Sydney Opera House"
        );
    }

    public function testAddressBookWithReadonlyFieldForCountry()
    {
        $member = $this->objFromFixture("Member", "joebloggs");
        $this->logInAs($member);
        $this->controller->init(); //reinit to connect up member

        // setup a single-country site
        $siteconfig = DataObject::get_one('SiteConfig');
        $siteconfig->AllowedCountries = "NZ";
        $siteconfig->write();
        $singlecountry = SiteConfig::current_site_config();
        $this->assertEquals(
            "NZ",
            $singlecountry->getSingleCountry(),
            "Confirm that the website is setup as a single country site"
        );

        // Open the Address Book page to test form submission with a readonly field
        $page = $this->get("account/addressbook/"); // goto address book page
        $this->assertEquals(200, $page->getStatusCode(), "a page should load");
        $this->assertContains(
            "Form_CreateAddressForm_Country_readonly",
            $page->getBody(),
            "The Country field is readonly"
        );
        $this->assertNotContains(
            "<option value=\"NZ\">New Zealand</option>",
            $page->getBody(),
            "Dropdown field is not shown"
        );

        // Create an address
        $data = array(
            "Address"    => "234 Hereford Street",
            "City"       => "Christchurch",
            "State"      => "Canterbury",
            "PostalCode" => "8011",
        );
        $this->submitForm("Form_CreateAddressForm", "action_saveaddress", $data);
        $this->assertEquals(200, $page->getStatusCode(), "a page should load");

        $nz_address = Address::get()->filter('PostalCode', '8011')->sort('ID')->last();
        $this->assertEquals(
            "NZ",
            $nz_address->Country,
            "New address successfully saved; even with a Country readonly field in the form"
        );
        $this->assertEquals(
            "234 Hereford Street",
            $nz_address->Address,
            "Ensure that the Address is 234 Hereford Street"
        );
    }

    public function testEditProfile()
    {
        $this->controller->editprofile();
        $this->controller->EditAccountForm();
        $this->controller->ChangePasswordForm();
        $this->markTestIncomplete("Add some assertions");
    }
}
