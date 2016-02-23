<?php

/**
 * Test member functionality added via ShopMember extension
 */
class ShopMemberTest extends FunctionalTest
{
    public static $fixture_file = array(
        'silvershop/tests/fixtures/ShopMembers.yml',
        'silvershop/tests/fixtures/shop.yml',
    );

    public function testGetByIdentifier()
    {
        Member::config()->unique_identifier_field = 'Email';
        $member = ShopMember::get_by_identifier('jeremy@peremy.com');
        $this->assertNotNull($member);
        $this->assertEquals('jeremy@peremy.com', $member->Email);
        $this->assertEquals('Jeremy', $member->FirstName);
    }

    public function testCMSFields()
    {
        singleton("Member")->getCMSFields();
        singleton("Member")->getMemberFormFields();
    }

    public function testPastOrders()
    {
        $member = $this->objFromFixture("Member", "joebloggs");
        $pastorders = $member->getPastOrders();
        $this->assertEquals(1, $pastorders->count());
    }

    public function testLoginJoinsCart()
    {
        Member::config()->login_joins_cart = true;
        $order = $this->objFromFixture("Order", "cart");
        ShoppingCart::singleton()->setCurrent($order);
        $member = $this->objFromFixture("Member", "jeremyperemy");
        $member->logIn();
        $this->assertEquals($member->ID, $order->MemberID);

        $member->logOut();

        $this->assertFalse(ShoppingCart::curr());
    }

    public function testLoginDoesntJoinCart()
    {
        Member::config()->login_joins_cart = false;
        $order = $this->objFromFixture("Order", "cart");
        ShoppingCart::singleton()->setCurrent($order);
        $member = $this->objFromFixture("Member", "jeremyperemy");
        $member->logIn();
        $this->assertEquals(0, $order->MemberID);

        $member->logOut();

        $this->assertTrue((bool)ShoppingCart::curr());
    }
}
