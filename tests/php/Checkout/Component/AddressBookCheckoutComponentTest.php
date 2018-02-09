<?php

namespace SilverShop\Tests\Checkout\Component;

use SilverShop\Checkout\CheckoutComponentConfig;
use SilverShop\Checkout\CheckoutConfig;
use SilverShop\Checkout\Component\AddressBookBilling;
use SilverShop\Model\Address;
use SilverShop\Model\Order;
use SilverShop\Tests\ShopTest;
use SilverStripe\Security\Member;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Security;

class AddressBookCheckoutComponentTest extends SapphireTest
{
    // Component namespace
    const COMPONENT_NS = 'SilverShop-Checkout-Component-AddressBookBilling';

    protected static $fixture_file = array(
        '../../Fixtures/Orders.yml',
        '../../Fixtures/ShopMembers.yml',
    );

    /**
     * @var Order $cart
     */
    protected $cart;

    /**
     * @var Member $member
     */
    protected $member;

    /**
     * @var Address $address1
     */
    protected $address1;

    /**
     * @var Address $address2
     */
    protected $address2;

    /**
     * @var CheckoutComponentConfig $config
     */
    protected $config;

    protected $fixtureNewAddress = array(
        self::COMPONENT_NS . '_BillingAddressID' => 'newaddress',
        self::COMPONENT_NS . '_Country'          => 'US',
        self::COMPONENT_NS . '_Address'          => '123 Test St',
        self::COMPONENT_NS . '_AddressLine2'     => 'Apt 4',
        self::COMPONENT_NS . '_City'             => 'Siloam Springs',
        self::COMPONENT_NS . '_State'            => 'AR',
        self::COMPONENT_NS . '_PostalCode'       => '72761',
        self::COMPONENT_NS . '_Phone'            => '11231231234',
    );

    public function setUp()
    {
        ShopTest::setConfiguration();
        CheckoutConfig::config()->membership_required = false;
        parent::setUp();

        $this->member = $this->objFromFixture(Member::class, "jeremyperemy");
        $this->cart = $this->objFromFixture(Order::class, "cart1");
        $this->address1 = $this->objFromFixture(Address::class, "address1");
        $this->address2 = $this->objFromFixture(Address::class, "address2");
        $this->config = new CheckoutComponentConfig($this->cart, true);

        $this->config->addComponent(new AddressBookBilling());

        $this->address1->MemberID = $this->member->ID;
        $this->address1->write();
    }

    public function testCreateNewAddress()
    {
        $this->assertTrue(
            $this->config->validateData($this->fixtureNewAddress)
        );
    }

    public function testIncompleteNewAddress()
    {
        $this->expectException(ValidationException::class);
        $data = $this->fixtureNewAddress;
        $data[self::COMPONENT_NS . '_Country'] = '';

        $this->config->validateData($data);
    }

    public function testUseExistingAddress()
    {
        Security::setCurrentUser($this->member);
        $this->assertTrue(
            $this->config->validateData(
                array(
                    self::COMPONENT_NS . '_BillingAddressID' => $this->address1->ID,
                )
            )
        );
    }

    public function testShouldRejectExistingIfNotLoggedIn()
    {
        $this->expectException(ValidationException::class);
        $this->assertTrue(
            $this->config->validateData(
                array(
                    self::COMPONENT_NS . '_BillingAddressID' => $this->address1->ID,
                )
            )
        );
    }

    public function testShouldRejectExistingIfNotOwnedByMember()
    {
        $this->expectException(ValidationException::class);
        Security::setCurrentUser($this->member);
        $this->address1->MemberID = 0;
        $this->address1->write();

        $this->assertTrue(
            $this->config->validateData(
                array(
                    self::COMPONENT_NS . '_BillingAddressID' => $this->address1->ID,
                )
            )
        );
    }

    public function testShouldNotCreateBlankAddresses()
    {
        $beforeCount = Address::get()->count();
        $this->config->setData(
            array(
                self::COMPONENT_NS . '_BillingAddressID' => $this->address1->ID,
            )
        );

        $this->assertEquals($this->cart->BillingAddressID, $this->address1->ID);
        $this->assertEquals($beforeCount, Address::get()->count());
    }
}
