<?php

namespace SilverShop\Tests\Page;

use SilverShop\Model\Address;
use SilverShop\Page\AccountPage;
use SilverShop\Page\AccountPageController;
use SilverShop\Tests\ShopTestControllerExtension;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\SSViewer;

class AccountPageTest extends FunctionalTest
{
    protected static $fixture_file = [
        __DIR__ . '/../Fixtures/Pages.yml',
        __DIR__ . '/../Fixtures/shop.yml',
    ];
    protected static $disable_theme = true;
    protected static $use_draft_site = true;

    /**
     * @var AccountPage
     */
    protected $accountpage;

    /**
     * @var AccountPageController
     */
    protected $controller;

    public function setUp(): void
    {
        parent::setUp();

        Controller::add_extension(ShopTestControllerExtension::class);
        $this->accountpage = $this->objFromFixture(AccountPage::class, "accountpage");
        $this->accountpage->publishSingle();
        $this->controller = new AccountPageController($this->accountpage);

        $r = new HTTPRequest('GET', '/');
        $r->setSession($this->session());

        $this->controller->setRequest($r);
    }

    public function testCanViewAccountPage()
    {
        $page = $this->get("account/");  // attempt to access the Account Page
        $this->assertEquals(200, $page->getStatusCode(), "a page should load");
        $this->assertTrue(
            $page->getHeader('X-TestPageClass') == Security::class && $page->getHeader('X-TestPageAction') == 'login',
            'Need to login before accessing the account page'
        );

        // login using form
        $this->submitForm(
            "MemberLoginForm_LoginForm",
            "action_doLogin",
            [
                'Email' => 'test@example.com',
                'Password' => '23u90oijlJKsa',
            ]
        );

        $page = $this->get("account/");  // try accessing the account page again
        $this->assertEquals(200, $page->getStatusCode(), "a page should load");

        $this->assertEquals(AccountPageController::class, $page->getHeader('X-TestPageClass'), "Account Page should open");
    }

    public function testGlobals()
    {
        $this->assertFalse($this->accountpage->canCreate(), "account page exists");
        $this->assertEquals(Controller::join_links(Director::baseURL() . "account"), AccountPage::find_link());
        $this->assertEquals(Controller::join_links(Director::baseURL() . "account/order/10"), AccountPage::get_order_link(10));
    }

    public function testAddressBook()
    {
        $member = $this->objFromFixture(Member::class, "joebloggs");
        $this->logInAs($member);

        $address = $this->objFromFixture(Address::class, "foobar");
        $address->MemberID = $member->ID;
        $address->write();

        $this->controller->init();
        $forms = $this->controller->addressbook();
        $createform = $forms['CreateAddressForm'];
        $defaultform = $forms['DefaultAddressForm'];
        $this->assertTrue($member->AddressBook()->exists());

        $this->assertTrue((boolean)$createform, "Create form exists");
        $this->assertTrue((boolean)$defaultform, "Default form exists");

        $page = $this->get('account/addressbook');
        $this->assertEquals(200, $page->getStatusCode(), 'a page should load');

        $this->submitForm(
            'Form_CreateAddressForm',
            'action_saveaddress',
            [
                'Address' => '123 Fake Street',
                'City' => 'Faketown',
                'State' => 'Greenland',
                'Country' => 'US',
            ]
        );
        $savedAddress = Address::get()->filter(
            [
                'MemberID' => $member->ID,
                'Address' => '123 Fake Street',
            ]
        )->first();
        $this->assertNotNull($savedAddress, 'Address should be saved');

        $page = $this->get('account/setdefaultshipping/' . $savedAddress->ID);
        $this->assertEquals(200, $page->getStatusCode(), 'a page should load');
        $page = $this->get('account/setdefaultbilling/' . $savedAddress->ID);
        $this->assertEquals(200, $page->getStatusCode(), 'a page should load');

        $member_updated = Member::get()->byID($member->ID);

        $this->assertEquals(
            $savedAddress->ID,
            $member_updated->DefaultShippingAddressID,
            'Default shipping address should be set'
        );
        $this->assertEquals(
            $savedAddress->ID,
            $member_updated->DefaultBillingAddressID,
            'Default billing address should be set'
        );
    }

    public function testAddressBookWithDropdownFieldToSelectCountry()
    {
        $this->useTestTheme(
            realpath(__DIR__ . '/../'),
            'shoptest',
            function () {
                $member = $this->objFromFixture(Member::class, 'joebloggs');
                $this->logInAs($member);

                // Open Address Book page
                $page = $this->get('account/addressbook/'); // goto address book page
                $this->assertEquals(200, $page->getStatusCode(), 'a page should load');
                $this->assertEquals(AccountPageController::class, $page->getHeader('X-TestPageClass'), 'Account page should open');
                $this->assertEquals('addressbook', $page->getHeader('X-TestPageAction'), 'Account addressbook should open');

                // Create an address
                $this->submitForm(
                    'Form_CreateAddressForm',
                    'action_saveaddress',
                    [
                        'Country' => 'AU',
                        'Address' => 'Sydney Opera House',
                        'AddressLine2' => 'Bennelong Point',
                        'City' => 'Sydney',
                        'State' => 'NSW',
                        'PostalCode' => '2000',
                        'Phone' => '1234 5678',
                    ]
                );
                $this->assertEquals(200, $page->getStatusCode(), 'a page should load');

                $au_address = Address::get()->filter('PostalCode', '2000')->sort('ID')->last();
                $this->assertEquals(
                    'AU',
                    $au_address->Country,
                    'New address successfully saved, using dropdown to select the country'
                );
                $this->assertEquals(
                    'Sydney Opera House',
                    $au_address->Address,
                    'Ensure that the Address is the Sydney Opera House'
                );
            }
        );
    }

    public function testAddressBookWithReadonlyFieldForCountry()
    {
        $this->useTestTheme(
            realpath(__DIR__ . '/../'),
            'shoptest',
            function () {
                $member = $this->objFromFixture(Member::class, 'joebloggs');
                $this->logInAs($member);

                // setup a single-country site
                $siteconfig = DataObject::get_one(SiteConfig::class);
                $siteconfig->AllowedCountries = '["NZ"]';
                $siteconfig->write();
                $singlecountry = SiteConfig::current_site_config();
                $this->assertEquals(
                    'NZ',
                    $singlecountry->getSingleCountry(),
                    'Confirm that the website is setup as a single country site'
                );

                // Open the Address Book page to test form submission with a readonly field
                $page = $this->get('account/addressbook/'); // goto address book page
                $this->assertEquals(200, $page->getStatusCode(), 'a page should load');
                $this->assertStringContainsString(
                    'Form_CreateAddressForm_Country_readonly',
                    $page->getBody(),
                    'The Country field is readonly'
                );
                $this->assertStringNotContainsString(
                    '<option value=\"NZ\">New Zealand</option>',
                    $page->getBody(),
                    'Dropdown field is not shown'
                );

                // Create an address
                $this->submitForm(
                    'Form_CreateAddressForm',
                    'action_saveaddress',
                    [
                        'Address' => '234 Hereford Street',
                        'City' => 'Christchurch',
                        'State' => 'Canterbury',
                        'PostalCode' => '8011',
                    ]
                );
                $this->assertEquals(200, $page->getStatusCode(), 'a page should load');

                $nz_address = Address::get()->filter('PostalCode', '8011')->sort('ID')->last();
                $this->assertEquals(
                    'NZ',
                    $nz_address->Country,
                    'New address successfully saved; even with a Country readonly field in the form'
                );
                $this->assertEquals(
                    '234 Hereford Street',
                    $nz_address->Address,
                    'Ensure that the Address is 234 Hereford Street'
                );
            }
        );
    }

    public function testEditProfile()
    {
        $member = $this->objFromFixture(Member::class, 'joebloggs');
        $this->logInAs($member);

        $page = $this->get('account/editprofile/'); // goto address book page
        $this->assertEquals(200, $page->getStatusCode(), 'a page should load');

        $this->submitForm(
            'ShopAccountForm_EditAccountForm',
            'action_submit',
            [
                'FirstName' => 'UpdatedName',
            ]
        );
        $member = Security::getCurrentUser();
        $this->assertEquals('UpdatedName', $member->FirstName, 'First name should be updated');

        $page = $this->submitForm(
            'ChangePasswordForm_ChangePasswordForm',
            'action_doChangePassword',
            [
                'OldPassword' => '23u90oijlJKsa',
                'NewPassword1' => 'newpassword123',
                'NewPassword2' => 'newpassword123'
            ]
        );
        $this->assertEquals(200, $page->getStatusCode(), 'a page should load');

        $authenticator = new MemberAuthenticator;
        $validation_result = $authenticator->checkPassword($member, 'newpassword123');
        $this->assertTrue(
            $validation_result->isValid(),
            'Password should have changed'
        );
    }
}
