<?php

namespace SilverShop\Tests\Checkout;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\Checkout;
use SilverShop\Checkout\CheckoutConfig;
use SilverShop\Checkout\ShopMemberFactory;
use SilverShop\Model\Address;
use SilverShop\Model\Order;
use SilverShop\Tests\ShopTest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Member;

class CheckoutTest extends SapphireTest
{
    protected static $fixture_file = [
        __DIR__ . '/../Fixtures/Pages.yml',
        __DIR__ . '/../Fixtures/Orders.yml',
        __DIR__ . '/../Fixtures/Addresses.yml',
        __DIR__ . '/../Fixtures/ShopMembers.yml',
    ];

    protected ShoppingCart|Order $cart;
    protected Address $address1;
    protected Address $address2;
    protected Checkout $checkout;
    protected ShopMemberFactory $memberFactory;

    public function setUp(): void
    {
        parent::setUp();
        ShopTest::setConfiguration();
        $this->cart = $this->objFromFixture(Order::class, "cart1");
        $this->address1 = $this->objFromFixture(Address::class, "address1");
        $this->address2 = $this->objFromFixture(Address::class, "address2");
        $this->checkout = Checkout::create($this->cart);
        $this->memberFactory = new ShopMemberFactory();

        Config::modify()
            ->set(CheckoutConfig::class, 'member_creation_enabled', true)
            ->set(CheckoutConfig::class, 'membership_required', false);
    }

    public function testSetUpShippingAddress(): void
    {
        $this->checkout->setShippingAddress($this->address1);
        $this->assertEquals(
            $this->address1->ID,
            $this->cart->ShippingAddressID,
            "shipping address was successfully added"
        );
    }

    public function testSetUpBillingAddress(): void
    {
        $this->checkout->setBillingAddress($this->address2);
        $this->assertEquals(
            $this->address2->ID,
            $this->cart->BillingAddressID,
            "billing address was successfully added"
        );
    }

    public function testSetPaymentMethod(): void
    {
        $this->assertTrue($this->checkout->setPaymentMethod("Dummy"), "Valid method set correctly");
        $this->assertEquals('Dummy', $this->checkout->getSelectedPaymentMethod(false));
    }

    /**
     * Tests the default membership configuration.
     * You can become a member, but it is not necessary
     */
    public function testCanBecomeMember(): void
    {
        //check can proceeed with/without order
        //check member exists
        $result = $this->memberFactory->create(
            [
                'FirstName' => 'Jane',
                'Surname'   => 'Smith',
                'Email'     => 'jane@example.com',
                'Password'  => 'janesmith2012',
            ]
        );
        $this->assertTrue(
            ($result instanceof Member),
            $this->checkout->getMessage() ?: '$result is an instanceof Member'
        );
    }

    public function testMustBecomeOrBeMember(): void
    {
        CheckoutConfig::config()->member_creation_enabled = true;
        CheckoutConfig::config()->membership_required = true;

        $member = $this->memberFactory->create(
            [
                'FirstName' => 'Susan',
                'Surname'   => 'Jackson',
                'Email'     => 'susan@example.com',
                'Password'  => 'jaleho3htgll',
            ]
        );

        $this->assertTrue($this->checkout->validateMember($member));
        //check can't proceed without being a member
        $this->assertFalse($this->checkout->validateMember(false));
    }

    public function testNoMemberships(): void
    {
        CheckoutConfig::config()->member_creation_enabled = false;
        CheckoutConfig::config()->membership_required = false;

        $this->expectException(ValidationException::class);

        $this->memberFactory->create(
            [
                'FirstName' => 'Susan',
                'Surname'   => 'Jackson',
                'Email'     => 'susan@example.com',
                'Password'  => 'jaleho3htgll',
            ]
        );
    }

    /**
     * @expectedException \SilverStripe\ORM\ValidationException
     * @expectedExceptionMessage Creating new memberships is not allowed
     */
    public function testMembersOnly(): void
    {
        CheckoutConfig::config()->member_creation_enabled = false;
        CheckoutConfig::config()->membership_required = true;

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Creating new memberships is not allowed');

        $this->memberFactory->create(
            [
                'FirstName' => 'Some',
                'Surname'   => 'Body',
                'Email'     => 'somebody@example.com',
                'Password'  => 'pass1234',
            ]
        );
    }

    /**
     * @expectedException \SilverStripe\ORM\ValidationException
     * @expectedExceptionMessage A password is required
     */
    public function testMemberWithoutPassword(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A password is required');
        $this->memberFactory->create(
            [
                'FirstName' => 'Jim',
                'Surname'   => 'Smith',
                'Email'     => 'jim@example.com',
            ]
        );
    }

    /**
     * @expectedException \SilverStripe\ORM\ValidationException
     * @expectedExceptionMessage A member already exists with the Email jeremy@example.com
     */
    public function testMemberAlreadyExists(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A member already exists with the Email jeremy@example.com');
        $this->memberFactory->create(
            [
                'FirstName' => 'Jeremy',
                'Surname'   => 'Peremy',
                'Email'     => 'jeremy@example.com',
                'Password'  => 'jeremyperemy',
            ]
        );
    }

    /**
     * @expectedException \SilverStripe\ORM\ValidationException
     * @expectedExceptionMessage Required field not found: Email
     */
    public function testMemberMissingIdentifier(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Required field not found: Email');
        $this->memberFactory->create(
            [
                'FirstName' => 'John',
                'Surname'   => 'Doe',
                'Password'  => 'johndoe1234',
            ]
        );
    }
}
