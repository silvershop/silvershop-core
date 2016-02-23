<?php

/**
 * @package    shop
 * @subpackage tests
 */
class OrderModifierTest extends FunctionalTest
{
    public static $fixture_file   = 'silvershop/tests/fixtures/shop.yml';
    public static $disable_theme  = true;
    public static $use_draft_site = true;

    public function setUp()
    {
        parent::setUp();
        ShopTest::setConfiguration();
        Order::config()->modifiers = array(
            "FlatTaxModifier",
        );
        FlatTaxModifier::config()->rate = 0.25;
        FlatTaxModifier::config()->name = "GST";

        $this->mp3player = $this->objFromFixture('Product', 'mp3player');
        $this->socks = $this->objFromFixture('Product', 'socks');
        $this->mp3player->publish('Stage', 'Live');
        $this->socks->publish('Stage', 'Live');
    }

    public function testModifierCalculation()
    {
        $order = $this->createOrder();
        $this->assertEquals(510, $order->calculate(), "Total with 25% tax");

        //remove modifiers
        Order::config()->modifiers = null;
        $order->calculate();
        $this->assertEquals(408, $order->calculate(), "Total with no modification");
    }

    public function createOrder()
    {
        $order = new Order();
        $order->write();
        $item1a = $this->mp3player->createItem(2);
        $item1a->write();
        $order->Items()->add($item1a);
        $item1b = $this->socks->createItem();
        $item1b->write();
        $order->Items()->add($item1b);
        return $order;
    }
}
