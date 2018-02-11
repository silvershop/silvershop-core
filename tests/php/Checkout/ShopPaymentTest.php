<?php

namespace SilverShop\Tests\Checkout;

use Guzzle\Http\Client;
use Guzzle\Plugin\Mock\MockPlugin;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\OrderProcessor;
use SilverShop\Model\Order;
use SilverShop\Page\CartPage;
use SilverShop\Page\CheckoutPage;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Dev\TestSession;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\Omnipay\Service\PaymentService;
use SilverStripe\Omnipay\Tests\Service\TestGatewayFactory;
use Symfony\Component\HttpFoundation\Request;

class ShopPaymentTest extends FunctionalTest
{
    protected static $fixture_file = array(
        __DIR__ . '/../Fixtures/Pages.yml',
        __DIR__ . '/../Fixtures/shop.yml',
    );
    public static $disable_theme = true;
    protected $autoFollowRedirection = false;

    public function setUp()
    {
        parent::setUp();

        /*
        DataObject::reset();
        ClassLoader::inst()->init(false, true);
        Debug::dump(ClassInfo::subclassesFor(OrderItem::class));
        */
        ShoppingCart::singleton()->clear();
        ShopTest::setConfiguration();

        //set supported gateways
        Config::modify()->set(
            Payment::class,
            'allowed_gateways',
            [
                'Dummy', //onsite
                'Manual', //manual
                'PaymentExpress_PxPay', //offsite
                'PaymentExpress_PxPost' //onsite
            ]
        )->set(
            Injector::class,
            'Omnipay\Common\GatewayFactory',
            [
                'class' => TestGatewayFactory::class
            ]
        );

        TestGatewayFactory::$httpClient = $this->getHttpClient();
        TestGatewayFactory::$httpRequest = $this->getHttpRequest();

        //publish products
        $this->logInWithPermission('ADMIN');
        $this->objFromFixture(Product::class, "socks")->publishSingle();
        $this->objFromFixture(CheckoutPage::class, "checkout")->publishSingle();
        $this->objFromFixture(CartPage::class, "cart")->publishSingle();
    }

    public function testManualPayment()
    {
        $this->markTestIncomplete("Process a manual payment");
    }

    public function testOnsitePayment()
    {
        $this->markTestIncomplete("Process an onsite payment");
    }

    public function testOffsitePayment()
    {
        $this->markTestIncomplete("Process an off-site payment");
    }

    public function testOffsitePaymentWithGatewayCallback()
    {
        //set up cart
        $cart = ShoppingCart::singleton()
            ->setCurrent($this->objFromFixture(Order::class, "cart"))
            ->current();
        //collect checkout details
        $cart->update(
            array(
                'FirstName' => 'Foo',
                'Surname' => 'Bar',
                'Email' => 'foo@example.com',
            )
        );
        $cart->write();
        //pay for order with external gateway
        $processor = OrderProcessor::create($cart);
        $this->setMockHttpResponse('paymentexpress/tests/Mock/PxPayPurchaseSuccess.txt');
        $response = $processor->makePayment("PaymentExpress_PxPay", array());
        //gateway responds (in a different session)
        $oldsession = $this->mainSession;
        $this->mainSession = new TestSession();
        ShoppingCart::singleton()->clear();
        $this->setMockHttpResponse('paymentexpress/tests/Mock/PxPayCompletePurchaseSuccess.txt');
        $this->getHttpRequest()->query->replace(array('result' => 'abc123'));
        $identifier = $response->getPayment()->Identifier;

        //bring back client session
        $this->mainSession = $oldsession;
        // complete the order
        $response = $this->get("paymentendpoint/$identifier/complete", $oldsession->session());

        //reload cart as new order
        $order = Order::get()->byId($cart->ID);
        $this->assertFalse($order->isCart(), "order is no longer in cart");
        $this->assertTrue($order->isPaid(), "order is paid");
        $this->assertNull($this->mainSession->session()->get("shoppingcartid"), "cart session id should be removed");
        $this->assertNotEquals(404, $response->getStatusCode(), "We shouldn't get page not found");

        $this->markTestIncomplete("Should assert other things");
    }

    protected $payment;
    protected $httpClient;
    protected $httpRequest;

    protected function getHttpClient()
    {
        if (null === $this->httpClient) {
            $this->httpClient = new Client();
        }

        return $this->httpClient;
    }

    protected function getHttpRequest()
    {
        if (null === $this->httpRequest) {
            $this->httpRequest = new Request();
        }

        return $this->httpRequest;
    }

    protected function setMockHttpResponse($paths)
    {
        $testspath = BASE_PATH . '/vendor/omnipay';
        $mock = new MockPlugin(null, true);
        $this->getHttpClient()->getEventDispatcher()->removeSubscriber($mock);
        foreach ((array)$paths as $path) {
            $mock->addResponse($testspath . '/' . $path);
        }
        $this->getHttpClient()->getEventDispatcher()->addSubscriber($mock);

        return $mock;
    }
}
