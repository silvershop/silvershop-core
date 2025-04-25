<?php

namespace SilverShop\Tests\Extension;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Extension\MemberExtension;
use SilverShop\Model\Order;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;

/**
 * Test member functionality added via ShopMember extension
 */
class MemberExtensionTest extends SapphireTest
{
    public static $fixture_file = [
        __DIR__ . '/../Fixtures/ShopMembers.yml',
        __DIR__ . '/../Fixtures/shop.yml',
    ];

    public function testGetByIdentifier(): void
    {
        Config::modify()->set(Member::class, 'unique_identifier_field', 'Email');
        $member = MemberExtension::get_by_identifier('jeremy@example.com');
        $this->assertNotNull($member);
        $this->assertEquals('jeremy@example.com', $member->Email);
        $this->assertEquals('Jeremy', $member->FirstName);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCMSFields(): void
    {
        singleton(Member::class)->getCMSFields();
        singleton(Member::class)->getMemberFormFields();
    }

    public function testPastOrders(): void
    {
        $member = $this->objFromFixture(Member::class, "joebloggs");
        $pastorders = $member->getPastOrders();
        $this->assertEquals(1, $pastorders->count());
    }

    public function testLoginJoinsCart(): void
    {
        Config::modify()->set(Member::class, 'login_joins_cart', true);
        $order = $this->objFromFixture(Order::class, "cart");
        ShoppingCart::singleton()->setCurrent($order);
        $member = $this->objFromFixture(Member::class, "jeremyperemy");
        $this->logInAs($member);
        $this->assertEquals($member->ID, $order->MemberID);
        $this->logOut();

        $this->assertNull(ShoppingCart::curr());
    }

    public function testLoginDoesntJoinCart(): void
    {
        Config::modify()->set(Member::class, 'login_joins_cart', false);
        $order = $this->objFromFixture(Order::class, "cart");
        ShoppingCart::singleton()->setCurrent($order);
        $member = $this->objFromFixture(Member::class, "jeremyperemy");
        $this->logInAs($member);
        $this->assertEquals(0, $order->MemberID);
        $this->logOut();

        $this->assertTrue((bool)ShoppingCart::curr());
    }
}
