<?php

class AddressBookCheckoutComponentTest extends SapphireTest {

	protected static $fixture_file = array(
		'shop/tests/fixtures/Orders.yml',
		'shop/tests/fixtures/ShopMembers.yml',
	);

	/** @var Order $cart */
	protected $cart;

	/** @var Member $member */
	protected $member;

	/** @var Address $address1 */
	protected $address1;

	/** @var Address $address2 */
	protected $address2;

	/** @var CheckoutComponentConfig $config */
	protected $config;

	protected $fixtureNewAddress = array(
		'BillingAddressBookCheckoutComponent_BillingAddressID' => 'newaddress',
		'BillingAddressBookCheckoutComponent_Country' => 'US',
		'BillingAddressBookCheckoutComponent_Address' => '123 Test St',
		'BillingAddressBookCheckoutComponent_AddressLine2' => 'Apt 4',
		'BillingAddressBookCheckoutComponent_City' => 'Siloam Springs',
		'BillingAddressBookCheckoutComponent_State' => 'AR',
		'BillingAddressBookCheckoutComponent_PostalCode' => '72761',
		'BillingAddressBookCheckoutComponent_Phone' => '11231231234',
	);

	public function setUp() {
		ShopTest::setConfiguration();
		CheckoutConfig::config()->membership_required = false;
		parent::setUp();

		$this->member   = $this->objFromFixture("Member", "jeremyperemy");
		$this->cart     = $this->objFromFixture("Order", "cart1");
		$this->address1 = $this->objFromFixture("Address", "address1");
		$this->address2 = $this->objFromFixture("Address", "address2");
		$this->config   = new CheckoutComponentConfig($this->cart, true);

		$this->config->addComponent( new BillingAddressBookCheckoutComponent() );

		$this->address1->MemberID = $this->member->ID;
		$this->address1->write();
	}

	public function testCreateNewAddress() {
		$this->assertTrue(
			$this->config->validateData($this->fixtureNewAddress)
		);
	}

	public function testIncompleteNewAddress() {
		$this->setExpectedException('ValidationException');
		$data = $this->fixtureNewAddress;
		$data['BillingAddressBookCheckoutComponent_Country'] = '';

		$this->config->validateData($data);
	}

	public function testUseExistingAddress() {
		$this->member->logIn();
		$this->assertTrue(
			$this->config->validateData(array(
				'BillingAddressBookCheckoutComponent_BillingAddressID' => $this->address1->ID,
			))
		);
	}

	public function testShouldRejectExistingIfNotLoggedIn() {
		$this->setExpectedException('ValidationException');
		$this->assertTrue(
			$this->config->validateData(array(
				'BillingAddressBookCheckoutComponent_BillingAddressID' => $this->address1->ID,
			))
		);
	}

	public function testShouldRejectExistingIfNotOwnedByMember() {
		$this->setExpectedException('ValidationException');
		$this->member->logIn();
		$this->address1->MemberID = 0;
		$this->address1->write();

		$this->assertTrue(
			$this->config->validateData(array(
				'BillingAddressBookCheckoutComponent_BillingAddressID' => $this->address1->ID,
			))
		);
	}

	public function testShouldNotCreateBlankAddresses() {
		$beforeCount = Address::get()->count();
		$this->config->setData(array(
			'BillingAddressBookCheckoutComponent_BillingAddressID' => $this->address1->ID
		));

		$this->assertEquals($this->cart->BillingAddressID , $this->address1->ID);
		$this->assertEquals($beforeCount, Address::get()->count());
	}

}