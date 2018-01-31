<?php

namespace SilverShop\Tests\Extension;


use SilverShop\Cart\ShoppingCart;
use SilverShop\Extension\MemberExtension;
use SilverShop\Model\Order;
use SilverStripe\Control\Email\Email;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;


/**
 * Test member functionality added via ShopMember extension
 */
class MemberExtensionTest extends FunctionalTest
{
    public static $fixture_file = array(
        '../Fixtures/ShopMembers.yml',
        '../Fixtures/shop.yml',
    );

    public function setUp()
    {
        parent::setUp();
        // clear session
        ShoppingCart::singleton()->clear();
    }

    public function testGetByIdentifier()
    {
        Member::config()->unique_identifier_field = Email::class;
        $member = MemberExtension::get_by_identifier('jeremy@example.com');
        $this->assertNotNull($member);
        $this->assertEquals('jeremy@example.com', $member->Email);
        $this->assertEquals('Jeremy', $member->FirstName);
    }

    public function testCMSFields()
    {
        singleton(Member::class)->getCMSFields();
        singleton(Member::class)->getMemberFormFields();
    }

    public function testPastOrders()
    {
        $member = $this->objFromFixture(Member::class, "joebloggs");
        $pastorders = $member->getPastOrders();
        $this->assertEquals(1, $pastorders->count());
    }

    public function testLoginJoinsCart()
    {
        Member::config()->login_joins_cart = true;
        $order = $this->objFromFixture(Order::class, "cart");
        ShoppingCart::singleton()->setCurrent($order);
        $member = $this->objFromFixture(Member::class, "jeremyperemy");
        Security::setCurrentUser($member);
        $this->assertEquals($member->ID, $order->MemberID);

        $member->logOut();

        $this->assertFalse(ShoppingCart::curr());
    }

    public function testLoginDoesntJoinCart()
    {
        Member::config()->login_joins_cart = false;
        $order = $this->objFromFixture(Order::class, "cart");
        ShoppingCart::singleton()->setCurrent($order);
        $member = $this->objFromFixture(Member::class, "jeremyperemy");
        Security::setCurrentUser($member);
        $this->assertEquals(0, $order->MemberID);

        $member->logOut();

        $this->assertTrue((bool)ShoppingCart::curr());
    }
}
