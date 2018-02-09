<?php

namespace SilverShop\Tests\Model\Modifiers;

use Exception;
use SilverShop\Model\Modifiers\Tax\FlatTax;
use SilverShop\Model\Order;
use SilverShop\Model\OrderModifier;
use SilverShop\Page\Product;
use SilverShop\Tests\Model\Product\CustomProduct_OrderItem;
use SilverShop\Tests\ShopTest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\ORM\DB;

/**
 * @package    shop
 * @subpackage tests
 */
class OrderModifierTest extends FunctionalTest
{
    public static $fixture_file = __DIR__ . '/../../Fixtures/shop.yml';
    public static $disable_theme = true;
    protected static $use_draft_site = true;

    /**
     * @var Product
     */
    protected $mp3player;

    /**
     * @var Product
     */
    protected $socks;

    protected static $extra_dataobjects = [
        CustomProduct_OrderItem::class,
        OrderModifierTest_TestModifier::class
    ];

    public function setUp()
    {
        parent::setUp();
        ShopTest::setConfiguration();

        Config::modify()
            ->set(
                Order::class,
                'modifiers',
                [
                    FlatTax::class
                ]
            )
            ->set(FlatTax::class, 'rate', 0.25)
            ->set(FlatTax::class, 'name', 'GST');


        $this->logInWithPermission('ADMIN');
        $this->mp3player = $this->objFromFixture(Product::class, 'mp3player');
        $this->socks = $this->objFromFixture(Product::class, 'socks');
        $this->mp3player->publishSingle();
        $this->socks->publishSingle();
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
        if (!DB::get_conn()->supportsTransactions()) {
            $this->markTestSkipped(
                'The Database doesn\'t support transactions.'
            );
        }

        Config::modify()->set(
            Order::class,
            'modifiers',
            [
                OrderModifierTest_TestModifier::class,
                FlatTax::class
            ]
        );

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
        } catch (Exception $e) {
        }

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
