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

    protected $extraDataObjects = array('OrderModifierTest_TestModifier');

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

    public function testModifierFailure()
    {
        if (!ShopTools::DBConn()->supportsTransactions()) {
            $this->markTestSkipped(
                'The Database doesn\'t support transactions.'
            );
        }

        Config::inst()->update('Order', 'modifiers', array(
            'OrderModifierTest_TestModifier'
        ));
        $order = $this->createOrder();
        $order->calculate();
        $order->write();

        // 408 from items + 10 from modifier + 25% from tax
        $this->assertEquals('522.5', $order->Total);

        $amounts = array();
        foreach ($order->Modifiers()->sort('Sort') as $modifier) {
            $amounts[] = (string)$modifier->Amount;
        }

        $this->assertEquals(array('10', '104.5'), $amounts);

        OrderModifierTest_TestModifier::$value = 42;

        try {
            // Calculate will now fail!
            $order->calculate();
        } catch (Exception $e){}

        // reload order from DB
        $order = Order::get()->byID($order->ID);

        // Order Total should not have changed
        $this->assertEquals('522.5', $order->Total);

        $amounts = array();
        foreach ($order->Modifiers()->sort('Sort') as $modifier) {
            $amounts[] = (string)$modifier->Amount;
        }

        $this->assertEquals(
            array('10', '104.5'),
            $amounts,
            'Modifiers aren\'t allowed to change upon failure'
        );
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

class OrderModifierTest_TestModifier extends OrderModifier implements TestOnly
{
    public static $value = 10;
    private $willFail = false;

    public function value($incoming)
    {
        if (self::$value === 42) {
            $this->willFail = true;
        }
        return self::$value;
    }

    protected function onAfterWrite()
    {
        parent::onAfterWrite();
        if ($this->willFail) {
            user_error('Modifier failure!');
        }
    }
}
