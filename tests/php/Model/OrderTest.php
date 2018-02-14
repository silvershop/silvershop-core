<?php

namespace SilverShop\Tests\Model;

use SilverShop\Checkout\OrderProcessor;
use SilverShop\Model\Address;
use SilverShop\Model\Modifiers\OrderModifier;
use SilverShop\Model\Modifiers\Tax\FlatTax;
use SilverShop\Model\Order;
use SilverShop\Model\OrderStatusLog;
use SilverShop\Model\Product\OrderItem;
use SilverShop\Page\Product;
use SilverShop\Tests\Model\Product\CustomProduct_OrderItem;
use SilverShop\Tests\ShopTest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\ORM\DataObject;

/**
 * Order Unit Tests
 *
 * @package    silvershop
 * @subpackage tests
 */
class OrderTest extends SapphireTest
{
    public static $fixture_file = __DIR__ . '/../Fixtures/shop.yml';

    // This seems to be required, because we query the OrderItem table and thus this gets included…
    // TODO: Remove once we figure out how to circumvent that…
    protected static $extra_dataobjects = [
        CustomProduct_OrderItem::class,
    ];

    /**
     * @var Product
     */
    protected $mp3player;

    /**
     * @var Product
     */
    protected $socks;

    /**
     * @var Product
     */
    protected $beachball;


    public function setUp()
    {
        parent::setUp();
        ShopTest::setConfiguration();
        $this->logInWithPermission('ADMIN');
        $this->mp3player = $this->objFromFixture(Product::class, 'mp3player');
        $this->mp3player->publishSingle();
        $this->socks = $this->objFromFixture(Product::class, 'socks');
        $this->socks->publishSingle();
        $this->beachball = $this->objFromFixture(Product::class, 'beachball');
        $this->beachball->publishSingle();
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->mp3player);
        unset($this->socks);
        unset($this->beachball);
    }

    public function testCMSFields()
    {
        //singleton(Order::class)->getCMSFields();
        $this->markTestIncomplete('assertions!');
    }

    public function testSearchFields()
    {
        //singleton(Order::class)->scaffoldSearchFields();
        $this->markTestIncomplete('assertions!');
    }

    public function testDebug()
    {
        //$order = $this->objFromFixture(Order::class, "cart");
        //$order->debug();
        $this->markTestIncomplete('assertions!');
    }

    public function testOrderItems()
    {
        $order = $this->objFromFixture(Order::class, "paid");
        $items = $order->Items();
        $this->assertNotNull($items);
        $this->assertListEquals(
            array(
                array('ProductID' => $this->mp3player->ID, 'Quantity' => 2, 'CalculatedTotal' => 400),
                array('ProductID' => $this->socks->ID, 'Quantity' => 1, 'CalculatedTotal' => 8),
            ),
            $items
        );
        $this->assertEquals(3, $items->Quantity(), "Quantity is 3");
        $this->assertTrue($items->Plural(), "There is more than one item");
        $this->assertEquals(0.7, $items->Sum('Weight', true), "Total order weight sums correctly", 0.0001);
    }

    public function testTotals()
    {
        $order = $this->objFromFixture(Order::class, "paid");
        $this->assertEquals(408, $order->SubTotal(), "Subtotal is correct"); // 200 + 200 + 8
        $this->assertEquals(408, $order->GrandTotal(), "Grand total is correct");
        $this->assertEquals(200, $order->TotalPaid(), "Outstanding total is correct");
        $this->assertEquals(208, $order->TotalOutstanding(), "Outstanding total is correct");
    }

    public function testRounding()
    {
        //create an order with unrounded total
        $order = new Order(
            array(
                'Total' => 123.257323,
                //NOTE: setTotal isn't called here, so un-rounded data *could* get in to the object
                'Status' => 'Unpaid',
            )
        );
        $order->Total = 123.257323; //setTotal IS called here
        $this->assertEquals(123.26, $order->Total(), "Check total rounds appropriately");
        $this->assertEquals(123.26, $order->TotalOutstanding(), "Check total outstanding rounds appropriately");
    }

    public function testPlacedOrderImmutability()
    {

        $order = $this->objFromFixture(Order::class, "paid");
        $processor = OrderProcessor::create($order)->placeOrder();
        $this->assertEquals(408, $order->Total(), "check totals");

        //make a changes to existing products
        $this->mp3player->BasePrice = 100;
        $this->mp3player->write();
        $this->socks->BasePrice = 20;
        $this->socks->write();

        //total doesn't change
        $this->assertEquals(408, $order->Total());
        $this->assertFalse($order->isCart());

        //item values don't change
        $items = $order->Items()
            //hack join to make thigns work
            ->innerJoin(
                "SilverShop_Product_OrderItem",
                '"SilverShop_OrderItem"."ID" = "SilverShop_Product_OrderItem"."ID"'
            );
        $this->assertNotNull($items);
        $this->assertListEquals(
            array(
                array('ProductID' => $this->mp3player->ID, 'Quantity' => 2, 'CalculatedTotal' => 400),
                array('ProductID' => $this->socks->ID, 'Quantity' => 1, 'CalculatedTotal' => 8),
            ),
            $items
        );

        $mp3player = $items->find('ProductID', $this->mp3player->ID);//join needed to provide ProductID
        $this->assertNotNull($mp3player, "MP3 player is in order");
        $this->assertEquals(200, $mp3player->UnitPrice(), "Unit price remains the same");
        $this->assertEquals(400, $mp3player->Total(), "Total remains the same");

        $socks = $items->find('ProductID', $this->socks->ID);
        $this->assertNotNull($socks, "Socks are in order");
        $this->assertEquals(8, $socks->UnitPrice(), "Unit price remains the same");
        $this->assertEquals(8, $socks->Total(), "Total remains the same");
    }

    public function testCanFunctions()
    {
        $order = $this->objFromFixture(Order::class, "cart");
        $order->calculate();
        $this->assertTrue($order->canPay(), "can pay when order is in cart");
        $this->assertFalse($order->canCancel(), "can't cancel when order is in cart");
        $this->assertFalse($order->canDelete(), "never allow deleting orders");
        $this->assertTrue($order->canEdit(), "orders can be edited by anyone");
        $this->assertFalse($order->canCreate(), "no body can create orders manually");

        $order = $this->objFromFixture(Order::class, "unpaid");
        $this->assertTrue($order->canPay(), "can pay an order that is unpaid");
        $this->assertTrue($order->canCancel());
        $this->assertFalse($order->canDelete(), "never allow deleting orders");

        // Override config
        Config::modify()->set(Order::class, 'cancel_before_payment', false);
        $this->assertFalse($order->canCancel());

        $order = $this->objFromFixture(Order::class, "paid");
        $this->assertFalse($order->canPay(), "paid order can't be paid for");
        $this->assertFalse($order->canCancel(), "paid order can't be cancelled");
        $this->assertFalse($order->canDelete(), "never allow deleting orders");

        Config::modify()->set(Order::class, 'cancel_before_processing', true);
        $this->assertTrue($order->canCancel(), "paid order can be cancelled when expcicitly set via config");

        $order->Status = 'Processing';
        $this->assertFalse($order->canPay(), "Processing order can't be paid for");
        $this->assertFalse($order->canCancel(), "Processing order can't be cancelled");
        $this->assertFalse($order->canDelete(), "never allow deleting orders");

        Config::modify()->set(Order::class, 'cancel_before_sending', true);
        $this->assertTrue($order->canCancel(), "Processing order can be cancelled when expcicitly set via config");

        $order->Status = 'Sent';
        $this->assertFalse($order->canPay(), "Sent order can't be paid for");
        $this->assertFalse($order->canCancel(), "Sent order can't be cancelled");
        $this->assertFalse($order->canDelete(), "never allow deleting orders");

        Config::modify()->set(Order::class, 'cancel_after_sending', true);
        $this->assertTrue($order->canCancel(), "Sent order can be cancelled when expcicitly set via config");
        Config::modify()->set(Order::class, 'cancel_after_sending', false);

        $order->Status = 'Complete';
        $this->assertFalse($order->canPay(), "Complete order can't be paid for");
        $this->assertFalse($order->canCancel(), "Complete order can't be cancelled");
        $this->assertFalse($order->canDelete(), "never allow deleting orders");

        Config::modify()->set(Order::class, 'cancel_after_sending', true);
        $this->assertTrue($order->canCancel(), "Completed order can be cancelled when expcicitly set via config");

        $order->Status = 'AdminCancelled';
        $this->assertFalse($order->canPay(), "Cancelled order can't be paid for");
        $this->assertFalse($order->canCancel(), "Cancelled order can't be cancelled");
        $this->assertFalse($order->canDelete(), "never allow deleting orders");

        $order->Status = 'MemberCancelled';
        $this->assertFalse($order->canPay(), "Cancelled order can't be paid for");
        $this->assertFalse($order->canCancel(), "Cancelled order can't be cancelled");
        $this->assertFalse($order->canDelete(), "never allow deleting orders");
    }

    public function testDelete()
    {
        Config::modify()
            ->set(FlatTax::class, 'rate', 0.25)
            ->merge(Order::class, 'modifiers', [FlatTax::class]);

        $order = Order::create();
        $shirt = $this->objFromFixture(Product::class, "tshirt");
        $mp3player = $this->objFromFixture(Product::class, "mp3player");
        $order->Items()->add($shirt->createItem(3));
        $order->Items()->add($mp3player->createItem(1));
        $order->write();
        $order->calculate();

        $statusLogId = OrderStatusLog::create()->update(
            [
                'Title' => 'Test status log',
                'OrderID' => $order->ID
            ]
        )->write();

        $paymentId = Payment::create()->update(
            [
                'OrderID' => $order->ID
            ]
        )->init('Manual', 343.75, 'NZD')->write();


        $this->assertEquals(4, $order->Items()->Quantity());
        $this->assertEquals(1, $order->Modifiers()->count());
        $this->assertEquals(1, $order->OrderStatusLogs()->count());
        $this->assertEquals(1, $order->Payments()->count());

        $itemIds = OrderItem::get()->filter('OrderID', $order->ID)->column('ID');
        $modifierIds = OrderModifier::get()->filter('OrderID', $order->ID)->column('ID');

        $order->delete();

        // Items should no longer be linked to order
        $this->assertEquals(0, $order->Items()->count());
        $this->assertEquals(0, $order->Modifiers()->count());
        $this->assertEquals(0, $order->OrderStatusLogs()->count());
        $this->assertEquals(0, $order->Payments()->count());

        // Ensure the order items have been deleted!
        $this->assertEquals(0, OrderItem::get()->filter('ID', $itemIds)->count());
        $this->assertEquals(0, OrderModifier::get()->filter('ID', $modifierIds)->count());
        $this->assertEquals(0, OrderStatusLog::get()->filter('ID', $statusLogId)->count());

        // Keep the payment… it might be relevant for book keeping
        $this->assertEquals(1, Payment::get()->filter('ID', $paymentId)->count());
    }

    public function testStatusChange()
    {
        Config::modify()->merge(Order::class, 'extensions', [OrderTest_TestStatusChangeExtension::class]);

        $order = Order::create();
        $orderId = $order->write();

        $order->Status = 'Unpaid';
        $order->write();

        $this->assertEquals(
            array(
                array('Cart' => 'Unpaid')
            ),
            OrderTest_TestStatusChangeExtension::$stack
        );

        OrderTest_TestStatusChangeExtension::reset();

        $order = Order::get()->byID($orderId);
        $order->Status = 'Paid';
        $order->write();

        $this->assertEquals(
            array(
                array('Unpaid' => 'Paid')
            ),
            OrderTest_TestStatusChangeExtension::$stack
        );

        $this->assertTrue((boolean)$order->Paid, 'Order paid date should be set');
    }

    public function testOrderAddress()
    {
        $order = $this->objFromFixture(Order::class, 'paid');

        // assert that order doesn't contain user information
        $this->assertNull($order->FirstName);
        $this->assertNull($order->Surname);
        $this->assertNull($order->Email);

        // The shipping address should use the members default shipping address
        $this->assertEquals(
            'Joe Bloggs, 12 Foo Street, Bar, Farmville, New Sandwich, US',
            $order->getShippingAddress()->toString()
        );

        $address = $this->objFromFixture(Address::class, 'pukekohe');
        $order->ShippingAddressID = $address->ID;
        $order->write();

        // Address doesn't have firstname and surname
        $this->assertNull(Address::get()->byID($order->ShippingAddressID)->FirstName);
        $this->assertNull(Address::get()->byID($order->ShippingAddressID)->Surname);

        // Shipping address should contain the name from the member object and new address information
        $this->assertEquals(
            'Joe Bloggs, 1 Queen Street, Pukekohe, Auckland, 2120',
            $order->getShippingAddress()->toString()
        );

        // changing fields on the Order will have precendence!
        $order->FirstName = 'Tester';
        $order->Surname = 'Mc. Testerson';
        $order->write();

        // Reset caches, otherwise the previously set name will persist (eg. Joe Bloggs)
        DataObject::reset();

        $this->assertEquals(
            'Tester Mc. Testerson, 1 Queen Street, Pukekohe, Auckland, 2120',
            $order->getShippingAddress()->toString()
        );
    }
}
